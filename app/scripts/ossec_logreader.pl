#!/usr/bin/perl
#
# OSSEC LogReader
#
# By: Justin C. Klein Keane <jukeane@sas.upenn.edu>
# Last Updated: May 8, 2012
#
# Designed to parse an OSSEC log text file for insert into a database
#
# Example OSSEC Log Entry:
# ------------------------
# ** Alert 1297702559.16083181: - apache,
# 2011 Feb 14 11:55:59 (oni.sas.upenn.edu) 128.91.55.19->/var/log/httpd/error_log
# Rule: 31410 (level 3) -> 'PHP Warning message.'
# Src IP: 128.91.34.6
# User: (none)
# [Mon Feb 14 11:56:00 2011] [error] [client 128.91.34.6] PHP Warning:  Call-time pass-by-reference has been deprecated - argument passed by value;  If you would like to pass it by reference, modify the declaration of task_send_extra_email().  If you would like to enable call-time pass-by-reference, you can set allow_call_time_pass_reference to true in your INI file.  However, future versions may not support this any longer.  in /www/data/drupal-6.19/sites/oni.sas.upenn.edu.taskmgr/modules/task/task.module on line 254, referer: https://oni.sas.upenn.edu/taskmgr/
# 
# ToDo: 
use DBI();

my $debug = 0;

my $workdir = substr($0, 0, rindex($0, 'ossec_logreader.pl'));
my $config_file = substr($0, 0, rindex($0, 'ossec_logreader.pl')) . '../conf/config.ini';
open(CONFINI, $config_file) or die "Couldn't open config file " . $config_file;
my $db, $dbhost, $dbuser, $dbpass = "";
while ($line = <CONFINI>) {
  if ($line =~ /^db\s*=/) {
    $db = trim(substr($line, index($line, "=")+1));
  }
  if ($line =~ /^db_host\s*=/) {
    $dbhost = trim(substr($line, index($line, "=")+1));
  }
  if ($line =~ /^db_user\s*=/) {
    $dbuser = trim(substr($line, index($line, "=")+1));
  }
  if ($line =~ /^db_pass\s*=/) {
    $dbpass = trim(substr($line, index($line, "=")+1));
  }
}
close CONFINI;

my $logentry,$alert_id,$dateline,$host,$client_ip,$loglocation,$rule,$level,$message,$src_ip,$user = "";
my $inserted_records,$skipped_records = 0;
my $orig;

my $dbh = DBI->connect("DBI:mysql:database=$db;host=$dbhost",$dbuser,$dbpass) || die("Error connecting to db.");

my $ossec_log = open_check_log();
open (OSSECLOG, "< $ossec_log") || die("Couldn't open log file.\n");

my $max_ossec_alert = db_select("select max(alert_ossec_id) as maxid from ossec_alert", "maxid");

process_logfile();

$dbh->disconnect();
close(OSSECLOG);
# Clean up
system("rm -f $ossec_log");

print "Done!\nInserted records:\t$inserted_records\nSkipped records:\t$skipped_records\n";

# Takes a specific input:
# db_select(sql_statment, id_column, arg1, arg2, etc)
# returns the id number or 0 if none are found
sub db_select {
  my $select_sql = shift(@_);
  my $id_col = shift(@_);
  my @sql_args = @_;
  my $id = 0;

  my $sth = $dbh->prepare($select_sql) || die("Couldn't prepare statement.");
  if (@sql_args eq undef) {
    die("SQL args are undefined!\n");
  }
  $sth->execute(@sql_args) || die("Couldn't execute statement for $select_sql.");
  while(my $ref = $sth->fetchrow_hashref()) {
    $id = $ref->{$id_col};
  }
  $sth->finish();
  return $id;
}

sub get_db_rule_id {
  my ($rule_no, $level, $message) = @_;
  my $select_sql = "select rule_id from ossec_rule where rule_number=? AND rule_level=? AND rule_message=?";
  my $rule_id = db_select($select_sql, "rule_id", $rule_no, $level, $message);

  # Insert the record if it can't be found
  if ($rule_id < 1) {
    $sth = $dbh->prepare("insert into ossec_rule (rule_number, rule_level, rule_message) values (?,?,?)") || die("Couldn't prep rule insert.");
    $sth->execute($rule_no, $level, $message) || die("Couldn't exec rule insert.");
    $sth->finish();
  }
  # Get the record we just found.
  $rule_id = db_select($select_sql, "rule_id", $rule_no, $level, $message);
  return $rule_id;
}

sub get_host_id {
  my $client_ip = shift(@_);
  if ($debug) { print "\n** Looking up host by host_ip $client_ip\n";}
  my $select_sql = "select host_id from host where host_ip = ?";
  my $host_id = db_select($select_sql, "host_id", $client_ip);

  # Insert the record if it can't be found
  if ($host_id < 1) {
    $sth = $dbh->prepare("insert into host(host_ip, host_name, host_note) values (?,?,?)") || die("Couldn't prep host insert.");
    $sth->execute($client_ip, $host, "OSSEC") || die("Couldn't exec host insert with IP:[$client_ip], Host:[$host].\n");
    $sth->finish();
  }
  # Get the record we just found.
  $host_id = db_select($select_sql, "host_id", $client_ip);
  return $host_id;
}

# Insert the actual log into the database
sub insert_ossec {
  # Check to see if the record exists
  my ($ossec_alert_id, $date, $host_id, $alert_log, $rule_id, $src_ip, $user, $message) = @_;
  my $sql_stmt = "insert into ossec_alert (alert_date, host_id, alert_log, rule_id, " . 
    "rule_src_ip, rule_src_ip_numeric, rule_user, rule_log, alert_ossec_id) values (?,?,?,?,?,inet_aton(rule_src_ip),?,?,?)";
  $sth = $dbh->prepare($sql_stmt) || die("Couldn't prep the ossec insert");
  $sth->execute(format_logdate($date), $host_id, $alert_log, $rule_id, $src_ip, $user, $message, $ossec_alert_id) || die("Couldn't exec ossec insert.");
  $sth->finish();
}

# Insert darknet record into another table
# This method depricated in favor of rsyslog->MySQL entry described at:
# http://www.madirish.net/content/creating-darknet-sensor-database
sub insert_darknet {
  my ($alert_id, $timestamp, $logline, $date) = @_;
  my $srcip = getsubstr($logline, "SRC=", " DST=");
  my $dstip = getsubstr($logline, "DST=", " LEN=");
  my $proto = getsubstr($logline, "PROTO=", " SPT=");
  my $srcpt = getsubstr($logline, "SPT=", " DPT=");
  my $dstpt;
  if ($proto eq "TCP") {
  	$dstpt = getsubstr($logline, "DPT=", " WINDOW=");
  }
  else {
  	$dstpt = getsubstr($logline, "DPT=", " LEN=");
  }
  my $time = format_logdate(substr($logline, 0, 15));
  # Strange id conflicts force the ON DUPLICATE KEY syntax, but it's probably
  # only necessary in edge case scenarios.
  my $sql_stmt = "INSERT INTO darknet (darknet_time, darknet_srcip, darknet_destip, " .
  	"darknet_proto, darknet_srcpt, darknet_destpt, alert_id) VALUES (?,?,?,?,?,?,( 
  	SELECT alert_id FROM ossec_alert WHERE alert_ossec_id =?)) 
  	ON DUPLICATE KEY UPDATE darknet_id = LAST_INSERT_ID(darknet_id), darknet_time = ?, 
  		darknet_srcip = ?, darknet_destip = ?, darknet_proto = ?, darknet_srcpt = ?, 
  		darknet_destpt = ?, alert_id = (SELECT alert_id FROM ossec_alert WHERE alert_ossec_id =?)";
  $sth = $dbh->prepare($sql_stmt) || die("Couldn't prep the ossec insert");
  $sth->execute(format_logdate($date), $srcip, $dstip, $proto, $srcpt, $dstpt, $alert_id, format_logdate($date), $srcip, $dstip, $proto, $srcpt, $dstpt, $alert_id);
  $sth->finish();
}

sub getsubstr {
  my ($str, $stok, $etok) = @_;
  $start = index($str, $stok) + length($stok);
  $end = index($str, $etok, $start);
  return substr($str, $start, $end-$start);
}

# Format the date from OSSEC log to MySQL
# 2011 Feb 14 11:55:59 to 2011-02-14 11:55:59
sub format_logdate {
  my $in = shift(@_);
  my ($year, $month, $day, $time) = split(/ /, $in);
  #print $month . "\n";
  %monthnum = (
    "Jan" => "01",
    "Feb" => "02",
    "Mar" => "03",
    "Apr" => "04",
    "May" => "05",
    "Jun" => "06",
    "Jul" => "07",
    "Aug" => "08",
    "Sep" => "09",
    "Oct" => 10,
    "Nov" => 11,
    "Dec" => 12,
  );
  my $retval = "$year-" . $monthnum{$month} . "-$day $time";
  return $retval;
}

sub open_check_log {
  # Unzip yesterday's log
  my $time = time();
  my $yesterday = localtime($time-86400);
  my @yestertime = split(/ +/, $yesterday);
  my $y_day = $yestertime[2];
  if ($y_day < 10) {
    $y_day = "0" . $y_day;
  }
  my $y_month = $yestertime[1];
  my $y_year = $yestertime[4];
  my $logdir = "/var/ossec/logs/alerts/$y_year/$y_month";
  chdir($workdir);
  system("cp $logdir/ossec-alerts-$y_day.log.gz $workdir");
  system("gunzip -d ossec-alerts-$y_day.log.gz");

  # Check the log file
  my $ossec_log = "ossec-alerts-$y_day.log";
  if (! -e $ossec_log) {
    die("Logfile $ossec_log doesn't exist or could not be found.\n");
  }
  return $ossec_log;
}

sub process_logfile {
  while ($record = <OSSECLOG>) {
    if ($record =~ /^\*\* Alert /) {
      # Make sure we aren't at the beginning
      if (! $alert_id eq "") {
        if ($debug) {print "Original Entry:\n";}
        if ($debug) {print $orig;}
        if ($debug) {print "Translated:\n";}
        if ($debug) {print "Alert [" . $alert_id . "]\n";}
        if ($debug) {print "Dateline - [" . $dateline . "]\n";}
        if ($debug) {print "Host - [" . $host . "]\n";}
        if ($debug) {print "Client IP - [" . $client_ip . "]\n";}
        if ($debug) {print "Log - [" . $loglocation ."]\n";}
        if ($debug) {print "Rule id [" . $rule . "]\n";}
        if ($debug) {print "Level [" . $level . "]\n";}
        if ($debug) {print "Message [" . $message . "]\n";}
        if ($debug) {print "Src IP [" . $src_ip . "]\n";}
        if ($debug) {print "User [" . $user . "]\n";}
        if ($debug) {print $logentry;}
        if ($debug) {print "\n-=-=-=-=-=-=-=-\n";}

        # Get the host from the database
        my $host_id = get_host_id($client_ip);
        # Get the database rule id (to save space)
        my $rule_id = get_db_rule_id($rule, $level, $message);
        # We only want to insert new alerts
        if ($alert_id > $max_ossec_alert) {
          $inserted_records++;
          insert_ossec($alert_id, $dateline, $host_id, $loglocation, $rule_id, $src_ip, $user, $logentry);
          # Depricated - see insert_darknet() function below for details
	      # if ($logentry=~ /DST=/ && $logentry =~ /SRC\=/) { # Assume a darknet record
	         #insert_darknet($alert_id, $dateline, $logentry, $dateline);
	      #}
        }
        else {
          $skipped_records++;
        }
      }
      $logentry = "";
      $orig = "";
      $alert_id = substr($record, 9, index($record, ':')-9);
    }
    else {
      # Match the timstamp and location line
      # Examples:
      # 	2012 Sep 20 00:20:34 jukeane01->/var/log/secure
      # 	Rule: 1002 (level 2) -> 'Unknown problem somewhere in the system.'
      # 	Src IP: (none)
      # 	User: (none)
      # 	Sep 20 00:20:34 jukeane01 polkitd(authority=local): Operator of unix-session:/org/freedesktop/ConsoleKit/Session1 FAILED to authenticate to gain authorization for action org.freedesktop.packagekit.system-sources-refresh for system-bus-name::1.3077 [/usr/bin/gpk-update-icon] (owned by unix-user:justin)
      # 	
      #		2012 Sep 21 00:00:00 (quasar.sas.upenn.edu) 128.91.234.145->/var/log/secure
      #		Rule: 5710 (level 5) -> 'Attempt to login using a non-existent user'
      #		Src IP: 180.183.115.55
      #		User: (none)
      #		Sep 20 23:59:58 quasar sshd[2164]: Failed password for invalid user kelly from 180.183.115.55 port 39975 ssh2
      
       	      
      if ($record =~ /^(\d){4} ([a-z]){3} \d\d \d\d:\d\d:\d\d /i) {
      	@dateHostLogLine = split(' ', $record);
      	$year = $dateHostLogLine[0];
      	$month = $dateHostLogLine[1];
      	$day = $dateHostLogLine[2];
      	$time = $dateHostLogLine[3];
      	# Handle format: 2012 Sep 21 00:00:00 (quasar.sas.upenn.edu) 128.91.234.145->/var/log/secure
      	if (index('(', $dateHostLogLine[4]) > -1) {
      		$host = $dateHostLogLine[4];
      		$host =~ s/(\(|\))//g; # Get rid of parens
      		@ipLog = split("->", $dateHostLogLine[5]);
      		$client_ip = $ipLog[0];
      		$loglocation = $ipLog[1];
      	}
      	# Handle format: 2012 Sep 20 00:20:34 jukeane01->/var/log/secure
      	else {
      		@hostLog = split("->", $dateHostLogLine[4]);
      		$client_ip = "127.0.0.1";
      		$host = $hostLog[0];
      		$loglocation = $hostLog[1];
      	}
      	$dateline = "$year $month $day $time";
        chomp($loglocation);
      }
      # Match the Rule line
      elsif ($record =~ /Rule\: (\d)+ \(level (\d)+\) /) {
        $rule = substr($record, index($record,": ")+2, index($record, " (level")-index($record,": ")-2);
        $level = substr($record, index($record,"(level ")+7, index($record, ')')-index($record, "(level ")-7);
        $message = substr($record, index($record,"->")+3);
        chomp($message);
      }
      # Match the Source IP
      elsif ($record =~ /^Src IP: /) {
      	if ($debug) { print "\nMatching source ip for [$record]\n";}
        $src_ip = substr($record, index($record, ": ")+2);
        chomp($src_ip);
        if ($debug) { print "\nSrc IP: $src_ip\n";}
        if ($src_ip eq '(none)') {
        	$src_ip = '127.0.0.1';
        }
        if ($debug) { print "\nSrc IP: $src_ip\n";}
      }
      # Match the user
      elsif ($record =~ /^User: /) {
        $user = substr($record, index($record, ": ")+2);
        chomp($user);
      }

      else {
        $logentry .= $record;
      }
      $orig .= $record;
    }
  }
}



sub trim {
    (my $s = $_[0]) =~ s/^\s+|\s+$//g;
    return $s;        
}

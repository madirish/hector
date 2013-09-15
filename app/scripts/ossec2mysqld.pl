#!/usr/bin/perl -w

use Socket;
use POSIX 'setsid';
# ---------------------------------------------------------------------------
# Author: Meir Michanie (meirm@riunx.com)
# Co-Author: J.A.Senger (jorge@br10.com.br)
# Modified by: Justin C. Klein Keane (justin@madirish.net) for HECTOR
# $Id$
# $Revision$
## ---------------------------------------------------------------------------
# http://www.riunx.com/
# ---------------------------------------------------------------------------
#
# ---------------------------------------------------------------------------
# About this script
# ---------------------------------------------------------------------------
#
# "Ossec to Mysql" records the OSSEC HIDS alert logs in MySQL database.
# It can run as a daemon (ossec2mysqld.pl), recording in real-time the logs in database or
# as a simple script (ossec2mysql.pl).
#
# ---------------------------------------------------------------------------
# Prerequisites
# ---------------------------------------------------------------------------
#
# MySQL Server
# Perl DBD::mysql module
# Perl DBI module
#
# ---------------------------------------------------------------------------
# Installation steps
# ---------------------------------------------------------------------------
# 
# 1) Create new database
# 2a) Run ossec2mysql.sql to create MySQL tables in your database
# 2b) Create BASE tables with snort tables extention
# 3) Create a user to access the database;
# 4) Copy ossec2mysql.conf to /etc/ossec2mysql.conf with 0600 permissions
# 3) Edit /etc/ossec2mysql.conf according to your configuration:
#   dbhost=localhost
#   database=ossecbase
#   debug=5
#   dbport=3306
#   dbpasswd=mypassword
#   dbuser=ossecuser
#   daemonize=0
#   resolve=1
#   
#
# ---------------------------------------------------------------------------
# License
# ---------------------------------------------------------------------------
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#
# ---------------------------------------------------------------------------
# About OSSEC HIDS
# ---------------------------------------------------------------------------
#
# OSSEC HIDS is an Open Source Host-based Intrusion Detection System.
# It performs log analysis and correlation, integrity checking,
# rootkit detection, time-based alerting and active response.
# http://www.ossec.net
#
# ---------------------------------------------------------------------------

# ---------------------------------------------------------------------------
# Parameters
# ---------------------------------------------------------------------------
$SIG{TERM} = sub { &gracefulend('TERM')};
$SIG{INT} = sub { &gracefulend('INT')};
my ($RUNASDAEMON)=0;
my ($DAEMONLOGFILE)='/var/log/ossec2mysql.log';
my ($DAEMONLOGERRORFILE) = '/var/log/ossec2mysql.err';
my ($LOGGER)='ossec2mysql';

use lib qw(/opt/hector/app/scripts);
use ossecmysql;

my %conf;
$conf{dbhost}='localhost';
$conf{database}='hector';
$conf{debug}=5;
$conf{dbport}='3306';
$conf{dbpasswd}='root';
$conf{dbuser}='';
$conf{daemonize}=0;
$conf{sensor}='sensor';
$conf{hids_interface}='ossec';
$conf{resolve}=1;


my($OCT) = '(?:25[012345]|2[0-4]\d|1?\d\d?)';

my($IP) = $OCT . '\.' . $OCT . '\.' . $OCT . '\.' . $OCT;

my $VERSION="0.4";
my $sig_class_id=1;
&help() unless @ARGV;
my $dump=0;
my ($hids_id,$hids,$hids_interface,$last_cid)=(undef, 'localhost', 'ossec',0);
my ($tempvar,$VERBOSE)=(0,0);
# ---------------------------------------------------------------------------
#  Arguments parsing
# ---------------------------------------------------------------------------
 
while (@ARGV){
        $_= shift @ARGV;
    if (m/^-d$|^--daemon$/){
        $conf{daemonize}=1;
    }elsif ( m/^-h$|^--help$/){
                &help();
    }elsif ( m/^-n$|^--noname$/){
                $conf{'resolve'}=0;
    }elsif ( m/^-v$|^--verbose$/){
                $VERBOSE=1;
    }elsif ( m/^--interface$/){
                $conf{hids_interface}= shift @ARGV if @ARGV; # ossec-rt/ossec-feed
        }elsif ( m/^--sensor$/){
                $conf{sensor}= shift @ARGV if @ARGV; # monitor
        }elsif ( m/^--conf$/){
                $conf{conf}= shift @ARGV if @ARGV; # localhost
        &loadconf(\%conf);
        }elsif ( m/^--dbhost$/){
                $conf{dbhost}= shift @ARGV if @ARGV; # localhost
        }elsif ( m/^--dbport$/){
                $conf{dbport}= shift @ARGV if @ARGV; # localhost
        }elsif ( m/^--dbname$/){
                $conf{database}= shift @ARGV if @ARGV; # snort
        }elsif ( m/^--dbuser$/){
                $conf{dbuser}= shift @ARGV if @ARGV; # root
        }elsif ( m/^--dbpass$/){
                $conf{dbpasswd}= shift @ARGV if @ARGV; # monitor
        }

}
if ($conf{dbpasswd}=~ m/^--stdin$/){
    print "dbpassword:";
    $conf{dbpasswd}=<>;
    chomp $conf{dbpasswd};
}
$hids=$conf{sensor} if exists($conf{sensor});
$hids_interface=$conf{hids_interface} if exists($conf{hids_interface});

&daemonize() if $conf{daemonize};
my $dbi= ossecmysql->new(%conf) || die ("Could not connect to $conf{dbhost}:$conf{dbport}:$conf{database} as $conf{dbpasswd}\n");
$hids_id = 0;
&forceprintlog ("SENSOR:$hids; feed:$hids_interface; id:$hids_id; last cid:$last_cid");
#exit ;

my $newrecord=0;
my %stats;
my %resolv;
my ($timestamp,$sec,$mail,$date,$alerthost,$alerthostip,$datasource,$rule,$level,$description,
    $srcip,$dstip,$user,$text)=();
my $lasttimestamp=0;
my $delta=0;
########################################################
my $datepath=`date "+%Y/%b/ossec-alerts-%d.log"`;
my $LOG='/var/ossec/logs/alerts/'. $datepath;
chomp $LOG;
&taillog($last_cid,$LOG);
###############################################################
sub forceprintlog(){
    $tempvar=$VERBOSE;
    $VERBOSE=1;
    &printlog (@_);
    $VERBOSE=$tempvar;
}
sub taillog {
   my ($last_cid,$LOG)=@_;
   my($line, $stall) = '';
   my $offset = 0;
   $offset = (-s $LOG) || 0; # Don't start at beginning, go to end

   while (1==1) {
       sleep(1);
    %resolv=();
       $| = 1;
       $stall += 1;
    $datepath=`date "+%Y/%b/ossec-alerts-%d.log"`;
    $LOG='/var/ossec/logs/alerts/'. $datepath;
    chomp $LOG;
    unless ( -f $LOG){&forceprintlog ("Error -f $LOG"); next; }
       if ((-s $LOG) < $offset) {
           &forceprintlog ("Log shrunk, resetting..");
           $offset = 0;
       }

        unless (open(TAIL, $LOG)){ &forceprintlog ("Error opening $LOG: $!\n");next ;}

        if (seek(TAIL, $offset, 0)) {
           # found offset, log not rotated
       } else {
           # log reset, follow
           $offset=0;
           seek(TAIL, $offset, 0);
       }
       while (<TAIL>) {
    if (m/^$/){
        $newrecord=1;
        next unless $timestamp;
        $alerthostip=$alerthost if $alerthost=~ m/^$IP$/;
        if ($alerthostip){
            $dstip=$alerthostip;
            $resolv{$alerthost}=$dstip;
        }else{
            if (exists $resolv{$alerthost}){
                $dstip=$resolv{$alerthost};
            }else{
                if ($conf{'resolve'}){
                    $dstip=`host $alerthost 2>/dev/null | grep 'has address' `;
                    if ($dstip =~m/(\d+\.\d+\.\d+\.\d+)/ ){
                        $dstip=$1;
                    }else{
                        $dstip=$srcip;
                    }
                }else{
                    $dstip=$alerthost;
                }
                $resolv{$alerthost}=$dstip;
                
            }
        }
        my $rule_id = &checkaddrule(
	        $rule,
	        $level,
	        $description,
        );
        $last_cid = &log2hector(
            $hids_id,
            $last_cid,
            $timestamp,
            $sec,
            $mail,
            $date,
            $alerthost,
            $alerthostip,
            $datasource,
            $rule_id,
            $srcip,
            $dstip,
            $user,
            $text,
            $rule
        );
        ($timestamp,$sec,$mail,$date,$alerthost,$alerthostip,$datasource,$rule,$level,$description,
        $srcip,$dstip,$user,$text,$rule_id)=();
        next ;
    }
    if (m/^\*\* Alert ([0-9]+).([0-9]+):(.*)$/){
        $timestamp=$1;
        if ( $timestamp == $lasttimestamp){
            $delta++;
        }else{
            $delta=0;
            $lasttimestamp=$timestamp;
        }
        $sec=$2;
        $mail=$3;
        $mail=$mail ? $mail : 'nomail';
#2006 Aug 29 17:19:52 firewall -> /var/log/messages
#2006 Aug 30 11:52:14 192.168.0.45->/var/log/secure
#2006 Sep 12 11:12:16 92382-Snort1 -> 172.16.176.132
#
    }elsif ( m/^([0-9]+\s\w+\s[0-9]+\s[0-9]+:[0-9]+:[0-9]+)\s+(\S+)\s*->(.*)$/){
        $date=$1;
        $alerthost=$2;
        $datasource=$3;
        if ($datasource=~ m/(\d+\.\d+\.\d+\.\d+)/){
            $alerthost=$1;
            $datasource="remoted";
        }

#2006 Aug 29 17:33:31 (recepcao) 10.0.3.154 -> syscheck
    }elsif ( m/^([0-9]+\s\w+\s[0-9]+\s[0-9]+:[0-9]+:[0-9]+)\s+\((.*?)\)\s+(\S+)\s*->(.*)$/){
        $date=$1;
        $alerthost=$2;
        $alerthostip=$3;
        $datasource=$4;
    }elsif ( m/^([0-9]+\s\w+\s[0-9]+\s[0-9]+:[0-9]+:[0-9]+)\s(.*?)$/){
                $date=$1;
                $alerthost='localhost';
                $datasource=$2;
    }elsif ( m/Rule: ([0-9]+) \(level ([0-9]+)\) -> (.*)$/ ){
        $rule=$1;
        $level=$2;
        $description= $3;
    }elsif ( m/Src IP:/){
        if ( m/($IP)/){
                        $srcip=$1;
                }else{
                        $srcip='0.0.0.0';
                }
    }elsif ( m/User: (.*)$/){
                $user=$1;
        }elsif( m/(.*)$/){
        $text .=$1;
    }
        

       } # End while read line
       $offset=tell(TAIL);
       close(TAIL);
   }
}

sub checkaddrule() {
	my $rule_id;
    my (
        $rule,
        $level,
        $description
    )=@_;   
    my $select_sql = "select rule_id from ossec_rule 
        where rule_number = ?";
    $rule_id = db_select($select_sql, "rule_id", $rule);

    # Insert the record if it can't be found
	if ($rule_id < 1) {
        my $sth = $dbi->{dbh}->prepare("insert into ossec_rule(rule_number, rule_level, rule_message) values (?,?,?)") || die("Couldn't prep rule insert.");
	    $sth->execute($rule, $level, $description) || die("Couldn't insert new rule.");
	    $sth->finish();  
        $rule_id = db_select($select_sql, "rule_id", $rule); # Set the new ID
	}
    return $rule_id;
}
sub log2hector() {
    my (
        $hids_id,
        $last_cid,
        $timestamp,
        $sec,
        $mail,
        $date,
        $alerthost,
        $alerthostip,
        $datasource,
        $rule_id,
        $srcip,
        $dstip,
        $user,
        $text,
        $rule_number
    )=@_;   
    my $host_id = get_host_id($alerthostip, $alerthost);
    insert_ossec(0, format_logdate($date),$host_id, $datasource, $rule_id, $srcip, $user, $text, $rule_number);
    
    return 1;
    
}
# Format the date from OSSEC log to MySQL
# 2011 Feb 14 11:55:59 to 2011-02-14 11:55:59
sub format_logdate {
  my $retval = "";
  my $in = shift(@_);
  my ($year, $month, $day, $time) = split(/ /, $in);
  my %monthnum = (
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
  if (defined $day && defined $month && defined $year && defined $time) {
  	$retval = "$year-" . $monthnum{$month} . "-$day $time";
  }
  return $retval;
}
sub get_host_id {
  my ($client_ip, $host) = @_;
  my $select_sql = "select host_id from host where host_ip = ?";
  my $host_id = db_select($select_sql, "host_id", $client_ip);
  if (! $client_ip) {
  	$client_ip = "";
  }
  # Insert the record if it can't be found
  if ($host_id < 1) {
    if ($client_ip eq "") {
        $client_ip = "127.0.0.1";
    }
    my $sth = $dbi->{dbh}->prepare("insert into host(host_ip, host_ip_numeric, host_name, host_note) values (?, inet_aton(?),?,?) ON DUPLICATE KEY UPDATE host_ip = ?") || die("Couldn't prep host insert.");
    $sth->execute($client_ip, $client_ip, $host, "OSSEC", $client_ip) || die("Couldn't exec host insert with IP:[$client_ip], Host:[$host].\n");
    $sth->finish();
  }
  # Get the record we just found.
  $host_id = db_select($select_sql, "host_id", $client_ip);
  return $host_id;
}

# Insert the actual log into the database
sub insert_ossec {
  # Check to see if the record exists
  my ($ossec_alert_id, $date, $host_id, $alert_log, $rule_id, $src_ip, $user, $message, $ossec_rule_number) = @_;
  if (! $src_ip or $src_ip == '') {
  	$src_ip = '127.0.0.1';
  }
  my $sql_stmt = "insert into ossec_alert (alert_date, host_id, alert_log, rule_id, " . 
    "rule_src_ip, rule_src_ip_numeric, rule_user, rule_log, alert_ossec_id) values (?,?,?,?,?,inet_aton(?),?,?,?)";
  my $sth = $dbi->{dbh}->prepare($sql_stmt) || die("Couldn't prep the ossec insert");
  $sth->execute($date, $host_id, $alert_log, $rule_id, $src_ip, $src_ip, $user, $message, $ossec_alert_id) || die("Couldn't exec ossec insert.");
  $sth->finish();
  if ($ossec_rule_number == 200001) {
    # Darknet detection
    my ($src, $dst, $spt, $dpt, $proto);
    $src = substr($message, index($message,"SRC=")+4, index($message, " DST=")-index($message,"SRC=")-4);
    $dst = substr($message, index($message,"DST=")+4, index($message, " LEN=")-index($message,"DST=")-4);
    $spt = substr($message, index($message,"SPT=")+4, index($message, " DPT=")-index($message,"SPT=")-4);
    $dpt = substr($message, index($message,"DPT=")+4, index($message, " WINDOW=")-index($message,"DPT=")-4);
    $proto = substr($message, index($message,"PROTO=")+6, index($message, " SPT=")-index($message,"PROTO=")-6);
    my $dnet_sql = "INSERT INTO darknet SET src_ip = inet_aton(?), dst_ip = inet_aton(?), src_port = ?, 
        dst_port = ?, proto = ?, received_at = ?";
    my $dnet_sth = $dbi->{dbh}->prepare($dnet_sql) || die("Couldn't prepare darknet insert statement.");
    $dnet_sth->execute($src, $dst, $spt, $dpt, $proto, $date);
    $dnet_sth->finish();
  }
  elsif ($ossec_rule_number == 200011) {
    # Kojoney2 command
    # 2013-08-22 16:17:32-0400 [SSHChannel session (0) on SSHService ssh-connection on SSHServerTransport,1,10.10.0.1] COMMAND IS : cd /opt
    my $datestamp = substr($message, 0, 19);
    my $servicestring = substr($message, index($message, '['), index($message, ']') - index($message, '['));
    my @sshservice = split(/,/, $servicestring);
    my $session_id = $sshservice[1];
    my $remote_ip = $sshservice[2]; 
    my $command = substr($message, index($message, 'COMMAND IS :') + 13);
    my $koj_exec_sql = "INSERT INTO koj_executed_command set time = ?, ip = ?,
        command = ?, ip_numeric = inet_aton(?), session_id = ?, 
        sensor_id = ?";
    my $koj_exec_sth = $dbi->{dbh}->prepare($koj_exec_sql) || die("Couldn't prepare kojoney insert statement.");
    $koj_exec_sth->execute($datestamp, $remote_ip, $command, $remote_ip, $session_id, $host_id);
    $koj_exec_sth->finish();
  }
  elsif ($ossec_rule_number == 200012) {
    # Kojoney2 login attampt
    # 2013-08-22 16:17:13-0400 [SSHService ssh-userauth on SSHServerTransport,1,10.10.0.1] login attempt [root password] succeeded
    # 2013-08-22 16:17:12-0400 [SSHService ssh-userauth on SSHServerTransport,1,10.10.0.1] login attempt [root toor] failed
    my $datestamp = substr($message, 0, 19);
    my $servicestring = substr($message, index($message, '['), index($message, ']') - index($message, '['));
    my @sshservice = split(/,/, $servicestring);
    my $session_id = $sshservice[1];
    my $remote_ip = $sshservice[2]; 
    my $userpass_string = substr($message, rindex($message, '['));
    my $username = $user;
    my $pwordstart = index($message, $user) + length($user) + 1;
    my $password = substr($message, $pwordstart, rindex($message, ']') - $pwordstart);
    my $koj_login_sql = "INSERT INTO koj_login_attempt SET time = ?, username = ?, password = ?, 
        ip_numeric = inet_aton(?), ip = ?, sensor_id = ?";
    my $koj_login_sth = $dbi->{dbh}->prepare($koj_login_sql) || die("Couldn't prepare kojoney login statement.");
    $koj_login_sth->execute($datestamp, $username, $password, $remote_ip, $remote_ip, $host_id);
    $koj_login_sth->finish();
  }
}
sub db_select {
  my $select_sql = shift(@_);
  my $id_col = shift(@_);
  my @sql_args = @_;
  my $id = 0;
  
  if (! defined($dbi->{dbh})) {
  	$dbi = ossecmysql->new(%conf)  || die ("Could not connect to $conf{dbhost}:$conf{dbport}:$conf{database} as $conf{dbpasswd}\n");
  }
  my $sth = $dbi->{dbh}->prepare($select_sql) || die("Couldn't prepare db_select statement.");
  if (! @sql_args) {
    die("SQL args are undefined!\n");
  }
  $sth->execute(@sql_args) || die("Couldn't execute statement for $select_sql.");
  while(my $ref = $sth->fetchrow_hashref()) {
    $id = $ref->{$id_col};
  }
  $sth->finish();
  return $id;
}

sub version(){
    print "OSSEC report tool $VERSION\n";
    print "Licensed under GPL\n";
    print "Contributor Meir Michanie\n";
}

sub help(){
    &version();
    print "This tool helps you import into base the alerts generated by ossec."
        . " More info in the doc directory .\n";
        print "Usage:\n";
        print "$0 [-h|--help] # This text you read now\n";
    print "Options:\n";
    print "\t--dbhost <hostname>\n";
    print "\t--dbname <database>\n";
    print "\t--dbport <[0-9]+>\n";
    print "\t--dbpass <dbpasswd>\n";
    print "\t--dbuser <dbuser>\n";
    print "\t-d|--daemonize\n";
    print "\t-n|--noname\n";
    print "\t-v|--verbose\n";
    print "\t--conf <ossec2based-config>\n";
    print "\t--sensor <sensor-name>\n";
    print "\t--interface <ifname>\n";
    
    exit 0;
}


sub daemonize {
        chdir '/'               or die "Can't chdir to /: $!";
        open STDIN, '/dev/null' or die "Can't read /dev/null: $!";
        open STDOUT, ">>$DAEMONLOGFILE"
                               or die "Can't write to $DAEMONLOGFILE: $!";
        defined(my $pid = fork) or die "Can't fork: $!";
        if ($pid){
                open (PIDFILE , ">/var/run/ossec2hector.pid") ;
                print PIDFILE "$pid\n";
                close (PIDFILE);
                exit 0;
        }
        setsid                  or die "Can't start a new session: $!";
        open STDERR, ">>$DAEMONLOGERRORFILE" or die "Can't write to $DAEMONLOGERRORFILE: $!";
}

sub gracefulend(){
        my ($signal)=@_;
        &forceprintlog ("Terminating upon signal $signal");
        close TAIL;
        &forceprintlog ("Daemon halted");
        close STDOUT;
    close STDERR;
        exit 0;
}

sub printlog(){
    return  unless $VERBOSE;
        my (@lines)=@_;
        foreach my $line(@lines){
                chomp $line;
                my ($date)=scalar localtime;
                $date=~ s/^\S+\s+(\S+.*\s[0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2}).*$/$1/;
                print "$date $LOGGER: $line\n";
        }
}


sub loadconf(){
    my ($hash_ref)=@_;
    my $conf=$hash_ref->{conf};
    unless (-f $conf) { &printlog ("ERROR: I can't find config file $conf"); exit 1;}
    unless (open ( CONF , "$conf")){ &printlog ("ERROR: I can't open file $conf");exit 1;}
    while (<CONF>){
        next if m/^$|^#/;
        if ( m/^(\S+)\s?=\s?(.*?)$/) {
                        $hash_ref->{$1} = $2;
                }
    }
    close CONF;
}
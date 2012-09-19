#!/usr/bin/perl 

use LWP::UserAgent; 
use HTTP::Request; 
use HTTP::Response; 
use XML::RSS;
use DBI();

my $workdir = substr($0, 0, rindex($0, 'feed_consumer.pl'));
my $config_file = $workdir . '../conf/config.ini';
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

my $dbh = DBI->connect("DBI:mysql:database=$db;host=$dbhost",$dbuser,$dbpass) || die("Error connecting to db.");
my $sth = $dbh->prepare('SELECT rss_url from rss');
$sth->execute();
while (@data = $sth->fetchrow_array()) {
	my $url = $data[0];
	my $rss = XML::RSS->new();
	my $ua = LWP::UserAgent->new(); 
	$ua->agent("HECTOR Security Intelligence Platform");
	my $req = HTTP::Request->new(GET => $url); 
	$req->referer("http://www.madirish.net");
	my $response = $ua->request($req);
	if ($response->is_error()) {
	     printf " %s\n", $response->status_line;
	} 
	else {
		$feed_data = $response->content();
	}
	if (length($feed_data) > 0) {
		$rss->parse($feed_data);
		my $channel = $rss->{channel};
		foreach $item ( @{$rss->{items}} ) {
			my $title =  $item->{title};
			my $link =  $item->{link};
			my $desc =  $item->{description};
			my $tstamp = $item->{pubDate};
			my $sql = 'SELECT COUNT(article_id) from article where article_url = ?';
			$sth2 = $dbh->prepare($sql);
			$sth2->execute($url);
			# Make sure we don't insert duplicates
			if ($sth2->fetchrow_array() == 0) {
				$sql = 'insert into article ' .
					'(article_title, article_url, article_date, article_body) ' . 
					'values (?,?,str_to_date(?, \'%a, %e %b %Y %H:%i:%S %x\'),?)';
				$sth3 = $dbh->prepare($sql) || die("Couldn't prep the feed insert");
				$sth3->execute($title, $link, $date, $desc) || die("Couldn't perform feed insert");
				$sth3->finish();
			}
			$sth2->finish();
		}
	}
}
$sth->finish();

$dbh->disconnect();

sub trim {
    (my $s = $_[0]) =~ s/^\s+|\s+$//g;
    return $s;        
}
<?php 


require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');



class TestOfOSSECImport extends UnitTestCase {
	
  private $alert;
  
  private $alert_id;
  
  private $simple_log_message = <<<EOM
** Alert 1463649776.2862: - syslog,sshd,authentication_success,
2016 May 19 05:22:56 hector->/var/log/secure
Rule: 5715 (level 3) -> 'SSHD authentication success.'
Src IP: 192.168.56.1
Dst IP: 10.111.176.53
User: kleiju
May 19 05:22:55 hector sshd[2207]: Accepted publickey for kleiju from 192.168.56.1 port 41344 ssh2
EOM;
  
  function setUp() {
  	$GLOBALS['override_approot'] = dirname(__FILE__) . '/../';
  	include(dirname(__FILE__) . '/../scripts/ossec/import.php');
  }
  
  function tearDown() {
  	
  }
  
  function testOSSECImport() {
  	$lines = explode("\n", $this->simple_log_message);
  	foreach ($lines as $line) {
  		process_log_line($line);
  	}
  }
}
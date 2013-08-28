<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.Config.php');


class TestOfConfigClass extends UnitTestCase {
	
	function testConfigClass() {
		$config = new Config();
		$this->assertNotNull($_SESSION['db']);
		$this->assertNotNull($_SESSION['db_host']);
		$this->assertNotNull($_SESSION['db_user']);
		$this->assertNotNull($_SESSION['db_pass']);
		$this->assertNotNull($_SESSION['config_file']);
		$this->assertNotNull($_SESSION['error_log']);
		$this->assertNotNull($_SESSION['message_log']);
		$this->assertNotNull($_SESSION['libroot']);
		$this->assertNotNull($_SESSION['approot']);
		$this->assertNotNull($_SESSION['php_exec_path']);
		$this->assertNotNull($_SESSION['nmap_exec_path']);
		$this->assertNotNull($_SESSION['python_exec_path']);
		$this->assertNotNull($_SESSION['xprobe2_exec_path']);
		$this->assertNotNull($_SESSION['ncrack_exec_path']);
		$this->assertNotNull($_SESSION['timezone']);
		$this->assertNotNull($_SESSION['site_url']);
		$this->assertNotNull($_SESSION['site_email']);
		$this->assertNotNull($_SESSION['alert_email']);
		$this->assertNotNull($_SESSION['bing_api_key']);
	}
}
?>
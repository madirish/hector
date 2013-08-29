<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.Log.php');


class TestOfLogClass extends UnitTestCase {
	
	function setUp() {
		$this->log = Log::get_instance();
	}
	
	function testLogClass() {
		$this->assertIsA($this->log, 'Log');
	}
	
	function testMessageLog() {
		$msg = 'This is just a unit test log message.';
		$this->log->write_message($msg);
		$this->assertTrue(strpos($this->log->return_message_log(), $msg));
	}
	
	function testErrorLog() {
		$msg = 'This is just a unit test log message.';
		$this->log->write_error($msg);
		$this->assertTrue(strpos($this->log->return_error_log(), $msg));
	}
	
	function testArchiveMessageLog() {
		$this->assertTrue($this->log->archive_message_log());
	}
	
	function testArchiveErrorLog() {
		$this->assertTrue($this->log->archive_error_log());
	}
	
	function testToggleStatus() {
		//$this->log->toggle_status();
	}
}
?>
<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.Dblog.php');
require_once(dirname(__FILE__) . '/../lib/class.Db.php');


class TestOfDblogClass extends UnitTestCase {
	
	function testDblogClass() {
		$dblog = Dblog::get_instance();
		$this->assertIsA($dblog, 'Dblog');
	}

	function testDblogMessage() {
		$dblog = Dblog::get_instance();
		$db = Db::get_instance();
		$type = 'TEST';
		$msg = 'Test message';
		$dblog->log($type, $msg);
		$sql = 'SELECT * FROM log WHERE log_type = "TEST" AND log_message = "Test message"';
		$logs = $db->fetch_object_array($sql);
		$this->assertTrue(count($logs) > 0);
		foreach ($logs as $log) {
			$this->assertEqual($log->log_type, $type);
			$this->assertEqual($log->log_message, $msg);
			$this->assertNotNull($log->log_timestamp);
		}
		$sql = array('DELETE FROM log WHERE log_type = \'?s\' AND log_message = \'?s\'',
			$type,
			$msg);
		$db->iud_sql($sql);
	}
}
?>
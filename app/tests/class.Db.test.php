<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.Db.php');


class TestOfDbClass extends UnitTestCase {
	
	function testDbClass() {
		$db = DB::get_instance();
		$this->assertIsA($db, 'Db');
	}
	function testDbFetchObjectArray() {
		$sql = 'SELECT now() from dual';
		$db = DB::get_instance();
		$arr = $db->fetch_object_array($sql);
		$this->assertIsA($arr, 'Array');
		$this->assertTrue(count($arr) == 1);
	}
	/*function testDbIudSQL() {
		$sql = array('INSERT INTO alert SET alert_string = \'?s\', host_id = ?i', array('test string', 0));
		$db = DB::get_instance();
		$db->iud_sql($sql);
		$alerts = $db->fetch_object_array('SELECT * FROM alert WHERE host_id = 0')
		foreach ($alerts as $alert) {
			
		}
	}*/
}
?>
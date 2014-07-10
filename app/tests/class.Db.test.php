<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.Db.php');


class TestOfDbClass extends UnitTestCase {
    
    function setup() {
        $this->db = DB::get_instance();
    }
	
	function testDbClass() {
		$this->assertIsA($this->db, 'Db');
	}
	function testDbFetchObjectArray() {
		$sql = 'SELECT now() from dual';
		$db = DB::get_instance();
		$arr = $this->db->fetch_object_array($sql);
		$this->assertIsA($arr, 'Array');
		$this->assertTrue(count($arr) == 1);
	}
	function testDbIudSQL() {
		$test_msg = 'test string';
		$sql = array('INSERT INTO alert SET alert_string = \'?s\', host_id = ?i', $test_msg, 0);
		$this->assertTrue($this->db->iud_sql($sql));
		$alerts = $this->db->fetch_object_array('SELECT * FROM alert WHERE host_id = 0');
		$this->assertEqual(count($alerts),1);
		foreach ($alerts as $alert) {
			$this->assertEqual($alert->host_id, 0);
			$this->assertEqual($alert->alert_string, $test_msg);
			$newalert = new Alert($alert->alert_id);
			$this->assertEqual($newalert->get_host_id(), 0);
			$this->assertEqual($newalert->get_string(), $test_msg);
		}
		$sql = 'DELETE FROM alert WHERE host_id = 0';
		$this->assertTrue($this->db->iud_sql($sql));
		$alerts = $this->db->fetch_object_array('SELECT * FROM alert WHERE host_id = 0');
		$this->assertEqual(count($alerts),0);
	}
    
    function testClose() {
    	// $this->assertTrue($this->db->close());
    }
}
?>
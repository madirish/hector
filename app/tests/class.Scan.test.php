<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.Scan.php');
require_once(dirname(__FILE__) . '/../lib/class.Scan_type.php');


class TestOfScanClass extends UnitTestCase {
	
	function setUp() {
		$this->scan = new Scan();
	}
	function tearDown() {
		$this->scan->delete();
	}
	
	function testScanClass() {
		$this->assertIsA($this->scan, 'Scan');
	}
	
	function testId() {
		$this->assertEqual($this->scan->get_id(), 0);
	}
    
    function testDaily() {
    	$this->assertTrue($this->scan->set_daily(1));
        $this->assertEqual($this->scan->get_daily(), 1);
        $this->assertEqual('Yes', $this->scan->get_friendly_daily());
    }
    
    function testDayofWeek() {
    	$this->assertTrue($this->scan->set_dayofweek(1));
        $this->assertEqual($this->scan->get_dayofweek(), 1);
        $this->assertEqual('Sunday', $this->scan->get_friendly_dayofweek());
        $this->assertFalse($this->scan->set_dayofweek('first'));
    }
    
    function testDayofMonth() {
    	$this->assertFalse($this->scan->set_dayofmonth(40));
        $this->assertTrue($this->scan->set_dayofmonth(12));
        $this->assertEqual(12, $this->scan->get_dayofmonth());
        $this->assertEqual(12, $this->scan->get_friendly_dayofmonth());
    }
    
    function testDayOfYear() {
    	$this->assertFalse($this->scan->set_dayofyear(400));
        $this->assertTrue($this->scan->set_dayofyear(150));
        $this->assertEqual(150, $this->scan->get_dayofyear());
        $this->assertEqual(150, $this->scan->get_friendly_dayofyear());
    }
    
    function testGroupIds() {
    	$ids = array(1,2,3);
        $this->assertTrue($this->scan->set_group_ids($ids));
        $this->assertEqual($ids, $this->scan->get_group_ids());
        $host1 = new Host_group();
        $host2 = new Host_group();
        $host1->set_name('foo');
        $host2->set_name('bar');
        $host1->save();
        $host2->save();
        $this->scan->set_group_ids(array($host1->get_id(), $host2->get_id()));
        $this->assertEqual('foo, bar', $this->scan->get_host_groups_readable());
        $host2->delete();
        $host1->delete();
    }
	
	function testName() {
		$name = 'Test';
		$this->scan->set_name($name);
		$this->assertEqual($this->scan->get_name(), $name);
	}
	
	function testGetAddAlterForm() {
		$this->assertIsA($this->scan->get_add_alter_form(), 'Array');
	}
	
	function testGetCollectionDefinition() {
		$this->assertIsA($this->scan->get_collection_definition(), 'String');
	}
    
    function testScanType() {
    	$stype = new Scan_type();
        $stype->save();
        $id = $stype->get_id();
        $this->scan->set_type_by_id($id);
        $this->assertEqual($id, $this->scan->get_scan_type_id());
        $this->assertTrue($this->scan->set_type($stype));
        $this->assertEqual($stype, $this->scan->get_type());
        $stype->delete();
    }
	
	function testSaveDelete() {
		$this->scan->set_name('Test');
		$this->assertTrue($this->scan->save());
		$this->assertTrue($this->scan->get_id() > 0 );
		$this->assertTrue($this->scan->delete());
	}
}
?>
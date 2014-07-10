<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.Nmap_result.php');


class TestOfNmap_resultClass extends UnitTestCase {
	
	function setUp() {
		$this->nr = new Nmap_result();
	}
	
	function tearDown() {
		$this->nr->delete();
	}
	
	function testLocationClass() {
		$this->assertIsA($this->nr, 'Nmap_result');
	}
	
	function testId() {
		$this->assertEqual($this->nr->get_id(), 0);
	}
	
	function testGetDetails() {
		$this->assertIsA($this->nr->get_details(), 'String');
	}
	
	function testGetCollectionDefinition() {
		$this->assertIsA($this->nr->get_collection_definition(), 'String');
	}
	
	function testHostId() {
		$this->nr->set_host_id(-1);
		$this->assertEqual($this->nr->get_host_id(), -1);
	}
    
    function testState() {
    	$this->nr->set_state_id(1);
        $this->assertEqual($this->nr->get_state_id(), 1);
        $this->assertFalse($this->nr->set_state_id(12));
        $this->assertFalse($this->nr->set_state_id('bad'));
    }
    
    function testProtocol() {
    	$this->assertFalse($this->nr->set_protocol('bad'));
        $this->nr->set_protocol('tcp');
        $this->assertEqual($this->nr->get_protocol(), 'tcp');
    }
    
    function testSave() {
    	$new = new Nmap_result();
        $new->set_state_id(1);
        $new->set_protocol('tcp');
        $new->set_port_number(80);
        $new->set_host_id(-1);
        $new->set_scan_id(-1);
        $this->assertTrue($new->save());
        $id = $new->get_id();
        $second = new Nmap_result($id);
        $this->assertEqual($second->get_id(), $id);
        $this->assertEqual($second->get_state(), 'open');
        $this->assertTrue($second->delete());
    }
    
    function testServiceName() {
    	$sn = 'Someservice';
        $bad = '<script>alert("xss");</script>';
        $this->nr->set_service_name($sn);
        $this->assertEqual($this->nr->get_service_name(), $sn);
        $this->nr->set_service_name($bad);
        $this->assertNotEqual($bad, $this->nr->get_service_name());
    }
    
    function testLookupScan() {
    	/**
         * @todo: Implement test for this complex method
         * lookup_scan()
    	 */
    }
    
    function testServiceVersion() {
    	$sv = 'foobar 1.2';
        $bad = '<script>alert("xss");</script>';
        $this->nr->set_service_version($sv);
        $this->assertEqual($this->nr->get_service_version(), $sv);
        $this->nr->set_service_version($bad);
        $this->assertNotEqual($bad, $this->nr->get_service_version());
    }
    
    function testTimeStamp() {
    	$this->assertEqual(null, $this->nr->get_timestamp());
        $this->nr->set_timestamp();
        $this->assertNotEqual(null, $this->nr->get_timestamp());
        $this->assertTrue(is_string($this->nr->get_timestamp()));
    }
    
    function testScanId() {
        $this->nr->set_scan_id(-1);
        $this->assertEqual($this->nr->get_scan_id(), -1);
        $this->assertFalse($this->nr->set_scan_id('foo'));
    }
	
	function testPortNumber() {
		$this->assertTrue($this->nr->set_port_number(80));
		$this->assertEqual($this->nr->get_port_number(), 80);
		$this->assertFalse($this->nr->set_port_number(-1));
		$this->assertFalse($this->nr->set_port_number(65536));
	}
}
?>
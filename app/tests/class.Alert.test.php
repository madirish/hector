<?php 

require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.Alert.php');


class TestOfAlertClass extends UnitTestCase {
	
  private $alert;
  
  private $alert_id;
  
  function setUp() {
  	$this->alert = new Alert();
  }
  
  function tearDown() {
  	$this->alert->delete();
  }

  function testAlertConstructor() {
  	$this->assertIsA($this->alert, 'Alert');
  }
  
  function testAlertSave() {
  	$this->assertEqual($this->alert->get_id(), 0);
  	$this->alert->set_host_id(1);
  	$this->alert->set_string('Test string');
  	$this->assertTrue($this->alert->save());
  	$this->alert_id = $this->alert->get_id();
  	$this->assertTrue($this->alert_id > 0);
  }
  
  function testSetAlertHostId() {
  	$this->alert->set_host_id(1);
  	$this->assertTrue($this->alert->get_host_id() == 1);
  }
  
  function testSetAlertString() {
  	$testval = 'Test string';
  	$this->alert->set_string($testval);
  	$this->assertEqual($this->alert->get_string(), $testval);
  }
  
  function testAlertTimeStamp() {
  	$this->alert->set_host_id(1);
  	$this->alert->set_string('test string');
  	$this->alert->save();
  	$id = $this->alert->get_id();
  	$secondAlert = new Alert($id);
  	$this->assertTrue($secondAlert->get_timestamp() > 0);
  }
  
  function testAlertGetPort() {
  	$this->alert->set_string('Port 22 changed from filtered to open on 130.91.128.192');
  	$this->assertEqual($this->alert->get_port(), 22);
  }
  
  function testAlertGetHost() {
  	$this->alert->set_string('Port 22 changed from filtered to open on 130.91.128.192');
  	$this->assertEqual($this->alert->get_host(), '130.91.128.192');
  }
  
  function testAlertGetHostLinked() {
    $this->alert->set_host_id(1);
    $host = $this->alert->get_host();
    $this->assertTrue(is_string($host));
  	$this->assertTrue(is_string($this->alert->get_host_linked()));
  }
  
  function testTimestamp() {
  	$tstamp = '2014-01-30 12:00:00';
    $this->alert->set_timestamp($tstamp);
    $this->assertEqual($tstamp, $this->alert->get_timestamp());
    $bad = '<script>alert("xss");</script>';
    $this->alert->set_timestamp($bad);
    $this->assertNotEqual($bad, $this->alert->get_timestamp());
  }
  
  function testGetCollectionDateIP() {
    $goodstartdate = '2014-01-01';
    $goodenddate = '2014-02-01';
    $goodip = '127.0.0.1';
    $badstartdate = '2014-02-30';
    $badenddate = '2013-01-01';
    $badip = '127.0.0.256';
    $barestring = 'SELECT a.alert_id FROM alert a, host h WHERE a.host_id = h.host_id  ORDER BY a.alert_timestamp DESC  LIMIT 200';
    $this->assertEqual($barestring, $this->alert->get_collection_by_dates_ip(array(),''));
  }
  function testAlertDelete() {
  	$this->alert->set_host_id(1);
  	$this->alert->set_string('Test string');
  	$this->assertTrue($this->alert->save());
  	$id = $this->alert->get_id();
  	$this->assertTrue($id > 0);
  	$this->alert->delete();
  	$this->assertEqual($this->alert->get_id(), 0);
  }
}
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
  	$this->alert = new Alert();
  	$this->alert->set_host_id(1);
  	$this->alert->set_string('test string');
  	$this->alert->save();
  	$id = $this->alert->get_id();
  	$secondAlert = new Alert($id);
  	$this->assertTrue($secondAlert->get_timestamp() > 0);
  }
  
  function testAlertGetPort() {
  	$this->alert = new Alert();
  	$this->alert->set_string('Port 22 changed from filtered to open on 130.91.128.192');
  	$this->assertEqual($this->alert->get_port(), 22);
  }
  
  function testAlertGetHost() {
  	$this->alert = new Alert();
  	$this->alert->set_string('Port 22 changed from filtered to open on 130.91.128.192');
  	$this->assertEqual($this->alert->get_host(), '130.91.128.192');
  }
  
  function testAlertDelete() {
  	$this->alert->set_host_id(1);
  	$this->alert->set_string('Test string');
  	$this->assertTrue($this->alert->save());
  	$id = $this->alert->get_id();
  	$this->alert->delete();
  	$secondAlert = new Alert($id);
  	$this->assertEqual($this->alert->get_id(), 0);
  }
}
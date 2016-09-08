<?php 

require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.Named_resolution.php');
require_once(dirname(__FILE__) . '/../lib/class.Domain.php');


class TestOfNamed_resolutionClass extends UnitTestCase {
	
  private $named_resolution;
  
  private $named_resolution_id;
  
  function setUp() {
  	$this->named_resolution = new Named_resolution();
  }
  
  function tearDown() {
  	$this->named_resolution->delete();
  }
  
  function testNamed_resolutionConstructor() {
  	$this->assertIsA($this->named_resolution, 'Named_resolution');
  }
  
  function testNamed_resolutionSave() {
  	$this->assertEqual($this->named_resolution->get_id(), 0);
  	$this->named_resolution->set_datetime(Named_resolution::conv_datetime('Aug 26 04:02:16'));
  	$this->named_resolution->set_domain(new Domain());
  	$this->named_resolution->set_ip('10.0.0.1');
  	$this->assertTrue($this->named_resolution->save());
  	$this->named_resolution_id = $this->named_resolution->get_id();
  	$this->assertTrue($this->named_resolution_id > 0);
  	$clone = new Named_resolution($this->named_resolution_id);
  	$this->assertEqual($this->named_resolution->get_id(), $clone->get_id());
  	$this->assertEqual($this->named_resolution->get_datetime(), $clone->get_datetime());
  	$this->assertEqual($this->named_resolution->get_domain()->get_id(), $clone->get_domain()->get_id());
  	$this->assertEqual($this->named_resolution->get_ip(), $clone->get_ip());
  	$this->assertEqual($this->named_resolution->get_ip_numeric(), $clone->get_ip_numeric());
  	//Test updating object
  	$this->named_resolution->set_datetime(Named_resolution::conv_datetime('Sep 26 04:02:16'));
  	$this->named_resolution->set_ip('10.0.0.230');
  	$this->named_resolution->save();
  	$clone = new Named_resolution($this->named_resolution_id);
  	$this->assertEqual($this->named_resolution->get_id(), $clone->get_id());
  	$this->assertEqual($this->named_resolution->get_datetime(), $clone->get_datetime());
  	$this->assertEqual($this->named_resolution->get_domain()->get_id(), $clone->get_domain()->get_id());
  	$this->assertEqual($this->named_resolution->get_ip(), $clone->get_ip());
  	$this->assertEqual($this->named_resolution->get_ip_numeric(), $clone->get_ip_numeric());
  }
  
  function testNamed_resolutionSet_datetime() {
  	$testval = 'Aug 26 04:02:16';
  	$this->named_resolution->set_datetime($testval);
  	$this->assertEqual($this->named_resolution->get_datetime(), $testval);
  }
  
  function testNamed_resolutionSet_domain() {
  	$testval = 'Test.domain.name';
  	$testDomain = new Domain;
  	$testDomain->set_name($testval);
  	$this->named_resolution->set_domain($testDomain);
  	$this->assertEqual($this->named_resolution->get_domain()->get_name(), $testval);
  }
  
  function testNamed_resolutionSet_ip() {
  	$testval = '10.0.0.1';
  	$testval2 = '192.168.1.1';
  	$this->named_resolution->set_ip($testval);
  	$this->assertEqual($this->named_resolution->get_ip(), $testval);
  	$this->assertEqual($this->named_resolution->get_ip_numeric(), ip2long($testval));
  	$this->named_resolution->set_ip_numeric(ip2long($testval2));
  	$this->assertEqual($this->named_resolution->get_ip(), $testval2);
  	$this->assertEqual($this->named_resolution->get_ip_numeric(), ip2long($testval2));
  }

  function testNamed_resolutionDelete() {
  	$this->assertEqual($this->named_resolution->get_id(), 0);
  	$this->named_resolution->set_datetime('Aug 26 04:02:16');
  	$this->named_resolution->set_domain(new Domain());
  	$this->named_resolution->set_ip('10.0.0.1');
  	$this->assertTrue($this->named_resolution->save());
  	$this->named_resolution_id = $this->named_resolution->get_id();
  	$this->assertTrue($this->named_resolution_id > 0);
  	$this->named_resolution->delete();
  	$this->assertEqual($this->named_resolution->get_id(), 0);
  }
  
  
}
?>
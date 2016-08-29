<?php 

require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.Infoblox_query.php');


class TestOfInfoblox_queryClass extends UnitTestCase {
	
  private $infoblox_query;
  
  private $infoblox_query_id;
  
  function setUp() {
  	$this->infoblox_query = new Infoblox_query();
  }
  
  function tearDown() {
  	$this->infoblox_query->delete();
  }
  
  function testInfoblox_queryConstructor() {
  	$this->assertIsA($this->infoblox_query, 'Infoblox_query');
  }
  
  function testInfoblox_querySave() {
  	$this->assertEqual($this->infoblox_query->get_id(), 0);
  	$this->infoblox_query->set_datetime(Infoblox_query::conv_datetime('Aug 26 04:02:16'));
  	$this->infoblox_query->set_domain_name('test.domain.com');
  	$this->infoblox_query->set_ip('10.0.0.1');
  	$this->assertTrue($this->infoblox_query->save());
  	$this->infoblox_query_id = $this->infoblox_query->get_id();
  	$this->assertTrue($this->infoblox_query_id > 0);
  	$clone = new Infoblox_query($this->infoblox_query_id);
  	$this->assertEqual($this->infoblox_query->get_id(), $clone->get_id());
  	$this->assertEqual($this->infoblox_query->get_datetime(), $clone->get_datetime());
  	$this->assertEqual($this->infoblox_query->get_domain_name(), $clone->get_domain_name());
  	$this->assertEqual($this->infoblox_query->get_ip(), $clone->get_ip());
  }
  
  function testInfoblox_querySet_datetime() {
  	$testval = 'Aug 26 04:02:16';
  	$this->infoblox_query->set_datetime($testval);
  	$this->assertEqual($this->infoblox_query->get_datetime(), $testval);
  }
  
  function testInfoblox_querySet_domain_name() {
  	$testval = 'Test.domain.name';
  	$this->infoblox_query->set_domain_name($testval);
  	$this->assertEqual($this->infoblox_query->get_domain_name(), $testval);
  }
  
  function testInfoblox_querySet_ip() {
  	$testval = '10.0.0.1';
  	$this->infoblox_query->set_ip($testval);
  	$this->assertEqual($this->infoblox_query->get_ip(), $testval);
  }

  function testInfoblox_queryDelete() {
  	$this->assertEqual($this->infoblox_query->get_id(), 0);
  	$this->infoblox_query->set_datetime('Aug 26 04:02:16');
  	$this->infoblox_query->set_domain_name('test.domain.com');
  	$this->infoblox_query->set_ip('10.0.0.1');
  	$this->assertTrue($this->infoblox_query->save());
  	$this->infoblox_query_id = $this->infoblox_query->get_id();
  	$this->assertTrue($this->infoblox_query_id > 0);
  	$this->infoblox_query->delete();
  	$this->assertEqual($this->infoblox_query->get_id(), 0);
  }
  
  
}
?>
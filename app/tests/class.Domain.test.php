<?php 

require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.Domain.php');


class TestOfDomainClass extends UnitTestCase {
	
  private $domain;
  
  private $domain_id;
  
  function setUp() {
  	$this->domain = new Domain();
  }
  
  function tearDown() {
  	$this->domain->delete();
  }
  
  function testDomainConstructor() {
  	$this->assertIsA($this->domain, 'Domain');
  }
  
  function testDomainSave() {
  	$this->assertEqual($this->domain->get_id(), 0);
  	$this->domain->set_name('test.domain.com');
  	$this->domain->set_is_malicious(true);
  	$this->domain->set_marked_malicious_datetime(date("Y-m-d H:i:s", time()));
  	$this->domain->set_service(new Malware_service);
  	$this->assertTrue($this->domain->save());
  	$this->domain_id = $this->domain->get_id();
  	$this->assertTrue($this->domain_id > 0);
  	$clone = new Domain($this->domain_id);
  	$this->assertEqual($this->domain->get_id(),$clone->get_id());
  	$this->assertEqual($this->domain->get_name(),$clone->get_name());
  	$this->assertEqual($this->domain->get_is_malicious(),$clone->get_is_malicious());
  	$this->assertEqual($this->domain->get_marked_malicious_datetime(), $clone->get_marked_malicious_datetime());
  	$this->assertEqual($this->domain->get_service()->get_id(), $clone->get_service()->get_id());
  	$this->domain->set_name('2test.domain.com');
  	$this->domain->set_is_malicious(false);
  	$this->domain->set_marked_malicious_datetime(date("Y-m-d H:i:s", time()));
  	$this->assertTrue($this->domain->save());
  	$clone = new Domain($this->domain_id);
  	$this->assertEqual($this->domain->get_id(),$clone->get_id());
  	$this->assertEqual($this->domain->get_name(),$clone->get_name());
  	$this->assertEqual($this->domain->get_is_malicious(),$clone->get_is_malicious());
  	$this->assertEqual($this->domain->get_marked_malicious_datetime(), $clone->get_marked_malicious_datetime());
  	$this->assertEqual($this->domain->get_service()->get_id(), $clone->get_service()->get_id());
  }
  
  
  function testDomainSet_name() {
  	$testval = 'Test.domain.name';
  	$this->domain->set_name($testval);
  	$this->assertEqual($this->domain->get_name(), $testval);
  }

  function testDomainDelete() {
  	$this->assertEqual($this->domain->get_id(), 0);
  	$this->domain->set_name('test.domain.com');
  	$this->assertTrue($this->domain->save());
  	$this->domain_id = $this->domain->get_id();
  	$this->assertTrue($this->domain_id > 0);
  	$this->domain->delete();
  	$this->assertEqual($this->domain->get_id(), 0);
  }
  
  function testDomainLookup_by_name() {
  	$testval = 'Test.domain.name';
  	$testval2 = 'Test.domain.name.2';
  	$this->domain->set_name($testval);
  	$this->domain->save();
  	$testobj = new Domain();
  	$testobj->lookup_by_name($testval);
  	$testobj2 = new Domain();
  	$testobj2->lookup_by_name($testval2);
  	$this->assertEqual($this->domain->get_id(), $testobj->get_id());
  	$this->assertNotEqual($this->domain->get_id(), $testobj2->get_id());
  }
}
?>
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
  	$this->assertTrue($this->domain->save());
  	$this->domain_id = $this->domain->get_id();
  	$this->assertTrue($this->domain_id > 0);
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
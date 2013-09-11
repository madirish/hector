<?php 

require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.Vuln.php');


class TestOfVulnClass extends UnitTestCase {
	
  private $vuln;
  
  private $vuln_id;
  
  function setUp() {
  	$this->vuln = new Vuln();
  }
	
  function tearDown() {
    $this->vuln->delete();
  }

  function testVulnConstructor() {
  	$this->assertIsA($this->vuln, 'Vuln');
  }
  
  function testVulnSave() {
  	$this->assertTrue($this->vuln->get_id() == 0);
  	$this->vuln->set_description('Test string');
  	$this->vuln->set_name('Test string');
  	$this->vuln->set_cve('Test string');
  	$this->vuln->set_osvdb('Test string');
  	$this->assertTrue($this->vuln->save());
  	$this->vuln_id = $this->vuln->get_id();
  	$this->assertTrue($this->vuln_id > 0);
  }
  function testSetVulnCVE() {
  	$testval = 'Test string';
  	$this->vuln->set_cve($testval);
  	$this->assertEqual($this->vuln->get_cve(), $testval);
  }
  
  function testSetVulnDescription() {
  	$testval = 'Test string';
  	$this->vuln->set_description($testval);
  	$this->assertEqual($this->vuln->get_description(), $testval);
  }
  
  function testSetVulnName() {
  	$testval = 'Test string';
  	$this->vuln->set_name($testval);
  	$this->assertEqual($this->vuln->get_name(), $testval);
  }
  
  function testSetVulnOSVDB() {
  	$testval = 'Test string';
  	$this->vuln->set_osvdb($testval);
  	$this->assertEqual($this->vuln->get_osvdb(), $testval);
  }
  
  function testVulnDelete() {
  	$id = $this->vuln_id;
  	$this->vuln = new Vuln($id);
  	$this->vuln->set_name('Test');
  	$this->assertFalse($this->vuln->delete());
  	$this->assertTrue($this->vuln->save());
  	$this->assertTrue($this->vuln->delete());
  	$this->assertTrue($this->vuln->get_id() == 0);
  }
}
?>
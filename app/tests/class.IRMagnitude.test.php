<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.IRMagnitude.php');


class TestOfIRMagnitudeClass extends UnitTestCase {
    
    function setUp() {
        $this->magnitude = new IRMagnitude();
    }
    
    function tearDown() {
        $this->magnitude->delete();
    }
    
    function testLocationClass() {
        $this->assertIsA($this->magnitude, 'IRMagnitude');
    }
    
    function testId() {
        $this->assertEqual($this->magnitude->get_id(), 0);
    }
    
    function testName() {
        $name = 'Test';
        $this->magnitude->set_name($name);
        $this->assertEqual($this->magnitude->get_name(), $name);
    }
    
    function testLevel() {
    	$this->magnitude->set_level(1);
        $this->assertEqual(1, $this->magnitude->get_level());
        $this->magnitude->set_level('bad');
        $this->assertNotEqual('bad', $this->magnitude->get_level());
    }
    
    function testGetAddAlterForm() {
        $this->assertIsA($this->magnitude->get_add_alter_form(), 'Array');
    }
    
    function testGetCollectionDefinition() {
        $this->assertIsA($this->magnitude->get_collection_definition(), 'String');
    }
    
    function testSaveDelete() {
        $this->magnitude->set_name('Test');
        $this->assertTrue($this->magnitude->save());
        $this->assertTrue($this->magnitude->get_id() > 0 );
        $this->assertTrue($this->magnitude->delete());
    }
}
?>
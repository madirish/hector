<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.IRTimeframe.php');


class TestOfIRTimeframeClass extends UnitTestCase {
    
    function setUp() {
        $this->timeframe = new IRTimeframe();
    }
    
    function tearDown() {
        $this->timeframe->delete();
    }
    
    function testLocationClass() {
        $this->assertIsA($this->timeframe, 'IRTimeframe');
    }
    
    function testId() {
        $this->assertEqual($this->timeframe->get_id(), 0);
    }
    
    function testName() {
        $name = 'Test';
        $this->timeframe->set_name($name);
        $this->assertEqual($this->timeframe->get_name(), $name);
    }
    
    function testGetAddAlterForm() {
        $this->assertIsA($this->timeframe->get_add_alter_form(), 'Array');
    }
    
    function testGetCollectionDefinition() {
        $this->assertIsA($this->timeframe->get_collection_definition(), 'String');
    }
    
    function testSaveDelete() {
        $this->timeframe->set_name('Test');
        $this->assertTrue($this->timeframe->save());
        $this->assertTrue($this->timeframe->get_id() > 0 );
        $this->assertTrue($this->timeframe->delete());
    }
}
?>
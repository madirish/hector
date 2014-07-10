<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.BulkHostAdder.php');


class TestOfBulkHostAdderClass extends UnitTestCase {
    
    private $bha;
    
    function setup() {
        $this->bha = new BulkHostAdder();
    }
    
    function testBulkHostAdderClass() {
        $this->assertIsA($this->bha, 'BulkHostAdder');
    }
    
    function testBulkHostAdderErrorMessage() {
        $this->assertEqual($this->bha->get_error(), '');
    }
    
    function testAddByIP() {
        $startip = '127.0.0.10';
        $endip = '127.0.0.5';
    	$this->assertFalse($this->bha->add_by_IP($startip, $endip));
        $this->assertEqual($this->bha->get_error(), 'Start IP must be less than end IP.');
    }
}
?>
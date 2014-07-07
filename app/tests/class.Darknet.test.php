<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.Darknet.php');


class TestOfDarknetClass extends UnitTestCase {
    
    private $dnet;
    
    function setup() {
        $this->dnet = new Darknet();
    }
  
    function tearDown() {
        $this->dnet->delete();
    }
    
    function testDarknetClass() {
        $this->assertIsA($this->dnet, 'Darknet');
        $this->assertEqual($this->dnet->get_id(), '');
    }
    
    function testDarknetCountryCode(){
    	$badValue = "a12b";
        $goodValue = "US";
        $this->dnet->set_country_code($badValue);
        $this->assertNotEqual($badValue, $this->dnet->get_country_code());
        $this->dnet->set_country_code($goodValue);
        $this->assertNotEqual('', $this->dnet->get_country_code());
        $this->assertEqual($goodValue, $this->dnet->get_country_code());
    }
    
    function testDarknetDstIP() {
    	
    }
}
?>
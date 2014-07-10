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
    
    function testDstPort() {
        $port = 22;
        $bad = 'foo';
    	$this->dnet->set_dst_port($port);
        $this->assertEqual($port, $this->dnet->get_dst_port());
        $this->dnet->set_dst_port($bad);
        $this->assertNotEqual($bad, $this->dnet->get_dst_port());
    }
    
    function testSrcPort() {
        $port = 22;
        $bad = 'foo';
        $this->dnet->set_src_port($port);
        $this->assertEqual($port, $this->dnet->get_src_port());
        $this->dnet->set_src_port($bad);
        $this->assertNotEqual($bad, $this->dnet->get_src_port());
    }
    
    function testSrcDstIP() {
    	$ip = '127.0.0.1';
        $bad = '127.0.5';
        $this->dnet->set_src_ip($ip);
        $this->assertEqual($this->dnet->get_src_ip(), $ip);
        $this->dnet->set_dst_ip($ip);
        $this->assertEqual($ip, $this->dnet->get_dst_ip());
        $this->dnet->set_src_ip($bad);
        $this->assertNotEqual($bad, $this->dnet->get_src_ip());
        $this->dnet->set_dst_ip($bad);
        $this->assertNotEqual($bad, $this->dnet->get_dst_ip);
    }
    
    function testCollectionByCountry() {
    	$this->assertTrue(is_string($this->dnet->get_collection_by_country('US')));
    }
    function testReceived() {
    	$time = '2012-03-12 13:45:50';
        $this->dnet->set_received_at($time);
        $this->assertEqual($time, $this->dnet->get_received_at());
        $bad = 'foo';
        $this->dnet->set_received_at($bad);
        $this->assertNotEqual($bad, $this->dnet->get_received_at());
    }
    
    function testProto() {
    	$proto = 'tcp';
        $bad = 'icmplay';
        $this->dnet->set_proto($proto);
        $this->assertEqual($proto, $this->dnet->get_proto());
        $this->dnet->set_proto($bad);
        $this->assertNotEqual($bad, $this->dnet->get_proto());
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
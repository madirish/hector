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
        $this->assertNotIdentical($bad, $this->dnet->get_dst_port());
    }
    
    function testSrcPort() {
        $port = 22;
        $bad = 'foo';
        $this->dnet->set_src_port($port);
        $this->assertEqual($port, $this->dnet->get_src_port());
        $this->dnet->set_src_port($bad);
        $this->assertNotIdentical($bad, $this->dnet->get_src_port());
    }
    
    function testSrcDstIP() {
    	$ip = '192.168.57.2';
        $bad = '127.0.5';
        // Test for source
        $this->assertFalse($this->dnet->set_src_ip($ip));
        $this->assertFalse($this->dnet->set_src_ip($bad));
        $this->assertTrue($this->dnet->set_src_ip(ip2long($ip)));
        $this->assertEqual($this->dnet->get_src_ip(), ip2long($ip));
        // Test for dest
        $this->assertFalse($this->dnet->set_dst_ip($ip));
        $this->assertFalse($this->dnet->set_dst_ip($bad));
        $this->dnet->set_dst_ip(ip2long($ip));
        $this->assertEqual(ip2long($ip), $this->dnet->get_dst_ip());
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
    
    function testSave() {
        $this->assertTrue($this->dnet->set_country_code('US'));
        $this->assertTrue($this->dnet->set_src_ip(ip2long('127.0.0.1')));
        $this->assertTrue($this->dnet->set_dst_ip(ip2long('127.0.0.1')));
        $this->assertTrue($this->dnet->set_received_at('2010-01-01 13:12:44'));
        $this->assertTrue($this->dnet->set_proto('tcp'));
        $this->assertTrue($this->dnet->set_src_port(0));
        $this->assertTrue($this->dnet->set_dst_port(0));
        $this->assertTrue($this->dnet->save());	
    }
    
    function testConstructBySyslogString() {
    	$string = 'May 15 04:19:58 servername kernel: iptables IN=eth0 OUT= MAC=00:1a:4b:dc:c3:68:88:43:e1:2f:45:1b:08:00 SRC=222.229.21.1 DST=208.88.12.61 LEN=40 TOS=0x00 PREC=0x00 TTL=241 ID=54321 PROTO=TCP SPT=46797 DPT=21320 WINDOW=65535 RES=0x00 SYN URGP=0 ';
    	$this->dnet->construct_by_syslog_string($string);
    	$this->assertNotIdentical(ip2long('127.0.0.1'), $this->dnet->get_src_ip());
    	$this->assertNotIdentical(ip2long('192.168.12.1'), $this->dnet->get_src_ip());
    	$this->assertIdentical(ip2long('222.229.21.1'), $this->dnet->get_src_ip());
    	$this->assertIdentical(ip2long('208.88.12.61'), $this->dnet->get_dst_ip());
    	$this->assertIdentical(46797, $this->dnet->get_src_port());
    	$this->assertIdentical(21320, $this->dnet->get_dst_port());
    	$this->assertIdentical('tcp', $this->dnet->get_proto());
    	$this->assertTrue($this->dnet->save());
		/* print_r("\nThe source IP is: " . ip2long('222.229.21.1') . "\n");
		print_r("\nThe country code is: " . $this->dnet->get_country_code() . "\n");
    	print_r($this->dnet); */
    }
}
?>
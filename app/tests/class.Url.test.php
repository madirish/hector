<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.Url.php');

class TestOfUrlClass extends UnitTestCase {
	
	function setUp(){
		$this->url = new Url();
	}
	
	function tearDown(){
		unset($this->url);
	}
	
	function testUrlClass() {
		$this->assertIsA($this->url,'Url');
	}
	
	function testUrlId() {
		$this->assertEqual($this->url->get_id(),0);
	}
	
	function testHost_id(){
		$good = 12;
		$bad = "godzilla";
		$this->url->set_host_id($good);
		$this->assertIdentical($good,$this->url->get_host_id());
		$this->url->set_host_id($bad);
		$this->assertNotIdentical($bad,$this->url->get_host_id());
	}
	
	function testUrl() {
		$good = "http://www.google.com";
		$bad = "malicious.input";
		$this->url->set_url($good);
		$this->assertEqual($good,$this->url->get_url());
		$this->url->set_url($bad);
		$this->assertNotEqual($bad,$this->url->get_url());
	}
	
	function testScreenshot() {
		$good = "http___128_91_40_49_1395722165.png";
		$bad = "<script>alert('xss')</script>";
		$this->url->set_screenshot($good);
		$this->assertEqual($good,$this->url->get_screenshot());
		$this->url->set_screenshot($bad);
		$this->assertNotEqual($bad,$this->url->get_screenshot());
	}
	
	function testGet_object_as_array(){
		$this->assertIsA($this->url->get_object_as_array(),'array');
	}
	
	function testGet_collection_definition(){
		$good = 'SELECT u.url_id from url u WHERE u.url_id > 0 ORDER BY u.host_id asc';
		$this->assertEqual($good,$this->url->get_collection_definition());
	}
	
	function testGet_host_name(){
		$this->assertEqual('',$this->url->get_host_name());
		$this->url->set_host_id(1);
		$this->assertNotEqual('',$this->url->get_host_name());
	}
	
	
	
	
}


?>
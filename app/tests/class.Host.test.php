<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.Host.php');


class TestOfHostClass extends UnitTestCase {
	
	function setUp() {
		$this->host = new Host();
	}
	
	function tearDown() {
		$this->host->delete();
	}
	
	function testHostClass() {
		$this->assertIsA($this->host, 'Host');
	}
	
	function testId() {
		$this->assertEqual($this->host->get_id(), 0);
	}
	
	function testName() {
		$name = 'Test';
		$this->host->set_name($name);
		$this->assertEqual($this->host->get_name(), 'test');
	}
	
	function testIP() {
		$ip = '127.0.0.1';
		$this->host->set_ip($ip);
		$this->assertEqual($this->host->get_ip(), $ip);
	}
	
	function testAltHostnames() {
		$althostname = 'foo.example.com';
		$this->host->set_alt_hostname($althostname);
		$altnames = $this->host->get_alt_hostnames();
		$this->assertIsA($altnames, 'Array');
		$this->assertEqual($altnames[0], $althostname);
	}
	
	function testOS() {
		$os = 'Test';
		$this->host->set_os($os);
		$this->assertEqual($this->host->get_os(), $os);
    $this->assertFalse($this->host->get_os() == 'ThisIsNotTheOS');
	}
    
  function testTechnical() {
  	$tech = 'Joe Support Dude';
    $this->host->set_technical($tech);
    $this->assertEqual($this->host->get_technical(), $tech);
    $this->assertNotEqual($this->host->get_technical(), '');
  }
    
  function testLink() {
    $link = "http://www.example.com";
    $this->host->set_link($link);
    $this->assertEqual($this->host->get_link(), $link);
    $this->assertFalse($this->host->get_link() == 'http://ThisIsNotTheLink.com');
    $this->assertFalse($this->host->set_link('ThisIsNotAValidLink'));
  }
  
  function testHostGroups() {
    $hg = array(1,2,3);
    $this->host->set_host_group_ids($hg);
    $this->assertEqual($this->host->get_host_group_ids(), $hg);	
    $this->assertEqual(count($this->host->get_host_group_ids()), 3);
  }
  
  function testTags() {
    $tag = array(1,2,3);
    $this->host->set_tag_ids($tag);
    $this->assertEqual($this->host->get_tag_ids(), $tag); 
    $this->assertEqual(count($this->host->get_tag_ids()), 3);
  }
  
  function testNote() {
  	$note = 'This is just a test note.';
    $this->host->set_note($note);
    $this->assertEqual($this->host->get_note(), $note);
    $htmlnote = '<script>alert("xss note");</script>';
    $this->host->set_note($htmlnote);
    $this->assertNotEqual($this->host->get_note(), $htmlnote);
  }
  
  function testPolicy() {
  	$this->host->set_policy(TRUE);
    $this->assertTrue($this->host->get_policy());
    $this->host->set_policy(FALSE);
    $this->assertFalse($this->host->get_policy());
  }
  
  function testSponsor() {
  	$sponsor = 'Edward Test';
    $this->host->set_sponsor($sponsor);
    $this->assertEqual($this->host->get_sponsor(), $sponsor);
    $this->assertNotEqual($this->host->get_sponsor(), '');
  }
  
  function testLocation() {
  	$this->location = new Location();
    $this->location->set_name('Test');
    $this->location->save();
    $id = $this->location->get_id();
    $this->assertTrue($this->host->set_location_id($id));
    $this->assertEqual($this->host->get_location_id(), $id);
    $this->assertEqual($this->host->get_location_name(), 'Test');
    $this->location->delete();
  }
	
	function testGetAddAlterForm() {
		$this->assertIsA($this->host->get_add_alter_form(), 'Array');
	}
	
	function testGetCollectionDefinition() {
		$this->assertIsA($this->host->get_collection_definition(), 'String');
	}
	
	function testSaveDelete() {
		$this->host->set_name('Test');
		$this->host->set_ip('10.255.255.250');
		$this->assertTrue($this->host->save());
		$this->assertTrue($this->host->get_id() > 0 );
		$this->assertTrue($this->host->delete());
	}
}
?>
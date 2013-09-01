<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.Feed.php');


class TestOfFeedClass extends UnitTestCase {
	
	function setUp() {
		$this->feed = new Feed();
	}
  
  function tearDown() {
  	$this->feed->delete();
  }
	
	function testFeedClass() {
		$this->assertIsA($this->feed, 'Feed');
	}
	
	function testId() {
		$this->assertEqual($this->feed->get_id(), 0);
	}
	
	function testName() {
		$name = 'Test';
		$this->feed->set_name($name);
		$this->assertEqual($this->feed->get_name(), $name);
	}
	
	function testURL() {
		$url = 'http://www.example.com/rss.xml';
		$this->feed->set_url($url);
		$this->assertEqual($this->feed->get_url(), $url);
	}
	
	function testGetAddAlterForm() {
		$this->assertIsA($this->feed->get_add_alter_form(), 'Array');
	}
	
	function testGetCollectionDefinition() {
		$this->assertIsA($this->feed->get_collection_definition(), 'String');
	}
	
	function testGetDisplays() {
		$this->assertIsA($this->feed->get_displays(), 'Array');
	}
	
	function testSaveDelete() {
		$this->feed->set_name('Test');
		$this->assertTrue($this->feed->save());
		$this->assertTrue($this->feed->get_id() > 0 );
		$this->assertNull($this->feed->delete());
	}
}
?>
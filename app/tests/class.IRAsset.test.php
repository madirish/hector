<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.IRAsset.php');


class TestOfIRAssetClass extends UnitTestCase {
	
	function setUp() {
		$this->asset = new IRAsset();
	}
	
	function tearDown() {
		$this->asset->delete();
	}
	
	function testLocationClass() {
		$this->assertIsA($this->asset, 'IRAsset');
	}
	
	function testId() {
		$this->assertEqual($this->asset->get_id(), 0);
	}
	
	function testName() {
		$name = 'Test';
		$this->asset->set_name($name);
		$this->assertEqual($this->asset->get_name(), $name);
	}
	
	function testGetAddAlterForm() {
		$this->assertIsA($this->asset->get_add_alter_form(), 'Array');
	}
	
	function testGetCollectionDefinition() {
		$this->assertIsA($this->asset->get_collection_definition(), 'String');
	}
	
	function testSaveDelete() {
		$this->asset->set_name('Test');
		$this->assertTrue($this->asset->save());
		$this->assertTrue($this->asset->get_id() > 0 );
		$this->assertTrue($this->asset->delete());
	}
}
?>
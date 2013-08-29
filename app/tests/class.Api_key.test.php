<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.Api_key.php');


class TestOfApi_keyClass extends UnitTestCase {
	
	function setUp() {
		$this->api_key = new Api_key();
	}
	
	function tearDown() {
		$this->api_key->delete();
	}
	
	function testApi_keyClass() {
		$this->assertIsA($this->api_key, 'Api_key');
	}
	
	function testId() {
		$this->assertEqual($this->api_key->get_id(), 0);
	}
	
	function testHolderName() {
		$name = 'Test';
		$this->api_key->set_holder_name($name);
		$this->assertEqual($this->api_key->get_holder_name(), $name);
	}
	
	function testHolderAffiliation() {
		$aff = 'Test';
		$this->api_key->set_holder_affiliation($aff);
		$this->assertEqual($this->api_key->get_holder_affiliation(), $aff);
	}
	
	function testKeyResoursce() {
		$res = 'http://127.0.0.1/feed';
		$this->api_key->set_key_resource($res);
		$this->assertEqual($this->api_key->get_key_resource(), $res);
	}
	
	function testHolderEmail() {
		$email = 'test@example.com';
		$this->assertTrue($this->api_key->set_holder_email($email));
		$this->assertEqual($this->api_key->get_holder_email(), $email);
		$this->assertFalse($this->api_key->set_holder_email('ImAnIllegalEmail.com@com#p'));
	}
	
	function testGetAddAlterForm() {
		$this->assertIsA($this->api_key->get_add_alter_form(), 'Array');
	}
	
	function testGetCollectionDefinition() {
		$this->assertIsA($this->api_key->get_collection_definition(), 'String');
	}
	
	function testSaveDelete() {
		$this->api_key->set_holder_name('Test');
		$this->api_key->set_key_resource('Test');
		$this->api_key->set_holder_email('test@example.com');
		$this->api_key->set_holder_affiliation('Test');
		$this->assertTrue($this->api_key->save());
		$this->assertTrue($this->api_key->get_id() > 0 );
		$this->assertTrue($this->api_key->delete());
	}
	
	function testDelete() {
		$this->assertFalse($this->api_key->delete());
	}
	
	function testValidate() {
		$this->api_key->set_holder_name('Test');
		$this->api_key->set_key_resource('Test');
		$this->api_key->set_holder_email('test@example.com');
		$this->api_key->set_holder_affiliation('Test');
		$this->assertTrue($this->api_key->save());
		$key = $this->api_key->get_key_value();
		$this->assertTrue($this->api_key->validate($key));
		$this->assertFalse($this->api_key->validate('ThisIsAnIllegalKeyValue'));
	}
}
?>
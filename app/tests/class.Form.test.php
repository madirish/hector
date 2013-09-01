<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.Form.php');


class TestOfFormClass extends UnitTestCase {
	
	function setUp() {
		$this->form = new Form();
	}
  
  function tearDown() {
  	$this->form->delete();
  }
	
	function testformClass() {
		$this->assertIsA($this->form, 'Form');
	}
	
	function testId() {
		$this->assertEqual($this->form->get_id(), 0);
	}
	
	function testName() {
		$name = 'Test';
		$this->form->set_name($name);
		$this->assertEqual($this->form->get_name(), $name);
	}
	
	function testSaveDelete() {
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$this->form->set_name('Test');
		$this->assertTrue($this->form->save());
		$this->assertTrue($this->form->get_id() > 0 );
		$this->assertNull($this->form->delete());
	}
	
	function testValidate() {
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$name = 'Test form';
		$ip = '127.0.0.1';
		$this->form->set_name($name);
		$token = $this->form->get_token();
		$this->assertTrue($this->form->save());
		$this->assertTrue($this->form->validate($name, $token, $ip));
		$this->assertFalse($this->form->validate('foo', $token, $ip));
		$this->assertFalse($this->form->validate($name, '12345', $ip));
		$this->assertFalse($this->form->validate($name, $token, '10.0.0.1'));
	}
}
?>
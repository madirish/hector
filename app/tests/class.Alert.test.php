<?php 

require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.Alert.php');

class TestOfAlertClass extends UnitTestCase {
  function testAlertConstructor() {
  	$alert = new Alert();
  	$this->assertIsA($alert, 'Alert');
  }
}
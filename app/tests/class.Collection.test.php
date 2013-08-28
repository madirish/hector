<?php 

require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.Collection.php');
require_once(dirname(__FILE__) . '/../lib/class.Alert.php');


class TestOfCollectionClass extends UnitTestCase {
	
	function testCollectionOfAlerts() {
		$alert1 = new Alert();
	  	$alert1->set_host_id(-1);
	  	$alert1->set_string('test string');
	  	$alert1->save();
	  	$alert2 = new Alert();
	  	$alert2->set_host_id(-1);
	  	$alert2->set_string('test string');
	  	$alert2->save();
	  	$alert3 = new Alert();
	  	$alert3->set_host_id(-1);
	  	$alert3->set_string('test string');
	  	$alert3->save();
	  	$alerts = new Collection('Alert',' AND host_id = -1');
	  	$this->assertIsA($alerts, 'Collection');
	  	foreach($alerts->members as $alert) {
	  		$this->assertIsA($alert, 'Alert');
	  		$this->assertTrue($alert->get_host_id() == -1);
	  	}
	  	$alert1->delete();
	  	$alert2->delete();
	  	$alert3->delete();
	}
}
?>
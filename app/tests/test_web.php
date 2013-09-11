<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../software/simpletest/web_tester.php');

class TestOfWebInterface extends WebTestCase {
    function testLogin() {
        $this->get('http://127.0.0.1/hector');
        $this->assertText('Please log in');
        $this->setField('username', 'administrator');
        $this->setField('password', 'notThePassword');
        $this->click("Log in");
        $this->assertText('Please log in');
        $this->assertText('Sorry, unrecognized credentials.');
        $this->setField('username', 'administrator');
        $this->setField('password', 'password');
        $this->click("Log in");
        $this->assertText('Asset Management');
    }
}
?>
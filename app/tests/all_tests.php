<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
// Require each test file
require_once('class_testfiles.php');

class AllTests extends TestSuite {
    function __construct() {
        parent::__construct();
        $this->add(new TestOfTestFiles());
    }
}
?>

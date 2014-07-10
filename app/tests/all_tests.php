<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
// Require each test file
foreach (scandir(dirname(__FILE__)) as $file) {
	$extension = explode('.', $file);
    if (array_pop($extension) == 'php') {
    	require_once($file);
    }
}

$GLOBALS['approot'] = '/opt/hector/app/';

class AllTests extends TestSuite {
    function __construct() {
        parent::__construct();
    }
}
?>

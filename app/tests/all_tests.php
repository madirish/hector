<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
// Require each test file
require_once('class_testfiles.php');
require_once('class.Alert.test.php');
require_once('class.Collection.test.php');
require_once('class.Config.test.php');
require_once('class.Db.test.php');
require_once('class.Dblog.test.php');
require_once('class.Feed.test.php');

require_once('class.Tag.test.php');

$GLOBALS['approot'] = '/opt/hector/app/';


class AllTests extends TestSuite {
    function __construct() {
        parent::__construct();
        $this->add(new TestOfTestFiles());
        $this->add(new TestOfAlertClass());
        $this->add(new TestOfCollectionClass());
        $this->add(new TestOfConfigClass());
        $this->add(new TestOfDbClass());
        $this->add(new TestOfDblogClass());
        $this->add(new TestOfFeedClass());
        
        $this->add(new TestOfTagClass());
    }
}
?>

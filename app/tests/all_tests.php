<?php
require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
// Require each test file
require_once('class_testfiles.php');
require_once('class.Api_key.test.php');
require_once('class.Alert.test.php');
require_once('class.Collection.test.php');
require_once('class.Config.test.php');
require_once('class.Db.test.php');
require_once('class.Dblog.test.php');
require_once('class.Feed.test.php');
require_once('class.Form.test.php');
//require_once('class.Host.test.php');
require_once('class.Host_group.test.php');
require_once('class.Location.test.php');
require_once('class.Log.test.php');
require_once('class.Maleable_Object.test.php');
require_once('class.Nmap_result.test.php');
require_once('class.Supportgroup.test.php');
require_once('class.Tag.test.php');
require_once('class.Vuln.test.php');

$GLOBALS['approot'] = '/opt/hector/app/';

class AllTests extends TestSuite {
    function __construct() {
        parent::__construct();
        $this->add(new TestOfTestFiles());
        $this->add(new TestOfAlertClass());
        $this->add(new TestOfApi_keyClass());
        $this->add(new TestOfCollectionClass());
        $this->add(new TestOfConfigClass());
        $this->add(new TestOfDbClass());
        $this->add(new TestOfDblogClass());
        $this->add(new TestOfFeedClass());
        $this->add(new TestOfFormClass());
        //$this->add(new TestOfHostClass());
        $this->add(new TestOfHost_groupClass());
        $this->add(new TestOfLocationClass());
        $this->add(new TestOfLogClass());
        $this->add(new TestOfMaleable_ObjectClass());
        $this->add(new TestOfNmap_resultClass());
        $this->add(new TestOfSupportgroupClass());
        $this->add(new TestOfTagClass());
        $this->add(new TestOfVulnClass());
    }
}
?>

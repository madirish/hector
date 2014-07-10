<?php 

require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');

class TestOfTestFiles extends UnitTestCase {
  function testThatTestFilesExist() {
    $libdir = dirname(__FILE__) . '/../lib';
    if ($dirhandle = opendir($libdir)) {
      while (false !== ($entry = readdir($dirhandle))) {
        if ($entry !== '.' && $entry !== '..') {
          $testfile = dirname(__FILE__) . '/' . substr($entry,0,-3) . 'test.php';
          if (! $this->assertTrue(file_exists($testfile))) {
            print(" *** Test missing for $entry, should be  " . dirname(__FILE__) . '/' . substr($entry,0,-3) . 'test.php' . "\n");
          }
          else {
          	 // Test that the test
            $classnamearray = explode('.', $entry);
            $classname = ucfirst($classnamearray[1]);
            include_once($libdir . '/' . $entry);
            $methods = get_class_methods($classname);
            foreach ($methods as $methodname) {
                // Ignore built-ins or inheirited methods
                if ($methodname == "__construct" || $methodname == "process_form" || $methodname == "get_displays" || $methodname == "get_label") continue;
                if (! $this->assertTrue(strpos(file_get_contents($testfile),$methodname) > 1)) {
                    print "*** Missing test for $classname::$methodname in $testfile !";
                }   
            }
          }
        }
      }
    }
    else {
     print("Failed to open directory $libdir");
    }
  }
}

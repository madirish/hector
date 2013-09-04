<?php 

require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');

class TestOfTestFiles extends UnitTestCase {
  function testThatTestFilesExist() {
    $libdir = dirname(__FILE__) . '/../lib';
    if ($dirhandle = opendir($libdir)) {
      while (false !== ($entry = readdir($dirhandle))) {
        if ($entry !== '.' && $entry !== '..') {
          if (! $this->assertTrue(file_exists(dirname(__FILE__) . '/' . substr($entry,0,-3) . 'test.php'))) {
            print("Test missing for $entry, should be  " . dirname(__FILE__) . '/' . substr($entry,0,-3) . 'test.php' . "\n");
          }
        }
      }
    }
    else {
     print("Failed to open directory $libdir");
    }
  }
}

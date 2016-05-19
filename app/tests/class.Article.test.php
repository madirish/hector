<?php 

require_once(dirname(__FILE__) . '/../software/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../lib/class.Article.php');


class TestOfArticleClass extends UnitTestCase {
    
  private $article;
  
  private $article_id;
  
  function setUp() {
    $this->article = new Article();
  }
  
  function tearDown() {
    $this->article->delete();
  }
  
  function testArticleDate() {
  	$date = '2013-04-09';
    $this->article->set_date($date);
    $this->assertEqual($date, $this->article->get_date());
  }

  function testArticleConstructor() {
    $this->assertIsA($this->article, 'Article');
  }
  
  function testArticleSave() {
    $this->assertEqual($this->article->get_id(), 0);
    $this->article->set_title('Test Title');
    $this->article->set_teaser('Test abstract');
    $this->article->set_body('Test body');
    $this->assertTrue($this->article->save());
    $this->article_id = $this->article->get_id();
    $this->assertTrue($this->article_id > 0);
  }
  
  function testSetArticleTitle() {
    $this->article->set_title('Title');
    $this->assertTrue($this->article->get_title() == 'Title');
    $this->article->set_title('<b>Title</b>');
    $this->assertTrue($this->article->get_title() == 'Title');
  }
  
  function testTagIDs() {
  	$tag_ids = array(1,2,3,4);
    $bad_tagids = array(1,2,3,'foo');
    $bad = 'foo';
    $this->assertFalse($this->article->set_tag_ids($bad));
    $this->assertTrue($this->article->set_tag_ids($tag_ids));
    $this->assertEqual($tag_ids, $this->article->get_tag_ids());
    $tag_ids[] = 5;
    $this->assertTrue($this->article->add_tag_id(5));
    $this->assertEqual($tag_ids, $this->article->get_tag_ids());
    $this->article->set_tag_ids($bad_tagids);
    $article_tag_ids_array = $this->article->get_tag_ids();
    $this->assertNotIdentical($bad_tagids[3], $article_tag_ids_array[3]);
  }
  
  function testURL() {
  	$url = 'http://localhost';
    $bad = 'foo';
    $evilurl = 'http://example.com/?id=<script>alert("xss");</script>';
    $this->assertTrue($this->article->set_url($url));
    $this->assertEqual($url, $this->article->get_url());
    $this->assertEqual('<a href="http://localhost">http://localhost</a>', $this->article->get_linked_url());
    $this->assertFalse($this->article->set_url($bad));
    $this->assertTrue($this->article->set_url($evilurl));
    $this->assertNotEqual($evilurl, $this->article->get_url());
  }
  
  function testAddAlterForm() {
  	$this->assertTrue(is_array($this->article->get_add_alter_form()));
  }
  
  
  function testTeaser() {
  	$teaser = 'foo';
    $bad = '<script>alert("xss");</script>';
    $this->assertTrue($this->article->set_teaser($teaser));
    $this->assertEqual($teaser, $this->article->get_teaser());
    $this->article->set_teaser($bad);
    // Need to update how we handle XSS As HTML for display is necessary
    // $this->assertFalse($this->article->get_teaser(), $bad);
  }
  
  function testSetArticleBody() {
    $this->article->set_body('Body string');
    $this->assertTrue($this->article->get_body() == 'Body string');
    $this->article->set_body('<Body string>');
    //$this->assertTrue($this->article->get_body() == '&lt;Body string&gt;');
  }
  
  function testArticleDelete() {
    $this->article->set_title('Test');
    $this->article->set_body('Test string');
    $this->assertTrue($this->article->save());
    $id = $this->article->get_id();
    $this->assertTrue($id > 0);
    $this->assertTrue($this->article->delete());
  }
}
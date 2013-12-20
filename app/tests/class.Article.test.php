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
    $this->article->set_title('<Title>');
    $this->assertTrue($this->article->get_title() == '&lt;Title&gt;');
  }
  
  function testSetArticleBody() {
    $this->article->set_body('Body string');
    $this->assertTrue($this->article->get_body() == 'Body string');
    $this->article->set_body('<Body string>');
    $this->assertTrue($this->article->get_body() == '&lt;Body string&gt;');
  }
  
  function testArticleDelete() {
    $this->article->set_title('Test');
    $this->article->set_body('Test string');
    $this->assertTrue($this->article->save());
    $id = $this->article->get_id();
    $this->assertTrue($id > 0);
    $this->article->delete();
    $this->assertEqual($this->article->get_id(), 0);
  }
}
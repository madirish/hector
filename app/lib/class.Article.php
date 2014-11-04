<?php
/**
 * HECTOR - class.Article.php
 *
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 */

/**
 * Error reporting
 */
error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/* user defined includes */
require_once('class.Config.php');
require_once('class.Db.php');
require_once('class.Log.php');
require_once('class.Collection.php');
require_once('interface.Maleable_Object_Interface.php');
require_once('class.Maleable_Object.php');

/**
 * Articles are open source news pieces added manually or
 * imported via RSS
 *
 * @package HECTOR
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 */
class Article extends Maleable_Object implements Maleable_Object_Interface {


    // --- ATTRIBUTES ---
    /**
     * Instance of the Db
     * 
     * @access private
     * @var Db An instance of the Db
     */
    private $db = null;
    
    /**
     * Instance of the Log
     * 
     * @access private
     * @var Log An instance of the Log
     */
    private $log = null;

    /**
     * Unique ID from the data layer
     *
     * @access protected
     * @var int Unique id
     */
    protected $id = null;

    /**
     * Article title
     * 
     * @access private
     * @var String The title of the article
     */
    private $title;

    /**
     * Article dateline
     * 
     * @access private
     * @var Date The date of publication
     */
    private $date;

    /**
     * Article url
     * 
     * @access private
     * @var String The url of the article
     */
    private $url;

    /**
     * Article body
     * 
     * @access private
     * @var String The body text of the article
     */
    private $body;

    /**
     * Article teaser
     * 
     * @access private
     * @var String The teaser or abstract of the article
     */
    private $teaser;

    /**
     * Array of associate Tag ids
     * 
     * @access private
     * @var Array An array of Tag ids associated with this article
     */
    private $tag_ids = array();

    // --- OPERATIONS ---

    /**
     * Construct a new blank Article or instantiate one
     * from the data layer based on ID
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param  Int The unique ID of the Article
     * @return void
     */
    public function __construct($id = '') {
        $this->db = Db::get_instance();
        $this->log = Log::get_instance();
        if ($id != '') {
            $sql = array(
                'SELECT * FROM article WHERE article_id = ?i',
                $id
            );
            $result = $this->db->fetch_object_array($sql);
            /**
             * There may not be a result, creating a new object
             * without a valid ID can be used to verify ID values
             */
            if (count($result) == 1 && isset($result[0]->article_id)) {
                $this->set_id($result[0]->article_id);
                $this->set_title($result[0]->article_title);
                $this->set_date($result[0]->article_date);
                $this->set_teaser($result[0]->article_teaser);
                $this->set_body($result[0]->article_body);
                $this->set_url($result[0]->article_url);
            }
            $sql = array('SELECT tag_id FROM article_x_tag WHERE article_id = ?i',$id);
            $result = $this->db->fetch_object_array($sql);
             if (count($result) > 0) {
             	require_once('class.Tag.php');
             	foreach ($result as $item) {
             		$this->add_tag_id($item->tag_id);
             	}
             }
        }
    }


    /**
     * Add a tag to the tag_ids attribute array
     *
     * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @return Boolean False if something goes awry
     */
	public function add_tag_id($id) {
		$this->tag_ids[] = intval($id);
		return ($id > 0) ? TRUE : FALSE;
	}

    /**
     * Delete the record from the database
     *
     * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @return Boolean False if something goes awry
     */
    public function delete() {
        $retval = FALSE;
        if ($this->id > 0 ) {
            // Delete an existing record
            $sql = array(
                'DELETE FROM article WHERE article_id = \'?i\'',
                $this->get_id()
            );
            $retval = $this->db->iud_sql($sql);
        }
        return (bool) $retval;
    }

    /**
     * This is a functional method designed to return
     * the form associated with altering an article.
     * 
     * @access public
     * @return Array The array for the default CRUD template.
     */
    public function get_add_alter_form() {
		// get the Tags array
		$tags = array();
		$collection = new Collection('Tag');
		if (is_array($collection->members)) {
			foreach ($collection->members as $element) {
				$tags[$element->get_id()]=$element->get_name();
			}
		}
        return array (
            array('label'=>'Title',
                    'type'=>'text',
                    'name'=>'title',
                    'value_function'=>'get_title',
                    'process_callback'=>'set_title'),
			array('label'=>'Date',
                    'type'=>'date',
                    'name'=>'date',
                    'value_function'=>'get_date',
                    'process_callback'=>'set_date'),
			array('label'=>'URL',
                    'type'=>'text',
                    'name'=>'url',
                    'value_function'=>'get_url',
                    'process_callback'=>'set_url'),
			array('label'=>'Teaser',
                    'type'=>'textarea',
                    'name'=>'teaser',
                    'value_function'=>'get_teaser',
                    'process_callback'=>'set_teaser'),
			array('label'=>'body',
                    'type'=>'textarea',
                    'name'=>'body',
                    'value_function'=>'get_body',
                    'process_callback'=>'set_body'),
			array('label'=>'Tags',
					'name'=>'tags[]',
					'type'=>'checkbox',
					'options'=>$tags,
					'value_function'=>'get_tag_ids',
					'process_callback'=>'set_tag_ids'),
        );
    }

    /**
     * The HTML safe body of the Article
     * 
     * @access public
     * @return String The HTML display safe body of the Article.
     */
    public function get_body() {
        global $approot;
        require_once($approot . 'software/htmlpurifier/library/HTMLPurifier.auto.php');
        $purifier = new HTMLPurifier();
        return $purifier->purify($this->body);
    }

    /**
     *  This function directly supports the Collection class.
     *
     * @return String SQL select string
     */
    public function get_collection_definition($filter = '', $orderby = '') {
        $query_args = array();
        $sql = 'SELECT a.article_id FROM article a WHERE a.article_id > 0';
        if ($filter != '' && is_array($filter))  {
            $sql .= ' ' . array_shift($filter);
            $sql = $this->db->parse_query(array($sql, $filter));
        }
        if ($filter != '' && ! is_array($filter))  {
            $sql .= ' ' . $filter . ' ';
        }
        if ($orderby != '') {
            $sql .= ' ' . $orderby;
        }
        else if ($orderby == '') {
            $sql .= ' ORDER BY a.article_date desc';
        }
        return $sql;
    }

    /**
     * The date of the Article
     * 
     * @access public
     * @return String The date of the Article.
     */
    public function get_date() {
        return htmlspecialchars($this->date);
    }
    
    /**
     * The formatted date of the Article
     * 
     * @access public
     * @return The html safe formated date of the article
     */
    public function get_date_readable(){
    	$date = new DateTime($this->get_date());
    	return $date->format('M d, Y');
    	
    }
    
    public function get_details() {
    	$output = array();
    	$output['id'] = $this->get_id();
    	$output['title'] = $this->get_title();
    	$output['teaser'] = $this->get_teaser();
    	$output['body'] = $this->get_body();
    	$output['date'] = $this->get_date();
    	$output['link'] = $this->get_linked_url();
    	$output['tags'] = array();
    	foreach ($this->get_tag_ids() as $tag_id) {
            require_once('class.Tag.php');
    		$output['tags'][] = new Tag($tag_id);
    	}
    	return $output;
    }

    /**
     * Get the displays for the default details template
     * 
     * @return Array Dispalays for default template
     */
    public function get_displays() {
        return array( 'Date'=>'get_date', 'Title'=>'get_linked_title', 'URL'=>'get_linked_url', 'Teaser'=>'get_teaser');
    }

    /**
     * Get the unique ID for the object
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @return Int The unique ID of the object
     */
    public function get_id() {
       return intval($this->id);
    } 
      
    /**
     * Return the printable string use for the object in interfaces
     *
     * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @return String The printable string of the object name
     */
    public function get_label() {
        return 'Article';
    } 

    /**
     * The HTML linked title of the Article
     * 
     * @access public
     * @return String The HTML linked safe title of the Article.
     */
    public function get_linked_title() {
        return '<a href="?action=details&object=article&id=' . $this->id . '">' . strip_tags($this->title) . '</a>';
    }

    /**
     * The HTML linked url of the Article
     * 
     * @access public
     * @return String The HTML linked safe url of the Article.
     */
    public function get_linked_url() {
        return '<a href="' . htmlentities($this->url) . '">' . htmlentities($this->url) . '</a>';
    }

    /**
     * Array of tag_ids for tags this article is tagged with
     * 
     * @access public
     * @return Array An array of integer tag_id values
     */
    
    public function get_tag_ids() {
    	return $this->tag_ids;
    }

    /**
     * The HTML safe teaser of the Article
     * 
     * @access public
     * @return String The HTML display safe teaser of the Article.
     */
    public function get_teaser() {
        return strip_tags($this->teaser);
    }

    /**
     * The HTML safe title of the Article
     * 
     * @access public
     * @return String The HTML display safe title of the Article.
     */
    public function get_title() {
        return strip_tags($this->title);
    }

    /**
     * The HTML safe url of the Article
     * 
     * @access public
     * @return String The HTML display safe url of the Article.
     */
    public function get_url() {
        return htmlentities($this->url);
    }
    
    /**
     * Gets the html safe domain or the url
     * 
     * @access public
     * @return String The html safe domain name of the url
     */
    public function get_source_linked(){
    	$url = $this->get_url();
    	$pattern = '@^(?:http[s]?://)?([^/]+)@i';
    	preg_match($pattern,$url,$matches);
    	if (!empty($matches)){
    		$source = $matches[0];
    	}else{
    		$source = $url;
    	}
    	$source_linked =  '<a href="' . htmlentities($url) . '">' . htmlentities($source) . '</a>';
    	
    	return $source_linked;
    }
    
    /**
     * Persist the Article to the data layer
     * 
     * @access public
     * @return Boolean True if everything worked, FALSE on error.
     */
    public function save() {
        $retval = FALSE;
        if ($this->id > 0 ) {
            // Update an existing user
            $sql = array(
                'UPDATE article SET article_title = \'?s\', ' .
                	'article_date = \'?d\', ' .
                	'article_url = \'?s\', ' .
                	'article_teaser = \'?s\', ' .
                	'article_body = \'?s\' ' .
                	' WHERE article_id = \'?i\'',
                $this->get_title(),
                $this->get_date(),
                $this->get_url(), 
                $this->get_teaser(),
                $this->get_body(),
                $this->get_id()
            );
            $retval = $this->db->iud_sql($sql);
        }
        else {
            $sql = array(
                'INSERT INTO article SET article_title = \'?s\', ' .
                	'article_date = \'?d\', ' .
                	'article_url = \'?s\', ' .
                	'article_teaser = \'?s\', ' .
                	'article_body = \'?s\'',
                $this->get_title(),
                $this->get_date(),
                $this->get_url(), 
                $this->get_teaser(),
                $this->get_body()
            );
            $retval = $this->db->iud_sql($sql);
            // Now set the id
            $sql = 'SELECT LAST_INSERT_ID() AS last_id';
            $result = $this->db->fetch_object_array($sql);
            if (isset($result[0]) && $result[0]->last_id > 0) {
                $this->set_id($result[0]->last_id);
            }
        }
	
		// Set/save the tags (if any)
		$sql = array(
			'DELETE FROM article_x_tag WHERE article_id = ?i',
			$this->get_id()
		);
		$this->db->iud_sql($sql);
		if (is_array($this->get_tag_ids()) && count($this->get_tag_ids()) > 0) {
			foreach ($this->get_tag_ids() as $tid) {
				$sql = array('INSERT INTO article_x_tag SET article_id = ?i, tag_id = ?i',
				$this->get_id(),
				$tid);
				$this->db->iud_sql($sql);
			}
		}
        return $retval;
    }
    
    public function set_body($text) {
    	$this->body = $text;
    }
    
    /**
     * Expects 2013-04-09
     */
    public function set_date($date) {
    	$datesplit = explode('-', $date);
    	if (checkdate(intval($datesplit[1]), intval($datesplit[2]), intval($datesplit[0]))) {
    		$this->date = $date;
    		return true;
    	}
    	else return false;
    }
    
    /**
     * Set the id attribute.
     * 
     * @access protected
     * @param Int The unique ID from the data layer
     */
    protected function set_id($id) {
        $this->id = intval($id);
    }
    
	/**
	 * Set tags associated with this Article
     *
     * @access public
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param Array An Array of Tag ids (integers)
     * @return void
	 */
    public function set_tag_ids($array) {
    	$retval = FALSE;
    	if (is_array($array)) {
	    	//sanitize the array
	    	$array = array_map('intval', $array);
	    	$this->tag_ids = $array;
	    	$reval = TRUE;
    	}
    	return $retval;
    }
    
    /**
     * Set the article teaser
     * 
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @author Ubani A Balogun <ubani@sas.upenn.edu>
     * @param String The text of the teaser
     * @return Boolean True just to return something
     */
    public function set_teaser($text) {
    	$limit = 500;
    	$pad = '...';
    	$break = '.';
        $teaser = substr($text, 0, $limit);
    	/**
        // This logic is breaking the assignment in some cases.
        if (strlen($text) <= $limit){
    		$teaser = $text;
    	}
        else {
    		if (false !== ($breakpoint = strpos($text, $break, $limit))){
    			if($breakpoint < strlen($text) - 1) {
    				$teaser = substr($text, 0, $breakpoint) . $pad;
    			}
    		}
    	}
        **/
    	$this->teaser = $teaser;
    	return TRUE;
    }

    /**
     * Set the title of the article
     * 
     * @access public
     * @param String The name of the article
     * @return Boolean True just to return something
     */
    public function set_title($title) {
        $this->title = $title;
    	return TRUE;
    }

    /**
     * Set the URL for this article
     * 
     * @access public
     * @param String The URL to set for this article
     * @return Boolean False if something goes awry
     */
    public function set_url($url) {
    	$retval = TRUE;
    	if(!filter_var($url, FILTER_VALIDATE_URL)) {
    		$retval = FALSE;
    	}
    	else {
    		$this->url = $url;
    	}
    	return TRUE;
    }
    
    /**
     * Returns the object as an array
     * 
     * @access public
     * @author Ubani A Balogun <ubani@sas.upenn.edu>
     * @return Array an associative array of the objects attributes
     * 
     */
    public function get_object_as_array(){
    	return array(
    			'id' => $this->get_id(),
    			'title' => $this->get_title(),
    			'teaser' => $this->get_teaser(),
    			'date' => $this->get_date(),
    			'date_readable' => $this->get_date_readable(),
    			'body' => $this->get_body(),
    			'url' => $this->get_url(),
    			'linked_url' =>$this->get_linked_url(),
    			'linked_title' => $this->get_linked_title(),
    			'source_linked' => $this->get_source_linked(),
    	);
    }

} /* end of class Article */

?>
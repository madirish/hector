<?php
/**
 * class.Maleable_Object.php
 *
 * @abstract This [singleton] class is intended to allow for logging throughout the application.
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 */
 
/**
 * Maleable object utility superclass to ensure standard API for each class.
 * 
 * @package HECTOR
 */
class Maleable_Object {

    /**
     * The unique id from the data layer.
     *
     * @access protected
     * @var int Unique id from the data layer
     */
    protected $id = 0;

    /**
     * Return the unique id from the data layer
     *
     * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @return Int The unique id from the data layer.
     */
    public function get_id() {
        return (int) $this->id;
    } 
	
	/**
	 * Process the add/edit form with the proper
	 * callback function.
	 * 
	 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
	 * @access public
	 * @param String Callback function to process values
	 * @param String Value to pass to the callback function.
     * @return void
	 */
	public function process_form($callback, $value) {
		$this->$callback($value);
	}
	
    /**
     * Set the object's unique id
     *
     * @access protected
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param  int The unique id for hte object
     * @return void
     */
    protected function set_id($id) {
       $this->id = (int) $id;
    }
	
}
?>
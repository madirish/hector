<?php

class Maleable_Object {

    /**
     * Short description of attribute id
     *
     * @access protected
     * @var int
     */
    protected $id = 0;

    /**
     * Short description of method get_id
     *
     * @access public
     * @author Justin C. Klein Keane, <jukeane@sas.upenn.edu>
     * @return int
     */
    public function get_id()
    {
        return (int) $this->id;
    } 
	
	
	public function process_form($callback, $value) {
		$this->$callback($value);
	}
	
	

    /**
     * Short description of method set_id
     *
     * @access protected
     * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
     * @param  int id
     * @return void
     */
    protected function set_id($id) {
       $this->id = (int) $id;
    }
	
}
?>
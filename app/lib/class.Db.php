<?php
/**
 * class.Db.php
 *
 * @package HECTOR
 * @abstract This class provides the interface to the database.
 * @author Justin C. Klein Keane <justin@madirish.net>
 */

/**
 * Required includes
 */
require_once("class.Config.php");
require_once("class.Log.php");

/**
 * This class provides the interface to the database.
 * @package HECTOR
 *
 */
Class DB {

	private $db_connection;

	/**
	 * Status set to 0 for errors
	 *
	 * @var int
	 */
	var $status = 1;

	/**
	 * Singleton implementation, contains Log()
	 *
	 * @var object
	 */
	static private $instance = NULL;

	/**
	 * This variable holds any error messages that might be encountered
	 *
	 * @var string
	 */
	public $message;

	/**
	 * The singleton instance of the Log object
	 */
	private $log;

	private $debug = 0;

	/**
	 * Construct the db connection and return it.  This function will die if magic quotes are left on.
	 *
	 * @return boolean
	 */
	private function __construct() {
		$this->log = Log::get_instance();

		if (! isset($_SESSION['db_host'], $_SESSION['db_user'], $_SESSION['db_pass'], $_SESSION['db'])) {
			new Config();
			if (! isset($_SESSION['db_host'], $_SESSION['db_user'], $_SESSION['db_pass'], $_SESSION['db'])) {
				$this->db_error("Required DB variables not set.");
				return false;
			}
		}
		$this->debug = $_SESSION['debug'];

		$this->db_connection = mysql_connect($_SESSION['db_host'], $_SESSION['db_user'], $_SESSION['db_pass'])
			or $this->db_error("FATAL ERROR:  Could not connect to the database.  " . mysql_error());
		$this->db = mysql_select_db($_SESSION['db'],$this->db_connection)
			or $this->db_error("FATAL ERROR:  Could not select requested database ".$_SESSION['db'].".  " . mysql_error($this->db_connection));

		// finally check magic quotes
		if (get_magic_quotes_gpc() == 1) {
			die("Magic quotes must be disabled for this application to function properly.");
		}
		return true;
	}

	public function fetch_object_array($sql) {
		$retval = false;
		$query = $this->parse_query($sql);
		if ($this->debug == 1) $this->log->write_message($query);
		$return_array = array();
		if ($result = mysql_query($query)) {
			while ($row = mysql_fetch_object($result)) {
				$return_array[] = $row;
			}
			$retval = $return_array;
		}
		else {
			$this->db_error("ERROR: There was a problem with the SQL " . $this->unstring($sql) . 
				" in Db::fetch_object_array.  " . mysql_error());
		}
		return $retval;
	}

	/**
	 * Perform an Insert, Update or Delete statement, 
	 * returning true if there was no issue with the
	 * query.
	 * 
	 * @param array - query followed by args
	 * @return boolean
	 */
	public function iud_sql($sql) {
		// Some queries in HECTOR take forever, make sure the database is still around
		$this->get_instance();
		$retval = false;
		$query = $this->parse_query($sql);
		if ($this->debug == 1) $this->log->write_message($query);
		if ($query !== false) {
			if (mysql_query($query)) {
				$retval = true;
			}
			else {
				$this->db_error('Problem with iud_sql().  Query:  ' . $this->unstring($sql) .' Error1:  ' . mysql_error());
			}
		}
		else {
			$this->db_error('Problem with iud_sql().  Query:  ' . $this->unstring($sql) .' Error2:  ' . mysql_error());
		}
		return $retval;
	}
	
	private function unstring($sqlstring) {
		if (is_array($sqlstring)) { 
			$sqlerr = '';
			foreach($sqlstring as $key=>$val) $sqlerr .= '[' . $key . '] => "' . $val . '" ';
		}
		else {
			$sqlerr = $sqlstring;
		}
		return $sqlerr;
	}

	/**
	 *
	 * @param Multi part array, the first element should be the actual
	 * tokenized SQL statement, all additional elements are the arguments
	 * to be filtered in.
	 */
	public function parse_query($sql) {
		$retval = false;
		if (is_array($sql)) {
			if (count($sql) == 1) {
				$retval = $sql;
			}
			else {
				$query = array_shift($sql);
				// check to ensure substitution tokens = arguments
				$token_count = substr_count($query, '?');
				if ($token_count == count($sql)) {
					// ==strip out the tokens==
					// First identify all the *original* tokens,
					// so we don't accidentally replace one that
					// gets inserted!
					$strings = array();
					$query_parts = $query;
					for ($i=0;$i<=$token_count;$i++) {
						$token_location = stripos($query_parts,'?');
						if ($token_location !== FALSE) {
							$strings[] = substr($query_parts, 0, $token_location+2);
							$query_parts = substr($query_parts, $token_location+2);
						}
						else {
							if (strlen($query_parts)>0) {
								$strings[] = $query_parts;
							}
						}

					}

					$final_query = '';
					foreach ($strings as $part) {
						$token_location = stripos($part, '?');
						if ($token_location !== FALSE) {
							$token = substr($part, $token_location, 2);
							$value = array_shift($sql);
							$final_query .= $this->token_replace($part, $token, $value);
						}
						else {
							$final_query .= $part;
						}
					}
					$retval = $final_query;
				}
				else {
					$this->db_error('Token count did not match array for replacement');
				}
			}
		}
		else {
			// in case we have a static query
			$retval = $sql;
		}
		//echo $retval."\n";
		return $retval;
	}

	private function token_replace($query, $token, $replace) {
		$retval = '';
		switch ($token) {
			case '?b':
					$retval = substr($query, 0, stripos($query, '?b'));
					$retval .= sprintf('%u', $replace);
					$retval .= substr($query, stripos($query, '?b')+2);
					break;
			case '?i':
					$retval = substr($query, 0, stripos($query, '?i'));
					$retval .= intval($replace);
					$retval .= substr($query, stripos($query, '?i')+2);
					break;
			case '?s':
					$retval = substr($query, 0, stripos($query, '?s'));
					$retval .= mysql_real_escape_string($replace);
					$retval .= substr($query, stripos($query, '?s')+2);
					break;
			case '?d':
					$retval = substr($query, 0, stripos($query, '?d'));
					$retval .= ($replace == '') ? date("Y-m-d H:i:s") : date("Y-m-d H:i:s", strtotime($replace));
					$retval .= substr($query, stripos($query, '?d')+2);
					break;
		}
		return $retval;
	}

	/**
	 * This is the Singleton interface
	 *
	 * @return Object
	 */
	public function get_instance() {
		if (self::$instance == NULL)
			self::$instance = new DB();

		return self::$instance;

	}

	/**
	 * Internal error logging
	 *
	 * @param String $err
	 * @todo implement this feature
	 */
	private function db_error($err) {
		$this->message .= "\n" . $err;
		$this->log->write_error($err);
	}

	/**
	 * Close the database connection
	 *
	 */
	public function close() {
		mysql_close($this->db_connection);
	}

}
?>
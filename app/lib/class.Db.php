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
 * @subpackage util
 *
 */
Class DB {

	private $db_connection;

	/**
	 * Status set to 0 for errors
	 *
	 * @access private
	 * @var int
	 */
	private $status = 1;

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
	 * @access private
	 * @return Boolean True if the singleton constructs, false if there was an issue.
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

	/**
	 * Return an array of objects based on the query.
	 * 
	 * @param String A SQL statement.
	 * @return Array An array of objects.
	 */
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
	 * @return Boolean True, or false if there was an error.
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
	 * Safety checking function that will parse a tokenized SQL query
	 * string in order to perform replacements.
	 *
	 * @param Array Multi part array, the first element should be the actual
	 * tokenized SQL statement, all additional elements are the arguments
	 * to be filtered in.
	 * @return String The interpolated string with tokens replaced with 
	 * SQL safe elements from input array.
	 */
	private function parse_query($sql) {
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

	/**
	 * This utility function supports the parse_query() method and
	 * performs SQL injection safety checks on all parameterized
	 * input then returns a safe version of the input for SQL query.
	 * 
	 * @return String The SQL safe version of the input
	 * @access private
	 * @param String Portion of the actual SQL query
	 * @param String The token to be replaced, indicating type
	 * @param String The value that should be sanitized.
	 */
	private function token_replace($query, $token, $replace) {
		$retval = '';
		switch ($token) {
			case '?b': // Boolean
					$retval = substr($query, 0, stripos($query, '?b'));
					$retval .= ($replace) ? 1 : 0;
					$retval .= substr($query, stripos($query, '?b')+2);
					break;
			case '?i': // Integer number
					$retval = substr($query, 0, stripos($query, '?i'));
					$retval .= intval($replace);
					$retval .= substr($query, stripos($query, '?i')+2);
					break;
			case '?s':  // String
					$retval = substr($query, 0, stripos($query, '?s'));
					$retval .= mysql_real_escape_string($replace);
					$retval .= substr($query, stripos($query, '?s')+2);
					break;
			case '?d':  // Datetime
					$retval = substr($query, 0, stripos($query, '?d'));
					$retval .= ($replace == '') ? date("Y-m-d H:i:s") : date("Y-m-d H:i:s", strtotime($replace));
					$retval .= substr($query, stripos($query, '?d')+2);
					break;
		}
		return $retval;
	}

	/**
	 * This is the Singleton interface and should be used instead of the
	 * constructor, which is private.  This ensures we create only one
	 * DB connection per page call.
	 *
	 * @access public
	 * @return DB An reference to the singleton instance of the DB object.
	 */
	public static function get_instance() {
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
	 * @access public
     * @return void
	 */
	public function close() {
		mysql_close($this->db_connection);
	}

}
?>
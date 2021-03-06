<?php
/**
 * class.Log.php
 *
 * @abstract This [singleton] class is intended to allow for logging throughout the application.
 * @author Justin C. Klein Keane <jukeane@sas.upenn.edu>
 * @package HECTOR
 */
 
/**
 * Required includes
 */
require_once("class.Config.php");

/**
 * This [singleton] class is intended to allow for logging throughout the application.
 * @package HECTOR
 * @subpackage util
 *
 */
Class Log {

	/**
	 * internal error tracking
	 *
	 * @var Boolean Whether there was an error
	 */
	private $error = FALSE;
	/**
	 * indicates a message for internal error tracking
	 *
	 * @var String Message string for internal errors
	 */
	private $message;

	/**
	 * Singleton implementation, contains Log()
	 *
	 * @var Log The singleton Log object.
	 */
	static private $instance = NULL;

	/**
	 * This variable contains the filesystem location of the error log.
	 *
	 * @var String The filesystem location of the error log
	 */
	private $error_log_location = '';

	/**
	 * This variable contains the filesystem location of the message log.
	 *
	 * @var String The filesystem location fo the message log
	 */
	private $message_log_location = '';

	/**
	 * This is the location on the filesystem of the config file
	 *
	 * @var String The filesystem location of the config file
	 */
	private $config_location = '';

	/**
	 * Open and provide the error and message logs, create them if necessary.
	 *
	 * @access private
	 * @return void
	 */
	private function __construct() {
		
		// Set up the defaults
		if (! isset($_SESSION['error_log'], $_SESSION['message_log'], $_SESSION['config_file'])) {
			new Config();
		}
		$this->config_location = $_SESSION['config_file'];
		$this->error_log_location = $_SESSION['error_log'];
		$this->message_log_location = $_SESSION['message_log'];
			
		
		if (! file_exists($this->error_log_location)) {
			touch($this->error_log_location) or die('The web server could not create the error log ['. $this->error_log_location . '] (permissions problem?).  Please contact a system administrator.');
		}
		if (! file_exists($this->message_log_location)) {
			touch($this->message_log_location) or die('The web server could not create the messages log  ['. $this->message_log_location . '] (permissions problem?).  Please contact a system administrator.');
		}
		$this->error = fopen($this->error_log_location, 'a') or die('The web server could not open the error log (permissions problem?).  Please contact a system administrator.');
		$this->message = fopen($this->message_log_location, 'a') or die('The web server could not open the message log (permissions problem?).  Please contact a system administrator.');
	}
	
	/**
	 * Conan the Destructor
	 * 
	 * @access public
	 * @return void
	 */
	public function __destruct() {
		$this->close();
	}

	/**
	 * Singleton interface
	 *
	 * @access public
	 * @return Log Returns a reference to the signleton instance of the Log object
	 */
	public static function get_instance() {
		if (self::$instance == NULL)
			self::$instance = new Log();
		return self::$instance;

	}

	/**
	 * Write an error to the log
	 *
	 * @access public
	 * @param String The error message to write to the log
	 * @return void
	 */
	public function write_error($err) {
        if (! isset($_SERVER['REMOTE_ADDR'])) $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$err = date('Y-m-d h:i:s') . "  ERROR: " .
				$_SERVER['REMOTE_ADDR'] . "  " .
				$err . "\t" .
				//$_SERVER['HTTP_USER_AGENT'] .
				"\n";
		if (! $this->error) $this->__construct();
		fwrite($this->error, $err) or die('Cannot write to error log.');
		
		// Write to syslog on error
		openlog("HECTOR", LOG_PID | LOG_PERROR, LOG_LOCAL0);
		syslog(LOG_WARNING, $err);
		closelog();
	}

	/**
	 * Write a message to the log
	 *
	 * @access public
	 * @param String The message to write to the log.
	 */
	public function write_message($msg) {
        if (! isset($_SERVER['REMOTE_ADDR'])) $_SERVER['REMOTE_ADDR'] = 'CLI';
		$msg = date('Y-m-d h:i:s') . "  MESSAGE: " .
				$_SERVER['REMOTE_ADDR'] . "  " .
				$msg . "\t" .
				//$_SERVER['HTTP_USER_AGENT'] .
				"\n";
		if (! $this->message) $this->__construct();
		fwrite($this->message, $msg);
	}

	/**
	 * Return the contents of the log file
	 *
	 * @access public
	 * @return String The error log file contents.
	 */
	public function return_error_log() {
		$filename = $this->error_log_location;
		$handle = fopen($filename, "r");
		$contents = fread($handle, filesize($filename));
		fclose($handle);
		return $contents;
	}

	/**
	 * Return the contents of the message log
	 *
	 * @access public
	 * @return String The contents of the message log.
	 */
	public function return_message_log() {
		$filename = $this->message_log_location;
		$handle = fopen($filename, "r");
		$contents = fread($handle, filesize($filename));
		fclose($handle);
		return $contents;
	}

	/**
	 * Close the log files
	 *
	 * @access public
	 * @return void
	 */
	public function close() {
		fclose($this->error);
		fclose($this->message);
	}

	/**
	 * This method gzips the current message log and renames it with a timestamp and starts a new log.
	 *
	 * @return Boolean True on success or false if there is an error.
	 */
	public function archive_message_log() {
		global $approot;
		$file = $this->message_log_location;
		$timestamp = time();
		$zip_error_log_filename = 'message_log_' . $timestamp . '.gz';
		if (! $zp = gzopen($approot . 'logs/' . $zip_error_log_filename, "w9")) return false;
		$contents = $this->return_message_log();
		if (! gzwrite($zp,$contents)) return false;
		if (! unlink($file)) return false;
		$this->__construct();
		$this->write_message("Message log reset.");
		return true;
	}
	/**
	 * This method gzips the current error log and renames it with a timestamp and starts a new log.
	 *
	 * @return Boolean True on success or false if there is an error.
	 */
	public function archive_error_log() {
		global $approot;
		$file = $this->error_log_location;
		$timestamp = time();
		$zip_error_log_filename = 'error_log_' . $timestamp . '.gz';
		if (! $zp = gzopen($approot . 'logs/' . $zip_error_log_filename, "w9")) return false;
		$contents = $this->return_message_log();
		if (! gzwrite($zp,$contents)) return false;
		if (! unlink($file)) return false;
		$this->__construct();
		$this->write_error("[notice] Error log reset.");
		return true;
	}

	/**
	 * This method toggles the status of the debugging in the config file
	 * 
	 * @access public
	 */
	public function toggle_status() {
		$contents = file_get_contents($this->config_location);
		$status='false';
		if (preg_match('/debug\s*\=\s*1/',$contents)) {
			$contents = preg_replace('/debug\s*\=\s*1/',"debug\t=\t0",$contents);
		}
		else {
			$contents = preg_replace('/debug\s*\=\s*0/',"debug\t=\t1",$contents);
			$status = 'true';
		}
		file_put_contents($this->config_location, $contents) or $this->write_error("Couldn't rewrite the config file from log.class.php.");
		$this->write_message("Message logging set to $status");

	}
}
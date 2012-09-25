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
	 * @var boolean
	 */
	private $error = FALSE;
	/**
	 * indicates a message for internal error tracking
	 *
	 * @var string
	 */
	private $message;

	/**
	 * Singleton implementation, contains Log()
	 *
	 * @var object
	 */
	static private $instance = NULL;

	/**
	 * This variable contains the filesystem location of the error log.
	 *
	 * @var String
	 */
	private $error_log_location = '';

	/**
	 * This variable contains the filesystem location of the message log.
	 *
	 * @var String
	 */
	private $message_log_location = '';

	/**
	 * This is the location on the filesystem of the config file
	 *
	 * @var String
	 */
	private $config_location = '';

	/**
	 * Open and provide the error and message logs, create them if necessary.
	 *
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
	 * Singleton interface
	 *
	 * @return $this->instance
	 */
	public function get_instance() {
		if (self::$instance == NULL)
			self::$instance = new Log();

		return self::$instance;

	}

	/**
	 * Write an error to the log
	 *
	 * @param String $err
	 */
	public function write_error($err) {
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
	 * @param String $msg
	 */
	public function write_message($msg) {
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
	 * @return String
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
	 * @return String
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
	 */
	public function close() {
		fclose($this->error);
		fclose($this->message);
	}

	/**
	 * This method gzips the current message log and renames it with a timestamp and starts a new log.
	 *
	 * @return boolean
	 */
	public function archive_message_log() {
		$file = $this->message_log_location;
		$timestamp = time();
		$zip_error_log_filename = 'message_log_' . $timestamp . '.gz';
		if (! $zp = gzopen('logs/' . $zip_error_log_filename, "w9")) return false;
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
	 * @return boolean
	 */
	public function archive_error_log() {
		$file = $this->error_log_location;
		$timestamp = time();
		$zip_error_log_filename = 'error_log_' . $timestamp . '.gz';
		if (! $zp = gzopen('logs/' . $zip_error_log_filename, "w9")) return false;
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
<?php
/**
 * class.config.php
 *
 * @package HECTOR
 * @author Justin C. Klein Keane <justin@madirish.net>
 * @abstract The purpose of this class is simply to load the config file into globals for use within other classes.
 *
 * @todo Fail more gracefully.
 */



/**
 * This [singleton] class loads config variables from the config file found in /conf/conf.ini into global variables
 * @package migallery
 */
Class Config {
	
	/**
	 * Location of the config file (static) set to .conf/config.ini'.
	 *
	 * @var string
	 */
	private $config_file = '';

	/**
	 * The constructor meerly checks to make guides the class.
	 *
	 */
	public function __construct($config='') {
		
		global $approot;
		$this->config_file = $approot . 'conf/config.ini';
	
		//allow the config file location to be overwritten
		if ($config != '') {
			$this->config_file = $config;
		}
		if ($this->check_file()) {
			$this->load_configs();
		}
	}

	/**
	 * Check to see if the file exists.
	 *
	 * @return true | die
	 */
	private function check_file() {
		if (! file_exists($this->config_file)) {
			die("Error in class.Config.php - could not load configuration " . 
				$this->config_file . 
				".  Check your approot variable settings in index.php.");
		}
		else {
			return true;
		}
	}

	/**
	 * Parse over the config.ini file and load the configs into globals.
	 *
	 */
	private function load_configs() {
		$ini_array = parse_ini_file($this->config_file, 'true');
		foreach ($ini_array['hector'] as $key=>$val) {
			$_SESSION[$key] = $val;
			if ($key == 'timezone') date_default_timezone_set($val);
		}
	}

}

?>

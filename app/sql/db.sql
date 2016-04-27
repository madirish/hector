# HECTOR database configuration file
# Author Justin C. Klein Keane <jukeane@sas.upenn.edu>

CREATE DATABASE IF NOT EXISTS hector;
use hector;

-- Alerts are OSSEC alerts
CREATE TABLE IF NOT EXISTS `alert` (
	`alert_id` INT NOT NULL AUTO_INCREMENT,
	`alert_timestamp` TIMESTAMP,
	`alert_string` VARCHAR(255),
	`host_id` INT NOT NULL,
	PRIMARY KEY  (`alert_id`),
	INDEX (`host_id`)
) ENGINE = INNODB;

-- API keys 
CREATE TABLE IF NOT EXISTS `api_key` (
  `api_key_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `api_key_value` VARCHAR(255) NOT NULL,
  `api_key_resource` VARCHAR(255) NOT NULL,
  `api_key_holder_name` VARCHAR(255) NOT NULL,
  `api_key_holder_affiliation` VARCHAR(255) NOT NULL,
  `api_key_holder_email` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`api_key_id`)
) ENGINE = INNODB;

-- Actual data from RSS feeds
CREATE TABLE IF NOT EXISTS `article` (
  `article_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `article_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `article_title` varchar(255),
  `article_teaser` text,
  `article_url` varchar (255),
  `article_body` text,
  PRIMARY KEY (`article_id`),
  INDEX USING BTREE (`article_date`)
) ENGINE = INNODB;

-- Allow free tagging of articles from RSS feeds
CREATE TABLE IF NOT EXISTS `article_x_tag` (
  `article_id` INT NOT NULL,
  `tag_id` INT NOT NULL,
  INDEX (`article_id`),
  INDEX (`tag_id`)
) ENGINE = INNODB;

-- If the article describes a vulnerability pair them
CREATE TABLE IF NOT EXISTS `article_x_vuln` (
  `article_id` INT NOT NULL,
  `vuln_id` INT NOT NULL,
  INDEX (`article_id`),
  INDEX (`vuln_id`)
);

-- Darknet sensor
CREATE TABLE IF NOT EXISTS `darknet` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`src_ip` INT UNSIGNED NOT NULL,
	`dst_ip` INT UNSIGNED NOT NULL,
	`src_port` INT UNSIGNED NOT NULL,
	`dst_port` INT UNSIGNED NOT NULL,
	`proto` ENUM('tcp','udp','icmp'),
	`country_code` VARCHAR(2),
	`received_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	INDEX USING HASH (`src_ip`)
) ENGINE = INNODB;

-- Form table is used for anti XSRF tokens
CREATE TABLE IF NOT EXISTS `form` (
	`form_id` INT NOT NULL AUTO_INCREMENT,
	`form_name` VARCHAR(255) NOT NULL,
	`form_token` VARCHAR(32) NOT NULL,
	`form_ip` VARCHAR(15) NOT NULL,
	`form_datetime` DATETIME NOT NULL,
	PRIMARY KEY  (`form_id`)
) ENGINE = INNODB;

-- http://geolite.maxmind.com/download/geoip/database/GeoIPCountryCSV.zip
CREATE TABLE IF NOT EXISTS `geoip` (
  `start_ip_str` VARCHAR(15),
  `end_ip_str` VARCHAR(15),
  `start_ip_long` INT UNSIGNED,
  `end_ip_long` INT UNSIGNED,
  `country_code` VARCHAR(2),
  `country_name` VARCHAR(255),
  INDEX USING HASH (`start_ip_long`),
  INDEX USING HASH (`end_ip_long`)
) ENGINE = INNODB;
DELETE FROM geoip;
LOAD DATA INFILE '/opt/hector/app/sql/GeoIPCountryWhois.csv' INTO TABLE geoip FIELDS TERMINATED BY "," ENCLOSED BY '"';

-- Hosts are IP based machines, the crux of the system
CREATE TABLE IF NOT EXISTS `host` (
  `host_id` INT NOT NULL AUTO_INCREMENT,
  `host_ip` VARCHAR(15) NOT NULL,
  `host_ip_numeric` INT UNSIGNED NOT NULL,
  `host_name` TINYTEXT NOT NULL,
  `host_os` VARCHAR(100) DEFAULT NULL,
  `host_link` VARCHAR(255) DEFAULT NULL,
  `host_note` TEXT DEFAULT NULL,
  `host_sponsor` VARCHAR(50) DEFAULT NULL, -- faculty/staff contact
  `host_technical` VARCHAR(255) DEFAULT NULL, -- technical contact
  `supportgroup_id` INT DEFAULT NULL, -- responsible lsp
  `host_verified` tinyint(1) DEFAULT '0', -- has the information been vetted
  `host_ignored` tinyint(1) DEFAULT '0', -- Don't check this host?
  `host_policy` tinyint(1) DEFAULT '0', -- Policy (i.e. "falls under confidential data policy")
  `location_id` INT DEFAULT NULL,
  `host_ignore_portscan` TINYINT(1) DEFAULT 0,
  `host_ignoredby_user_id` INT DEFAULT NULL,
  `host_ignoredfor_days` INT DEFAULT 0,
  `host_ignored_timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `host_ignored_note` TEXT DEFAULT NULL,
  PRIMARY KEY  (`host_id`),
  INDEX (`location_id`),
  INDEX (`host_ip`),
  INDEX (`host_ip_numeric`),
  INDEX (`supportgroup_id`)
) ENGINE = INNODB;

-- Track alternative IP addresses and domain names
CREATE TABLE IF NOT EXISTS `host_alts` (
	`host_id` INT NOT NULL,
	`host_alt_ip` varchar(15),
	`host_alt_name` varchar(255),
  PRIMARY KEY (`host_id`)
) ENGINE = INNODB;

-- For grouping hosts (say, "HR Machines")
CREATE TABLE IF NOT EXISTS `host_group` (
	`host_group_id` INT NOT NULL AUTO_INCREMENT,
	`host_group_name` VARCHAR(255) NOT NULL,
	`host_group_detail` TEXT,
  PRIMARY KEY  (`host_group_id`)
) ENGINE = INNODB;

-- Mapping table for hosts to groups
CREATE TABLE IF NOT EXISTS `host_x_host_group` (
	`host_group_id` INT NOT NULL,
	`host_id` INT NOT NULL,
  INDEX USING BTREE (`host_group_id`),
  INDEX USING BTREE (`host_id`)
) ENGINE = INNODB;

-- Free tagging of hosts
CREATE TABLE IF NOT EXISTS `host_x_tag` (
	`host_id` INT NOT NULL,
	`tag_id` INT NOT NULL,
  INDEX (`host_id`),
  INDEX (`tag_id`)
) ENGINE = INNODB;

-- Master incident table
CREATE TABLE IF NOT EXISTS `incident` (
  `incident_id` INT NOT NULL AUTO_INCREMENT,
  `incident_title` VARCHAR(255) NOT NULL,
  `incident_month` TINYINT NOT NULL,
  `incident_year` INT NOT NULL,
  `agent_id` INT NOT NULL,
  `action_id` INT NOT NULL,
  `asset_id` INT NOT NULL,
  `confidential_data` INT(1) DEFAULT 0,
  `integrity_loss` TEXT,
  `authenticity_loss` TEXT,
  `availability_loss_timeframe_id` INT NOT NULL,
  `utility_loss` TEXT,
  `action_to_discovery_timeframe_id` INT NOT NULL,
  `discovery_to_containment_timeframe_id` INT NOT NULL,
  `discovery_id` INT NOT NULL,
  `discovery_evidence_sources` TEXT,
  `discovery_metrics` TEXT,
  `2020_hindsight` TEXT,
  `correction_recommended` TEXT,
  `asset_loss_magnitude_id` INT NOT NULL,
  `disruption_magnitude_id` INT NOT NULL,
  `response_cost_magnitude_id` INT NOT NULL,
  `impact_magnitude_id` INT NOT NULL,
  INDEX (`agent_id`), 
  INDEX (`action_id`), 
  INDEX (`asset_id`), 
  INDEX (`impact_magnitude_id`),
  INDEX (`availability_loss_timeframe_id`),
  INDEX (`action_to_discovery_timeframe_id`),
  INDEX (`discovery_to_containment_timeframe_id`),
  INDEX (`discovery_id`),
  INDEX (`asset_loss_magnitude_id`),
  INDEX (`disruption_magnitude_id`),
  INDEX (`response_cost_magnitude_id`),
  INDEX (`impact_magnitude_id`),
  PRIMARY KEY (`incident_id`)
) ENGINE = INNODB;  

-- Action that caused the incident
CREATE TABLE IF NOT EXISTS `incident_action` (
  `action_id` INT NOT NULL AUTO_INCREMENT,
  `action_action` VARCHAR(255) NOT NULL,
  PRIMARY KEY  (`action_id`)
) ENGINE = INNODB;
INSERT INTO `incident_action` SET `action_id` = 1, `action_action` = 'Malware' ON DUPLICATE KEY UPDATE `action_id`=1;
INSERT INTO `incident_action` SET `action_id` = 2, `action_action` = 'Hacking' ON DUPLICATE KEY UPDATE `action_id`=2;
INSERT INTO `incident_action` SET `action_id` = 3, `action_action` = 'Social' ON DUPLICATE KEY UPDATE `action_id`=3;
INSERT INTO `incident_action` SET `action_id` = 4, `action_action` = 'Spam' ON DUPLICATE KEY UPDATE `action_id`=4;
INSERT INTO `incident_action` SET `action_id` = 5, `action_action` = 'Misuse' ON DUPLICATE KEY UPDATE `action_id`=5;
INSERT INTO `incident_action` SET `action_id` = 6, `action_action` = 'Physical' ON DUPLICATE KEY UPDATE `action_id`=6;
INSERT INTO `incident_action` SET `action_id` = 7, `action_action` = 'Error' ON DUPLICATE KEY UPDATE `action_id`=7;
INSERT INTO `incident_action` SET `action_id` = 8, `action_action` = 'Environmental' ON DUPLICATE KEY UPDATE `action_id`=8;
INSERT INTO `incident_action` SET `action_id` = 9, `action_action` = 'Phishing' ON DUPLICATE KEY UPDATE `action_id`=9;

-- Source of the agent who caused the incident
CREATE TABLE IF NOT EXISTS `incident_agent` (
  `agent_id` INT NOT NULL AUTO_INCREMENT,
  `agent_agent` VARCHAR(255) NOT NULL,
  PRIMARY KEY  (`agent_id`)
) ENGINE = INNODB;
INSERT INTO `incident_agent` SET `agent_id` = 1, `agent_agent` = 'External' ON DUPLICATE KEY UPDATE `agent_id`=1;
INSERT INTO `incident_agent` SET `agent_id` = 2, `agent_agent` = 'Internal' ON DUPLICATE KEY UPDATE `agent_id`=2;
INSERT INTO `incident_agent` SET `agent_id` = 3, `agent_agent` = 'Partner' ON DUPLICATE KEY UPDATE `agent_id`=3;
INSERT INTO `incident_agent` SET `agent_id` = 4, `agent_agent` = 'Other/Unknown' ON DUPLICATE KEY UPDATE `agent_id`=4;
  
-- Asset affected by the incident
CREATE TABLE IF NOT EXISTS `incident_asset` (
  `asset_id` INT NOT NULL AUTO_INCREMENT,
  `asset_asset` VARCHAR(255) NOT NULL,
  PRIMARY KEY  (`asset_id`)
) ENGINE = INNODB;
INSERT INTO `incident_asset` SET `asset_id` = 1, `asset_asset` = 'Database server' ON DUPLICATE KEY UPDATE `asset_id`=1;
INSERT INTO `incident_asset` SET `asset_id` = 2, `asset_asset` = 'Desktop / Workstation' ON DUPLICATE KEY UPDATE `asset_id`=2;
INSERT INTO `incident_asset` SET `asset_id` = 3, `asset_asset` = 'Laptop' ON DUPLICATE KEY UPDATE `asset_id`=3;
INSERT INTO `incident_asset` SET `asset_id` = 4, `asset_asset` = 'Mail server' ON DUPLICATE KEY UPDATE `asset_id`=4;
INSERT INTO `incident_asset` SET `asset_id` = 5, `asset_asset` = 'Mobile device' ON DUPLICATE KEY UPDATE `asset_id`=5;
INSERT INTO `incident_asset` SET `asset_id` = 6, `asset_asset` = 'Multifunction printer' ON DUPLICATE KEY UPDATE `asset_id`=6;
INSERT INTO `incident_asset` SET `asset_id` = 7, `asset_asset` = 'Removable media' ON DUPLICATE KEY UPDATE `asset_id`=7;
INSERT INTO `incident_asset` SET `asset_id` = 8, `asset_asset` = 'Web app or server' ON DUPLICATE KEY UPDATE `asset_id`=8;
INSERT INTO `incident_asset` SET `asset_id` = 9, `asset_asset` = 'Credentials' ON DUPLICATE KEY UPDATE `asset_id`=9;
INSERT INTO `incident_asset` SET `asset_id` = 10, `asset_asset` = 'Proxy server' ON DUPLICATE KEY UPDATE `asset_id`=10;

-- Method of incident discovery
CREATE TABLE IF NOT EXISTS `incident_discovery` (
  `discovery_id` INT NOT NULL AUTO_INCREMENT,
  `discovery_method` VARCHAR(100) NOT NULL,
  PRIMARY KEY  (`discovery_id`)
) ENGINE = INNODB;  
INSERT INTO `incident_discovery` SET `discovery_id` = 1, `discovery_method` = '3rd party event monitoring' ON DUPLICATE KEY UPDATE `discovery_id`=1;
INSERT INTO `incident_discovery` SET `discovery_id` = 2, `discovery_method` = 'Network intrusion detection system (NIDS)' ON DUPLICATE KEY UPDATE `discovery_id`=2;
INSERT INTO `incident_discovery` SET `discovery_id` = 3, `discovery_method` = 'Host-based intrusion detection system (HIDS)' ON DUPLICATE KEY UPDATE `discovery_id`=3;
INSERT INTO `incident_discovery` SET `discovery_id` = 4, `discovery_method` = 'Anti-virus' ON DUPLICATE KEY UPDATE `discovery_id`=4;
INSERT INTO `incident_discovery` SET `discovery_id` = 5, `discovery_method` = 'Internal security audit' ON DUPLICATE KEY UPDATE `discovery_id`=5;
INSERT INTO `incident_discovery` SET `discovery_id` = 6, `discovery_method` = 'Unusual system behaviour' ON DUPLICATE KEY UPDATE `discovery_id`=6;
INSERT INTO `incident_discovery` SET `discovery_id` = 7, `discovery_method` = 'End user report' ON DUPLICATE KEY UPDATE `discovery_id`=7;
INSERT INTO `incident_discovery` SET `discovery_id` = 8, `discovery_method` = 'Application monitoring system' ON DUPLICATE KEY UPDATE `discovery_id`=8;
INSERT INTO `incident_discovery` SET `discovery_id` = 9, `discovery_method` = 'Public disclosure via 3rd party' ON DUPLICATE KEY UPDATE `discovery_id`=9;

-- Incident magnitudes
CREATE TABLE IF NOT EXISTS `incident_magnitude` (
  `magnitude_id` INT NOT NULL AUTO_INCREMENT,
  `magnitude_name` VARCHAR(20) NOT NULL,
  `magnitude_level` INT NOT NULL,
  PRIMARY KEY  (`magnitude_id`)
) ENGINE = INNODB;  
INSERT INTO `incident_magnitude` SET `magnitude_id` = 1, `magnitude_name` = 'None', `magnitude_level` = 0 ON DUPLICATE KEY UPDATE `magnitude_id`=1;
INSERT INTO `incident_magnitude` SET `magnitude_id` = 2, `magnitude_name` = 'Insignificant', `magnitude_level` = 1 ON DUPLICATE KEY UPDATE `magnitude_id`=2;
INSERT INTO `incident_magnitude` SET `magnitude_id` = 3, `magnitude_name` = 'Minor', `magnitude_level` = 2 ON DUPLICATE KEY UPDATE `magnitude_id`=3;
INSERT INTO `incident_magnitude` SET `magnitude_id` = 4, `magnitude_name` = 'Moderate', `magnitude_level` = 3 ON DUPLICATE KEY UPDATE `magnitude_id`=4;
INSERT INTO `incident_magnitude` SET `magnitude_id` = 5, `magnitude_name` = 'Major', `magnitude_level` = 4 ON DUPLICATE KEY UPDATE `magnitude_id`=5;
INSERT INTO `incident_magnitude` SET `magnitude_id` = 6, `magnitude_name` = 'Unknown', `magnitude_level` = '-1' ON DUPLICATE KEY UPDATE `magnitude_id`=6;

-- Timeframes for incident discovery, containment, and outages
CREATE TABLE IF NOT EXISTS `incident_timeframe` (
  `timeframe_id` INT NOT NULL AUTO_INCREMENT,
  `timeframe_duration` VARCHAR(50) NOT NULL,
  PRIMARY KEY  (`timeframe_id`)
) ENGINE = INNODB;  
INSERT INTO `incident_timeframe` SET `timeframe_id` = 1, `timeframe_duration` = 'n/a' ON DUPLICATE KEY UPDATE `timeframe_id`=1;
INSERT INTO `incident_timeframe` SET `timeframe_id` = 2, `timeframe_duration` = 'seconds' ON DUPLICATE KEY UPDATE `timeframe_id`=2;
INSERT INTO `incident_timeframe` SET `timeframe_id` = 3, `timeframe_duration` = 'minutes' ON DUPLICATE KEY UPDATE `timeframe_id`=3;
INSERT INTO `incident_timeframe` SET `timeframe_id` = 4, `timeframe_duration` = 'hours' ON DUPLICATE KEY UPDATE `timeframe_id`=4;
INSERT INTO `incident_timeframe` SET `timeframe_id` = 5, `timeframe_duration` = 'days' ON DUPLICATE KEY UPDATE `timeframe_id`=5;
INSERT INTO `incident_timeframe` SET `timeframe_id` = 6, `timeframe_duration` = 'weeks' ON DUPLICATE KEY UPDATE `timeframe_id`=6;
INSERT INTO `incident_timeframe` SET `timeframe_id` = 7, `timeframe_duration` = 'months' ON DUPLICATE KEY UPDATE `timeframe_id`=7;
INSERT INTO `incident_timeframe` SET `timeframe_id` = 8, `timeframe_duration` = 'years' ON DUPLICATE KEY UPDATE `timeframe_id`=8;
INSERT INTO `incident_timeframe` SET `timeframe_id` = 9, `timeframe_duration` = 'forever' ON DUPLICATE KEY UPDATE `timeframe_id`=9;

-- Free tagging of incidents
CREATE TABLE IF NOT EXISTS `incident_x_tag` (
  `incident_id` INT NOT NULL,
  `tag_id` INT NOT NULL,
  INDEX (`incident_id`),
  INDEX (`tag_id`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `koj_executed_command` (
  `id` INT(12) AUTO_INCREMENT NOT NULL,
  `time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` VARCHAR(15) NOT NULL,
  `command` VARCHAR(255),
  `ip_numeric` INT UNSIGNED NOT NULL,
  `session_id` INT UNSIGNED,
  `sensor_id` INT UNSIGNED,
  `country_code` VARCHAR(2),
  PRIMARY KEY (`id`)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `koj_login_attempt` (
  `id` INT(12) AUTO_INCREMENT NOT NULL,
  `time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` VARCHAR(15) NOT NULL,
  `username` VARCHAR(50),
  `password` VARCHAR(50),
  `ip_numeric` INT UNSIGNED NOT NULL,
  `sensor_id` INT(10) UNSIGNED,
  `country_code` VARCHAR(2),
  PRIMARY KEY (`id`)
) ENGINE = InnoDB;

-- Physical addresses for hosts
CREATE TABLE IF NOT EXISTS `location` (
	`location_id` INT NOT NULL AUTO_INCREMENT,
	`location_name` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`location_id`)
);

-- Log file table
CREATE TABLE IF NOT EXISTS `log` (
	`log_id` INT NOT NULL AUTO_INCREMENT,
	`log_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`log_type` VARCHAR(255) DEFAULT NULL,
	`log_message` text NOT NULL,
	PRIMARY KEY (`log_id`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `malware` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `time` TIMESTAMP,
  `source` VARCHAR(255),
  `source_ip` VARCHAR(15) NOT NULL,
  `source_ip_numeric` INT UNSIGNED NOT NULL,
  `source_url` VARCHAR(255),
  `md5sum` VARCHAR(32),
  `filetype` VARCHAR(255),
  `clamsig` text,
  `sensor_id` INT(10) UNSIGNED,
  `file` LONGBLOB,
  PRIMARY KEY (`id`),
  INDEX (`source_ip_numeric`), 
  INDEX (`md5sum`)
) ENGINE = InnoDB;

-- Add ability to free tag malware
CREATE TABLE IF NOT EXISTS `malware_x_tag` (
  `malware_id` INT UNSIGNED NOT NULL,
  `tag_id` INT UNSIGNED NOT NULL,
  INDEX (`malware_id`), 
  INDEX (`tag_id`)
) ENGINE = INNODB;

-- Results of NMAP scans
CREATE TABLE IF NOT EXISTS `nmap_result` (
	`nmap_result_id` INT NOT NULL AUTO_INCREMENT,
	`host_id` INT NOT NULL,
  `state_id` INT NOT NULL,
  `scan_id` INT NOT NULL,
	`nmap_result_port_number` INT NOT NULL,
  `nmap_result_protocol` varchar(4),
  `nmap_result_service_name` VARCHAR(50) NOT NULL,
  `nmap_result_service_version` VARCHAR(255) NOT NULL,
	`nmap_result_is_new` INT NOT NULL DEFAULT 1,
	`nmap_result_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`nmap_result_id`),
  INDEX (`host_id`),
  INDEX (`nmap_result_port_number`),
  INDEX (`scan_id`)
) ENGINE = INNODB;

-- OSSEC alerts from clients
CREATE TABLE IF NOT EXISTS `ossec_alert` (
	`alert_id` INT NOT NULL AUTO_INCREMENT,
	`alert_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`host_id` INT NOT NULL,
	`alert_log` VARCHAR(255) DEFAULT NULL,
	`rule_id` INT NOT NULL,
	`rule_src_ip` VARCHAR(15) DEFAULT NULL,
	`rule_src_ip_numeric` INT UNSIGNED,
	`rule_user` VARCHAR(20) DEFAULT NULL,
	`rule_log` TEXT DEFAULT NULL,
	`alert_ossec_id` VARCHAR(50) NOT NULL,
	PRIMARY KEY (`alert_id`),
	INDEX (`host_id`), 
	INDEX (`rule_id`),
	INDEX USING HASH (`rule_src_ip_numeric`),
  INDEX USING HASH (`rule_id`),
  INDEX USING HASH (`host_id`),
	INDEX USING BTREE (`alert_date`)
) ENGINE = INNODB;

-- OSSEC rules (defined in the server)
CREATE TABLE IF NOT EXISTS `ossec_rule` (
	`rule_id` INT NOT NULL AUTO_INCREMENT,
	`rule_number` INT NOT NULL,
	`rule_level` INT NOT NULL,
	`rule_message` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`rule_id`),
	INDEX (`rule_number`),
	INDEX (`rule_level`),
  INDEX USING BTREE (rule_level)
);

-- Risk rating, for vulnerabilities
CREATE TABLE IF NOT EXISTS `risk` (
  `risk_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `risk_name` varchar(25) NOT NULL,
  `risk_weight` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`risk_id`)
) ENGINE = INNODB;
INSERT INTO `risk` SET `risk_id`=1, `risk_name`='none', `risk_weight`= 0 ON DUPLICATE KEY UPDATE `risk_id` = 1;
INSERT INTO `risk` SET `risk_id`=2, `risk_name`='low', `risk_weight`= 5 ON DUPLICATE KEY UPDATE `risk_id` = 2;
INSERT INTO `risk` SET `risk_id`=3, `risk_name`='medium', `risk_weight`= 10 ON DUPLICATE KEY UPDATE `risk_id` = 3;
INSERT INTO `risk` SET `risk_id`=4, `risk_name`='high', `risk_weight`= 15 ON DUPLICATE KEY UPDATE `risk_id` = 4;
INSERT INTO `risk` SET `risk_id`=5, `risk_name`='critical', `risk_weight`= 20 ON DUPLICATE KEY UPDATE `risk_id` = 5;

-- Table for regularly generated reports
CREATE TABLE IF NOT EXISTS `report` (
	`report_id` INT NOT NULL AUTO_INCREMENT,
	`report_title` VARCHAR(255),
	`report_filename` VARCHAR(255),
	`report_daily` TINYINT(1) DEFAULT 0,
	`report_weekly` TINYINT(1) DEFAULT 0,
	`report_monthly` TINYINT(1) DEFAULT 0,
	PRIMARY KEY (`report_id`)
);

-- RSS feed import table (for scheduling)
CREATE TABLE IF NOT EXISTS `rss` (
  `rss_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rss_name` varchar(255),
  `rss_url` varchar(255) NOT NULL,
  PRIMARY KEY (`rss_id`)
) ENGINE = INNODB;

-- Scans are a generic network poke for scheduling
CREATE TABLE IF NOT EXISTS `scan` (
	`scan_id` INT NOT NULL AUTO_INCREMENT,
	`scan_type_id` INT NOT NULL,
	`scan_name` VARCHAR(255),
	`scan_daily` INT(1) DEFAULT 0,
	`scan_dayofweek` INT DEFAULT 0,
	`scan_dayofmonth` INT DEFAULT 0,
	`scan_dayofyear` INT DEFAULT 0,
	PRIMARY KEY (`scan_id`)
) ENGINE = INNODB;

-- Scan process refers to the type of program to run (NMAP, Nikto, etc.)
CREATE TABLE IF NOT EXISTS `scan_type` (
	`scan_type_id` INT NOT NULL AUTO_INCREMENT,
	`scan_type_name` VARCHAR(255) NOT NULL, -- Friendly name of the program
	`scan_type_flags` VARCHAR(255) DEFAULT NULL,
	`scan_type_script` VARCHAR(255) NOT NULL, -- Actual system path to the php controller
	PRIMARY KEY (`scan_type_id`)
) ENGINE = INNODB;
INSERT INTO `scan_type` 
  SET `scan_type_name` = 'NMAP network scanner', 
	 `scan_type_id`=1, 
	 `scan_type_script`='nmap_scan.php' 
	ON DUPLICATE KEY UPDATE `scan_type_id`=1;

-- Map scans to host groups
CREATE TABLE IF NOT EXISTS `scan_x_host_group` (
	`host_group_id` INT NOT NULL,
	`scan_id` INT NOT NULL,
  INDEX (`host_group_id`),
  INDEX (`scan_id`)
) ENGINE = INNODB;

-- Port states (1=open, 2=closed, 3=filtered) but room for more
CREATE TABLE IF NOT EXISTS `state` (
	`state_id` INT NOT NULL AUTO_INCREMENT,
	`state_state` VARCHAR(50),
	PRIMARY KEY (`state_id`)
);
INSERT INTO `state` SET `state_id`=1, `state_state`='open' ON DUPLICATE KEY UPDATE `state_state` = 'open';
INSERT INTO `state` SET `state_id`=2, `state_state`='closed' ON DUPLICATE KEY UPDATE `state_state` = 'closed';
INSERT INTO `state` SET `state_id`=3, `state_state`='filtered' ON DUPLICATE KEY UPDATE `state_state` = 'filtered';
INSERT INTO `state` SET `state_id`=4, `state_state`='open|filtered' ON DUPLICATE KEY UPDATE `state_state` = 'open|filtered';
INSERT INTO `state` SET `state_id`=5, `state_state`='other' ON DUPLICATE KEY UPDATE `state_state` = 'other';


-- Support groups are entities composed of individuals that
-- handle host support
CREATE TABLE IF NOT EXISTS `supportgroup` (
	`supportgroup_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`supportgroup_name` VARCHAR(255) NOT NULL,
	`supportgroup_email` varchar(100) DEFAULT NULL, -- Distribution e-mail alias
  PRIMARY KEY  (`supportgroup_id`)
) ENGINE = INNODB;

-- Free tags (for hosts)
CREATE TABLE IF NOT EXISTS `tag` (
  `tag_id` INT NOT NULL AUTO_INCREMENT,
  `tag_name` VARCHAR(50) NOT NULL,
  PRIMARY KEY  (`tag_id`)
) ENGINE = INNODB;

-- URL's and screenshot files for hosts
CREATE TABLE IF NOT EXISTS `url` (
  `url_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `url_url` varchar(255) NOT NULL,
  `url_screenshot` varchar(255) DEFAULT NULL,
  `host_id` INT NOT NULL,
  PRIMARY KEY (`url_id`),
  UNIQUE KEY (`url_url`)
) ENGINE = INNODB;

-- Finally the user table
CREATE TABLE IF NOT EXISTS `user` (
	`user_id` INT NOT NULL AUTO_INCREMENT,
	`user_name` VARCHAR(255) NOT NULL,
	`user_email` VARCHAR(255) NOT NULL,
	`user_pass` VARCHAR(255) NOT NULL,
	`user_is_admin` INT(1) DEFAULT 0,
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY (`user_name`)
) ENGINE = INNODB;
INSERT INTO `user` set `user_id`=1, 
	`user_name`='administrator', 
	`user_pass`='$1$afQP7QmR$4cRYamEz5Z7lyxpsRTow/1', -- just "password" 
	`user_is_admin`=1 
	ON DUPLICATE KEY UPDATE `user_id` = 1;
	
-- Map users to support groups
CREATE TABLE IF NOT EXISTS `user_x_supportgroup` (
  `user_id` INT NOT NULL,
  `supportgroup_id` INT NOT NULL,	
  INDEX (`user_id`), 
  INDEX (`supportgroup_id`)
) ENGINE = INNODB;

-- Vulnerability classes
CREATE TABLE IF NOT EXISTS `vuln` (
  `vuln_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `vuln_name` varchar(255) NOT NULL,
  `vuln_description` text,
  `vuln_cve` varchar(45),
  `vuln_osvdb` varchar(45),
  PRIMARY KEY (`vuln_id`)
) ENGINE = INNODB;

-- Add URL for website with info on vuln
CREATE TABLE IF NOT EXISTS `vuln_url` (
  `vuln_url_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `vuln_id` INT UNSIGNED NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  INDEX (`vuln_id`),
  PRIMARY KEY (`vuln_url_id`)
) ENGINE = INNODB;

-- Add ability to free tag vulnerabilities
CREATE TABLE IF NOT EXISTS `vuln_x_tag` (
  `vuln_id` INT UNSIGNED NOT NULL,
  `tag_id` INT UNSIGNED NOT NULL,
  INDEX (`vuln_id`), 
  INDEX (`tag_id`)
) ENGINE = INNODB;

-- Vulnerablities details
CREATE TABLE IF NOT EXISTS `vuln_detail` (
  `vuln_detail_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `vuln_detail_text` text,
  `vuln_detail_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `vuln_detail_ignore` int(1) NOT NULL DEFAULT '0',
  `vuln_detail_ignore_datetime` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  `vuln_detail_ignoredby_user_id` INT,
  `vuln_detail_fixed` int(1) NOT NULL DEFAULT '0',
  `vuln_detail_fixed_datetime` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  `vuln_detail_fixedby_user_id` INT,
  `vuln_detail_fixed_notes` text,
  `vuln_detail_ticket` VARCHAR(255),
  `risk_id` INT UNSIGNED NOT NULL DEFAULT 1,
  `host_id` INT UNSIGNED NOT NULL,  
  `vuln_id` INT UNSIGNED NOT NULL,
  INDEX (`vuln_id`), 
  INDEX (`host_id`),
  INDEX (`risk_id`),
  PRIMARY KEY (`vuln_detail_id`)
) ENGINE = INNODB;

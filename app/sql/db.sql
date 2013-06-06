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
	KEY `host_id` (`host_id`)
) ENGINE = INNODB;

-- Actual data from RSS feeds
CREATE TABLE IF NOT EXISTS `article` (
  `article_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `article_date` TIMESTAMP NOT NULL DEFAULT NOW(),
  `article_title` varchar(255),
  `article_url` varchar (255),
  `article_body` text,
  PRIMARY KEY (`article_id`),
  INDEX USING BTREE (`article_date`)
) ENGINE = INNODB;

-- Allow free tagging of articles from RSS feeds
CREATE TABLE IF NOT EXISTS `article_x_tag` (
  `article_id` INT NOT NULL,
  `tag_id` INT NOT NULL
) ENGINE = INNODB;

-- If the article describes a vulnerability pair them
CREATE TABLE IF NOT EXISTS `article_x_vuln` (
  `article_id` INT NOT NULL,
  `vuln_id` INT NOT NULL
);

-- Darknet sensor
CREATE TABLE IF NOT EXISTS `darknet` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`src_ip` INT UNSIGNED NOT NULL,
	`dst_ip` INT UNSIGNED NOT NULL,
	`src_port` INT UNSIGNED NOT NULL,
	`dst_port` INT UNSIGNED NOT NULL,
	`proto` ENUM('tcp','udp','icmp'),
	`received_at` TIMESTAMP,
	PRIMARY KEY (`id`),
	INDEX USING HASH (src_ip)
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
  `host_ignored_timestamp` TIMESTAMP DEFAULT NOW(),
  `host_ignored_note` TEXT DEFAULT NULL,
  PRIMARY KEY  (`host_id`),
  KEY `location_id` (`location_id`),
  UNIQUE KEY `host_ip` (`host_ip`)
) ENGINE = INNODB;

--  For end user notes about a host
CREATE TABLE IF NOT EXISTS `hostnote` (
	`hostnote_id` INT NOT NULL AUTO_INCREMENT,
	`host_id` INT NOT NULL,
	`hostnote_note` TEXT DEFAULT NULL,
  PRIMARY KEY  (`hostnote_id`),
  KEY `host_id` (`host_id`)
) ENGINE = INNODB;

-- Track alternative IP addresses and domain names
CREATE TABLE IF NOT EXISTS `host_alts` (
	`host_id` INT NOT NULL,
	`host_alt_ip` varchar(15),
	`host_alt_name` varchar(255),
  PRIMARY KEY  (`host_id`)
) ENGINE = INNODB;

-- For grouping hosts (say, "HR Machines")
CREATE TABLE IF NOT EXISTS `host_group` (
	`host_group_id` INT NOT NULL AUTO_INCREMENT,
	`host_group_name` VARCHAR(255) NOT NULL,
  PRIMARY KEY  (`host_group_id`)
) ENGINE = INNODB;

-- Mapping table for hosts to groups
CREATE TABLE IF NOT EXISTS `host_x_host_group` (
	`host_group_id` INT NOT NULL,
	`host_id` INT NOT NULL,
  KEY  (`host_group_id`),
  KEY `host_id` (`host_id`)
) ENGINE = INNODB;

-- Track vulnerabilities discovered in certain hosts
CREATE TABLE IF NOT EXISTS `host_x_vuln` (
  `host_id` INT UNSIGNED NOT NULL,
  `vuln_id` INT UNSIGNED NOT NULL,
  `scan_id` INT UNSIGNED,
  `dated` TIMESTAMP NOT NULL DEFAULT NOW(),
  INDEX USING HASH (`host_id`),
  INDEX USING HASH (`vuln_id`),
  INDEX USING BTREE (`dated`)
) ENGINE = INNODB;

-- Free tagging of hosts
CREATE TABLE IF NOT EXISTS `host_x_tag` (
	`host_id` INT NOT NULL,
	`tag_id` INT NOT NULL,
  KEY `host_id` (`host_id`),
  KEY `tag_id` (`tag_id`)
) ENGINE = INNODB;

-- Physical addresses for hosts
CREATE TABLE IF NOT EXISTS `location` (
	`location_id` INT NOT NULL AUTO_INCREMENT,
	`location_name` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`location_id`)
);

-- Log file table
CREATE TABLE IF NOT EXISTS `log` (
	`log_id` INT NOT NULL AUTO_INCREMENT,
	`log_timestamp` TIMESTAMP NOT NULL DEFAULT NOW(),
	`log_type` VARCHAR(255) DEFAULT NULL,
	`log_message` text NOT NULL,
	PRIMARY KEY (`log_id`)
);

-- NMAP scan tracking table
CREATE TABLE IF NOT EXISTS `nmap_scan` (
	`nmap_scan_id` INT NOT NULL AUTO_INCREMENT,
	`nmap_scan_datetime` DATETIME NOT NULL,
	PRIMARY KEY (`nmap_scan_id`)
) ENGINE = INNODB;

-- Results of NMAP scans
CREATE TABLE IF NOT EXISTS `nmap_scan_result` (
	`nmap_scan_result_id` INT NOT NULL AUTO_INCREMENT,
	`host_id` INT NOT NULL,
	`nmap_scan_result_port_number` INT NOT NULL,
	`state_id` INT NOT NULL,
  `nmap_scan_result_service_name` VARCHAR(50) NOT NULL,
  `nmap_scan_result_service_version` VARCHAR(255) NOT NULL,
	`nmap_scan_result_is_new` INT NOT NULL DEFAULT 1,
	`scan_id` INT NOT NULL,
	`nmap_scan_result_timestamp` TIMESTAMP NOT NULL DEFAULT NOW(),
	PRIMARY KEY (`nmap_scan_result_id`),
  KEY `host_id` (`host_id`),
  KEY `nmap_scan_result_port_number` (`nmap_scan_result_port_number`),
  KEY `scan_id` (`scan_id`)
);

-- OSSEC alerts from clients
CREATE TABLE IF NOT EXISTS `ossec_alerts` (
	`alert_id` INT NOT NULL AUTO_INCREMENT,
	`alert_date` TIMESTAMP NOT NULL DEFAULT NOW(),
	`host_id` INT NOT NULL,
	`alert_log` VARCHAR(255) DEFAULT NULL,
	`rule_id` INT NOT NULL,
	`rule_src_ip` VARCHAR(15) DEFAULT NULL,
	`rule_src_ip_numeric` INT UNSIGNED,
	`rule_user` VARCHAR(20) DEFAULT NULL,
	`rule_log` TEXT DEFAULT NULL,
	`alert_ossec_id` VARCHAR(50) NOT NULL,
	PRIMARY KEY (`alert_id`),
	KEY `host_id` (`host_id`),
	KEY `rule_id` (`rule_id`),
	INDEX USING HASH (rule_src_ip_numeric),
	INDEX USING BTREE (alert_date)
) ENGINE = INNODB;

-- OSSEC rules (defined in the server)
CREATE TABLE IF NOT EXISTS `ossec_rules` (
	`rule_id` INT NOT NULL AUTO_INCREMENT,
	`rule_number` INT NOT NULL,
	`rule_level` INT NOT NULL,
	`rule_message` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`rule_id`),
	KEY `rule_number` (`rule_number`),
	KEY `rule_level` (`rule_level`)
);

-- Table for regularly generated reports
CREATE TABLE IF NOT EXISTS `reports` (
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
INSERT INTO `scan_type` SET `scan_type_name` = 'NMAP network scanner', 
	`scan_type_id`=1, 
	`scan_type_script`='nmap_scan.php' ON DUPLICATE KEY UPDATE `scan_type_id`=1;

-- Map scans to host groups
CREATE TABLE IF NOT EXISTS `scan_x_host_group` (
	`host_group_id` INT NOT NULL,
	`scan_id` INT NOT NULL,
  KEY `host_group_id` (`host_group_id`),
  KEY `scan_id` (`scan_id`)
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


-- Support groups are entities composed of individuals that
-- handle host support
CREATE TABLE IF NOT EXISTS `supportgroup` (
	`supportgroup_id` INT NOT NULL AUTO_INCREMENT,
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

-- FQDN's that resolve to hosts
CREATE TABLE IF NOT EXISTS `url` (
  `host_id` INT NOT NULL,
  `host_ip` INT NOT NULL,
  `url_url` varchar(255) NOT NULL,
  `url_screenshot` varchar(255) DEFAULT NULL,
  UNIQUE KEY `url_url` (`url_url`)
) ENGINE = INNODB;

-- Finally the user table
CREATE TABLE IF NOT EXISTS `user` (
	`user_id` INT NOT NULL AUTO_INCREMENT,
	`user_name` VARCHAR(255) NOT NULL,
	`user_pass` VARCHAR(255) NOT NULL,
	`user_is_admin` INT(1) DEFAULT 0,
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `user_name` (`user_name`)
) ENGINE = INNODB;
INSERT INTO `user` set `user_id`=1, 
	`user_name`='administrator', 
	`user_pass`='$1$afQP7QmR$4cRYamEz5Z7lyxpsRTow/1', -- just "password" 
	`user_is_admin`=1 
	ON DUPLICATE KEY UPDATE `user_id` = 1;
	
-- Map users to support groups
CREATE TABLE IF NOT EXISTS `user_x_supportgroup` (
	`user_id` INT NOT NULL,
	`supportgroup_id` INT NOT NULL		
) ENGINE = INNODB;

-- Vulnerabilities
CREATE TABLE IF NOT EXISTS `vuln` (
  `vuln_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `vuln_name` varchar(255),
  `vuln_description` text,
  PRIMARY KEY (`vuln_id`)
) ENGINE = INNODB;

-- Vulnerabilities discovered
CREATE TABLE IF NOT EXISTS `vuln_x_host` (
  `vuln_id` INT UNSIGNED NOT NULL,
  `host_id` INT UNSIGNED NOT NULL
) ENGINE = INNODB;

-- Add ability to free tag vulnerabilities
CREATE TABLE IF NOT EXISTS `vuln_x_tag` (
  `vuln_id` INT UNSIGNED NOT NULL,
  `tag_id` INT UNSIGNED NOT NULL
) ENGINE = INNODB;


--
-- Create views to the Kojoney2 tables if it's installed
--
DROP PROCEDURE IF EXISTS kojoney_views;
DELIMITER $$
CREATE PROCEDURE kojoney_views()
BEGIN
	SET @kojoney_table_count := (SELECT COUNT(SCHEMA_NAME) FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = 'kojoney');
		-- Only create views if Kojoney2 is installed
    IF @kojoney_table_count > 0 THEN
			CREATE OR REPLACE VIEW koj_login_attempts AS SELECT * FROM kojoney.login_attempts;
			CREATE OR REPLACE VIEW koj_executed_commands AS SELECT * FROM kojoney.executed_commands;
			CREATE OR REPLACE VIEW koj_downloads AS SELECT * FROM kojoney.downloads;
		END IF;
END$$
DELIMITER ;

call kojoney_views();



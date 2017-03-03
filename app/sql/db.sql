# HECTOR database configuration file
# Author Justin C. Klein Keane <jukeane@sas.upenn.edu>

CREATE DATABASE IF NOT EXISTS hector;
use hector;

-- Alerts are OSSEC alerts
CREATE TABLE IF NOT EXISTS `hector`.`alert` (
  `alert_id` INT(11) NOT NULL AUTO_INCREMENT,
  `alert_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `alert_string` VARCHAR(255) NULL DEFAULT NULL,
  `host_id` INT(11) NOT NULL,
  PRIMARY KEY (`alert_id`),
  INDEX `host_id` (`host_id` ASC),
  CONSTRAINT `fk_alert_1`
    FOREIGN KEY (`host_id`)
    REFERENCES `hector`.`host` (`host_id`)
    ON DELETE CASCADE
) ENGINE = INNODB;

-- API keys 
CREATE TABLE IF NOT EXISTS `hector`.`api_key` (
  `api_key_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `api_key_value` VARCHAR(255) NOT NULL,
  `api_key_resource` VARCHAR(255) NOT NULL,
  `api_key_holder_name` VARCHAR(255) NOT NULL,
  `api_key_holder_affiliation` VARCHAR(255) NOT NULL,
  `api_key_holder_email` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`api_key_id`)
) ENGINE = INNODB;

-- Actual data from RSS feeds
CREATE TABLE IF NOT EXISTS `hector`.`article` (
  `article_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `article_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `article_title` VARCHAR(255) NULL DEFAULT NULL,
  `article_teaser` TEXT NULL DEFAULT NULL,
  `article_url` VARCHAR(255) NULL DEFAULT NULL,
  `article_body` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`article_id`),
  INDEX `article_date` USING BTREE (`article_date` ASC)
) ENGINE = INNODB;

-- Allow free tagging of articles from RSS feeds
CREATE TABLE IF NOT EXISTS `article_x_tag` (
  `article_id` INT NOT NULL,
  `tag_id` INT NOT NULL,
  INDEX (`article_id`),
  INDEX (`tag_id`)
) ENGINE = INNODB;

-- If the article describes a vulnerability pair them
CREATE TABLE IF NOT EXISTS `hector`.`article_x_tag` (
  `article_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `tag_id` INT(11) NOT NULL,
  INDEX `article_id` (`article_id` ASC),
  INDEX `tag_id` (`tag_id` ASC),
  CONSTRAINT `fk_article_x_tag_2`
    FOREIGN KEY (`article_id`)
    REFERENCES `hector`.`article` (`article_id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_article_x_tag_1`
    FOREIGN KEY (`tag_id`)
    REFERENCES `hector`.`tag` (`tag_id`)
    ON DELETE CASCADE
);

-- Darknet sensor
CREATE TABLE IF NOT EXISTS `hector`.`darknet` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `src_ip` INT(10) UNSIGNED NOT NULL,
  `dst_ip` INT(10) UNSIGNED NOT NULL,
  `src_port` INT(10) UNSIGNED NOT NULL,
  `dst_port` INT(10) UNSIGNED NOT NULL,
  `proto` ENUM('tcp', 'udp', 'icmp') NULL DEFAULT NULL,
  `country_code` VARCHAR(2) NULL DEFAULT NULL,
  `received_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `received` DATE NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `src_ip` USING HASH (`src_ip` ASC)
) ENGINE = INNODB;

-- Table used to hold totals for darknet hits to speed up reporting
CREATE TABLE IF NOT EXISTS `hector`.`darknet_totals` (
  `countrytime` VARCHAR(15) NOT NULL,
  `country_code` VARCHAR(3) NULL DEFAULT NULL,
  `day_of_total` DATE NOT NULL,
  `count` INT(11) NOT NULL,
  UNIQUE INDEX `countrytime` (`countrytime` ASC)
) ENGINE = INNODB;

-- Domains
CREATE TABLE IF NOT EXISTS `hector`.`domain` (
  `domain_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `domain_name` VARCHAR(255) NOT NULL,
  `domain_is_malicious` INT(1) NOT NULL DEFAULT '0',
  `domain_marked_malicious_datetime` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  `domain_categories` VARCHAR(255) NULL DEFAULT NULL,
  `malware_service_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`domain_id`),
  UNIQUE INDEX `domain_name` (`domain_name` ASC)
) ENGINE = INNODB;

-- Form table is used for anti XSRF tokens
CREATE TABLE IF NOT EXISTS `hector`.`form` (
  `form_id` INT(11) NOT NULL AUTO_INCREMENT,
  `form_name` VARCHAR(255) NOT NULL,
  `form_token` VARCHAR(32) NOT NULL,
  `form_ip` VARCHAR(15) NOT NULL,
  `form_datetime` DATETIME NOT NULL,
  PRIMARY KEY (`form_id`)
) ENGINE = INNODB;

-- http://geolite.maxmind.com/download/geoip/database/GeoIPCountryCSV.zip
CREATE TABLE IF NOT EXISTS `hector`.`geoip` (
  `start_ip_str` VARCHAR(15) NULL DEFAULT NULL,
  `end_ip_str` VARCHAR(15) NULL DEFAULT NULL,
  `start_ip_long` INT(10) UNSIGNED NULL DEFAULT NULL,
  `end_ip_long` INT(10) UNSIGNED NULL DEFAULT NULL,
  `country_code` VARCHAR(2) NULL DEFAULT NULL,
  `country_name` VARCHAR(255) NULL DEFAULT NULL,
  INDEX `start_ip_long` USING HASH (`start_ip_long` ASC),
  INDEX `end_ip_long` USING HASH (`end_ip_long` ASC)
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
  `host_os_family` VARCHAR(100) DEFAULT NULL,
  `host_os_type` VARCHAR(100) DEFAULT NULL,
  `host_os_vendor` VARCHAR(100) DEFAULT NULL,
  `host_link` VARCHAR(255) DEFAULT NULL,
  `host_note` TEXT DEFAULT NULL,
  `host_sponsor` VARCHAR(50) DEFAULT NULL, -- user contact
  `host_technical` VARCHAR(255) DEFAULT NULL, -- technical contact
  `supportgroup_id` INT DEFAULT NULL, -- responsible support of field staff
  `host_verified` tinyint(1) DEFAULT '0', -- has the information been vetted
  `host_ignored` tinyint(1) DEFAULT '0', -- Don't scan this host
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
CREATE TABLE IF NOT EXISTS `hector`.`host_alts` (
  `host_id` INT NOT NULL,
  `host_alt_ip` VARCHAR(15) NULL DEFAULT NULL,
  `host_alt_name` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`host_id`),
  CONSTRAINT `fk_host_alts_1`
    FOREIGN KEY (`host_id`)
    REFERENCES `hector`.`host` (`host_id`)
    ON DELETE CASCADE
) ENGINE = INNODB;

-- For grouping hosts (say, "HR Machines")
CREATE TABLE IF NOT EXISTS `hector`.`host_group` (
  `host_group_id` INT(11) NOT NULL AUTO_INCREMENT,
  `host_group_name` VARCHAR(255) NOT NULL,
  `host_group_detail` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`host_group_id`)
) ENGINE = INNODB;

-- Mapping table for hosts to groups
CREATE TABLE IF NOT EXISTS `hector`.`host_x_host_group` (
  `host_group_id` INT(11) NOT NULL,
  `host_id` INT(11) NOT NULL,
  INDEX `host_group_id` USING BTREE (`host_group_id` ASC),
  INDEX `host_id` USING BTREE (`host_id` ASC),
  CONSTRAINT `fk_host_x_host_group_1`
    FOREIGN KEY (`host_group_id`)
    REFERENCES `hector`.`host_group` (`host_group_id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_host_x_host_group_2`
    FOREIGN KEY (`host_id`)
    REFERENCES `hector`.`host` (`host_id`)
    ON DELETE CASCADE
) ENGINE = INNODB;

-- Free tagging of hosts
CREATE TABLE IF NOT EXISTS `hector`.`host_x_tag` (
  `host_id` INT(11) NOT NULL,
  `tag_id` INT(11) NOT NULL,
  INDEX `host_id` (`host_id` ASC),
  INDEX `tag_id` (`tag_id` ASC),
  CONSTRAINT `fk_host_x_tag_2`
    FOREIGN KEY (`tag_id`)
    REFERENCES `hector`.`tag` (`tag_id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_host_x_tag_1`
    FOREIGN KEY (`host_id`)
    REFERENCES `hector`.`host` (`host_id`)
    ON DELETE CASCADE
) ENGINE = INNODB;

-- Master incident table
CREATE TABLE IF NOT EXISTS `hector`.`incident` (
  `incident_id` INT(11) NOT NULL AUTO_INCREMENT,
  `incident_title` VARCHAR(255) NOT NULL,
  `incident_month` TINYINT(4) NOT NULL,
  `incident_year` INT(11) NOT NULL,
  `agent_id` INT(11) NOT NULL,
  `action_id` INT(11) NOT NULL,
  `asset_id` INT(11) NOT NULL,
  `confidential_data` INT(1) NULL DEFAULT '0',
  `integrity_loss` TEXT NULL DEFAULT NULL,
  `authenticity_loss` TEXT NULL DEFAULT NULL,
  `availability_loss_timeframe_id` INT(11) NOT NULL,
  `utility_loss` TEXT NULL DEFAULT NULL,
  `action_to_discovery_timeframe_id` INT(11) NOT NULL,
  `discovery_to_containment_timeframe_id` INT(11) NOT NULL,
  `discovery_id` INT(11) NOT NULL,
  `discovery_evidence_sources` TEXT NULL DEFAULT NULL,
  `discovery_metrics` TEXT NULL DEFAULT NULL,
  `2020_hindsight` TEXT NULL DEFAULT NULL,
  `correction_recommended` TEXT NULL DEFAULT NULL,
  `asset_loss_magnitude_id` INT(11) NOT NULL,
  `disruption_magnitude_id` INT(11) NOT NULL,
  `response_cost_magnitude_id` INT(11) NOT NULL,
  `impact_magnitude_id` INT(11) NOT NULL,
  PRIMARY KEY (`incident_id`),
  INDEX `agent_id` (`agent_id` ASC),
  INDEX `action_id` (`action_id` ASC),
  INDEX `asset_id` (`asset_id` ASC),
  INDEX `impact_magnitude_id` (`impact_magnitude_id` ASC),
  INDEX `availability_loss_timeframe_id` (`availability_loss_timeframe_id` ASC),
  INDEX `action_to_discovery_timeframe_id` (`action_to_discovery_timeframe_id` ASC),
  INDEX `discovery_to_containment_timeframe_id` (`discovery_to_containment_timeframe_id` ASC),
  INDEX `discovery_id` (`discovery_id` ASC),
  INDEX `asset_loss_magnitude_id` (`asset_loss_magnitude_id` ASC),
  INDEX `disruption_magnitude_id` (`disruption_magnitude_id` ASC),
  INDEX `response_cost_magnitude_id` (`response_cost_magnitude_id` ASC),
  INDEX `impact_magnitude_id_2` (`impact_magnitude_id` ASC),
  CONSTRAINT `fk_incident_11`
    FOREIGN KEY (`impact_magnitude_id`)
    REFERENCES `hector`.`incident_magnitude` (`magnitude_id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_incident_1`
    FOREIGN KEY (`agent_id`)
    REFERENCES `hector`.`incident_agent` (`agent_id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_incident_10`
    FOREIGN KEY (`response_cost_magnitude_id`)
    REFERENCES `hector`.`incident_magnitude` (`magnitude_id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_incident_2`
    FOREIGN KEY (`action_id`)
    REFERENCES `hector`.`incident_action` (`action_id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_incident_3`
    FOREIGN KEY (`asset_id`)
    REFERENCES `hector`.`incident_asset` (`asset_id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_incident_4`
    FOREIGN KEY (`availability_loss_timeframe_id`)
    REFERENCES `hector`.`incident_timeframe` (`timeframe_id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_incident_5`
    FOREIGN KEY (`action_to_discovery_timeframe_id`)
    REFERENCES `hector`.`incident_timeframe` (`timeframe_id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_incident_6`
    FOREIGN KEY (`discovery_to_containment_timeframe_id`)
    REFERENCES `hector`.`incident_timeframe` (`timeframe_id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_incident_7`
    FOREIGN KEY (`discovery_id`)
    REFERENCES `hector`.`incident_discovery` (`discovery_id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_incident_8`
    FOREIGN KEY (`asset_loss_magnitude_id`)
    REFERENCES `hector`.`incident_magnitude` (`magnitude_id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_incident_9`
    FOREIGN KEY (`disruption_magnitude_id`)
    REFERENCES `hector`.`incident_magnitude` (`magnitude_id`)
    ON DELETE CASCADE
) ENGINE = INNODB;  

-- Action that caused the incident
CREATE TABLE IF NOT EXISTS `hector`.`incident_action` (
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
INSERT INTO `incident_action` SET `action_id` = 10, `action_action` = 'Other/Unknown' ON DUPLICATE KEY UPDATE `action_id`=10;
INSERT INTO `incident_action` SET `action_id` = 11, `action_action` = 'Phishing' ON DUPLICATE KEY UPDATE `action_id`=11;

-- Source of the agent who caused the incident
CREATE TABLE IF NOT EXISTS `hector`.`incident_agent` (
  `agent_id` INT NOT NULL AUTO_INCREMENT,
  `agent_agent` VARCHAR(255) NOT NULL,
  PRIMARY KEY  (`agent_id`)
) ENGINE = INNODB;
INSERT INTO `incident_agent` SET `agent_id` = 1, `agent_agent` = 'External' ON DUPLICATE KEY UPDATE `agent_id`=1;
INSERT INTO `incident_agent` SET `agent_id` = 2, `agent_agent` = 'Internal' ON DUPLICATE KEY UPDATE `agent_id`=2;
INSERT INTO `incident_agent` SET `agent_id` = 3, `agent_agent` = 'Partner' ON DUPLICATE KEY UPDATE `agent_id`=3;
INSERT INTO `incident_agent` SET `agent_id` = 4, `agent_agent` = 'Other/Unknown' ON DUPLICATE KEY UPDATE `agent_id`=4;
  
-- Asset affected by the incident
CREATE TABLE IF NOT EXISTS `hector`.`incident_asset` (
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
INSERT INTO `incident_asset` SET `asset_id` = 11, `asset_asset` = 'Fileserver server' ON DUPLICATE KEY UPDATE `asset_id`=11;
INSERT INTO `incident_asset` SET `asset_id` = 12, `asset_asset` = 'Management server' ON DUPLICATE KEY UPDATE `asset_id`=12;
INSERT INTO `incident_asset` SET `asset_id` = 13, `asset_asset` = 'IoT device' ON DUPLICATE KEY UPDATE `asset_id`=13;
INSERT INTO `incident_asset` SET `asset_id` = 14, `asset_asset` = 'Medical device' ON DUPLICATE KEY UPDATE `asset_id`=14;
INSERT INTO `incident_asset` SET `asset_id` = 15, `asset_asset` = 'None' ON DUPLICATE KEY UPDATE `asset_id`=15;
INSERT INTO `incident_asset` SET `asset_id` = 16, `asset_asset` = 'PII data' ON DUPLICATE KEY UPDATE `asset_id`=16;

-- Method of incident discovery
CREATE TABLE IF NOT EXISTS `hector`.`incident_discovery` (
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
INSERT INTO `incident_discovery` SET `discovery_id` = 10, `discovery_method` = 'Host based intrusion detection system (HIDS)' ON DUPLICATE KEY UPDATE `discovery_id`=10;
INSERT INTO `incident_discovery` SET `discovery_id` = 11, `discovery_method` = 'Partner or vendor' ON DUPLICATE KEY UPDATE `discovery_id`=11;

-- Incident magnitudes
CREATE TABLE IF NOT EXISTS `hector`.`incident_magnitude` (
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
CREATE TABLE IF NOT EXISTS `hector`.`incident_timeframe` (
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
CREATE TABLE IF NOT EXISTS `hector`.`incident_x_tag` (
  `incident_id` INT(11) NOT NULL,
  `tag_id` INT(11) NOT NULL,
  INDEX `incident_id` (`incident_id` ASC),
  INDEX `tag_id` (`tag_id` ASC),
  CONSTRAINT `fk_incident_x_tag_2`
    FOREIGN KEY (`tag_id`)
    REFERENCES `hector`.`tag` (`tag_id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_incident_x_tag_1`
    FOREIGN KEY (`incident_id`)
    REFERENCES `hector`.`incident` (`incident_id`)
    ON DELETE CASCADE
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `hector`.`koj_executed_command` (
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

CREATE TABLE IF NOT EXISTS `hector`.`koj_login_attempt` (
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
CREATE TABLE IF NOT EXISTS `hector`.`location` (
	`location_id` INT NOT NULL AUTO_INCREMENT,
	`location_name` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`location_id`)
);

-- Log file table
CREATE TABLE IF NOT EXISTS `hector`.`log` (
	`log_id` INT NOT NULL AUTO_INCREMENT,
	`log_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`log_type` VARCHAR(255) DEFAULT NULL,
	`log_message` text NOT NULL,
	PRIMARY KEY (`log_id`)
) ENGINE = INNODB;

-- Keep track of malware uploaded to HECTOR
CREATE TABLE IF NOT EXISTS `hector`.`malware` (
  `id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `source` VARCHAR(255) NULL DEFAULT NULL,
  `source_ip` VARCHAR(15) NOT NULL,
  `source_ip_numeric` INT(10) UNSIGNED NOT NULL,
  `source_url` VARCHAR(255) NULL DEFAULT NULL,
  `md5sum` VARCHAR(32) NULL DEFAULT NULL,
  `filetype` VARCHAR(255) NULL DEFAULT NULL,
  `clamsig` TEXT NULL DEFAULT NULL,
  `sensor_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `file` LONGBLOB NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `source_ip_numeric` (`source_ip_numeric` ASC),
  INDEX `md5sum` (`md5sum` ASC)
) ENGINE = InnoDB;

-- Add ability to free tag malware
CREATE TABLE IF NOT EXISTS `hector`.`malware_x_tag` (
  `malware_id` INT(10) UNSIGNED NOT NULL,
  `tag_id` INT(11) NULL DEFAULT NULL,
  INDEX `malware_id` (`malware_id` ASC),
  INDEX `tag_id` (`tag_id` ASC),
  CONSTRAINT `fk_malware_x_tag_2`
    FOREIGN KEY (`tag_id`)
    REFERENCES `hector`.`tag` (`tag_id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_malware_x_tag_1`
    FOREIGN KEY (`malware_id`)
    REFERENCES `hector`.`malware` (`id`)
    ON DELETE CASCADE
) ENGINE = INNODB;

-- Services that identify malware domains
CCREATE TABLE IF NOT EXISTS `hector`.`malware_service` (
  `malware_service_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `malware_service_name` VARCHAR(255) NOT NULL,
  `malware_service_url` VARCHAR(255) NULL DEFAULT NULL,
  `malware_service_api_key` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`malware_service_id`),
  UNIQUE INDEX `malware_service_name` (`malware_service_name` ASC)
) ENGINE = INNODB;

-- NameD resolutions
CREATE TABLE IF NOT EXISTS `hector`.`named_resolution` (
  `named_resolution_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `named_resolution_src_ip` VARCHAR(15) NOT NULL,
  `named_resolution_src_ip_numeric` INT(10) UNSIGNED NOT NULL,
  `domain_id` INT(10) UNSIGNED NOT NULL,
  `named_resolution_datetime` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  `named_src_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`named_resolution_id`),
  INDEX `domain_id` (`domain_id` ASC),
  INDEX `named_resolution_src_ip_numeric` (`named_resolution_src_ip_numeric` ASC),
  INDEX `named_resolution_datetime` (`named_resolution_datetime` ASC),
  INDEX `fk_named_resolution_2` (`named_src_id` ASC),
  CONSTRAINT `fk_named_resolution_2`
    FOREIGN KEY (`named_src_id`)
    REFERENCES `hector`.`named_src` (`named_src_id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_named_resolution_1`
    FOREIGN KEY (`domain_id`)
    REFERENCES `hector`.`domain` (`domain_id`)
    ON DELETE CASCADE
) ENGINE = INNODB;

-- NameD sources
CREATE TABLE IF NOT EXISTS `hector`.`named_src` (
  `named_src_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `named_src_name` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`named_src_id`),
  UNIQUE INDEX `named_src_name` (`named_src_name` ASC)
) ENGINE = INNODB;

-- Results of NMAP scans
CREATE TABLE IF NOT EXISTS `hector`.`nmap_result` (
  `nmap_result_id` INT(11) NOT NULL AUTO_INCREMENT,
  `host_id` INT(11) NOT NULL,
  `state_id` INT(11) NOT NULL,
  `scan_id` INT(11) NOT NULL,
  `nmap_result_port_number` INT(11) NOT NULL,
  `nmap_result_protocol` VARCHAR(4) NULL DEFAULT NULL,
  `nmap_result_service_name` VARCHAR(50) NOT NULL,
  `nmap_result_service_version` VARCHAR(255) NOT NULL,
  `nmap_result_is_new` INT(11) NOT NULL DEFAULT '1',
  `nmap_result_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`nmap_result_id`),
  INDEX `host_id` (`host_id` ASC),
  INDEX `nmap_result_port_number` (`nmap_result_port_number` ASC),
  INDEX `scan_id` (`scan_id` ASC),
  INDEX `fk_nmap_result_state` (`state_id` ASC),
  CONSTRAINT `fk_nmap_result_scan`
    FOREIGN KEY (`scan_id`)
    REFERENCES `hector`.`scan` (`scan_id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_nmap_result_host`
    FOREIGN KEY (`host_id`)
    REFERENCES `hector`.`host` (`host_id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_nmap_result_state`
    FOREIGN KEY (`state_id`)
    REFERENCES `hector`.`state` (`state_id`)
    ON DELETE CASCADE
) ENGINE = INNODB;

-- OSSEC alerts from clients
CREATE TABLE IF NOT EXISTS `hector`.`ossec_alert` (
  `alert_id` INT(11) NOT NULL AUTO_INCREMENT,
  `alert_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `host_id` INT(11) NOT NULL,
  `alert_log` VARCHAR(255) NULL DEFAULT NULL,
  `rule_id` INT(11) NOT NULL,
  `rule_src_ip` VARCHAR(15) NULL DEFAULT NULL,
  `rule_src_ip_numeric` INT(10) UNSIGNED NULL DEFAULT NULL,
  `rule_user` VARCHAR(20) NULL DEFAULT NULL,
  `rule_log` TEXT NULL DEFAULT NULL,
  `alert_ossec_id` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`alert_id`),
  INDEX `host_id` (`host_id` ASC),
  INDEX `rule_id` (`rule_id` ASC),
  INDEX `rule_src_ip_numeric` USING HASH (`rule_src_ip_numeric` ASC),
  INDEX `rule_id_2` USING HASH (`rule_id` ASC),
  INDEX `host_id_2` USING HASH (`host_id` ASC),
  INDEX `alert_date` USING BTREE (`alert_date` ASC),
  CONSTRAINT `fk_ossec_alert_2`
    FOREIGN KEY (`rule_id`)
    REFERENCES `hector`.`ossec_rule` (`rule_id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_ossec_alert_1`
    FOREIGN KEY (`host_id`)
    REFERENCES `hector`.`host` (`host_id`)
    ON DELETE CASCADE
) ENGINE = INNODB;

-- OSSEC rules (defined in the server)
CREATE TABLE IF NOT EXISTS `hector`.`ossec_rule` (
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
CREATE TABLE IF NOT EXISTS hector`.`risk` (
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
CREATE TABLE IF NOT EXISTS hector`.`report` (
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
INSERT INTO `hector`.`supportgroup` (`supportgroup_id`, `supportgroup_name`) VALUES (0, `No support group`);

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

-- Vulnerabilities details
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
  `vulnscan_id` VARCHAR(255) DEFAULT NULL,
  `risk_id` INT UNSIGNED NOT NULL DEFAULT 1,
  `host_id` INT UNSIGNED NOT NULL,  
  `vuln_id` INT UNSIGNED NOT NULL,
  INDEX (`vuln_id`), 
  INDEX (`host_id`),
  INDEX (`risk_id`),
  PRIMARY KEY (`vuln_detail_id`)
) ENGINE = INNODB;

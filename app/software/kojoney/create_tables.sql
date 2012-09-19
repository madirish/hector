USE hector;

DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `time` TIMESTAMP NOT NULL DEFAULT NOW(),
  `ip` VARCHAR(15) NOT NULL,
  `username` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255),
	PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `executed_commands`;
CREATE TABLE `executed_commands` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `time` TIMESTAMP NOT NULL DEFAULT NOW(),
  ip VARCHAR(15),
  command VARCHAR(255),
  PRIMARY KEY (`id`)
);

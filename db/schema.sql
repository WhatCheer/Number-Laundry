CREATE TABLE `prefixes` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`country` VARCHAR(127) NOT NULL,
	`country_code` CHAR(2) NOT NULL,
	`twilio_rate` VARCHAR(32) NOT NULL,
	`prefix` VARCHAR(15) NOT NULL,
	PRIMARY KEY ( `id` ),
	UNIQUE KEY `uniq_prefix` ( `prefix` (15) )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `last_temperature` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sensor` enum('Closet','Inside','Outside') NOT NULL,
  `temperature` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `index_date` (`date`),
  KEY `index_sensor` (`sensor`)
);

CREATE TABLE `temperature` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sensor` enum('Closet','Inside','Outside') NOT NULL,
  `temperature` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `index_date` (`date`),
  KEY `index_sensor` (`sensor`)
);

CREATE TABLE `dns` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mac` char(17) NOT NULL,
  `ip` varchar(15) DEFAULT '',
  `dhcp_ip` varchar(15) DEFAULT '',
  `hostname` varchar(40) DEFAULT '',
  `client_hostname` varchar(100) DEFAULT '',
  `comment` varchar(100) DEFAULT '',
  `user_id` int(11) DEFAULT NULL,
  `track_trips` tinyint(4) DEFAULT '1',
  `date_lastseen` datetime DEFAULT NULL,
  `manufacturer` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mac` (`mac`)
);

CREATE TABLE `device_visits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` int(11) DEFAULT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_lastseen` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `user_visits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_lastseen` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) DEFAULT NULL,
  `name` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
);


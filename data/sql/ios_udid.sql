drop table if exists `ios_udid`;
CREATE TABLE `ios_udid` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mail` varchar(255) NOT NULL,
  `device_uuid` varchar(255) NOT NULL,
  `device_udid` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_mail` (`mail`),
  KEY `idx_device_uuid` (`device_uuid`),
  KEY `idx_device_udid` (`device_udid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


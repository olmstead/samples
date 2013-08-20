CREATE TABLE IF NOT EXISTS `location` (
  `wban` SMALLINT NOT NULL UNIQUE,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`wban`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `day` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wban` SMALLINT NOT NULL,
  `day_of_year` date DEFAULT NULL,
  `max_temp` float DEFAULT NULL,
  `min_temp` float DEFAULT NULL,
  `avg_temp` float DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `location_ibfk_1` FOREIGN KEY (`wban`) REFERENCES `location` (`wban`) ON DELETE CASCADE 
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `location` (`name`, `WBAN`) VALUES ('San Francisco', 23234);
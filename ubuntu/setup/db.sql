SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE IF NOT EXISTS `airlines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21013 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `airports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `city_id` int(11) NOT NULL,
  `iata` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `icao` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `latitude` float NOT NULL,
  `longitude` float NOT NULL,
  `altitude` int(11) DEFAULT NULL,
  `timezone` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dst` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `db_timezone` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `source` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `airports_cities_id_fk` (`city_id`),
  CONSTRAINT `airports_cities_id_fk` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12058 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `cities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cities_name_country_id_uindex` (`name`,`country_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6640 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text` text COLLATE utf8_unicode_ci,
  `city_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `comments_cities_id_fk` (`city_id`),
  KEY `comments_users_id_fk` (`user_id`),
  CONSTRAINT `comments_cities_id_fk` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`),
  CONSTRAINT `comments_users_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `countries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `countries_name_uindex` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=238 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `routes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `airline_id` int(11) NOT NULL,
  `source_airport_id` int(11) NOT NULL,
  `destination_airport_id` int(11) NOT NULL,
  `codeshare` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `stops` int(11) NOT NULL DEFAULT '0',
  `equipment` char(3) COLLATE utf8_unicode_ci NOT NULL,
  `price` float NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `routes_airline_id_source_destination_uindex` (`airline_id`,`source_airport_id`,`destination_airport_id`),
  KEY `destination_airports_id_fk` (`destination_airport_id`),
  KEY `source_airports_id_fk` (`source_airport_id`),
  CONSTRAINT `destination_airports_id_fk` FOREIGN KEY (`destination_airport_id`) REFERENCES `airports` (`id`),
  CONSTRAINT `routes_airlines_id_fk` FOREIGN KEY (`airline_id`) REFERENCES `airlines` (`id`),
  CONSTRAINT `source_airports_id_fk` FOREIGN KEY (`source_airport_id`) REFERENCES `airports` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=65602 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `username` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `salt` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `role` enum('admin','user') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'user',
  `token` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `token_expires` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_uindex` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP FUNCTION IF EXISTS `CALCULATE_DISTANCE`;

DELIMITER ;;
CREATE FUNCTION `CALCULATE_DISTANCE`(lat1 FLOAT, lng1 FLOAT, lat2 FLOAT, lng2 FLOAT) RETURNS float
    DETERMINISTIC
BEGIN
    RETURN 6371 * 2 * ASIN(SQRT(
                POWER(SIN((lat1 - abs(lat2)) * pi()/180 / 2),
                      2) + COS(lat1 * pi()/180 ) * COS(abs(lat2) *
                                                       pi()/180) * POWER(SIN((lng1 - lng2) *
                                                                             pi()/180 / 2), 2) ));
END ;;
DELIMITER ;

SET FOREIGN_KEY_CHECKS=1;

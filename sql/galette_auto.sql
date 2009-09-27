--
-- Table structure for table `galette_auto_bodies`
--

CREATE TABLE `galette_auto_bodies` (
  `id_body` int(11) NOT NULL AUTO_INCREMENT,
  `body` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_body`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `galette_auto_brands`
--

CREATE TABLE `galette_auto_brands` (
  `id_brand` int(11) NOT NULL AUTO_INCREMENT,
  `brand` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_brand`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `galette_auto_cars`
--

CREATE TABLE `galette_auto_cars` (
  `id_car` int(11) NOT NULL AUTO_INCREMENT,
  `car_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `car_registration` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `car_first_registration_date` date NOT NULL,
  `car_first_circulation_date` date NOT NULL,
  `car_mileage` int(10) DEFAULT NULL,
  `car_comment` text COLLATE utf8_unicode_ci,
  `car_creation_date` date NOT NULL,
  `car_chassis_number` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `car_seats` int(1) DEFAULT NULL,
  `car_horsepower` int(4) DEFAULT NULL,
  `car_engine_size` int(11) DEFAULT NULL,
  `car_fuel` int(2) DEFAULT NULL,
  `id_color` int(11) NOT NULL,
  `id_body` int(11) NOT NULL,
  `id_state` int(11) NOT NULL,
  `id_transmission` int(11) NOT NULL,
  `id_finition` int(11) NOT NULL,
  `id_model` int(11) NOT NULL,
  PRIMARY KEY (`id_car`),
  KEY `galette_car_color` (`id_color`),
  KEY `galette_car_body` (`id_body`),
  KEY `galette_car_state` (`id_state`),
  KEY `galette_car_transmission` (`id_transmission`),
  KEY `galette_car_finition` (`id_finition`),
  KEY `galette_car_model` (`id_model`),
  CONSTRAINT `galette_car_color` FOREIGN KEY (`id_color`) REFERENCES `galette_auto_colors` (`id_color`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `galette_car_body` FOREIGN KEY (`id_body`) REFERENCES `galette_auto_bodies` (`id_body`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `galette_car_state` FOREIGN KEY (`id_state`) REFERENCES `galette_auto_states` (`id_state`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `galette_car_transmission` FOREIGN KEY (`id_transmission`) REFERENCES `galette_auto_transmissions` (`id_transmission`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `galette_car_finition` FOREIGN KEY (`id_finition`) REFERENCES `galette_auto_finitions` (`id_finition`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `galette_car_model` FOREIGN KEY (`id_model`) REFERENCES `galette_auto_models` (`id_model`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `galette_auto_colors`
--

CREATE TABLE `galette_auto_colors` (
  `id_color` int(11) NOT NULL AUTO_INCREMENT,
  `color` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_color`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `galette_auto_finitions`
--

CREATE TABLE `galette_auto_finitions` (
  `id_finition` int(11) NOT NULL AUTO_INCREMENT,
  `finition` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_finition`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `galette_auto_history`
--

CREATE TABLE `galette_auto_history` (
  `id_car` int(11) NOT NULL,
  `id_adh` int(10) NOT NULL,
  `history_date` date NOT NULL,
  `history_registration` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `id_color` int(11) NOT NULL,
  `id_state` int(11) NOT NULL,
  PRIMARY KEY (`id_car`,`id_adh`,`history_date`),
  KEY `galette_auto_history_car` (`id_car`),
  KEY `galette_auto_history_member` (`id_adh`),
  KEY `galette_auto_history_color` (`id_color`),
  KEY `galette_auto_history_state` (`id_state`),
  CONSTRAINT `galette_auto_history_car` FOREIGN KEY (`id_car`) REFERENCES `galette_auto_cars` (`id_car`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `galette_auto_history_member` FOREIGN KEY (`id_adh`) REFERENCES `galette_adherents` (`id_adh`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `galette_auto_history_color` FOREIGN KEY (`id_color`) REFERENCES `galette_auto_colors` (`id_color`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `galette_auto_history_state` FOREIGN KEY (`id_state`) REFERENCES `galette_auto_states` (`id_state`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `galette_auto_models`
--

CREATE TABLE `galette_auto_models` (
  `id_model` int(11) NOT NULL AUTO_INCREMENT,
  `model` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `id_brand` int(11) NOT NULL,
  PRIMARY KEY (`id_model`),
  KEY `galette_models_brand` (`id_brand`),
  CONSTRAINT `galette_models_brand` FOREIGN KEY (`id_brand`) REFERENCES `galette_auto_brands` (`id_brand`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `galette_auto_states`
--

CREATE TABLE `galette_auto_states` (
  `id_state` int(11) NOT NULL,
  `state` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `galette_auto_transmissions`
--

DROP TABLE IF EXISTS `galette_auto_transmissions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `galette_auto_transmissions` (
  `id_transmission` int(11) NOT NULL AUTO_INCREMENT,
  `transmission` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_transmission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

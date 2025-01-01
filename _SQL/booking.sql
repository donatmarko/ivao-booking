SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `airports` (
  `id` int(11) NOT NULL,
  `icao` varchar(4) NOT NULL,
  `name` varchar(50) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `charts` varchar(50) DEFAULT NULL,
  `order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE `config` (
  `key` varchar(30) NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `config` (`key`, `value`) VALUES
('auto_turnover', '1'),
('date_end', '2024-11-09 17:00:00'),
('date_start', '2024-11-09 09:00:00'),
('discord_webhook_url', ''),
('division_discord', 'https://discord.hu.ivao.aero/'),
('division_email', 'hu-staff@ivao.aero'),
('division_facebook', 'https://www.facebook.com/ivaohu'),
('division_instagram', 'https://www.instagram.com/ivao_hu/'),
('division_logo', 'img/logo.svg'),
('division_name', 'IVAO Hungary'),
('division_web', 'https://hu.ivao.aero'),
('event_name', 'Budapest RFE'),
('mode', '0'),
('time_only_in_list', '1'),
('wx_url', 'https://aviationweather.gov/api/data/{type}?ids={icao}');

CREATE TABLE `flights` (
  `id` int(11) NOT NULL,
  `turnover_id` int(11) DEFAULT NULL,
  `flight_number` varchar(10) NOT NULL,
  `callsign` varchar(10) NOT NULL,
  `origin_icao` varchar(4) NOT NULL,
  `destination_icao` varchar(4) NOT NULL,
  `departure_time` datetime DEFAULT NULL,
  `arrival_time` datetime DEFAULT NULL,
  `aircraft_icao` varchar(4) NOT NULL,
  `aircraft_freighter` tinyint(1) NOT NULL,
  `terminal` varchar(10) NOT NULL,
  `gate` varchar(10) NOT NULL,
  `route` text NOT NULL,
  `briefing` text DEFAULT NULL,
  `booked` int(11) NOT NULL COMMENT '0-free, 1-reserved, 2-booked',
  `booked_by` int(11) DEFAULT NULL,
  `booked_at` datetime DEFAULT NULL,
  `token` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE `slots` (
  `id` int(11) NOT NULL,
  `timeframe_id` int(11) NOT NULL,
  `callsign` varchar(10) NOT NULL,
  `origin_icao` varchar(4) NOT NULL,
  `destination_icao` varchar(4) NOT NULL,
  `aircraft_icao` varchar(4) NOT NULL,
  `aircraft_freighter` tinyint(1) NOT NULL,
  `terminal` varchar(10) NOT NULL,
  `gate` varchar(10) NOT NULL,
  `route` text NOT NULL,
  `booked` int(11) NOT NULL COMMENT '1-requested, 2-confirmed',
  `booked_by` int(11) NOT NULL,
  `booked_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE `timeframes` (
  `id` int(11) NOT NULL,
  `airport_icao` varchar(4) NOT NULL,
  `type` int(11) NOT NULL,
  `time` datetime NOT NULL,
  `count` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `vid` int(11) NOT NULL,
  `firstname` varchar(30) NOT NULL,
  `lastname` varchar(30) NOT NULL,
  `rating_atc` varchar(3) NOT NULL,
  `rating_pilot` varchar(3) NOT NULL,
  `email` varchar(50) NOT NULL,
  `privacy` tinyint(1) NOT NULL,
  `division` varchar(2) NOT NULL,
  `country` varchar(2) NOT NULL,
  `staff` text DEFAULT NULL,
  `permission` int(11) NOT NULL,
  `last_login` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


ALTER TABLE `airports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `icao` (`icao`),
  ADD KEY `order` (`order`);

ALTER TABLE `config`
  ADD PRIMARY KEY (`key`);

ALTER TABLE `flights`
  ADD PRIMARY KEY (`id`),
  ADD KEY `flight_number` (`flight_number`,`origin_icao`,`destination_icao`),
  ADD KEY `flights_FK_turnover_id` (`turnover_id`);

ALTER TABLE `slots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `timeframe_id` (`timeframe_id`,`origin_icao`,`destination_icao`);

ALTER TABLE `timeframes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `airport_icao` (`airport_icao`),
  ADD KEY `time` (`time`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vid` (`vid`);


ALTER TABLE `airports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `flights`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `timeframes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `flights`
  ADD CONSTRAINT `flights_FK_turnover_id` FOREIGN KEY (`turnover_id`) REFERENCES `flights` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

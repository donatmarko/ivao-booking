SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
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
  `charts` varchar(50) NOT NULL,
  `order` int(11) NOT NULL
);

CREATE TABLE `config` (
  `key` varchar(30) NOT NULL,
  `value` varchar(50) NOT NULL
);

INSERT INTO `config` (`key`, `value`) VALUES
('mode', '0'),
('division_name', 'IVAO Vatican'),
('event_name', 'Vatican RFE'),
('division_web', 'https://va.ivao.aero'),
('division_facebook', 'https://www.facebook.com/IVAO.Vatican/'),
('division_twitter', ''),
('date_start', '2019-08-19 10:00:00'),
('division_email', 'events@va.ivao.aero'),
('wx_url', 'https://api.donatus.hu/oavi/wx.php'),
('date_end', '2019-08-19 22:00:00');

CREATE TABLE `flights` (
  `id` int(11) NOT NULL,
  `flight_number` varchar(10) NOT NULL,
  `callsign` varchar(10) NOT NULL,
  `origin_icao` varchar(4) NOT NULL,
  `destination_icao` varchar(4) NOT NULL,
  `departure_time` datetime NOT NULL,
  `arrival_time` datetime NOT NULL,
  `aircraft_icao` varchar(4) NOT NULL,
  `aircraft_freighter` tinyint(1) NOT NULL,
  `terminal` varchar(10) NOT NULL,
  `gate` varchar(10) NOT NULL,
  `route` text NOT NULL,
  `booked` int(11) NOT NULL COMMENT '0-free, 1-prebooked, 2-booked',
  `booked_by` int(11) NOT NULL,
  `booked_at` datetime NOT NULL,
  `token` text NOT NULL
);

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
  `booked` int(11) NOT NULL COMMENT '0-???, 1-requested, 2-confirmed',
  `booked_by` int(11) NOT NULL,
  `booked_at` datetime NOT NULL
);

CREATE TABLE `timeframes` (
  `id` int(11) NOT NULL,
  `airport_icao` varchar(4) NOT NULL,
  `time` datetime NOT NULL,
  `count` int(11) NOT NULL
);

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `vid` int(11) NOT NULL,
  `firstname` varchar(30) NOT NULL,
  `lastname` varchar(30) NOT NULL,
  `rating_atc` varchar(3) NOT NULL,
  `rating_pilot` varchar(3) NOT NULL,
  `email` text NOT NULL DEFAULT '',
  `privacy` tinyint(1) NOT NULL,
  `division` varchar(2) NOT NULL,
  `country` varchar(2) NOT NULL,
  `skype` varchar(30) NOT NULL,
  `staff` text NOT NULL,
  `permission` int(11) NOT NULL,
  `last_login` datetime NOT NULL
);


ALTER TABLE `airports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `icao` (`icao`);

ALTER TABLE `config`
  ADD PRIMARY KEY (`key`);

ALTER TABLE `flights`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `slots`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `timeframes`
  ADD PRIMARY KEY (`id`);

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

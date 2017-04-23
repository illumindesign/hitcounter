SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE `stats` (
  `id` int(11) NOT NULL,
  `site` varchar(64) NOT NULL DEFAULT '',
  `page` varchar(255) NOT NULL DEFAULT '0',
  `ip` varchar(15) NOT NULL DEFAULT '',
  `browser` varchar(255) NOT NULL DEFAULT '',
  `os` varchar(8) DEFAULT NULL,
  `osversion` varchar(8) DEFAULT NULL,
  `browserbase` varchar(8) DEFAULT NULL,
  `browserversion` varchar(16) DEFAULT NULL,
  `ref` varchar(255) NOT NULL DEFAULT '',
  `ref_tld` varchar(32) DEFAULT NULL,
  `date` int(13) NOT NULL DEFAULT '0',
  `update` int(13) NOT NULL DEFAULT '0',
  `code` varchar(10) NOT NULL DEFAULT '',
  `hits` int(5) NOT NULL DEFAULT '0',
  `language` varchar(32) DEFAULT NULL,
  `country` varchar(32) DEFAULT NULL,
  `countrycode` varchar(2) DEFAULT NULL,
  `region` varchar(32) DEFAULT NULL,
  `city` varchar(32) DEFAULT NULL,
  `year` int(4) DEFAULT NULL,
  `week` int(2) DEFAULT NULL,
  `month` int(2) DEFAULT NULL,
  `day` int(2) DEFAULT NULL,
  `hour` int(2) DEFAULT NULL,
  `minute` int(2) DEFAULT NULL,
  `dayofyear` int(3) DEFAULT NULL,
  `dayofweek` int(1) DEFAULT NULL,
  `minuteofday` int(5) DEFAULT NULL,
  `bot` int(1) DEFAULT NULL,
  `mobile` int(1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


ALTER TABLE `stats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ip` (`ip`),
  ADD KEY `site` (`site`),
  ADD KEY `year` (`year`),
  ADD KEY `month` (`month`),
  ADD KEY `day` (`day`),
  ADD KEY `country` (`country`),
  ADD KEY `language` (`language`),
  ADD KEY `countrycode` (`countrycode`),
  ADD KEY `hour` (`hour`),
  ADD KEY `minute` (`minute`),
  ADD KEY `minuteofday` (`minuteofday`),
  ADD KEY `dayofyear` (`dayofyear`),
  ADD KEY `bot` (`bot`),
  ADD KEY `os` (`os`),
  ADD KEY `osversion` (`osversion`),
  ADD KEY `browserbase` (`browserbase`),
  ADD KEY `browserversion` (`browserversion`),
  ADD KEY `mobile` (`mobile`),
  ADD KEY `region` (`region`),
  ADD KEY `city` (`city`),
  ADD KEY `dayofweek` (`dayofweek`);


ALTER TABLE `stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

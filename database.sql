SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE `deposits` (
  `id` int(10) UNSIGNED NOT NULL,
  `uid` int(10) UNSIGNED NOT NULL,
  `txid` varchar(64) NOT NULL,
  `amount` int(11) UNSIGNED NOT NULL,
  `btc` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `games` (
  `uid` int(10) UNSIGNED NOT NULL,
  `secret` varchar(39) NOT NULL,
  `square` varchar(51) NOT NULL,
  `bet` int(11) UNSIGNED NOT NULL,
  `time` int(10) UNSIGNED NOT NULL,
  `test` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `auth` varchar(40) NOT NULL,
  `balance` int(11) UNSIGNED NOT NULL,
  `ref` int(10) UNSIGNED NOT NULL,
  `wallet` varchar(36) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `lock_dep` tinyint(1) NOT NULL,
  `btc` tinyint(1) NOT NULL,
  `email` varchar(40) NOT NULL,
  `password` varchar(32) NOT NULL,
  `online` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `withdraws` (
  `id` int(10) UNSIGNED NOT NULL,
  `uid` int(10) UNSIGNED NOT NULL,
  `txid` varchar(64) NOT NULL,
  `amount` int(11) UNSIGNED NOT NULL,
  `btc` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `deposits`
  ADD PRIMARY KEY (`id`);
  
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `withdraws`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `deposits`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `withdraws`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

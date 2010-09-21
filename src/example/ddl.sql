-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- ホスト: localhost
-- 生成時間: 2010 年 9 月 16 日 11:56
-- サーバのバージョン: 5.1.41
-- PHP のバージョン: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- データベース: `peewee`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `dept`
--

CREATE TABLE IF NOT EXISTS `dept` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10002 ;

--
-- テーブルのデータをダンプしています `dept`
--

INSERT INTO `dept` (`id`, `name`) VALUES
(10001, 'dept1');

-- --------------------------------------------------------

--
-- テーブルの構造 `emp`
--

CREATE TABLE IF NOT EXISTS `emp` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `age` int(5) NOT NULL,
  `insertdate` datetime NOT NULL,
  `dept_id` int(5) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- テーブルのデータをダンプしています `emp`
--

INSERT INTO `emp` (`id`, `name`, `age`, `insertdate`, `dept_id`) VALUES
(1, 'emp1', 33, '2010-09-16 11:45:38', NULL),
(2, 'emp2', 33, '2010-09-16 11:45:39', 10001);

-- --------------------------------------------------------

--
-- テーブルの構造 `mixed`
--

CREATE TABLE IF NOT EXISTS `mixed` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `col_int` int(5) NOT NULL,
  `col_str` varchar(200) NOT NULL,
  `col_date` date NOT NULL,
  `col_bool` tinyint(1) NOT NULL,
  `col_blob` blob,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- テーブルのデータをダンプしています `mixed`
--


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

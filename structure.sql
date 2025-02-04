-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2024-08-23 15:54:57
-- 服务器版本： 5.7.43-log
-- PHP 版本： 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `onlinetext`
--

-- --------------------------------------------------------

--
-- 表的结构 `accessinfo`
--

CREATE TABLE `accessinfo` (
  `id` int(11) NOT NULL COMMENT '用户ID',
  `filelengthlimit` int(11) DEFAULT NULL COMMENT 'NULL=无限制，-1=禁止编辑文件',
  `filescountlimit` int(11) DEFAULT NULL COMMENT 'NULL=无限制，-1=禁止编辑文件',
  `deleteable` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否允许用户删除文件'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `accessinfo`
--

INSERT INTO `accessinfo` (`id`, `filelengthlimit`, `filescountlimit`, `deleteable`) VALUES
(2, 20000, 100, 1),
(3, 20000, 100, 1);

-- --------------------------------------------------------

--
-- 表的结构 `files`
--

CREATE TABLE `files` (
  `id` int(11) NOT NULL COMMENT '文件ID',
  `name` varchar(45) NOT NULL COMMENT '文件名称',
  `ownerid` int(11) NOT NULL COMMENT '持有人ID',
  `content` mediumblob NOT NULL COMMENT '文件内容',
  `lastmodifiedtime` datetime NOT NULL COMMENT '上次更改时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `readsharedfilelog`
--

CREATE TABLE `readsharedfilelog` (
  `id` int(11) NOT NULL COMMENT '记录ID',
  `userid` int(11) NOT NULL COMMENT '读取人ID',
  `fileid` int(11) NOT NULL COMMENT '读取文件ID',
  `readtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '读取时间',
  `comment` tinytext COMMENT '注释'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `readsharedfilelog`
--

INSERT INTO `readsharedfilelog` (`id`, `userid`, `fileid`, `readtime`, `comment`) VALUES
(9, 3, 78, '2024-03-21 13:23:56', NULL);

-- --------------------------------------------------------

--
-- 表的结构 `sharedinfo`
--

CREATE TABLE `sharedinfo` (
  `fileid` int(11) NOT NULL COMMENT '文件ID',
  `sharecode` varchar(8) NOT NULL COMMENT '分享码',
  `avaliable` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '共享状态'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `sharedinfo`
--

INSERT INTO `sharedinfo` (`fileid`, `sharecode`, `avaliable`) VALUES
(1, 'WBA2BA', 0),
(67, 'LZ7HN1', 1),
(68, 'XBIPOI', 1),
(69, '9NLOFX', 1),
(70, 'MKOWFB', 0),
(71, 'VFF1AG', 1),
(72, '16BBZV', 0),
(73, '75ZBJT', 0),
(74, 'LDYXKG', 0),
(75, '47LE9D', 1),
(76, 'EK7JH0', 1),
(77, 'GV741J', 0),
(78, 'M4TZCT', 1),
(82, 'PHP9KS', 0),
(83, '4N3GRT', 0),
(84, 'MQ2ESV', 0),
(88, 'V9PSFP', 0),
(98, 'BZB33J', 0);

-- --------------------------------------------------------

--
-- 表的结构 `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL COMMENT 'ID',
  `name` varchar(20) NOT NULL COMMENT '名称',
  `password` tinyblob NOT NULL COMMENT '密码',
  `lastonlinetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '最后在线时间',
  `lastloginIP` tinytext NOT NULL COMMENT '上次登录IP',
  `token` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `users`
--

INSERT INTO `users` (`id`, `name`, `password`, `lastonlinetime`, `lastloginIP`, `token`) VALUES
(2, 'Leoministry', 0x37633461386430396361333736326166363165353935323039343364633236343934663839343162, '2024-08-21 22:44:28', '38.207.136.53', 'gJ3n1qt1IjbwxzC0'),
(3, 'jamesmay', 0x37633461386430396361333736326166363165353935323039343364633236343934663839343162, '2024-03-21 21:23:45', '183.213.85.222', '');

--
-- 转储表的索引
--

--
-- 表的索引 `accessinfo`
--
ALTER TABLE `accessinfo`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ownerid` (`ownerid`);

--
-- 表的索引 `readsharedfilelog`
--
ALTER TABLE `readsharedfilelog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userid` (`userid`),
  ADD KEY `fileid` (`fileid`);

--
-- 表的索引 `sharedinfo`
--
ALTER TABLE `sharedinfo`
  ADD PRIMARY KEY (`fileid`);

--
-- 表的索引 `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `files`
--
ALTER TABLE `files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '文件ID';

--
-- 使用表AUTO_INCREMENT `readsharedfilelog`
--
ALTER TABLE `readsharedfilelog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '记录ID', AUTO_INCREMENT=10;

--
-- 使用表AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID', AUTO_INCREMENT=4;

--
-- 限制导出的表
--

--
-- 限制表 `accessinfo`
--
ALTER TABLE `accessinfo`
  ADD CONSTRAINT `accessinfo_ibfk_1` FOREIGN KEY (`id`) REFERENCES `users` (`id`);

--
-- 限制表 `files`
--
ALTER TABLE `files`
  ADD CONSTRAINT `files_ibfk_1` FOREIGN KEY (`ownerid`) REFERENCES `users` (`id`);

--
-- 限制表 `readsharedfilelog`
--
ALTER TABLE `readsharedfilelog`
  ADD CONSTRAINT `readsharedfilelog_ibfk_2` FOREIGN KEY (`userid`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `readsharedfilelog_ibfk_3` FOREIGN KEY (`fileid`) REFERENCES `files` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

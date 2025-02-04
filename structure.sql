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

CREATE TABLE `Page` (
  `PageID` int(11) NOT NULL AUTO_INCREMENT,
  `Title` varchar(255) NOT NULL,
  `PageKey` varchar(255) NOT NULL,
  `Summary` varchar(255) NOT NULL,
  `Content` longtext NOT NULL,
  `MemberOnly` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`PageID`),
  UNIQUE KEY `Key_UNIQUE` (`PageKey`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
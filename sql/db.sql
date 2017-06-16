CREATE TABLE `tblGitReleaseTool_Task` (
  `iNo` int(11) NOT NULL AUTO_INCREMENT,
  `intProjectNo` varchar(50) DEFAULT NULL COMMENT '项目编码',
  `strName` varchar(255) DEFAULT NULL,
  `dtDate` datetime DEFAULT NULL,
  `strUser` varchar(255) DEFAULT NULL,
  `intUpdateStatus` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0：未更新，1：已更新，2：更新出错，3：已恢复',
  `intBakStatus` varchar(4) NOT NULL DEFAULT '0' COMMENT '0：未备份，1：已备份',
  `dtUpdateDate` datetime DEFAULT NULL,
  `dtBakDate` datetime DEFAULT NULL COMMENT '备份日期',
  PRIMARY KEY (`iNo`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='' AUTO_INCREMENT=1 ;

CREATE TABLE `tblGitReleaseTool_FileList` (
  `iNo` int(11) NOT NULL AUTO_INCREMENT,
  `intTaskPK` int(11) NOT NULL DEFAULT '0',
  `strFilePath` varchar(255) DEFAULT NULL,
  `intBakStatus` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0：未备份；1：有备份；2：新文件无需备份',
  `intUpdateStatus` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0：未更新；1:更新成功；2：更新出错；3：无需更新',
  PRIMARY KEY (`iNo`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='' AUTO_INCREMENT=1 ;
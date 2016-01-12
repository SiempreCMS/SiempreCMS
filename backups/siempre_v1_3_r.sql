-- Siempre CMS V1.3.0 DB
-- 12/11/2015

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `siempre_cms`
--
CREATE DATABASE IF NOT EXISTS `siempre_cms` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `siempre_cms`;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `cms_version` ( 
	`version` varchar(10) NOT NULL	
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `cms_version` (`version`) VALUES ('1.3.4');

--
-- Table structure for table `cms_content`
--

CREATE TABLE IF NOT EXISTS `cms_content` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nodeID` bigint(20) unsigned NOT NULL,
  `version` bigint(20) unsigned NOT NULL,
  `created` datetime DEFAULT NULL,
  `createdBy` int(11) NOT NULL,
  `lastUpdated` datetime DEFAULT NULL,
  `lastUpdatedBy` int(11) NOT NULL,
  `notes` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `templateID` bigint(20) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `languageID` int(11) NOT NULL DEFAULT '1',
  `noCache` BOOLEAN NOT NULL DEFAULT FALSE,
  `parentIDs` VARCHAR(500) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `nodeID` (`nodeID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1000 ;


INSERT INTO `cms_content` (`ID`, `nodeID`, `version`, `created`, `createdBy`, `lastUpdated`, `lastUpdatedBy`, `notes`, `templateID`, `published`, `languageID`) VALUES
(1, 1, 0, '2015-01-01 11:11:11', 1000, '2015-01-01 11:11:11', 1000, 'Site wide content, master template and settings', 1, 1, 1),
(1000, 1000, 0, '2015-01-01 11:11:11', 1000, '2015-01-01 11:11:11', 1000, 'Customise your own homepage!', 100, 1, 1);


-- --------------------------------------------------------

--
-- Table structure for table `cms_entity`
--

CREATE TABLE IF NOT EXISTS `cms_entity` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `entity_type` int(10) unsigned NOT NULL,
  `title` varchar(250) NOT NULL,
  `description` text NOT NULL,
  `templateID` int(10) NOT NULL,
  `template_tabID` int(10) NOT NULL,
  `sort_order` int(11) DEFAULT NULL,
  `sectionID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID` (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=101 ;

-- --------------------------------------------------------

INSERT INTO `cms_entity` (`ID`, `name`, `entity_type`, `title`, `description`, `templateID`, `template_tabID`, `sort_order`, `sectionID`) VALUES
(1, 'SiteTitle', 3, 'SiteTitle', '', 1, 1, 1, NULL),
(2, 'FooterHeader', 5, 'FooterHeader', '', 1, 2, 1, NULL),
(3, 'FooterContent', 5, 'FooterContent', '', 1, 2, 2, NULL),
(100, 'PageTitle', 3, 'PageTitle', 'Page title is shown in the browser title', 100, 100, 1, NULL);

--
-- Table structure for table `cms_entity_value_date`	
--

CREATE TABLE IF NOT EXISTS `cms_entity_value_date` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `entityID` int(10) unsigned NOT NULL,
  `contentID` int(10) unsigned NOT NULL,
  `value` datetime DEFAULT NULL,
  `section_instanceID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=100 ;

-- --------------------------------------------------------

--
-- Table structure for table `cms_entity_value_int`
--

CREATE TABLE IF NOT EXISTS `cms_entity_value_int` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `entityID` int(10) unsigned NOT NULL,
  `contentID` int(10) unsigned NOT NULL,
  `value` bigint(20) DEFAULT NULL,
  `section_instanceID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=100 ;

-- --------------------------------------------------------

--
-- Table structure for table `cms_entity_value_longtext`
--

CREATE TABLE IF NOT EXISTS `cms_entity_value_longtext` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `entityID` int(10) unsigned NOT NULL,
  `contentID` int(10) unsigned NOT NULL,
  `value` text,
  `section_instanceID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID` (`ID`),
  KEY `contentID` (`contentID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=100 ;

INSERT INTO `cms_entity_value_longtext` (`ID`, `entityID`, `contentID`, `value`, `section_instanceID`) VALUES
(1, 2, 1, '<p>Copyright 2015</p>', NULL),
(2, 3, 1, '<p>Siempre CMS</p>', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cms_entity_value_money`
--

CREATE TABLE IF NOT EXISTS `cms_entity_value_money` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `entityID` int(10) unsigned NOT NULL,
  `contentID` int(10) unsigned NOT NULL,
  `value` decimal(19,4) DEFAULT NULL,
  `section_instanceID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=100 ;

-- --------------------------------------------------------

--
-- Table structure for table `cms_entity_value_shorttext`
--

CREATE TABLE IF NOT EXISTS `cms_entity_value_shorttext` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `entityID` int(10) unsigned NOT NULL,
  `contentID` int(10) unsigned NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  `section_instanceID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `contentID` (`contentID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=100 ;

INSERT INTO `cms_entity_value_shorttext` (`ID`, `entityID`, `contentID`, `value`, `section_instanceID`) VALUES
(1, 1, 1, 'Welcome to Siempre CMS', NULL),
(2, 100, 1000, 'Homepage | Welcome to Siempre CMS', NULL);


-- --------------------------------------------------------

--
-- Table structure for table `cms_language`
--

CREATE TABLE IF NOT EXISTS `cms_language` (
  `ID` int(11) NOT NULL,
  `language` varchar(50) NOT NULL,
  `path` varchar(20) NOT NULL,
  `default` tinyint(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `ID` (`ID`),
  KEY `ID_2` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

INSERT INTO `cms_language` (`ID`, `language`, `path`, `default`) VALUES
(1, 'UK English', 'en', 1),
(2, 'Français', 'fr', 0),
(3, 'español', 'es', 0);


--
-- Table structure for table `cms_login_ip`
--

CREATE TABLE IF NOT EXISTS `cms_login_ip` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IP` varchar(45) NOT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `cms_node_dependency`
--

CREATE TABLE IF NOT EXISTS `cms_node_dependency` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `nodeID` int(11) NOT NULL,
  `subnodeID` int(11) NOT NULL,
  `level` int(11) DEFAULT NULL COMMENT 'Specific level or 0 = al, null = specific dependency',
  UNIQUE KEY `ID` (`ID`),
  KEY `nodeID` (`nodeID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `cms_page_path`
--

CREATE TABLE IF NOT EXISTS `cms_page_path` (
  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
  `path` varchar(2000) DEFAULT NULL,
  `nodeID` bigint(20) DEFAULT NULL,
  `type` int(11) NOT NULL DEFAULT '0',
  `module` varchar(32) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID_UNIQUE` (`ID`),
  KEY `nodeID` (`nodeID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=100 ;

INSERT INTO `cms_page_path` (`ID`, `path`, `nodeID`, `type`, `module`) VALUES
(-1, 'sitemap.xml', NULL, 10, ''),
(1, '', 1000, 0, '');


-- --------------------------------------------------------

--
-- Table structure for table `cms_section`
--

CREATE TABLE IF NOT EXISTS `cms_section` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `template_tabID` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID` (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=100 ;

-- --------------------------------------------------------

--
-- Table structure for table `cms_section_instance`
--

CREATE TABLE IF NOT EXISTS `cms_section_instance` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `contentID` bigint(20) unsigned NOT NULL,
  `sectionID` int(11) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ID`),
  KEY `contentID` (`contentID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=100 ;

-- --------------------------------------------------------

--
-- Table structure for table `cms_template`
--

CREATE TABLE IF NOT EXISTS `cms_template` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `version` bigint(20) unsigned NOT NULL,
  `created` datetime DEFAULT NULL,
  `lastUpdated` datetime DEFAULT NULL,
  `content` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `useParentTemplate` tinyint(1) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=100 ;

INSERT INTO `cms_template` (`ID`, `name`, `version`, `created`, `lastUpdated`, `content`, `useParentTemplate`, `description`) VALUES
(1, 'SiteSettings', 1, '2014-05-19 00:00:00', '2015-02-09 15:08:09', '<!DOCTYPE html>\n<html lang="en">\n	<head>\n		<meta charset="utf-8">\n		{|@if( {|Page.PageTitle|} != "")\n	{|if|}\n		<title>{|Page.PageTitle|}</title>\n	{|/if|}\n	{|else|}\n		<title>{|Site.SiteTitle|}</title>\n	{|/else|}\n|}\n	</head>\n	<body><div class\n		\n{|@childContent|}\n	</body>\n	<footer>\n		<h3>{|Site.FooterHeader|}</h3>\n		{|Site.FooterContent|}\n	</footer>\n</html>', 0, 'Site wide content, master template and settings'),
(100, 'Homepage', 0, '2014-11-13 17:14:01', '2015-02-09 12:51:52', '<div class="contents">\n<h1>{|Page.PageTitle|}</h1>\n<p>This is just a temporary page!</p></div>', 1, 'An example homepage');


-- --------------------------------------------------------

--
-- Table structure for table `cms_template_tab`
--

CREATE TABLE IF NOT EXISTS `cms_template_tab` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `order` int(10) NOT NULL,
  `templateID` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID` (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=101;

INSERT INTO `cms_template_tab` (`ID`, `name`, `order`, `templateID`) VALUES
(1, 'Header', 1, 1),
(2, 'Footer', 2, 1),
(100, 'Contents', 1, 100);


-- --------------------------------------------------------

--
-- Table structure for table `cms_tree_data`
--

CREATE TABLE IF NOT EXISTS `cms_tree_data` (
  `id` int(10) unsigned NOT NULL,
  `nm` varchar(255) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

INSERT INTO `cms_tree_data` (`id`, `nm`) VALUES
(1, 'Website'),
(1000, 'Homepage');

--
-- Table structure for table `cms_tree_struct`
--

CREATE TABLE IF NOT EXISTS `cms_tree_struct` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lft` int(10) unsigned NOT NULL,
  `rgt` int(10) unsigned NOT NULL,
  `lvl` int(10) unsigned NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  `pos` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1000 ;

-- --------------------------------------------------------

INSERT INTO `cms_tree_struct` (`id`, `lft`, `rgt`, `lvl`, `pid`, `pos`) VALUES
(1, 1, 68, 0, 0, 0),
(1000, 52, 53, 1, 1, 0);

--
-- Table structure for table `cms_user`
--

CREATE TABLE IF NOT EXISTS `cms_user` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `foreName` varchar(100) DEFAULT NULL,
  `lastName` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `passwordhash` char(40) DEFAULT NULL,
  `lastLogin` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `lastUpdated` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1001 ;



INSERT INTO `cms_user` (`ID`, `username`, `active`, `foreName`, `lastName`, `email`, `passwordhash`, `lastLogin`, `created`, `lastUpdated`) VALUES
(-1, 'WEB', 1, 'website', 'website', 'website@here.com', '14f9802c-e08b-11e1-871a-f23c91aea89d', NULL, NULL, NULL),
(1000, 'admin', 1, 'admin', 'admin', 'here@yourdomain.com', '670fa70d8eb5868b18517675d1b2b002f3e6461a', '2015-02-22 13:05:51', NULL, '2015-02-10 13:36:54');


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

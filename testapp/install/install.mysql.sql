
--
-- Structure de la table `jacl_group`
--

CREATE TABLE `jacl_group` (
  `id_aclgrp` int(11) NOT NULL auto_increment,
  `name` varchar(150) NOT NULL default '',
  `grouptype` tinyint(4) NOT NULL default '0',
  `ownerlogin` varchar(50) default NULL,
  PRIMARY KEY  (`id_aclgrp`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=49 ;


-- --------------------------------------------------------
--
-- Structure de la table `jacl_right_values`
--

CREATE TABLE `jacl_right_values` (
  `value` varchar(20) NOT NULL default '',
  `label_key` varchar(50) NOT NULL default '',
  `id_aclvalgrp` int(11) NOT NULL default '0',
  PRIMARY KEY  (`value`,`id_aclvalgrp`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



-- --------------------------------------------------------

--
-- Structure de la table `jacl_right_values_group`
--

CREATE TABLE `jacl_right_values_group` (
  `id_aclvalgrp` int(11) NOT NULL default '0',
  `label_key` varchar(50) NOT NULL default '',
  `type_aclvalgrp` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id_aclvalgrp`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `jacl_rights`
--

CREATE TABLE `jacl_rights` (
  `id_aclsbj` varchar(255) NOT NULL default '',
  `id_aclgrp` int(11) NOT NULL default '0',
  `id_aclres` varchar(100) NOT NULL default '',
  `value` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`id_aclsbj`,`id_aclgrp`,`id_aclres`,`value`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Structure de la table `jacl_subject`
--

CREATE TABLE `jacl_subject` (
  `id_aclsbj` varchar(100) NOT NULL default '',
  `id_aclvalgrp` int(11) NOT NULL default '0',
  `label_key` varchar(100) default NULL,
  PRIMARY KEY  (`id_aclsbj`),
  KEY `id_aclvalgrp` (`id_aclvalgrp`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Structure de la table `jacl_user_group`
--

CREATE TABLE `jacl_user_group` (
  `login` varchar(50) NOT NULL default '',
  `id_aclgrp` int(11) NOT NULL default '0',
  PRIMARY KEY  (`login`,`id_aclgrp`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


-- Liste des groupes
DROP TABLE IF EXISTS `jacl2_group`;
CREATE TABLE `jacl2_group` (
  `id_aclgrp` int(11) NOT NULL auto_increment,
  `name` varchar(150) NOT NULL default '',
  `grouptype` tinyint(4) NOT NULL default '0',
  `ownerlogin` varchar(50) default NULL,
  PRIMARY KEY  (`id_aclgrp`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- liste des groupes associés à chaque utilisateur
DROP TABLE IF EXISTS `jacl2_user_group`;
CREATE TABLE `jacl2_user_group` (
  `login` varchar(50) NOT NULL default '',
  `id_aclgrp` int(11) NOT NULL default '0',
  KEY `login` (`login`,`id_aclgrp`)
) TYPE=MyISAM;


-- liste des sujets, avec leur appartenance à un groupe de valeurs de droits
DROP TABLE IF EXISTS `jacl2_subject`;
CREATE TABLE `jacl2_subject` (
  `id_aclsbj` varchar(100) NOT NULL default '',
  `label_key` varchar(100) default NULL,
  PRIMARY KEY  (`id_aclsbj`)
) TYPE=MyISAM;

-- table centrale
-- valeurs du droit pour chaque couple sujet/groupe ou triplet sujet/groupe/ressource
DROP TABLE IF EXISTS `jacl2_rights`;
CREATE TABLE `jacl2_rights` (
  `id_aclsbj` varchar(100) NOT NULL default '',
  `id_aclgrp` int(11) NOT NULL default '0',
  `id_aclres` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id_aclsbj`,`id_aclgrp`,`id_aclres`)
) TYPE=MyISAM;


-- --------------------------------------------------------

--
-- Structure de la table `jlx_user`
--

CREATE TABLE `jlx_user` (
  `usr_login` varchar(50) NOT NULL default '',
  `usr_password` varchar(50) NOT NULL default '',
  `usr_email` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`usr_login`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Structure de la table `myconfig`
--

CREATE TABLE `myconfig` (
  `cfg_key` varchar(150) NOT NULL default '',
  `cfg_value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`cfg_key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `myconfig`
--

INSERT INTO `myconfig` (`cfg_key`, `cfg_value`) VALUES ('foo', 'foovalue'),
('bar', 'barvalue'),
('name', 'laurent'),
('engine', 'jelix'),
('browser', 'firefox'),
('33', '456ghjk'),
('test', '33');

-- for unit tests
CREATE TABLE `product_test` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` VARCHAR( 150 ) NOT NULL ,
`price` FLOAT NOT NULL,
`create_date` datetime NOT NULL
) TYPE = MYISAM ;

CREATE TABLE `product_tags_test` (
`product_id` INT NOT NULL ,
`tag` VARCHAR( 50 ) NOT NULL ,
PRIMARY KEY ( `product_id` , `tag` )
) ENGINE = MYISAM ;

CREATE TABLE `labels_test` (
`key` INT NOT NULL ,
`lang` VARCHAR( 5 ) NOT NULL ,
`label` VARCHAR( 50 ) NOT NULL ,
PRIMARY KEY ( `key` , `lang` )
);

-- for the crud example
CREATE TABLE `products` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` VARCHAR( 150 ) NOT NULL ,
`price` FLOAT   default '0',
`promo` BOOL NOT NULL 
) TYPE = MYISAM ;



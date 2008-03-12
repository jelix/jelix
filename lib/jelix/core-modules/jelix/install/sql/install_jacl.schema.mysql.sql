
-- Liste des groupes
DROP TABLE IF EXISTS `jacl_group`;
CREATE TABLE `jacl_group` (
  `id_aclgrp` int(11) NOT NULL auto_increment,
  `name` varchar(150) NOT NULL default '',
  `grouptype` tinyint(4) NOT NULL default '0',
  `ownerlogin` varchar(50) default NULL,
  PRIMARY KEY  (`id_aclgrp`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

-- liste des groupes associés à chaque utilisateur
DROP TABLE IF EXISTS `jacl_user_group`;
CREATE TABLE `jacl_user_group` (
  `login` varchar(50) NOT NULL default '',
  `id_aclgrp` int(11) NOT NULL default '0',
  KEY `login` (`login`,`id_aclgrp`)
) TYPE=MyISAM;


-- groupes de valeurs de droits
-- type_aclvalgrp : 0 = valeurs pouvant être combinées, 1= valeurs exclusives
DROP TABLE IF EXISTS `jacl_right_values_group`;
CREATE TABLE `jacl_right_values_group` (
  `id_aclvalgrp` int(11) NOT NULL default '0',
  `label_key` varchar(50) NOT NULL default '',
  `type_aclvalgrp` tinyint(4) NOT NULL default '0', 
  PRIMARY KEY  (`id_aclvalgrp`)
) TYPE=MyISAM;

-- liste des valeurs possibles dans chaque groupe de valeurs de droits
DROP TABLE IF EXISTS `jacl_right_values`;
CREATE TABLE `jacl_right_values` (
  `value` varchar(20) NOT NULL default '',
  `label_key` varchar(50) NOT NULL default '',
  `id_aclvalgrp` int(11) NOT NULL default '0',
  PRIMARY KEY  (`value`,`id_aclvalgrp`)
) TYPE=MyISAM;


-- liste des sujets, avec leur appartenance à un groupe de valeurs de droits
DROP TABLE IF EXISTS `jacl_subject`;
CREATE TABLE `jacl_subject` (
  `id_aclsbj` varchar(100) NOT NULL default '',
  `id_aclvalgrp` int(11) NOT NULL default '0',
  `label_key` varchar(100) default NULL,
  PRIMARY KEY  (`id_aclsbj`),
  KEY `id_aclvalgrp` (`id_aclvalgrp`)
) TYPE=MyISAM;

-- table centrale
-- valeurs du droit pour chaque couple sujet/groupe ou triplet sujet/groupe/ressource
DROP TABLE IF EXISTS `jacl_rights`;
CREATE TABLE `jacl_rights` (
  `id_aclsbj` varchar(100) NOT NULL default '',
  `id_aclgrp` int(11) NOT NULL default '0',
  `id_aclres` varchar(100) NOT NULL default '',
  `value` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`id_aclsbj`,`id_aclgrp`,`id_aclres`, `value`)
) TYPE=MyISAM;

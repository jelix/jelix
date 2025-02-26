
-- Liste des groupes
CREATE TABLE  IF NOT EXISTS `%%PREFIX%%jacl2_group` (
  `id_aclgrp` varchar(330) NOT NULL,
  `name` varchar(150) NOT NULL default '',
  `grouptype` tinyint(4) NOT NULL default '0',
  `ownerlogin` varchar(320) default NULL,
  PRIMARY KEY  (`id_aclgrp`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- liste des groupes associés à chaque utilisateur
CREATE TABLE IF NOT EXISTS `%%PREFIX%%jacl2_user_group` (
  `login` varchar(320) NOT NULL,
  `id_aclgrp` varchar(330) NOT NULL,
  PRIMARY KEY (`login`,`id_aclgrp`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- liste des sujets, avec leur appartenance à un groupe de valeurs de droits
CREATE TABLE IF NOT EXISTS `%%PREFIX%%jacl2_subject` (
  `id_aclsbj` varchar(100) NOT NULL,
  `label_key` varchar(100) default NULL,
  `id_aclsbjgrp` VARCHAR( 50 ) default NULL ,
  PRIMARY KEY  (`id_aclsbj`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- table centrale
-- valeurs du droit pour chaque couple sujet/groupe ou triplet sujet/groupe/ressource
CREATE TABLE IF NOT EXISTS `%%PREFIX%%jacl2_rights` (
  `id_aclsbj` varchar(100) NOT NULL,
  `id_aclgrp` varchar(330) NOT NULL,
  `id_aclres` varchar(100) NOT NULL default '-',
  canceled boolean NOT NULL default 0,
  PRIMARY KEY  (`id_aclsbj`,`id_aclgrp`,`id_aclres`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `%%PREFIX%%jacl2_subject_group` (
`id_aclsbjgrp` VARCHAR( 50 ) NOT NULL ,
`label_key` VARCHAR( 60 ) NOT NULL ,
PRIMARY KEY ( `id_aclsbjgrp` )
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

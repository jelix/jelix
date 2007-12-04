DROP TABLE IF EXISTS `jsessions`;
CREATE TABLE `jsessions` (
  `id` varchar(64) NOT NULL,
  `creation` datetime NOT NULL,
  `access` datetime NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;
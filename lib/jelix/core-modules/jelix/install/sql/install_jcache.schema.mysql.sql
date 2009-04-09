CREATE TABLE `jlx_cache` (
  `cache_key` varchar(255) NOT NULL default '',
  `cache_data` longtext,
  `cache_date` datetime default NULL,
  PRIMARY KEY  (`cache_key`)
) ENGINE=MyISAM;

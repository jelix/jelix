
CREATE TABLE IF NOT EXISTS myconfig (
  cfg_key varchar(150) NOT NULL default '',
  cfg_value varchar(255) NOT NULL default '',
  PRIMARY KEY  (cfg_key)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


INSERT INTO `myconfig` (`cfg_key`, `cfg_value`) VALUES
('foo', 'foovalue'),
('bar', 'barvalue'),
('name', 'laurent'),
('engine', 'jelix'),
('browser', 'firefox'),
('33', '456ghjk'),
('test', '33');

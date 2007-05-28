
CREATE TABLE myconfig (
  cfg_key varchar(150) NOT NULL default '',
  cfg_value varchar(255) NOT NULL default '',
  PRIMARY KEY  (cfg_key)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


INSERT INTO myconfig VALUES ('foo', 'foovalue');
INSERT INTO myconfig VALUES ('bar', 'barvalue');
INSERT INTO myconfig VALUES ('name', 'laurent');
INSERT INTO myconfig VALUES ('engine', 'jelix');
INSERT INTO myconfig VALUES ('browser', 'firefox');


CREATE TABLE `product_test` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` VARCHAR( 150 ) NOT NULL ,
`price` FLOAT NOT NULL
) TYPE = MYISAM ;
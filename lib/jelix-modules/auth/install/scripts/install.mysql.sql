CREATE TABLE `jlx_user` (
  `usr_login` varchar(50) NOT NULL default '',
  `usr_password` varchar(50) NOT NULL default '',
  `usr_email` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`usr_login`)
) AUTO_INCREMENT=2 ;

INSERT INTO `jlx_user` VALUES ('admin', '893942cbbaf6dd55d6721353f6776df9', 'no@mail.com');

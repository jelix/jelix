CREATE TABLE `jlx_user` (
  `usr_login` varchar(50) NOT NULL DEFAULT '',
  `usr_password` varchar(50) NOT NULL DEFAULT '',
  `usr_email` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY  (`usr_login`)
);

INSERT INTO `backend`.`jlx_user` (`usr_login` ,`usr_password` ,`usr_email`)VALUES ('admin', MD5( 'admin' ) , 'admin@localhost.localdomain');
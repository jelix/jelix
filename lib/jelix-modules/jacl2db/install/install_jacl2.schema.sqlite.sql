
CREATE TABLE %%PREFIX%%jacl2_group (
    id_aclgrp INTEGER AUTOINCREMENT PRIMARY KEY,
    name varchar(150) NOT NULL DEFAULT '',
    code varchar(30),
    grouptype int(5) NOT NULL DEFAULT '0',
    ownerlogin varchar(50)
);

CREATE TABLE %%PREFIX%%jacl2_subject (
  id_aclsbj varchar(100) NOT NULL DEFAULT '',
  label_key varchar(100) DEFAULT NULL,
  PRIMARY KEY (id_aclsbj)
) ;

CREATE TABLE %%PREFIX%%jacl2_user_group (
  login varchar(50) NOT NULL DEFAULT '',
  id_aclgrp int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (login, id_aclgrp)
) ;

CREATE TABLE %%PREFIX%%jacl2_rights (
  id_aclsbj varchar(100) NOT NULL DEFAULT '',
  id_aclgrp int(11) NOT NULL DEFAULT '0',
  id_aclres varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (id_aclsbj,id_aclgrp,id_aclres)
) ;


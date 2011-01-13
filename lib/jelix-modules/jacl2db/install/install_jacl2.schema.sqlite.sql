

CREATE TABLE %%PREFIX%%%jacl2_subject (
  id_aclsbj varchar(100) NOT NULL DEFAULT '',
  label_key varchar(100) DEFAULT NULL,
  PRIMARY KEY (id_aclsbj)
) ;


CREATE TABLE %%PREFIX%%%jacl2_user_group (
  login varchar(50) NOT NULL DEFAULT '',
  id_aclgrp int(11) NOT NULL DEFAULT '0'
) ;
CREATE UNIQUE INDEX %%PREFIX%%%jacl2_user_group_login ON %%PREFIX%%%jacl2_user_group (login);


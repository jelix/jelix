--
-- PostgreSQL database dump
--

DROP TABLE IF EXISTS %%PREFIX%%jacl2_group;
CREATE TABLE %%PREFIX%%jacl2_group (
    id_aclgrp serial NOT NULL,
    name character varying(150) NOT NULL DEFAULT '',
    code character varying(30),
    grouptype smallint NOT NULL,
    ownerlogin character varying(50),
    CONSTRAINT %%PREFIX%%jacl2_group_id_aclgrp_pk PRIMARY KEY (id_aclgrp)
);
-- SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('%%PREFIX%%jacl2_group', 'id_aclgrp'), 1, false);

DROP TABLE IF EXISTS %%PREFIX%%jacl2_rights;
CREATE TABLE %%PREFIX%%jacl2_rights (
    id_aclsbj character varying(255) NOT NULL,
    id_aclgrp integer NOT NULL DEFAULT '0',
    id_aclres character varying(100) NOT NULL,
    CONSTRAINT %%PREFIX%%jacl2_rights_id_aclsbj_id_aclgrp_id_aclres_pk PRIMARY KEY (id_aclsbj, id_aclgrp, id_aclres)

);

DROP TABLE IF EXISTS %%PREFIX%%jacl2_subject;
CREATE TABLE %%PREFIX%%jacl2_subject (
    id_aclsbj character varying(100) NOT NULL,
    label_key character varying(100) DEFAULT NULL,
    CONSTRAINT %%PREFIX%%jacl2_subject_id_aclsbj_pk PRIMARY KEY (id_aclsbj)
);

DROP TABLE IF EXISTS %%PREFIX%%jacl2_user_group;
CREATE TABLE %%PREFIX%%jacl2_user_group (
    "login" character varying(50) NOT NULL,
    id_aclgrp integer NOT NULL DEFAULT '0',
    CONSTRAINT %%PREFIX%%jacl2_user_group_login_pk PRIMARY KEY ("login", id_aclgrp)
);

ALTER TABLE ONLY %%PREFIX%%jacl2_rights
    ADD CONSTRAINT %%PREFIX%%jacl2_rights_id_aclgrp_fkey FOREIGN KEY (id_aclgrp) REFERENCES %%PREFIX%%jacl2_group(id_aclgrp);

ALTER TABLE ONLY %%PREFIX%%jacl2_rights
    ADD CONSTRAINT %%PREFIX%%jacl2_rights_id_aclsbj_fkey FOREIGN KEY (id_aclsbj) REFERENCES %%PREFIX%%jacl2_subject(id_aclsbj);

ALTER TABLE ONLY %%PREFIX%%jacl2_user_group
    ADD CONSTRAINT %%PREFIX%%jacl2_user_group_id_aclgrp_fkey FOREIGN KEY (id_aclgrp) REFERENCES %%PREFIX%%jacl2_group(id_aclgrp);

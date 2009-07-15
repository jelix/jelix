--
-- PostgreSQL database dump
--

SET client_encoding = 'UTF8';
SET check_function_bodies = false;
SET client_min_messages = warning;
SET search_path = public, pg_catalog;
SET default_tablespace = '';
SET default_with_oids = false;

CREATE TABLE jacl2_group (
    id_aclgrp serial NOT NULL,
    name character varying(150) NOT NULL,
    grouptype smallint NOT NULL,
    ownerlogin character varying(50)
);

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('jacl2_group', 'id_aclgrp'), 1, false);





CREATE TABLE jacl2_rights (
    id_aclsbj character varying(255) NOT NULL,
    id_aclgrp integer NOT NULL,
    id_aclres character varying(100) NOT NULL
);

CREATE TABLE jacl2_subject (
    id_aclsbj character varying(100) NOT NULL,
    label_key character varying(100)
);

CREATE TABLE jacl2_user_group (
    "login" character varying(50) NOT NULL,
    id_aclgrp integer NOT NULL
);


ALTER TABLE ONLY jacl2_group
    ADD CONSTRAINT jacl2_group_pkey PRIMARY KEY (id_aclgrp);

ALTER TABLE ONLY jacl2_rights
    ADD CONSTRAINT jacl2_rights_pkey PRIMARY KEY (id_aclsbj, id_aclgrp, id_aclres);

ALTER TABLE ONLY jacl2_subject
    ADD CONSTRAINT jacl2_subject_pkey PRIMARY KEY (id_aclsbj);

ALTER TABLE ONLY jacl2_user_group
    ADD CONSTRAINT jacl2_user_group_pkey PRIMARY KEY ("login", id_aclgrp);

ALTER TABLE ONLY jacl2_rights
    ADD CONSTRAINT jacl2_rights_id_aclgrp_fkey FOREIGN KEY (id_aclgrp) REFERENCES jacl2_group(id_aclgrp);

ALTER TABLE ONLY jacl2_rights
    ADD CONSTRAINT jacl2_rights_id_aclsbj_fkey FOREIGN KEY (id_aclsbj) REFERENCES jacl2_subject(id_aclsbj);

ALTER TABLE ONLY jacl2_user_group
    ADD CONSTRAINT jacl2_user_group_id_aclgrp_fkey FOREIGN KEY (id_aclgrp) REFERENCES jacl2_group(id_aclgrp);

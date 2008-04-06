--
-- PostgreSQL database dump
--

SET client_encoding = 'UTF8';
SET check_function_bodies = false;
SET client_min_messages = warning;
SET search_path = public, pg_catalog;
SET default_tablespace = '';
SET default_with_oids = false;

CREATE TABLE jacl_group (
    id_aclgrp serial NOT NULL,
    name character varying(150) NOT NULL,
    grouptype smallint NOT NULL,
    ownerlogin character varying(50)
);

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('jacl_group', 'id_aclgrp'), 1, false);

CREATE TABLE jacl_right_values (
    value character varying(20) NOT NULL,
    id_aclvalgrp integer NOT NULL,
    label_key character varying(50) NOT NULL
);

CREATE TABLE jacl_right_values_group (
    id_aclvalgrp integer DEFAULT 0 NOT NULL,
    label_key character varying(50) NOT NULL,
    type_aclvalgrp smallint DEFAULT 0 NOT NULL
);

CREATE TABLE jacl_rights (
    id_aclsbj character varying(255) NOT NULL,
    id_aclgrp integer NOT NULL,
    id_aclres character varying(100) NOT NULL,
    value character varying(20) NOT NULL
);

CREATE TABLE jacl_subject (
    id_aclsbj character varying(100) NOT NULL,
    id_aclvalgrp integer NOT NULL,
    label_key character varying(100)
);

CREATE TABLE jacl_user_group (
    "login" character varying(50) NOT NULL,
    id_aclgrp integer NOT NULL
);

CREATE TABLE jlx_user (
    usr_login character varying(50) NOT NULL,
    usr_password character varying(50) NOT NULL,
    usr_email character varying(255) NOT NULL
);

CREATE TABLE labels_tests (
    "key" integer NOT NULL,
    lang character varying(5) NOT NULL,
    label character varying(50) NOT NULL
);

CREATE TABLE myconfig (
    cfg_key character varying(150) NOT NULL,
    cfg_value character varying(255) NOT NULL
);

CREATE TABLE product_tags_test (
    product_id integer NOT NULL,
    tag character varying(50) NOT NULL
);

CREATE TABLE product_test (
    id serial NOT NULL,
    name character varying(150) NOT NULL,
    price real NOT NULL,
    create_date timestamp with time zone NOT NULL
);

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('product_test', 'id'), 1, false);


CREATE TABLE products (
    id serial NOT NULL,
    name character varying(150) NOT NULL,
    price real DEFAULT 0,
    promo boolean NOT NULL
);

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('products', 'id'), 1, false);



ALTER TABLE ONLY jacl_group
    ADD CONSTRAINT jacl_group_pkey PRIMARY KEY (id_aclgrp);

ALTER TABLE ONLY jacl_right_values_group
    ADD CONSTRAINT jacl_right_values_group_pkey PRIMARY KEY (id_aclvalgrp);

ALTER TABLE ONLY jacl_right_values
    ADD CONSTRAINT jacl_right_values_pkey PRIMARY KEY (value, id_aclvalgrp);

ALTER TABLE ONLY jacl_rights
    ADD CONSTRAINT jacl_rights_pkey PRIMARY KEY (id_aclsbj, id_aclgrp, id_aclres, value);

ALTER TABLE ONLY jacl_subject
    ADD CONSTRAINT jacl_subject_pkey PRIMARY KEY (id_aclsbj);

ALTER TABLE ONLY jacl_user_group
    ADD CONSTRAINT jacl_user_group_pkey PRIMARY KEY ("login", id_aclgrp);

ALTER TABLE ONLY jlx_user
    ADD CONSTRAINT jlx_user_pkey PRIMARY KEY (usr_login);

ALTER TABLE ONLY labels_tests
    ADD CONSTRAINT labels_tests_pkey PRIMARY KEY ("key", lang);

ALTER TABLE ONLY myconfig
    ADD CONSTRAINT myconfig_pkey PRIMARY KEY (cfg_key);

ALTER TABLE ONLY product_tags_test
    ADD CONSTRAINT product_tags_test_pkey PRIMARY KEY (product_id, tag);

ALTER TABLE ONLY product_test
    ADD CONSTRAINT product_test_pkey PRIMARY KEY (id);

ALTER TABLE ONLY products
    ADD CONSTRAINT products_pkey PRIMARY KEY (id);

ALTER TABLE ONLY jacl_right_values
    ADD CONSTRAINT jacl_right_values_id_aclvalgrp_fkey FOREIGN KEY (id_aclvalgrp) REFERENCES jacl_right_values_group(id_aclvalgrp);

ALTER TABLE ONLY jacl_rights
    ADD CONSTRAINT jacl_rights_id_aclgrp_fkey FOREIGN KEY (id_aclgrp) REFERENCES jacl_group(id_aclgrp);

ALTER TABLE ONLY jacl_rights
    ADD CONSTRAINT jacl_rights_id_aclsbj_fkey FOREIGN KEY (id_aclsbj) REFERENCES jacl_subject(id_aclsbj);

ALTER TABLE ONLY jacl_subject
    ADD CONSTRAINT jacl_subject_id_aclvalgrp_fkey FOREIGN KEY (id_aclvalgrp) REFERENCES jacl_right_values_group(id_aclvalgrp);

ALTER TABLE ONLY jacl_user_group
    ADD CONSTRAINT jacl_user_group_id_aclgrp_fkey FOREIGN KEY (id_aclgrp) REFERENCES jacl_group(id_aclgrp);



INSERT INTO myconfig (cfg_key, cfg_value) VALUES ('foo', 'foovalue');
INSERT INTO myconfig (cfg_key, cfg_value) VALUES ('bar', 'barvalue');
INSERT INTO myconfig (cfg_key, cfg_value) VALUES ('name', 'laurent');
INSERT INTO myconfig (cfg_key, cfg_value) VALUES ('engine', 'jelix');
INSERT INTO myconfig (cfg_key, cfg_value) VALUES ('browser', 'firefox');
INSERT INTO myconfig (cfg_key, cfg_value) VALUES ('33', '456ghjk');
INSERT INTO myconfig (cfg_key, cfg_value) VALUES ('test', '33');





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








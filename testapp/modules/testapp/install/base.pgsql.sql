CREATE TABLE myconfig (
    cfg_key character varying(150) NOT NULL,
    cfg_value character varying(255) NOT NULL
);

ALTER TABLE ONLY myconfig
    ADD CONSTRAINT myconfig_pkey PRIMARY KEY (cfg_key);


INSERT INTO myconfig (cfg_key, cfg_value) VALUES ('foo', 'foovalue');
INSERT INTO myconfig (cfg_key, cfg_value) VALUES ('bar', 'barvalue');
INSERT INTO myconfig (cfg_key, cfg_value) VALUES ('name', 'laurent');
INSERT INTO myconfig (cfg_key, cfg_value) VALUES ('engine', 'jelix');
INSERT INTO myconfig (cfg_key, cfg_value) VALUES ('browser', 'firefox');
INSERT INTO myconfig (cfg_key, cfg_value) VALUES ('33', '456ghjk');
INSERT INTO myconfig (cfg_key, cfg_value) VALUES ('test', '33');

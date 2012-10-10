
ALTER TABLE %%PREFIX%%jacl2_rights DROP CONSTRAINT %%PREFIX%%jacl2_rights_id_aclsbj_id_aclgrp_id_aclres_pk;

UPDATE ".$cn->prefixTable('jacl2_rights')." 
            SET id_aclres='-' WHERE id_aclres='' OR id_aclres IS NULL

faire des alter table sur jacl2_rights (mysql, oci, pgsql, sqlite)
-> id_aclres varchar(100) NOT NULL default '-'


jacl2_group (sqlite)
jacl2_subject (mysql, sqlite)
->id_aclsbj varchar(100) NOT NULL DEFAULT ''  <- enlever default ''





ALTER TABLE %%PREFIX%%jacl2_rights DROP PRIMARY KEY;
ALTER TABLE %%PREFIX%%jacl2_rights CHANGE id_aclres id_aclres varchar(100) NOT NULL default '-';
UPDATE %%PREFIX%%jacl2_rights SET id_aclres='-' WHERE id_aclres='' OR id_aclres IS NULL;
ALTER TABLE %%PREFIX%%jacl2_rights ADD PRIMARY KEY ( `id_aclsbj` , `id_aclgrp` , `id_aclres`);

ALTER TABLE %%PREFIX%%jacl2_subject DROP PRIMARY KEY;
ALTER TABLE %%PREFIX%%jacl2_subject CHANGE id_aclsbj id_aclsbj varchar(100) NOT NULL;
ALTER TABLE %%PREFIX%%jacl2_subject ADD PRIMARY KEY ( `id_aclsbj`);



ALTER TABLE %%PREFIX%%jacl2_group DROP PRIMARY KEY;
ALTER TABLE %%PREFIX%%jacl2_group CHANGE id_aclsbj id_aclsbj varchar(100) NOT NULL;
ALTER TABLE %%PREFIX%%jacl2_group ADD PRIMARY KEY ( `id_aclsbj`);





ALTER TABLE jacl2_rights DROP PRIMARY KEY;
ALTER TABLE jacl2_rights CHANGE id_aclres id_aclres varchar(100) NOT NULL default '-';
UPDATE jacl2_rights SET id_aclres='-' WHERE id_aclres='' OR id_aclres IS NULL;
ALTER TABLE jacl2_rights ADD PRIMARY KEY ( `id_aclsbj` , `id_aclgrp` , `id_aclres`);

ALTER TABLE jacl2_subject DROP PRIMARY KEY;
ALTER TABLE jacl2_subject CHANGE id_aclsbj id_aclsbj varchar(100) NOT NULL;
ALTER TABLE jacl2_subject ADD PRIMARY KEY ( `id_aclsbj`);



ALTER TABLE jacl2_group DROP PRIMARY KEY;
ALTER TABLE jacl2_group CHANGE id_aclsbj id_aclsbj varchar(100) NOT NULL;
ALTER TABLE jacl2_group ADD PRIMARY KEY ( `id_aclsbj`);


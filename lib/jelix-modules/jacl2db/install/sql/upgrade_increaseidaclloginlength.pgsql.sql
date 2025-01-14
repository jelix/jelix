ALTER TABLE %%PREFIX%%jacl2_group ALTER COLUMN id_aclgrp TYPE character varying(330);
ALTER TABLE %%PREFIX%%jacl2_group ALTER COLUMN ownerlogin TYPE character varying(320);
ALTER TABLE %%PREFIX%%jacl2_user_group ALTER COLUMN id_aclgrp TYPE character varying(330);
ALTER TABLE %%PREFIX%%jacl2_user_group ALTER COLUMN login TYPE character varying(320);
ALTER TABLE %%PREFIX%%jacl2_rights ALTER COLUMN id_aclgrp TYPE character varying(330);


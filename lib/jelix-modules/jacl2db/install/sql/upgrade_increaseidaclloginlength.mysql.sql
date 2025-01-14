ALTER TABLE %%PREFIX%%jacl2_group MODIFY id_aclgrp varchar(330) ;
ALTER TABLE %%PREFIX%%jacl2_group MODIFY ownerlogin varchar(320) ;
ALTER TABLE %%PREFIX%%jacl2_user_group MODIFY id_aclgrp varchar(330);
ALTER TABLE %%PREFIX%%jacl2_user_group MODIFY login varchar(320);
ALTER TABLE %%PREFIX%%jacl2_rights MODIFY id_aclgrp varchar(330);

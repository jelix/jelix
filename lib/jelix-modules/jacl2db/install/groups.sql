

INSERT INTO %%PREFIX%%jacl2_group (id_aclgrp, name, code, grouptype, ownerlogin) VALUES (1, 'admins', 'admins', 0, NULL);
INSERT INTO %%PREFIX%%jacl2_group (id_aclgrp, name, code, grouptype, ownerlogin) VALUES (2, 'users', 'users', 1, NULL);
INSERT INTO %%PREFIX%%jacl2_group (id_aclgrp, name, code, grouptype, ownerlogin) VALUES (0, 'anonymous', 'anonymous', 0, NULL);

INSERT INTO %%PREFIX%%jacl2_rights (id_aclsbj, id_aclgrp, id_aclres) VALUES ('acl.group.modify', 1, '');
INSERT INTO %%PREFIX%%jacl2_rights (id_aclsbj, id_aclgrp, id_aclres) VALUES ('acl.group.create', 1, '');
INSERT INTO %%PREFIX%%jacl2_rights (id_aclsbj, id_aclgrp, id_aclres) VALUES ('acl.group.delete', 1, '');
INSERT INTO %%PREFIX%%jacl2_rights (id_aclsbj, id_aclgrp, id_aclres) VALUES ('acl.group.view', 1, '');
INSERT INTO %%PREFIX%%jacl2_rights (id_aclsbj, id_aclgrp, id_aclres) VALUES ('acl.user.modify', 1, '');
INSERT INTO %%PREFIX%%jacl2_rights (id_aclsbj, id_aclgrp, id_aclres) VALUES ('acl.user.view', 1, '');

INSERT INTO %%PREFIX%%jacl2_rights (id_aclsbj, id_aclgrp, id_aclres) VALUES ('auth.users.list', 1, '');
INSERT INTO %%PREFIX%%jacl2_rights (id_aclsbj, id_aclgrp, id_aclres) VALUES ('auth.users.view', 1, '');
INSERT INTO %%PREFIX%%jacl2_rights (id_aclsbj, id_aclgrp, id_aclres) VALUES ('auth.users.modify', 1, '');
INSERT INTO %%PREFIX%%jacl2_rights (id_aclsbj, id_aclgrp, id_aclres) VALUES ('auth.users.create', 1, '');
INSERT INTO %%PREFIX%%jacl2_rights (id_aclsbj, id_aclgrp, id_aclres) VALUES ('auth.users.delete', 1, '');
INSERT INTO %%PREFIX%%jacl2_rights (id_aclsbj, id_aclgrp, id_aclres) VALUES ('auth.users.change.password', 1, '');

INSERT INTO %%PREFIX%%jacl2_rights (id_aclsbj, id_aclgrp, id_aclres) VALUES ('auth.user.view', 1, '');
INSERT INTO %%PREFIX%%jacl2_rights (id_aclsbj, id_aclgrp, id_aclres) VALUES ('auth.user.modify', 1, '');
INSERT INTO %%PREFIX%%jacl2_rights (id_aclsbj, id_aclgrp, id_aclres) VALUES ('auth.user.change.password', 1, '');
INSERT INTO %%PREFIX%%jacl2_rights (id_aclsbj, id_aclgrp, id_aclres) VALUES ('auth.user.view', 2, '');
INSERT INTO %%PREFIX%%jacl2_rights (id_aclsbj, id_aclgrp, id_aclres) VALUES ('auth.user.modify', 2, '');
INSERT INTO %%PREFIX%%jacl2_rights (id_aclsbj, id_aclgrp, id_aclres) VALUES ('auth.user.change.password', 2, '');

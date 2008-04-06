--

INSERT INTO `jacl_group` VALUES (1, 'administrateur', 0, NULL);
INSERT INTO `jacl_group` VALUES (2, 'utilisateurs', 1, NULL);


-- INSERT INTO `jacl_subject` VALUES ('jauth.users.management', 'jelix~acldb.sbj.users.management');
INSERT INTO `jacl_subject` VALUES ('jauth.users.management.list', 'jelix~acldb.valgrp.users.list');
INSERT INTO `jacl_subject` VALUES ('jauth.users.management.details', 'jelix~acldb.valgrp.users.details');
INSERT INTO `jacl_subject` VALUES ('jauth.users.management.update', 'jelix~acldb.valgrp.users.update');
INSERT INTO `jacl_subject` VALUES ('jauth.users.management.create', 'jelix~acldb.valgrp.users.create');
INSERT INTO `jacl_subject` VALUES ('jauth.users.management.delete', 'jelix~acldb.valgrp.users.delete');
INSERT INTO `jacl_subject` VALUES ('jauth.users.management.changepassword', 'jelix~acldb.valgrp.users.password');

INSERT INTO `jacl_subject` VALUES ('jacldb.groups.management.list', 'jelix~acldb.valgrp.groups.list');
INSERT INTO `jacl_subject` VALUES ('jacldb.groups.management.create', 'jelix~acldb.valgrp.groups.create');
INSERT INTO `jacl_subject` VALUES ('jacldb.groups.management.rename', 'jelix~acldb.valgrp.groups.rename');
INSERT INTO `jacl_subject` VALUES ('jacldb.groups.management.delete', 'jelix~acldb.valgrp.groups.delete');


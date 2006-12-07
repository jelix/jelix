--

INSERT INTO `jacl_group` VALUES (1, 'administrateur', 0, NULL);
INSERT INTO `jacl_group` VALUES (2, 'utilisateurs', 1, NULL);

--

INSERT INTO `jacl_right_values_group` VALUES (1, 'jxacl~db.valgrp.truefalse', 1);
INSERT INTO `jacl_right_values_group` VALUES (2, 'jxacl~db.valgrp.crudl',0);
INSERT INTO `jacl_right_values_group` VALUES (3, 'jxacl~db.valgrp.yesno',1);
INSERT INTO `jacl_right_values_group` VALUES (4, 'jxacl~db.valgrp.groups',0);
INSERT INTO `jacl_right_values_group` VALUES (5, 'jxacl~db.valgrp.users',0);

--
INSERT INTO `jacl_right_values` VALUES ('FALSE', 'jxacl~db.valgrp.truefalse.false', 1);
INSERT INTO `jacl_right_values` VALUES ('TRUE',  'jxacl~db.valgrp.truefalse.true', 1);

INSERT INTO `jacl_right_values` VALUES ('LIST',   'jxacl~db.valgrp.crudl.list', 2);
INSERT INTO `jacl_right_values` VALUES ('CREATE', 'jxacl~db.valgrp.crudl.create', 2);
INSERT INTO `jacl_right_values` VALUES ('READ',   'jxacl~db.valgrp.crudl.read', 2);
INSERT INTO `jacl_right_values` VALUES ('UPDATE', 'jxacl~db.valgrp.crudl.update', 2);
INSERT INTO `jacl_right_values` VALUES ('DELETE', 'jxacl~db.valgrp.crudl.delete', 2);

INSERT INTO `jacl_right_values` VALUES ('NO',  'jxacl~db.valgrp.yesno.no', 3);
INSERT INTO `jacl_right_values` VALUES ('YES', 'jxacl~db.valgrp.yesno.yes', 3);

INSERT INTO `jacl_right_values` VALUES ('LIST',   'jxacl~db.valgrp.groups.list', 4);
INSERT INTO `jacl_right_values` VALUES ('CREATE', 'jxacl~db.valgrp.groups.create', 4);
INSERT INTO `jacl_right_values` VALUES ('RENAME', 'jxacl~db.valgrp.groups.rename', 4);
INSERT INTO `jacl_right_values` VALUES ('DELETE', 'jxacl~db.valgrp.groups.delete', 4);

INSERT INTO `jacl_right_values` VALUES ('LIST',    'jxacl~db.valgrp.users.list', 5);
INSERT INTO `jacl_right_values` VALUES ('DETAILS', 'jxacl~db.valgrp.users.details', 5);
INSERT INTO `jacl_right_values` VALUES ('UPDATE',  'jxacl~db.valgrp.users.update', 5);
INSERT INTO `jacl_right_values` VALUES ('CREATE',  'jxacl~db.valgrp.users.create', 5);
INSERT INTO `jacl_right_values` VALUES ('DELETE',  'jxacl~db.valgrp.users.delete', 5);
INSERT INTO `jacl_right_values` VALUES ('CHANGE_PASSWORD', 'jxacl~db.valgrp.users.password', 5);

--
INSERT INTO `jacl_subject` VALUES ('jxauth.users.management', 5, 'jxacl~db.sbj.users.management');
INSERT INTO `jacl_subject` VALUES ('jxacl.groups.management', 4, 'jxacl~db.sbj.groups.management');

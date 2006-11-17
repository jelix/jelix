-- mot de passe : jelix
INSERT INTO `jlx_user` VALUES ('admin', '893942cbbaf6dd55d6721353f6776df9', 'no@mail.com');

INSERT INTO `jacl_subject` VALUES ('auth.users.management', 30, 'jxauth~dbacl.users.management');
INSERT INTO `jacl_right_values_group` VALUES (30, 'jxauth~dbacl.valgrp.users.management');

INSERT INTO `jacl_right_values` VALUES (1, 'jxauth~dbacl.valgrp.users.management.list', 30);
INSERT INTO `jacl_right_values` VALUES (2, 'jxauth~dbacl.valgrp.users.management.details', 30);
INSERT INTO `jacl_right_values` VALUES (4, 'jxauth~dbacl.valgrp.users.management.update', 30);
INSERT INTO `jacl_right_values` VALUES (8, 'jxauth~dbacl.valgrp.users.management.create', 30);
INSERT INTO `jacl_right_values` VALUES (16, 'jxauth~dbacl.valgrp.users.management.delete', 30);
INSERT INTO `jacl_right_values` VALUES (32, 'jxauth~dbacl.valgrp.users.management.password', 30);

INSERT INTO `jacl_group` VALUES ( '1', 'Administrateurs', '0', NULL);
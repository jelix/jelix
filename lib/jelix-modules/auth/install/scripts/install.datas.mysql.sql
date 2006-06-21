-- mot de passe : jelix
INSERT INTO `jlx_user` VALUES ('admin', '893942cbbaf6dd55d6721353f6776df9', 'no@mail.com');

INSERT INTO `jacl_subject` VALUES ('auth.users.management', 30, 'auth~dbacl.users.management');
INSERT INTO `jacl_right_values_group` VALUES (30, 'auth~dbacl.valgrp.users.management');

INSERT INTO `jacl_right_values` VALUES (1, 'auth~dbacl.valgrp.users.management.list', 30);
INSERT INTO `jacl_right_values` VALUES (2, 'auth~dbacl.valgrp.users.management.details', 30);
INSERT INTO `jacl_right_values` VALUES (4, 'auth~dbacl.valgrp.users.management.update', 30);
INSERT INTO `jacl_right_values` VALUES (8, 'auth~dbacl.valgrp.users.management.create', 30);
INSERT INTO `jacl_right_values` VALUES (16, 'auth~dbacl.valgrp.users.management.delete', 30);
INSERT INTO `jacl_right_values` VALUES (32, 'auth~dbacl.valgrp.users.management.password', 30);

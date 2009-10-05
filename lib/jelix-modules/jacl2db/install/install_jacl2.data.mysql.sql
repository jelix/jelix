--
INSERT INTO `jacl2_group` (`id_aclgrp`, `name`, `grouptype`, `ownerlogin`) VALUES 
(1, 'admins', 0, NULL),
(2, 'users', 1, NULL),
(3, 'admin', 2, 'admin'),
(0, 'anonymous', 1, NULL);


INSERT INTO `jacl2_user_group` (`login`, `id_aclgrp`) VALUES
('admin', 1),
('admin', 3);

INSERT INTO `jacl2_subject` (`id_aclsbj`, `label_key`) VALUES 
('acl.user.view', 'jelix~acl2db.acl.user.view'),
('acl.user.modify', 'jelix~acl2db.acl.user.modify'),
('acl.group.modify', 'jelix~acl2db.acl.group.modify'),
('acl.group.create', 'jelix~acl2db.acl.group.create'),
('acl.group.delete', 'jelix~acl2db.acl.group.delete'),
('acl.group.view', 'jelix~acl2db.acl.group.view'),
('auth.users.list',   'jelix~auth.acl.users.list'),
('auth.users.view',   'jelix~auth.acl.users.view'),
('auth.users.modify', 'jelix~auth.acl.users.modify'),
('auth.users.create', 'jelix~auth.acl.users.create'),
('auth.users.delete', 'jelix~auth.acl.users.delete'),
('auth.users.change.password', 'jelix~auth.acl.users.change.password'),
('auth.user.view', 'jelix~auth.acl.user.view'),
('auth.user.modify', 'jelix~auth.acl.user.modify'),
('auth.user.change.password', 'jelix~auth.acl.user.change.password');

INSERT INTO `jacl2_rights` (`id_aclsbj`, `id_aclgrp`, `id_aclres`) VALUES
('acl.group.modify', 1, ''),
('acl.group.create', 1, ''),
('acl.group.delete', 1, ''),
('acl.group.view', 1, ''),
('acl.user.modify', 1, ''),
('acl.user.view', 1, ''),
('auth.users.list', 1, ''),
('auth.users.view', 1, ''),
('auth.users.modify', 1, ''),
('auth.users.create', 1, ''),
('auth.users.delete', 1, ''),
('auth.users.change.password', 1, ''),
('auth.user.view', 1, ''),
('auth.user.modify', 1, ''),
('auth.user.change.password', 1, ''),
('auth.user.view', 2, ''),
('auth.user.modify', 2, ''),
('auth.user.change.password', 2, '');

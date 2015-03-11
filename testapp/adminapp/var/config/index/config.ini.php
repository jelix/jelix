;<?php die(''); ?>
;for security reasons , don't remove or modify the first line

startModule=master_admin
startAction="default:index"

[modules]
jacl.access=0
jacldb.access=0
junittests.access=0
jsoap.access=0
jauth.access=2
master_admin.access=2
jauthdb.access=2
jauthdb.installparam=defaultuser
jauthdb_admin.access=2
jacl2.access=2
jacl2db.access=2
jacl2db.installparam=defaultuser
jacl2db_admin.access=2
jpref.access=2
jpref_admin.access=2

[simple_urlengine_entrypoints]
index="jacl2db~*@classic, jauth~*@classic, jacl2db_admin~*@classic, jauthdb_admin~*@classic, master_admin~*@classic, jpref_admin~*@classic"

[coordplugins]
auth="index/auth.coord.ini.php"
jacl2=1

[coordplugin_jacl2]
on_error=2
error_message="jacl2~errors.action.right.needed"
on_error_action="jelix~error:badright"

[acl2]
driver=db

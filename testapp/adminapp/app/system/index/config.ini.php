;<?php die(''); ?>
;for security reasons , don't remove or modify the first line

[coordplugins]
auth="index/auth.coord.ini.php"
jacl2=1

[coordplugin_jacl2]
on_error=2
error_message="jacl2~errors.action.right.needed"
on_error_action="jelix~error:badright"


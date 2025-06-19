;<?php die(''); ?>
;for security reasons , don't remove or modify the first line
; this is configuration specific to the local server, to this specific instance.
; it overrides app/system/mainconfig.ini.php parameters


[jResponseHtml]
; list of active plugins for jResponseHtml
; remove the debugbar plugin on production server
plugins = debugbar

[mailer]
mailerType = file

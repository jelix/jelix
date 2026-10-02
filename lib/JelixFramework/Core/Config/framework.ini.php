
; ----- example of a framework.ini.php

[installation]
; disable all installers and the installer.ini.php
; useful only if you manage the installation of modules by hands (not recommanded)
; @deprecated
disableInstallers=off
; if set to on, all modules are enabled (not recommanded)
; @deprecated
enableAllModules=off


[entrypoint:index.php]
; path to the configuration file
config="index/config.ini.php"
;type=classic/xmlrpc...
; default module
;default=1


[module:foo]
enabled=on
; path to the module directory, if it is not setup by application.init.php or if you want
; to provide alternative sources of the module
;path=
;dbprofile=
;installparam=
;skipinstaller=0/1

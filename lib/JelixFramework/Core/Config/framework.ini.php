
; ----- example of a framework.ini.php

; configuration parameters for the installation of modules
[installation]
; disable all installers and the installer.ini.php
; useful only if you manage the installation of modules by hands (not recommanded)
; @deprecated
disableInstallers=off
; if set to on, all modules are enabled (not recommanded)
; @deprecated
enableAllModules=off

; example of a section to declare an entrypoint of the application
; the name of the section must start with 'entrypoint:' followed by the filename of the entrypoint
[entrypoint:index.php]
; path to the configuration file, relative to app/system/ or var/config directories
config="index/config.ini.php"
; type of the entrypoint: classic, soap, xmlrpc..
;type=classic
; indicate if the entrypoint is the default one
default=1

; example of a section declaring a module
; the name of the section must start with 'module:' followed by the module name
[module:foo]
; indicate that the module is enabled
enabled=on
; Installation parameters. Parameters, separated by a semi-colon, can be simple keyword,
; or some key/value. For values that are list of values, each value is separated by a coma.
;installparam="enablecategories;defaultcategory=In the world"
; or it can be set as array
;installparam[enablecategories]=1
;installparam[defaultcategory]="In the world"

; path to the module directory, if it is not setup by application.init.php or if you want
; to provide alternative sources of the module
;path=

; the jDb profile to use in the installer. It can be useful to use a different profile than
; the one indicated into the installer
;dbprofile=

; deactivate the installer of the module. Use it only if you know what you are doing,
; the module may not work as expected.
; @deprecated
;skipinstaller=1

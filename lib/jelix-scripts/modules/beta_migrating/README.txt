This module contains a script to convert, in your php scripts, all old action selector (jelix beta 3.1)  to new action selector (jelix 1.0)
eg "module~ctrl_method" to "module~ctrl:method"

It convert only selector which are used as parameter of :

    * jUrl::get(...
    * {jurl ... (in templates)
    * ->action = ... (for redirection)

It run only under a *nix system.

Your files are not modified directly, but are copied then modified in the temp directory.


1) if you haven't a cmdline.php script, copy install/cmdline.php to yourApp/scripts/cmdline.php and copy install/config.ini.php to yourApp/var/config/cmdline/config.ini.php 

2) declare the jelix-scripts/modules/ in your config file :
  modulesPath= lib:jelix-modules/,app:modules/,lib:jelix-scripts/modules/

3) go into yourApp/scripts

4) run the script :

   php cmdline.php "beta_migrating~default:changeActionSelector"

it will convert all selector in yourApp/modules/

If you want specify another directory :

    php cmdline.php "beta_migrating~default:changeActionSelector" --directory "/other/directory"

5) all modified files are in your JELIX_APP_TEMP_PATH.'changeActionSelector' directory.


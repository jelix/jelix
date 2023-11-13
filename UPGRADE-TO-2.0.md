Migration
==========

Here are instructions to migrate your Jelix application from Jelix 1.8 to the dev-master
version.

If you didn't use Composer with Jelix 1.8, or if you used Jelix 1.6, first upgrade to Jelix 1.8 with Composer.
Read the migration documentation of Jelix 1.8. You have to do some things in
your application.init.php and configuration first.

## Composer

Modify your composer.json file to require this version of Jelix

```json
  {
    ...
    "require": {
        "jelix/jelix": "dev-master"
    }
  }
```

- launch composer in the directory where composer.json is:

```
   composer install
```

Then Jelix and all of its dependencies are installed into a yourapp/vendor/ directory.

## Module and app identity changed

module.xml and project.xml files are deprecated. Replace them by respectively jelix-module.json
and jelix-app.json files.

jelix-module.json:
```json
{
    "name":"the module name",
    "version": "the module version",
    "date": "date of the release",
    "label": "a label",
    "description": "a description",
    "homepage":"",
    "license":"",
    "authors":[],
    "autoload" : { /*same syntax content as in a composer.json*/ },
    "required-modules" : {
        "module name": "version (composer.json syntax)"
    }
}
```

jelix-app.json:

```json
    {
        "name":"the app name",
        "version": "the app version",
        "date": "date of the release",
        "label": "a label",
        "description": "a description",
        "homepage":"",
        "license":"",
        "authors":[],
        "directories": {
            "config":"",
            "var":"",
            "www":"",
            "log":"",
            "temp":""
        },
        "entrypoints" : [
            { "file": "entrypoint.php", "config":"config file", "type": "classic|soap|jsonrpc..."}
        ]
    }
```

if you want to keep your module.xml files, modify them:

- attributes minversion and maxversion on <dependency> elements are deprecated. Replace
  them by a version attribute, containing same syntax as in Composer
  eg: ```minversion="1.0" maxversion="1.1"```
  should become ```version=">=1.0,<=1.1"```

## Convert old controllers for command lines to commands for Symfony Console

Old command line script using controllers based on `jControllerCmdLine` and entrypoints based on `jCmdlineCoordinator`
are not supported anymore. The upgrade scripts delete automatically corresponding entrypoints, but you have
to convert your controllers into commands based on Symfony Console. See documentation of Jelix.

## changes into `application.init.php`

`App::initPaths()` and `jApp::initPaths()`: the `$scriptPath` parameter is deprecated and not used anymore.
Be sure you don't give this parameter into your `application.init.php` script.

## changes into your entrypoints

- replace `checkAppNotInstalled()` and/or `checkAppOpened()`
  by `\Jelix\Core\AppManager::errorIfAppInstalled()` and `\Jelix\Core\AppManager::errorIfAppClosed()`

## Convert old installation scripts of your module

The deprecated installation system, based on jInstaller* classes, has gone. You should
use the official installation system, based on `\Jelix\Installer\` classes.


## Convert test inside modules

Since the script runtests.php and the unit test mechanism for modules
(tests inside modules) don't exist anymore, you must write tests outside modules,
in order to not include them into Composer packages or other deployment system.
It also allows you to use the PHPunit version you want, or to use other unit tests framework.

So migrate your existing tests inside modules to another place, and configure
your own PHPunit setup.

And delete the runtests.php script from your application if it exist.

## Other API changed

- If you use plugins, you have to change their base class name for some of them:
   - jelix\core\ConfigCompilerPluginInterface to Jelix\Core\Config\CompilerPluginInterface
        the signature of the onModule method has changed to support composer.json content.
        You have to change this method in your plugins
        See Jelix\Core\Config\CompilerPluginInterface

- Files that have gone
   - lib/jelix/checker.php: if you included these file, replace the inclusion instruction
     by a call to ```\Jelix\Installer\Checker\CheckerPage::show();```

- Functions declared into the namespace `Jelix\Utilities` are now into the namespace `Jelix\Core\Utilities`

- See the list of classes and methods that have been removed (see [CHANGELOG-2.0.md]), and be sure you don't use it anymore into your application.

- See the list of classes and methods that have been marked as deprecated (see [CHANGELOG-2.0.md]), and it is
  strongly recommended to not use it anymore from now. However, you could do changes later, until the release of 
  Jelix 3.0, from which these deprecated classes and methods will be removed.


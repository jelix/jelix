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


## API changed

- If you use plugins, you have to change their base class name for some of them:
   - jelix\core\ConfigCompilerPluginInterface to Jelix\Core\Config\CompilerPluginInterface
        the signature of the onModule method has changed to support composer.json content.
        You have to change this method in your plugins
        See Jelix\Core\Config\CompilerPluginInterface

- The deprecated installation system, based on jInstaller* classes, has gone. You should
  use the official installation system, based on `\Jelix\Installer\` classes.

- Files that have gone
   - lib/jelix/checker.php: if you included these file, replace the inclusion instruction
     by a call to ```\Jelix\Installer\Checker\CheckerPage::show();```

- in your entry points, replace `checkAppNotInstalled()` and/or `checkAppOpened()`
  by `\Jelix\Core\AppManager::errorIfAppInstalled()` and `\Jelix\Core\AppManager::errorIfAppClosed()`


## Modules gone




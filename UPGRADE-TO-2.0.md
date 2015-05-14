Migration
==========

Here are instructions to migrate your Jelix application from Jelix 1.6 to the dev-master
version.


## Composerify your application


WARNING: this is experimental!!

- Install Composer http://getcomposer.org
- Create a composer.json file into your application directory.
  If you already have a such file in your project, add the package "jelix/jelix"
  in requirements.

  Here is an example of such file:

```json
  {
    "name": "MyCompany/MyApp",
    "type": "application",
    "description": "",
    "require": {
        "jelix/jelix": "dev-master"
    }
  }
```

- launch composer in the directory where composer.json is:

```
   composer install
```

- then Jelix and all of its dependencies are installed into a yourapp/vendor/ directory.
  Many external libs integrated into Jelix in previous version are now installed only
  with Composer.

- in your application.init.php, you must replace

    require (__DIR__.'/../lib/jelix/init.php');

  by

    require (__DIR__.'/vendor/autoload.php');

## Module and app identity changed

module.xml and project.xml files are deprecated. Replace them by respectively jelix-module.json
and jelix-app.json

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
    "autoload" :{ same syntax content as in a composer.json },
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


- If you made some classes inheriting from internal classes of jInstaller (except jInstallerModule),
   you should know that their API have changed.

- Files that have gone
   - lib/jelix/checker.php: if you included it, call ```\Jelix\Installer\Checker\CheckerPage::show();``` instead

- Classes that don't exist anymore:
   - jInstallerApplication

## Modules gone

- Simpletest and the module junittest are gone. If you are using them, convert your tests
  to PHPUnit.


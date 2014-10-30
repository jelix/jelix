Migration
==========

Here are instructions to migrate your Jelix application from Jelix 1.6 to the dev-master version

WARNING: this is experimental!!

- Install Composer http://getcomposer.org
- Create a composer.json file into your application directory.
  If you already have a such file in your project, add the package "jelix/jelix"
  in requirements.

  Here is an example of such file:

```
  {
    "name": "MyCompany/MyApp",
    "type": "application",
    "description": "",
    "require": {
        "php": ">=5.3.3",
        "jelix/jelix": "dev-master"
    },
    "repositories" : [
      { "type": "composer", "url":"http://packages.jelix.org" }
    ]
  }
```

- launch composer in the directory where composer.json is:

```
   composer install
```

- then Jelix and all of its dependencies are installed into a yourapp/vendor/ directory.

- in your application.init.php, you must replace

    require (__DIR__.'/../lib/jelix/init.php');

  by

    require (__DIR__.'/vendor/autoload.php');


- If you use plugins, you have to change their base class name for some of them:
   - jelix\core\ConfigCompilerPluginInterface to Jelix\Core\Config\CompilerPluginInterface
        the signature of the onModule method has changed to support composer.json content.
        You have to change this method in your plugins
        See Jelix\Core\Config\CompilerPluginInterface

- Modify your module.xml files :
    - attributes minversion and maxversion on <dependency> elements are deprecated. Replace
      them by a version attribute, containing same syntax as in Composer
      eg: minversion="1.0" maxversion="1.1"
      should become version=">=1.0,<=1.1"

- If you made some classes inheriting from internal classes of jInstaller (except jInstallerModule),
   you should know that their API have changed.

- Classes that don't exist anymore:
   - jInstallerApplication

- Simpletest and the module junittest are gone. If you are using them, convert your tests to PHPUnit

- That's all for the moment.

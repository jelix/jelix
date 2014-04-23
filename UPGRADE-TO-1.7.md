Migration
=========


Here are instructions to migrate an Jelix 1.5.* application to the trunk version

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

- then Jelix 1.6 and all of its dependencies are installed into a yourapp/vendor/ directory.
- in your application.init.php, you must replace

    require (__DIR__.'/../lib/jelix/init.php');

  by

    require (__DIR__.'/vendor/autoload.php');


- If you use plugins, you have to change their base class name for some of them:
   - jelix\core\ConfigCompilerPluginInterface to Jelix\Core\Config\CompilerPluginInterface
        the signature of the onModule method has changed to support composer.json content.
        You have to change this method in your plugins
        See Jelix\Core\Config\CompilerPluginInterface

- That's all for the moment.


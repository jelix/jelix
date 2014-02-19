Migration
=========


Here are instructions to migrate an Jelix 1.5.* application to this version

WARNING: the composer support is a work in progress. The jelix/jelix package does not
exist yet. Following instructions are for later.

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
        "jelix/jelix": "1.6a1"
    },
    "autoload": {
        "psr-0": { },
        "classmap": [ ],
        "files": [ "lib/jelix/init.php" ]
    }
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

- That's all for the moment.


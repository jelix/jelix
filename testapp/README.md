
Testapp is a web application used to test Jelix. It contains some pages where you can try
some features, and some unit tests.

A Docker configuration is available to execute testapp 
in a preconfigured environment with all needed tools. 
You have to install Docker on your computer.

Sources of testapp
==================

You must clone the git repository from https://github.com/jelix/jelix.git to retrieve
testapp source files.


Testapp with Docker
===================

A docker configuration is provided to launch the application into a container.

build
-----
Before launching containers, you have to run these commands:

```
./run-docker build
```


launch
-------

To launch containers, just run `./run-docker`.

The first time you run the containers, you have to initialize databases and
application configuration by executing these commands:

```
./app-ctl ldap-reset
./app-ctl reset
./app-ctl install
```

If you made change into jelix, you can rerun these commands.

Then you can launch unit tests:

```
./app-ctl unit-tests
```

You can execute some commands into containers, by using this script:

```
./app-ctl <command>
```

Available commands:

* `reset`: to reinitialize the application (It reinstall the configuration files,
  remove temp files, create tables in databases, and it launches the jelix installer...) 
* `composer-update` and `composer-install`: to install update PHP packages 
* `unit-tests`: to launch unit tests. you can also indicate a path of tests directory.
* `clean-temp`: to delete temp files 
* `install`: to launch the Jelix installer, if you changed the version of a module,
   or after you reset all things by hand.
* `ldap-reset`: to restore default users in the ldap
* `ldap-users`: to show users defined in the ldap

browsing the application
------------------------

You can view the application at `http://localhost:8818` in your browser. 
Or, if you set `127.0.0.1 testapp.local` into your `/etc/hosts`, you can
view at `http://testapp.local:8818`.


You can change the port by setting the environment variable `TESTAPP_WEB_PORT`
before launching `run-docker`.

```
export TESTAPP_WEB_PORT=12345
./run-docker
```

Using a specific php version
-----------------------------

By default, PHP 7.4 is installed. If you want to use an other PHP version,
set the environment variable `PHP_VERSION`, and rebuild the containers:

```
export PHP_VERSION=7.3

./run-docker stop # if containers are running
./run-docker build
./run-docker
```

Working with Xdebug
===================

Into PhpStorm
-------------

Into `File > Settings > PHP > Servers`, add a new server with the name "testappsrv".
Indicate `testapp.local` as the host and `8818` as port. Check `use path mappings`
and indicate to map `/jelixapp/` on the root of your project.

Into `File > Settings > PHP > Debug`, indicate `9003` as port.

Into `Run > Edit Configurations`, add a new configuration of type "PHP remote debug" :
Check "filter debug connection by IDE key" then choose the server "testapp" you configured
previously and use the IDE key "PHPSTORM"

To debug the testapp web site :

1. In your browser, install the xdebug extension, and indicate "PHPSTORM" as IDE key.
2. Go to `http://testapp.local:8818` and activate the xdebug extension
2. Into PHPStorm, add breakpoints, and activate the debug listener
4. browse testapp on pages concerned by your breakpoints, PHPStorm should halt the execution on these breakpoints.

To debug cli scripts,

1. Into PHPStorm, add breakpoints, and activate the debug listener
2. Go into the phpfpm container (`./app-ctl shell`)
3. Launch commands:
   - for installer.php, configurator.php and dev.php scripts, launch them with the `--xdebug` option
   - for any other scripts, type `export XDEBUG_SESSION=1` before launching them.

Into VisualStudio Code
----------------------

- Install the plugin php-xdebug into VSC
- Create the `.vscode/launch.json` with this content:

```json
{
  "version": "0.2.0",
  "configurations": [
    {
      "name": "Testapp web",
      "type": "php",
      "request": "launch",
      "pathMappings": {
        "/jelixapp/": "${workspaceFolder}/"
      },
      "port": 9003,
      "xdebugSettings": {
        "max_data": 1024,
        "max_depth": 5
      }
    },
    {
      "name": "Testapp CLI",
      "type": "php",
      "request": "launch",
      "pathMappings": {
        "/jelixapp/": "${workspaceFolder}/"
      },
      "port": 9003,
      "xdebugSettings": {
        "max_data": 1024,
        "max_depth": 5
      }
    }
  ]
}
```

- restart VSC
- You can now add breakpoint into your code
- in the "run and debug" tab, click on "listen for xdebug"
- browse the website, and then the code will halt on your break points


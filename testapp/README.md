
Testapp is a web application used to test Jelix. It contains some pages where you can try
some features, and some unit tests.

A Vagrant configuration and a Docker configuration are available to execute testapp 
in a preconfigured environment with all needed tools. 
You have to install Docker or VirtualBox and Vagrant on your computer.

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

You can view the application at `http://localhost:8817` in your browser. 
Or, if you set `127.0.0.1 testapp.local` into your `/etc/hosts`, you can
view at `http://testapp.local:8817`.

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


Testapp with Vagrant
====================

See testapp/vagrant/README.md.

Note that this way to test Jelix is deprecated and will be removed in futur branches.


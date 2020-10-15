
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

To launch containers the first time:

```
cd docker-conf
./setup.sh
cd ..
./run-docker build
./run-docker
./app-ctl ldap-reset
./app-ctl reset
```

You can execute some commands into the php container, by using this command:

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


You can view the application at `http://localhost:8817` in your browser. 
Or, if you set `127.0.0.1 testapp.local` into your `/etc/hosts`, you can
view at `http://testapp.local:8817`.

You can change the port by setting the environment variable `TESTAPP_WEB_PORT`
before launching `run-docker`.

```
export TESTAPP_WEB_PORT=12345
./run-docker
```

Testapp with Vagrant
====================

See testapp/vagrant/README.md.

Note that this way to test Jelix is deprecated and will be removed in futur branches.


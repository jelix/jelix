
Testapp is a web application used to test Jelix. It contains some pages where you can try
some features, and some unit tests.

A Vagrant configuration is given to execute testapp in an preconfigured environment with
all needed tools. You just have to install VirtualBox or Vagrant on your computer.

Sources of testapp
================

It's better to clone the git repository from https://github.com/jelix/jelix.git to retrieve
testapp source files.

You can also retrieve a zip package of testapp available on download.jelix.org, but
you have also to install a Jelix archive to install Jelix libraries. If you choose this
method, be sure that the testapp folder is at the same level of the lib folder of Jelix.


Testapp with Vagrant
=================

- install [Virtual box](https://www.virtualbox.org/) and [Vagrant](http://www.vagrantup.com/downloads.html)
- go into the testapp directory and launch the vagrant virtual machine

```
cd testapp
vagrant up
```

It will create a virtual machine with all needed software for tests: mysql,
postgresql, redis, apache, php, phpunit, Composer etc.

It can take time the first time. It depends of your internet connection.

When the "Done" message appears, and if there are no errors, Testapp is
ready. Go on http://localhost:8020/ to see the app and launch Simpletests unit tests.

You have also phpmyadmin : http://localhost:8020/phpmyadmin/ (login:root, password: jelix)

To shutdown the virtual machine, type

```
vagrant halt
```

You can also add in your hosts file a declaration of the testapp.local domain

```
127.0.0.1  testapp.local
```

And then use http://testapp.local:8020/ instead of http://localhost:8020/


Running tests in Vagrant
---------------------

After installing Testapp, you can run tests on Jelix with Testapp.

- go into the testapp directory and launch the vagrant virtual machine

```
  vagrant up
```

- go into this vm

```
  vagrant ssh
```

If you wrote new install or migration scripts, you can run the installer/updater

```
cd /jelixapp/_build/testapp/install
php installer.php
```

Finally, to launch tests:

```
  cd /jelixapp/testapp/tests-jelix/
  phpunit
```

To reinstall testapp in Vagrant
-------------------------------

During development, it may appears that testapp is completely broken. You can reinstall
it without recreating the whole vm.

Follow these instructions:

```
# connection into the vm
vagrant ssh
# in the vm, go into the right directory and lanch the script which reset all things
cd /jelixapp/testapp/vagrant/
./reset_testapp.sh
```

Full Reinstall
--------------

You should destroy the vm:

```
cd testapp
vagrant destroy
```

Then you can follow instruction to install testapp. See above.


Installation of testapp without Vagrant
==================================

- Install [Composer](http://getcomposer.com)
- Install a web server (Apache or Nginx) + php module or php-fpm + mysql
- download and install a "developer" edition of Jelix
- move the testapp directory at the same level of the lib/ directory of jelix.
- in the testapp/ directory, launch composer to install dependancies (phpunit..)

```
  composer install
```

- create a database in mysql
- rename testapp/var/config/profiles.ini.php.dist to testapp/var/config/profiles.ini.php
- configure parameters in profiles.ini.php:
    - section jdb:testapp and jdb:testapp_mysqli for mysql,
    - section jdb:testapppdo if you have pdo extension
    - section jdb:testapp_pgsql if you have postgresql sql
    don't touch other  "jdb" sections !
- configure other section in profiles.ini.php: "jkvdb:", "jcache:", "jsoapclient:"
- if you have specific configuration setting, set them into a testapp/var/config/localconfig.ini.php
  instead of modifying testapp/var/config/mainconfig.ini.php
- put write access for apache on testapp/temp/, testapp/var/log/ and testapp/var/mails
- run the script in install/installer.php 
    cd testapp/install && php installer.php
- configure your web server to access to testapp/www with a browser
- you can then run testapp in your browser

To launch tests, go into tests-jelix and run phpunit.

```
cd testapp/tests-jelix/
../vendor/bin/phpunit
```



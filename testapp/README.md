
Testapp is a web application used to test Jelix. It contains some pages where you can try
some features, and some unit tests.

Installation of testapp
=======================

- download and extract an archive of Jelix
- move the testapp directory at the same level of the lib/ directory of jelix.

- install [Virtual box](https://www.virtualbox.org/) and [Vagrant](http://www.vagrantup.com/downloads.html)
- go into the testapp directory

- launch the vagrant virtual machine

```
vagrant up
```

It will create a virtual machine with all needed software for tests: mysql,
postgresql, redis, apache, php, phpunit etc.

It can take time the first time. It depends of your internet connection.

When the "Done" message appears, and if there are no errors, the virtual machine
is ready.

Then connect to the vm
 
```
  vagrant ssh
  cd /jelixapp/
```

- install dependencies of Jelix and Testapp

```
  composer install
  cd testapp/
  composer install
```

- launch the installation of the application

```
  php install/installer.php
```

Testapp is browsable on http://localhost:8020/

Running tests
=============

After installing Testapp, you can run tests on Jelix with Testapp.

- go into the testapp directory and launch the vagrant virtual machine

```
  vagrant up
```

- go into this vm

```
  vagrant ssh
```

- then

```
  cd /jelixapp/testapp/tests-jelix/
  phpunit
```

To reinstall testapp
====================

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
==============

If you destroy the vagrant vm (vagrant destroy), remove the testapp/var/config/installer.ini.php file.
so you could reinstall the application in a new vagrant vm.


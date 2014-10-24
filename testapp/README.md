
Testapp is a web application used to test Jelix. It contains some pages where you can try
some features, and some unit tests.

Installation of testapp
=======================

- download and extract an archive of Jelix
- move the testapp directory at the same level of the lib/ directory of jelix.


- install virtualbox and vagrant http://www.vagrantup.com/, then
- go into the testapp directory
- retrieve a virtual machine
  vagrant box add ubuntu/trusty64

- launch the vagrant virtual machine

   vagrant up

It will create a virtual machine with all needed software for tests: mysql,
postgresql, redis, apache, php, phpunit etc.

- then connect to the vm
  
  vagrant ssh
  cd /jelixapp/

- install dependencies of Jelix and Testapp

  composer install
  cd testapp/
  composer install

- launch the installation of the application

  php install/installer.php

Testapp is browsable on http://localhost:8020/

Running tests
=============

After installing Testapp, you can run tests on Jelix with Testapp.

- go into the testapp directory and launch the vagrant virtual machine

  vagrant up

- go into this vm

  vagrant ssh

- then

  cd /jelixapp/testapp/tests-jelix/
  phpunit


Reinstall
=========

If you destroy the vagrant vm (vagrant destroy), remove the testapp/var/config/installer.ini.php file.
so you could reinstall the application in a new vagrant vm.



This directory contains all things needed to run Testapp and execute
tests in a virtual machine with Vagrant.


First installation
==================

- First install [Virtual box](https://www.virtualbox.org/) and [Vagrant](http://www.vagrantup.com/downloads.html)
- open a terminal, go into the dev/ directory 
- then fo into one of its sub-directory, depending on which PHP version you want 
  to launch tests : php7.0, php7.1, php5.6 or php5.3.
```
cd dev/php5.6/
```


- then you launch updatesrc.sh: it creates a build of jelix into a _build directory 
```
cd dev/
./updatesrc.sh
```
 
- Then you can launch the vagrant virtual machine

```
vagrant up
```

It can take time the first time. It depends of your internet connection.

When the "Done" message appears, and if there are no errors, Testapp is
ready. Go on http://10.205.1.16/ to see the app and launch Simpletests unit tests.

You have also phpmyadmin : http://10.205.1.16/phpmyadmin/ (login:root, password: jelix)

To shutdown the virtual machine, type

```
vagrant halt
```

You can also add in your hosts file a declaration of the testapp.local domain

```
10.205.1.16  testapp16.local
```

And then use http://testapp16.local/ instead of http://10.205.1.16/

Update Jelix and testapp
========================

Each time you do a modification in the Jelix or Testapp source code, launch
(outside the VM) the updatesrc.sh script.


Running phpunit tests
=====================

You should enter into the VM

```
vagrant ssh
```

If you wrote new install or migration scripts, you can run the installer

```
cd /jelixapp/testapp/install
php installer.php
```

To launch tests, go inside the vagrant machine (```vagrant ssh```) and:

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
cd dev/php5.6/
# connection into the vm
vagrant ssh
# in the vm, go into the right directory and lanch the script which reset all things
cd /vagrantscripts/
./reset_testapp.sh
```

Full Reinstall
--------------

You should destroy the vm. Example:

```
cd dev/php7.0
vagrant destroy
```

Then you can follow instruction to install testapp. See above.



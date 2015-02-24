
This directory contains all things needed to run Testapp and execute
tests in a virtual machine with Vagrant.


First installation
==================

- First install [Virtual box](https://www.virtualbox.org/) and [Vagrant](http://www.vagrantup.com/downloads.html)
- open a terminal, go into the dev/ directory and launch updatesrc.sh.

```
cd dev/
./udpdatesrc.sh
```

- launch vagrant

```
vagrant up
```

It can take time the first time. It depends of your internet connection.

When the "Done" message appears, and if there are no errors, Testapp is
ready. Go on http://localhost:8016/ to see the app and launch Simpletests unit tests.

You have also phpmyadmin : http://localhost:8016/phpmyadmin/ (login:root, password: jelix)

To shutdown the virtual machine, type

```
vagrant halt
```

You can also add in your hosts file a declaration of the testapp.local domain

```
127.0.0.1  testapp.local
```

And then use http://testapp.local:8016/ instead of http://localhost:8016/

Update Jelix and testapp
========================

Each time you do a modification in the Jelix or Testapp source code, launch
(outside the VM) the dev/updatesrc.sh script.


Running phpunit tests
=====================

You should enter into the VM

```
vagrant ssh
```

If you wrote new install or migration scripts, you can run the installer

```
cd /jelixapp/_build/testapp/install
php installer.php
```

To launch tests, go inside the vagrant machine (```vagrant ssh```) and:

```
cd /jelixapp/_build/testapp/tests-jelix/
phpunit
```

To reinstall testapp
====================

During development, it may appears that testapp is completely broken. You can reinstall
it without recreating the whole vm.

Follow these instructions:

```
cd dev/
# connection into the vm
vagrant ssh
# in the vm, go into the right directory and lanch the script which reset all things
cd /jelixapp/vagrant/
./reset_testapp.sh
```



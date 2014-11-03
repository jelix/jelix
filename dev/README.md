This directory contains all things needed to run Testapp and execute
tests in a virtual machine with Vagrant.

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

Then go into the VM

```
vagrant ssh
```

Go into the testapp dir built by updatesrc.sh, and launch the installer

```
cd /jelixapp/_build/testapp/var/config/
cp localconfig.ini.php.dist localconfig.ini.php
cp profiles.ini.php.dist profiles.ini.php
cd /jelixapp/_build/testapp/

composer install
php install/installer.php
```

The application is ready. Go on http://localhost:8016/ to see the app.
You have also phpmyadmin : http://localhost:8016/phpmyadmin/ (root/jelix)

To launch tests, go inside the vagrant machine (```vagrant ssh```) and:

```
cd /jelixapp/_build/testapp/tests-jelix/
../vendor/bin/phpunit
```

Each time you do a modification in the Jelix or Testapp source code, launch
(outside the VM) the dev/updatesrc.sh script.



This directory contains all things needed to run Testapp and execute
tests in a virtual machine with Vagrant.


- First install Virtual box https://www.virtualbox.org/ and Vagrant http://www.vagrantup.com/downloads.html
- open a terminal
- launch updatesrc.sh. Execute it each time you want to test a change
  in the source code of Jelix (lib/) or testapp (testapp/)
- launch vagrant

```
vagrant up
```

It can take time. It depends of your internet connection.

Then go into the VM

```
vagrant ssh
```

Go into the testapp dir built by updatesrc.sh, and launch the installer

```
cd /jelixapp/_build/testapp/var/config/
cp localconfig.ini.php.dist localconfig.ini.php
cp profiles.ini.php.dist profiles.ini.php
cd /jelixapp/_build/testapp/install/
php installer.php
```

The application is ready. Go on http://localhost:8016/ to see the app.
You have also phpmyadmin : http://localhost:8016/phpmyadmin/ (root/jelix)

To launch tests, go inside the vagrant machine (```vagrant ssh```) and:

```
cd /jelixapp/_build/testapp/tests-jelix/
phpunit
```


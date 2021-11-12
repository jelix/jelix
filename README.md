# Jelix

[![License](https://poser.pugx.org/jelix/jelix/license)](https://packagist.org/packages/jelix/jelix)
[![Latest Stable Version](https://poser.pugx.org/jelix/jelix/v/stable)](https://packagist.org/packages/jelix/jelix)
[![Total Downloads](https://poser.pugx.org/jelix/jelix/downloads)](https://packagist.org/packages/jelix/jelix)
[![Latest Unstable Version](https://poser.pugx.org/jelix/jelix/v/unstable)](https://packagist.org/packages/jelix/jelix)

What is Jelix?
==============

Jelix is an open-source framework for PHP. Jelix 1.7 is compatible from PHP 7.2 to 8.0.
It may be compatible also with PHP 5.6 and PHP 7.0/7.1 (see below).

It has a modular, extensible architecture. Applications based on Jelix are made with
modules, which allow to reuse features in several projects.

For more informations, read [details about its features](https://jelix.org/articles/en/features).

Installation
============

The best way is to use [Composer](https://getcomposer.org).
Create a directory and a composer.json file : 

```
{
    "name": "...",
    "require": {
        "php": ">=7.2",
        "jelix/jelix": "1.7.0-rc.* || ^1.7.0"
    }
}
```

Then you run:

```
composer install
```

Instead of using Composer, you can also download directly a package containing Jelix ready
to use. See [the download page](https://jelix.org/articles/en/download).

Read [the documentation to create an application](https://docs.jelix.org/en/manual-1.7/installation/create-application).

Documentation and community
===========================

There is a full manual to learn Jelix. You can read it [direcly on the website](https://docs.jelix.org/en/manual-1.7).

You can ask your questions [on the forum](https://jelix.org/forums/forum/cat/2-english) or
on our IRC Channel, #jelix, on the irc.freenode.net network.

Contribution & development
===========================

If you want to contribute, you can use the provided Vagrant configuration
which install all what is needed to run and test Jelix, and launch unit tests. See the
testapp/README.md file in the repository.

Fill issues on Github https://github.com/jelix/jelix/.

Compatibility with some PHP version
====================================

Until Jelix 1.7.6, unit tests of the framework was running with a version of
PHPUnit that was compatible with PHP 5.6 to PHP 7.4. Since we fixed
some issues into Jelix 1.7.7 to be compatible also with PHP 8.0, we had to upgrade PHPUnit.
Because the new version of PHPunit we are using is not compatible with PHP 7.1-, 
we cannot test any more the framework against these old PHP versions.

However bug fixes and minor improvements in this branch will not use specific
syntax of PHP 7.3+/8.x.

So Jelix 1.7.7+ is working well with PHP 7.2 and higher, including PHP 8.0.
It **may** works well with PHP 7.1 and lower, but we cannot guarantee it. 

Anyway, it is higly recommanded to migrate to PHP 7.3 or higher, as PHP 7.2 and
lower are not maintained any more by the PHP team. See https://www.php.net/supported-versions.php.

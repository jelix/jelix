# Jelix

[![Build Status](https://travis-ci.org/jelix/jelix.svg?branch=jelix-1.7.x)](https://travis-ci.org/jelix/jelix)
[![License](https://poser.pugx.org/jelix/jelix/license)](https://packagist.org/packages/jelix/jelix)

Since Jelix is available as a Composer Package on packagist.org:

[![Latest Stable Version](https://poser.pugx.org/jelix/jelix/v/stable)](https://packagist.org/packages/jelix/jelix)
[![Total Downloads](https://poser.pugx.org/jelix/jelix/downloads)](https://packagist.org/packages/jelix/jelix)
[![Latest Unstable Version](https://poser.pugx.org/jelix/jelix/v/unstable)](https://packagist.org/packages/jelix/jelix)

What is Jelix?
==============

Jelix is an open-source framework for PHP. Jelix 1.7 is compatible from PHP 5.6 to PHP 7.1. 

It has a modular, extensible architecture. Applications based on Jelix are made with
modules, which allow to reuse features in several projects.

For more informations, read [details about its features](http://jelix.org/articles/en/features).

Installation
============

You can use sources from the repository. 

For applications with Jelix 1.7, you could use [Composer](http://getcomposer.org).
You should declare the jelix package dependency in your composer.json:

```
{
    "name": "...",
    "require": {
        "php": ">=5.6",
        "jelix/jelix": "1.7.x-dev"
    }
}
```

Then you run:

```
php composer.phar install
```

Instead of using Composer, you can also download directly a package containing Jelix ready
to use. See [the download page](http://jelix.org/articles/en/download).

Read [the documentation to create an application](http://docs.jelix.org/en/manual-1.7/create-application).

Documentation and community
===========================

There is a full manual to learn Jelix. You can read it [direcly on the website](http://docs.jelix.org/en/manual-1.7).

You can ask your questions [on the forum](http://jelix.org/forums/forum/cat/2-english) or
on our IRC Channel, #jelix, on the irc.freenode.net network.

Contribution & development
===========================

If you want to contribute, you can use the provided Vagrant configuration
which install all what is needed to run and test Jelix, and launch unit tests. See the
testapp/README.md file in the repository.

Fill issues on Github https://github.com/jelix/jelix/.

What is Jelix?
==============

Jelix is an open-source framework for PHP5.

It has a modular and extensible architecture. Applications based on Jelix are made
with modules, which allow to reuse features in several projects.

For more informations, read http://jelix.org/articles/en/features

Installation
==============

WARNING: many changes occurs in the master branch, because of a "namespacification" which
is not finished yet. For a stable source code, see the jelix-1.6.x branch.

The source code of the master branch is compatible only with PHP 5.4 or more.

If you want to migrate an existing application to this unstable Jelix version,
read the file [UPGRADE-TO-1.7.md].

For a new application, you should use [Composer](http://getcomposer.org). Jelix packages
are not yet into Packagist, so you should indicate the Jelix packages repository in your
composer.json, and declare the jelix package dependency:

```
{
    "name": "...",
    "require": {
        "php": ">=5.3.3",
        "jelix/jelix": "dev-master"
    },
    "repositories" : [
        { "type": "composer", "url":"http://packages.jelix.org" }
    ]
}
```

Then you run:

```
php composer.phar install
```

Then read [the documentation to create an application](http://docs.jelix.org/en/manual-1.7/create-application).

Documentation and community
===========================

[The documentation](http://docs.jelix.org) is not updated yet with all changes since the
release of Jelix 1.6.x. But it should be ok for most of things.

You can ask your questions [on the forum](http://jelix.org/forums/forum/cat/2-english) or
on our IRC Channel, #jelix, on the irc.freenode.net network.

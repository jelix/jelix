What is Jelix?
==============

Jelix is an open-source framework for PHP.

It has a modular and extensible architecture. Applications based on Jelix are made
with modules, which allow to reuse features in several projects.

For more informations, read http://jelix.org/articles/en/features

About stable versions and branches
==================================

**WARNING**: many changes occurs in the master branch, because of a "namespacification"
which is not finished yet. So API may change or may be broken (even if we try to no do it)
**Don't use it for production for the moment**! However if you want to migrate an existing
application to this unstable Jelix version ( **just for tests**! ), read the file
[UPGRADE-TO-2.0.md].

The master branch will be Jelix 2.0.

For the current stable release, see the jelix-1.6.x branch. For the next stable release,
see the jelix-1.7.x branch.

**Please**, to fix issues on stable versions, do it on their corresponding branches,
not master! So **do pull requests** on stable branches!

Installation
===========

The source code of the master branch (Jelix 2.0) is compatible only with PHP 5.6 or more.

For applications with the future Jelix 2.0, you should use
[Composer](http://getcomposer.org). You should indicate the Jelix packages repository
in your composer.json, and declare the jelix package dependency:

```
{
    "name": "...",
    "require": {
        "php": ">=5.4",
        "jelix/jelix": "dev-master"
    }
}
```

Then you run:

```
php composer.phar install
```

Then read [the documentation to create an application](http://docs.jelix.org/en/manual-1.7/create-application).

Documentation and community
========================

[The documentation](http://docs.jelix.org) is not updated yet with all changes since the
release of Jelix 1.7.x. But it should be ok for most of things.

You can ask your questions [on the forum](http://jelix.org/forums/forum/cat/2-english) or
on our IRC Channel, #jelix, on the irc.freenode.net network.

Contribution & develppement
===========================

If you want to contribute, you can use the provided Vagrant configuration
which install all what is needed to run and test Jelix, and launch unit tests. See the
testapp/README.md file in the repository.

Fill issues on Github https://github.com/jelix/jelix/.

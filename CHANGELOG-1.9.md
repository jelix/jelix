Changes into Jelix 1.9.0
========================

Not released yet.

Minimum version of PHP is 8.1.

Features
--------

- In urls.xml, entrypoint can have an "alias" attribute, to indicate an alternate
  name, that could be used into the `declareUrls` of configurator. It is useful
  when your entrypoint name is not the name expected by external modules. For
  example, a module wants to be attached to the `admin` entrypoint, but the
  entrypoint corresponding to the administration interface is named `foo`, you
  can declare the alias `admin` on this entrypoint, and then the module can
  be installed.

- jDb: Support of generated column from Postgresql (contributor: Riccardo Beltrami)

- New methods `jLocale::getBundle()` and `jBundle::getAllKeys()`

- New methods `jFile::mergeIniFile()` and `jFile::mergeIniContent()`


Removes
-------

*  remove support of bytecode cache other than opcache.

Deprecated API and features
---------------------------

* `jClassBinding`
* `jelix_read_ini`

Internal changes
----------------


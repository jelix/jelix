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

- Locales can be in a directory outside an application, like into a Composer package.
  The directory should be declared with the new API `jApp::declareLocalesDir()`.

- jForms:
  - support of `<placeholder>` into `<input>`, `<textarea>`, `<htmleditor>`, `<wikieditor>`,

- jDao:
  - native support of JSON fields : dao properties having the datatype `json` 
    are automatically encoded during insert/update, or decoded during a select.

Removes
-------

* remove support of bytecode cache other than opcache.
* Remove jacl and jacldb modules. There are still available into the `jelix/jacl-module` package.
  You should migrate to jacl2 and jacl2db.
* Remove jpref and jpref_admin modules. There are still available into the `jelix/jpref-module` package.

Deprecated API and features
---------------------------

* `jClassBinding`
* `jelix_read_ini`

Internal changes
----------------

Contributors
------------

- Laurent Jouanneau
- Raphael Martin

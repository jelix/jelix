Changes into Jelix 1.9.0
========================

Not released yet.

Minimum version of PHP is 8.1.

New features
------------

**Core**

- In urls.xml, entrypoint can have an "alias" attribute, to indicate an alternate
  name, that could be used into the `declareUrls` of configurator. It is useful
  when your entrypoint name is not the name expected by external modules. For
  example, a module wants to be attached to the `admin` entrypoint, but the
  entrypoint corresponding to the administration interface is named `foo`, you
  can declare the alias `admin` on this entrypoint, and then the module can
  be installed.

- New methods `jFile::mergeIniFile()` and `jFile::mergeIniContent()`


**jLocale**

- New methods `jLocale::getBundle()` and `jBundle::getAllKeys()`
- Locales can be in a directory outside an application, like into a Composer package.
  The directory should be declared with the new API `jApp::declareLocalesDir()`.

**jForms**

- support of `<placeholder>` into `<input>`, `<textarea>`, `<htmleditor>`, `<wikieditor>`,


**jDb**
 
- Support of generated column from Postgresql (contributor: Riccardo Beltrami)
- Support of Identity column for Postgresql
- Fix usage of `jDbPDOResulset` as an iterator (with `foreach` for example): the 
  `jDbPDOResulset::fetch()` method is now called during iteration, so modifiers
  are called on records.
- Minimum version of supported databases:
  - Mysql: 8.*
  - PostgreSQL: 13 

**jDao**
 
- native support of JSON fields: dao properties having the datatype `json` 
  are automatically encoded during insert/update, or decoded during a select.
- Possibility to indicate a class from which the generated factory class will inherit.
  The classname should be indicated into the `extends` attribute of `<factory>`.
  The class can be anywhere and should be autoloadable. The class must inherit
  from `jDaoFactoryBase` and it must be abstract.
  
  
Removes
-------

* remove support of bytecode cache other than opcache.
* Remove `jacl` and `jacldb` modules. There are still available into the `jelix/jacl-module` package.
  But you should consider it as an archive. You should migrate to jacl2 and jacl2db.
* Remove `jpref` and `jpref_admin` modules. There are still available into the `jelix/jpref-module` package.
  But you should consider it as an archive. You should develop an alternative to these modules.

Deprecated API and features
---------------------------

* `\Jelix\Core\Profiles` must be used instead of `jProfiles`. Same API, but it is now relied on the JelixProfiles library. `jProfiles` is deprecated.
* If there are some plugins for jProfiles, they must inherit from `\Jelix\Profiles\ReaderPlugin` 
  instead of `jProfilesCompilerPlugin`, or they must implement at least of the new
  interface `\Jelix\Profiles\ProfilePluginInterface`.  `jProfilesCompilerPlugin` is deprecated.
* `jClassBinding`
* `jelix_read_ini`
* jDao: method type `xml`. A PHP class should be used instead, that is declared
  into the `extends` attribute of `<factory>`.
* `css` http request object is deprecated.
* constant `JELIX_SCRIPTS_PATH` is now deprecated, as it becomes useless.
* Functions declared into the namespace `Jelix\Utilities` are deprecated. Use them from the namespace `Jelix\Core\Utilities`.
* Interface `\jelix\Core\ConfigCompilerPluginInterface` is deprecated. use `\Jelix\Core\Config\CompilerPluginInterface` instead.

Internal changes
----------------

- constant `JELIX_SCRIPTS_PATH`: its value is now `<vendor path>/lib/JelixFramework/DevHelper/`.


Contributors
------------

- Laurent Jouanneau
- Raphael Martin
- Riccardo Beltrami

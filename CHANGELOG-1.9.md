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


jDb is now relying on [JelixDatabase](https://github.com/jelix/JelixDatabase).
The `jDb` class is still existing, but most of internal classes of jDb
are gone and replaced by classes of JelixDatabase, although many of old classes
are still existing (but can be empty) to not break typed parameters into your
classes.

It brings some new features:

- support of schema names into table names, for some API
- Support of generated column from Postgresql (contributor: Riccardo Beltrami)
- Support of Identity column for Postgresql
- Fix lastInsertId for SQLServer
- Support of the mssql API removed. Only the connector using the sqlsrv API is available.
- Minimum version of supported databases:
  - Mysql: 8.*
  - PostgreSQL: 13 

Plugins for jDb (aka "drivers") implementing connectors etc, are not supported
anymore. They have been replaced by classes provided directly by the 
JelixDatabase package.



**jDao**

jDao is now relying on [JelixDao](https://github.com/jelix/JelixDao).
The `jDao` class is still the main class to use to load and use Dao.
Most of internal classes of jDao are gone and replaced by classes of JelixDao, 
although many of old classes are still existing (but can be empty) to not break 
typed parameters into your classes.

It brings some new features:

- native support of JSON fields: dao properties having the datatype `json` 
  are automatically encoded during insert/update, or decoded during a select.
- Possibility to indicate a class from which the generated factory class will inherit.
  The classname should be indicated into the `extends` attribute of `<factory>`.
  The class can be anywhere and should be autoloadable. The class must inherit
  from `jDaoFactoryBase` and it must be abstract.


Plugins for jDaoCompiler (type 'daobuilder'), are not supported anymore.
  
Removes
-------

* remove support of bytecode cache other than opcache.
* Remove `jacl` and `jacldb` modules. There are still available into the `jelix/jacl-module` package.
  But you should consider it as an archive. You should migrate to jacl2 and jacl2db.
* Remove `jpref` and `jpref_admin` modules. There are still available into the `jelix/jpref-module` package.
  But you should consider it as an archive. You should develop an alternative to these modules.
* Plugins for jDb and jDao don't exist anymore.
* All error messages are now only in english. No more `jelix~db.*`, `jelix~daoxml.*` and `jelix~dao.*` locales.


Broken API
----------

- Catching an exception about invalid/unknown profil (jProfile) must be done 
  by catching `\Jelix\Profiles\Exception` instead of `jException` from now.


Deprecated API and features
---------------------------

* `\Jelix\Core\Profiles` must be used instead of `jProfiles`. Same API, but it is now relying on the JelixProfiles library. 
  `jProfiles` is deprecated as well as methods `storeInPool`, `getFromPool`, `getOrStoreInPool`. Use `storeConnectorInPool`,
  `getConnectorFromPool`, `getConnectorFromCallback` instead.
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

About jDb:

- `jDbConnection` and `jDbPDOConnection` are deprecated and replaced by objects implementing `Jelix\Database\ConnectionInterface` and  `Jelix\Database\ConnectionConstInterface`
    - constants `FETCH_*`, `ATTR_*` and `CURSOR_*` are moved to `ConnectionConstInterface`
- `jDbResultSet` and `jDbPDOResultSet` are deprecated and replaced by objects implementing `Jelix\Database\ResultSetInterface`
- `jDbParameters` is deprecated and replaced by `\Jelix\Database\AccessParameters`
    - public methods are the same, however `getParameters()` is deprecated, `getNormalizedParameters()` should be used instead
- `jDbTools` is deprecated and replaced by objects implementing `Jelix\Database\Schema\SqlToolsInterface`
    - methods `getFieldList()` and `getTableList()` are deprecated
    - constants `IBD_*` are moved to `SqlToolsInterface`
- `jDbSchema` is deprecated and replaced by objects implementing `Jelix\Database\Schema\SchemaInterface`
- `jDbIndex`, `jDbConstraint`, `jDbUniqueKey`, `jDbPrimaryKey`, `jDbReference`,
  `jDbColumn`, `jDbTable` are replaced by some classes of the `Jelix\Database\Schema\` namespace.
- `jDbUtils` and `jDbUtils::getTools()` is deprecated and is replaced by `\Jelix\Database\Connection::getTools()`
- `jDbWidget` is deprecated and replaced by `Jelix\Database\Helpers`
- `jDaoDbMapper::createTableFromDao()` returns an object `\Jelix\Database\Schema\TableInterface` instead of `jTable`

About jDao:

- `jDaoFactoryBase` is replaced by objects implementing `Jelix\Dao\DaoFactoryInterface`
- `jDaoRecordBase` is replaced by objects implementing `Jelix\Dao\DaoRecordInterface`
    - methods `getDbProfile` and `setDbProfile` don't exist on  `Jelix\Dao\DaoRecordInterface`.
    - method  `getSelector()` is deprecated, `getDaoName()` should be used instead.
- `jDaoConditions` and `jDaoCondition` are deprecated and replaced by
  `\Jelix\Dao\DaoConditions` and `\Jelix\Dao\DaoCondition`.
- `jDaoXmlException` is deprecated and not used anymore. The parser generates `Jelix\Dao\Parser\ParserException` instead.
- `jDaoGenerator` and `jDaoParser` are removed
- `jDaoMethod` is replaced by `Jelix\Dao\Parser\DaoMethod`
- `jDaoProperty` is replaced by `Jelix\Dao\Parser\DaoProperty`
- `jDaoDbMapper` is replaced by `Jelix\Dao\DbMapper`

Internal changes
----------------

- constant `JELIX_SCRIPTS_PATH`: its value is now `<vendor path>/lib/JelixFramework/DevHelper/`.


Contributors
------------

- Laurent Jouanneau
- Raphael Martin
- Riccardo Beltrami



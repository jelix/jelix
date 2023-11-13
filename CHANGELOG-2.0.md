# Changes into Jelix 2.0

- minimum PHP version is 8.1.0

- Many Jelix classes are now under a namespace, but some classes with old names
  still exist to ease the transition, although it is recommended to use new name
  as these old classes are deprecated.

- `jApp::coord()` is replaced by `\Jelix\Core\App::router()`

- Jelix config is able to read namespaces declaration as in composer.json

- project.xml is replaced by jelix-app.json
- module.xml is replaced by composer.json
- Installer internal API have been changed

- module.xml: 'creator' and 'contributor' elements changed to 'author'
- module.xml: 'minversion' and 'maxversion' are changed to 'version'
    Same syntax in this new attribute as in composer.json

- Composer package: module name are now normalized. The module name is now the
  package name with the `/` replaced by `_`. Except if the module name is
  indicated into the composer file in `composer.extra.jelix.moduleName`.

- Remove support of infoIDSuffix from jelix-scripts.ini files

- Functions declared into the namespace `Jelix\Utilities` are now into the namespace `Jelix\Core\Utilities`

- the script runtests.php and the unit test mechanism for modules
  (tests inside modules) are now gone. See upgrade instructions.
- the modules jacl and jacldb are not provided anymore. Use jacl2 and jacl2db instead.

- remove support of the deprecated command line scripts system of Jelix <=1.6. Only Symphony console scripts are supported from now.

## changes in jDb

jDb is now relying on [JelixDatabase](https://github.com/jelix/JelixDatabase).
The `jDb` class is still existing, but most of internal classes of jDb
are gone and replaced by classes of JelixDatabase:

- `jDbConnection` and `jDbPDOConnection` are replaced by objects implementing `Jelix\Database\ConnectionInterface`
- `jDbResultSet` and `jDbPDOResultSet` are replaced by objects implementing `Jelix\Database\ResultSetInterface`
- `jDbParameters` is deprecated and replaced by `\Jelix\Database\AccessParameters`
- `jDbTools` is  replaced by objects implementing `Jelix\Database\Schema\SqlToolsInterface`
- `jDbSchema` is replaced by objects implementing `Jelix\Database\Schema\SchemaInterface`
- `jDbIndex`, `jDbConstraint`, `jDbUniqueKey`, `jDbPrimaryKey`, `jDbReference`,
  `jDbColumn`, `jDbTable` are replaced by some classes of the `Jelix\Database\Schema\` namespace.
- `jDbUtils::getTools()` is deprecated and is replaced by `\Jelix\Database\Connection::getTools()` 
- `jDbWidget` is deprecated and replaced by `Jelix\Database\Helpers`
- `jDaoDbMapper::createTableFromDao()` returns an object `\Jelix\Database\Schema\TableInterface` instead of `jTable`

Plugins for jDb (aka "drivers"), implementing connectors etc, are not supported
anymore.

All error messages are now only in english. No more `jelix~db.*` locales.

## changes in jDao

jDao is now relying on [JelixDao](https://github.com/jelix/JelixDao).
The `jDao` class is still the main class to use to load and use Dao.
Some internal classes are gone.

- `jDaoFactoryBase` is replaced by objects implementing `Jelix\Dao\DaoFactoryInterface`
- `jDaoRecordBase` is replaced by objects implementing `Jelix\Dao\DaoRecordInterface`
- `jDaoGenerator` and `jDaoParser` are removed
- `jDaoMethod` is replaced by `Jelix\Dao\Parser\DaoMethod`
- `jDaoProperty` is replaced by `Jelix\Dao\Parser\DaoProperty`
- `jDaoConditions` and `jDaoCondition` are deprecated and replaced by 
  `\Jelix\Dao\DaoConditions` and `\Jelix\Dao\DaoCondition`.
- `jDaoXmlException` is deprecated. The parser generates `Jelix\Dao\Parser\ParserException` instead.

New classes:

- `jDaoContext`
- `jDaoHooks`


Plugins for jDaoCompiler (type 'daobuilder'), are not supported anymore.

All error messages are now only in english. No more `jelix~daoxml.*` and `jelix~dao.*` locales.

## test environment

- upgrade PHPUnit to 8.5.0


## internal


## deprecated

- `App::initPaths()` and `jApp::initPaths()`: the `$scriptPath` parameter is deprecated and not used anymore
- `\Jelix\Installer\EntryPoint::isCliScript()` (it returns always false from now)

## removed classes and methods

- `jJsonRpc`
- `JelixTestSuite`, `junittestcase`, `junittestcasedb`
- `jAuth::reloadUser()`
- `jIUrlSignificantHandler`
- `App::appConfigPath()`, `App::configPath()`
- `jHttpResponseException`
- `jResponseHtml::$_CSSIELink` `jResponseHtml::$_JSIELink` `jResponseHtml::getJSIELinks` `jResponseHtml::setJSIELinks` `jResponseHtml::getCSSIELinks` `jResponseHtml::setCSSIELinks`
- `jResponseStreamed`
- `jEvent::clearCache()`, `Jelix\Event\Event::clearCache()`
- `jFormsDaoDatasource::getDependentControls()`
- `jFormsControlCaptcha::$question`, `jFormsControlCaptcha::initExpectedValue()`
- `Jelix\Forms\HtmlWidget\RootWidget::$builder`
- `jFile::getMimeType()`, `jFile::shortestPath()`, `jFile::normalizePath()`
- `jIniFile`, `jIniFileModifier`, `jIniMultiFilesModifier`

From the command line scripts system of Jelix <=1.6:

- `jApp::scriptsPath()`, `App::scriptsPath()`, `AppInstance::$scriptsPath`, 
- `jControllerCmdLine`, `jCmdLineRequest`, `jResponseCmdline`, `jCmdlineCoordinator`, `jCmdUtils`
- `Jelix\DevHelper\CommandConfig::$layoutScriptsPath`


## Removed modules

- jacl and jacldb. Use jacl2 and jacl2db instead.

## Removed plugins

- kvdb: file2

## other removes:

_ `j_jquerypath` variable in templates
- configuration parameter `loadClasses` from the `sessions` section

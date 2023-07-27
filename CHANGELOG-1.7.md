Changes into Jelix 1.7
======================

Next
----

* Support of a default `Reply-To` header into jMailer
* new method `ConfigurationHelpers::updateEntryPointFile()`
* new method `InstallHelpers::updateEntryPointFile()` and `InstallHelpers::removeEntryPoint()`
* backport method `ConfigurationHelpers::removeEntryPoint()`
* Update header of API documentation
* Fix `Jelix\Utilities\utf8_*` functions



Jelix 1.7.15
------------

* Fix package build: zip and tar.gz did not contain the vendor directory.
* Installer: 
  * fix error about undefined method `Jelix\IniFile\IniModifierReadOnly::setValue()` 
    in legacy installers/upgraders
  * Fix `jInstallerEntryPoint::getSingleLocalConfigIni()`
* tests: upgrade Qunit

Jelix 1.7.14
------------

* Fix compatibility with PHP 8.2.
* Fix styles for questions into Symfony console commands
* Installer:
  * fix the migration of localconfig during local migration
  * Fix the selection of upgraders to execute: in some case, some upgraders may not be executed
* Configurator:
  * Fix configurator with modules installed locally
  * Fix error when a module and its dependencies are upgraded
  * jacl2 and jauth configurator: do not ask entrypoint if not needed
* Scripts: fix exit code for console.php and dev.php
* Fix issues during the launch of the app:init-admin command
* Fix error in cli commands when using jUrl.
* Fix mail test commands: template format was not good
* jForms: replace some calls of deprecated objects

Contributors: René-Luc Dhont and Raphael Martin.

Jelix 1.7.13
------------

* jForms:
  * `jFormsBase::saveFile`: new boolean parameter to delete original file
  * support of `filterhtml` on `<input>`
  * Fix loading of jforms js: return http 404 when the corresponding form doesn't exist
  * Fix html form widget, when retrieving the jquery.js from current webassets collection
  * Fix jForms compiler: allow image into a widget group
* Installer: upgrade.php should have priority over upgrade_1_6.php
* Fix file kv driver: use the isResource method instead of is_resource.
* Fix jDb, AccessParameters: must not generate pdooptions
* Fix File logger: date and ip were duplicated
* Fix notices into pagelinks tpl plugin
* Fix jAcl2Authentication adapter: use jAcl2JAuthAdapter by default


Jelix 1.7.12
------------

* jDb:
  * reintroduce the support of placeholders `$1`, `$2` etc into prepared queries, like in Jelix 1.6
* jAuth: fix support of driver configuration stored into a `auth_<driver>` section 
* jCache: fix the garbage API of the file plugin. It tried to remove non-empty directories
* Fix installer: configuration of entrypoints were not saved
* Fix the load of plugins configuration for the router (jCoordinator)
* Configurator of the jelix module: add `nosetup` in the choice of `jelix-www` installation
* Upgrade Symfony/Console to 5.4 to be compatible with PHP 8.1.
* Fix `jFile::write()`: directory were not created with the chmod of configuration
* assets: add missing ui.fr.js for the datepicker
* new `php dev.php app:ini-merge` command to merge two ini files.

From Jelix 1.6:

* `closed.html` can now be stored into `var/themes/`
* Replace the use of deprecated `utf8_*` functions
* jDb: New option `session_role` in profiles for pgsql to set session role
* Fix installer: useDbProfile must not change the profile name
* jMailer: new value "unencrypted" for secure_protocol
* Fix error "two few arguments" during call of some error handlers

Jelix 1.7.11
------------

* Fix compatibility issues with PHP 8
* fix logout into jAuth: the persistant cookie was not deleted correctly
* Installer:
  * Fix the setup of the web files of a module, deleted the content of the directory when it was the target of a link.
  * Configurator of jelix module: configure database access only if needed
  * Restore the support of `<module>.path` into the configuration. It eases migration from jelix 1.6.x and is useful to declare a module with a configuration script
* new support of revision number on JS/CSS links for cache:  A revision parameter can be added on JS/CSS links automatically, in order to bypass the browser cache on JS/CSS
* debugbar: it can now moved to the top center of the page
* jTpl
  * upgrade Castor to 1.1.0. New major features in templates :
    - support of macro
    - autoescaping
  * pagelinks plugin: new display properties for classes. CSS classes can now be set on each elements of a list of pages.
* jForms:
  * fix time widget: use webassets
  * date, datetime and time widgets: add placeholders
  * translate strings into autocomplete widgets
  * fix the possibility to setup CSS class on buttons widgets
  * Support of the Image control into the XML format
  * Improve widgets to facilitate the overriding of html content. On some widgets there are new methods that output only Html content, so new widget just has to override these methods to change the generated HTML.
  * more documentation into the code of jFormsSession
  * new template plugin `{ifctrltype}`
  * backport the method `HtmlBuilder::outputAllControlsValues()` from Jelix 1.8
* Jelix commands:
  * fix some comments and bad returned values
  * `app:ini-change` command: support of section deletion
* all enhancements and bug fixes from Jelix 1.6.37
  * jforms, image selector: support of "auto" for width and height of the dialog
  * New `jApp::setApplicationInitFile()` to indicate an application.init.php file
  * Fix jforms javascript: selection was loose when reloading a menulist via XHR
  * jacl2db_admin: fix CSS to stick headers of the rights table
  * new method `jAuth::getReasonToForbiddenPasswordChange()` and new interface `jIAuthDriver3`
* all enhancements and bug fixes from Jelix 1.6.36
  * Fix jacl2db: id_aclgrp field should be bigger than login
  * Fix entrypoint installation: it did not update path when the `require` instruction for `application.init.php` does not use parenthesis.
  * Fix jauthdb_admin: it should call `jAuth::canChangePassword()` when needed
  * jForms, formfull widget: display correctly checkboxes
  * jForms: separate each item of checkboxes/radioboxes by new line
  * Fix: error pages should not require authentication
  * Fix the retrieval of the documentRoot when compiling configuration


Jelix 1.7.10
------------

* fix a regression into jAuth for module installers. `password_hash_method` and `password_hash_options` were 
  missing for installers which don't use the new method `jAuth::getDriverConfig()`


Jelix 1.7.9
-----------

* New locale to format money value with or without taxes, `jelix~format.format.monetary.wtax` and `jelix~format.format.monetary.wotax`
* New controller for CRUD with a filters form: `jControllerDaoCrudFilter`
* New `jAuth::reloadUserSession()`
* master_admin: new default menu items group for CRUD (id: `crud`)
* Backport enhancement and bug fix from JelixDatabase
  * Pgsql tools: new methods to parse en generate pgsql array values
  * new methods `jDbResultSet::fetchAssociative()` and `jDbResultSet::fetchAllAssociative()`
  * new method `jDbResultSet::free()`
  * new method `ConnectionInterface::close()`
  * Fix the support of query parameters given to the `execute()` method of mysql and postgresql connectors
  * Fix the parsing of query parameters: `::something` should not be read as parameter
* all enhancements and bug fixes from Jelix 1.6.35
  * jForms
    * support of option `widgetsAttributes` on the form tag. With this option, we can indicate attributes to set on widgets. Useful when you don't have a ctrl_control tag for a specific widget on which you want to set attributes.
    * Fix control time in `jforms_light.js`: bad month/day values
    * fix setting of default attributes in upload widgets
    * fix autoload of `jFormsControlImageUpload`
    * fix some issues into upload2 and imageupload widgets
    * fix the algorithm to guess modified controls with uploads
    * fix a JS error when closing the image editor dialog
    * fix the label of the cancel button of the image editor dialog
  * jAuth
    * Add hooks on the login form template. It allows to other modules to add content on the login form, like buttons to use other authentication methods.
    * jAuthDb installer should be able to use compatible drivers. If some authentication module have their own driver but are using the same dao/table of jAuthDb, jAuthDb should be able to create default user when needed.
    * Fix the `jauth~login:form` controller when after_login is `jauth~login:form`
  * jAcl2
    * jacl2db_admin: add title on the pages
    * Fix jacl2db sql upgrade script about `jacl2_group.code`
    * Fix jacl2db sql upgrade script about rights for anonymous users
  * jDb
    * Fix mysqli connector: execMulti did not return errors. When the given sql scripts fails, there was not an exception, contrary as it is expected.
  * Core
    * Support of `<name>.class` properties in the `coordplugins` section of the configuration. It allows to specify a class if its name is different from `*CoordPlugin`.
    * Backport support of localframework.ini.php. It allows for modules installers to declare a new entrypoint. Installers should call the new method `createEntryPoint()`.
    * Fix jelix upgrade script about `availableLanguageCode`
  * jTpl
    * Fix `number_format` modifier with decimal point value, when `''` is given
  * Utils
    * Backport some features of `\Jelix\IniFile` into `jIniModifier`
  * Installer
    * `jInstallerBase::createEntryPoint()` should set the path to application.init.php
  * Fix typo in some locales
  * Fix the load of some classes with some tools like phpstan
  * Fix the file2 driver for jKvDb
  * Fix some doc comments
  * Rename namespace `jelix` by `Jelix`. No consequence on your code, but it helps to generate a better reference documentation



Jelix 1.7.8
-----------

* have all bug fixes and improvements from Jelix 1.6.34:
  * Fix the installer during the setup of the module access
  * Fix `jResponseHtml::addJsLink`: possibility to setup the `type` attribute
  * Fix jauthdb_admin user creation: login name should be trimmed
  * Fix automatic domain name and port retrieval. In some Nginx configuration, SERVER_NAME may be initialized with the port, and so jUrl may generate some url with two port.
  * Fix crash in PHP 8 and warning in PHP 7.4 within jDb and the core
  * Translations are now available in several languages

Jelix 1.7.7
-----------

* have all bug fixes and improvements from Jelix 1.6.31, 1.6.32 and 1.6.33:
  * Fix various issues with PHP 8.0
  * Fix some dao locales that have a bad pattern for sprintf
  * Fix float to string convertion into jDb
  * Fix pgsql schema: should list only tables from the search_path
  * Fix comparison of values in the jForms modified check
  * Fix many issues in the checking of admin rights in administration UI. There were some situation when the checking was badly done, so there were some possibility into the UI to remove completely admin rights.
  * jauthdb_admin: adding autocomplete to search users
  * jInstaller, module.xml: allow http:// as well as https:// into the namespace value
  * Fix php 7 compat issue in the memcache driver
  * Fix jacl2db_admin: missing translations
  * Fix command acl2right: forbidden rights were displayed as authorized rights
  * Fix acl2right command: subcommand to forbid a right was missing
  * jacl2db_admin: little improvements in the display of list of rights to be more usable
  * New option `force_new` in profiles for pgsql to force a new connection

* Fix some issues in the docker stack for tests
* Upgrade PHPUnit to 8.5.14

Because there is not a version of PHPUnit that is compatible with PHP 5.6, 7.x and 8.0
at the same time, Jelix 1.7 cannot be tested any more against PHP 5.6 to PHP 7.1.
So, starting from this version, there is no more guarantee that Jelix 1.7 works 
well on these old version of PHP. However, bug fixes and minor improvements in this
branch will not use specific syntax of PHP 7.3+/8.x, so it could not be an issue.

Anyway, it is higly recommanded to migrate to PHP 7.3 or higher, as PHP 7.2 and
lower are not maintained any more by the PHP team. See https://www.php.net/supported-versions.php.

Jelix 1.7.6
-----------

* have all bug fixes and improvements from Jelix 1.6.30.
    * Fix basePath in the context of a command line script
    * Fix a PHP error in the listbox form widget
    * Fix a issue in jAcl2 admin: an administrator could put himself into a group which forbid some admin rights, and so he was not an administrator anymore.
    * new command to test the mailer. To send an email to check mailer parameters, 
      execute `php console.php mailer:test my.email@example.com`
    * jForms: new control jControlTime, and support of a `<time>` element in jforms 
    * New method `jEvent::getParameters()`
    * New method `jAuth::setUserSession()`
    * jAcl2 admin interface: confirmation on groups delete button
    * jAcl2 admin interface: added a separator between groups in users list
    * Fix compat with php 7.4 in jCmdUtils
    * New methods `jServer::getDomainName()`, `jServer::getServerURI()`, 
      `jServer::getPort()`, `jServer::isHttps()`

* Fix web assets upgrader with jforms_datepicker and jforms_datetimepicker
* Fix web assets loading of the datetime widget
* Fix console: initialize a coordinator so components could work well
* Fix: some components should check if the coordinator is there or not 
* Authentication: `checkCookieToken()` does not trigger anymore a 500 error page
  if the cookie token is invalid.
* jAcl2: adapter system to make the glue to authentication.    
  It allows to use authentication library other than jAuth, like
  the jelix/authentication-module library.
* jAcl2: reword terms 'role' to 'right'. Rewording 'subject' to 'role' into Jelix 1.7 was a mistake.
  So some API have been renamed, but old API are still usable, even if deprecated. 
  - `jAcl2DbManager::addRole()` becomes `createRight()`
  - `jAcl2DbManager::removeRole()` becomes `deleteRight()`
  - `jAcl2DbManager::removeRole()` becomes `deleteRight()`
  - `jAcl2DbManager::copyRoleRights()` becomes `copyRightSettings()`
  - `jAcl2DbManager::addRoleGroup()` becomes `createRightGroup()`
  - `jAcl2DbManager::removeRoleGroup()` becomes `deleteRightGroup()`
  - dao method `jacl2rights::getRightsByRole()` becomes `getRightSettings()`
  - dao method `jacl2rights::deleteByRoleRes()` becomes `deleteByRightRes()`
  - dao method `jacl2rights::deleteByRole()` becomes `deleteByRight()`
  - dao method `jacl2rights::deleteByGroupAndRoles()` becomes `deleteByGroupAndRights()`
  - dao method `jacl2subject::findAllRoles()` becomes `findAllRights()`
  - dao method `jacl2subject::removeRolesFromGroup()` becomes `removeRightsFromRightsGroup()`
  - dao method `jacl2subject::replaceRoleGroup()` becomes `replaceRightsGroup()`
  - console command `acl2:role-create` becomes `acl2:right-create`
  - console command `acl2:role-delete` becomes `acl2:right-delete`
  - console command `acl2:role-group-create` becomes `acl2:rights-group-create`
  - console command `acl2:role-group-delete` becomes `acl2:rights-group-delete`
  - console command `acl2:role-group-list` becomes `acl2:rights-groups-list`
  - console command `acl2:roles-list` becomes `acl2:rights-list`
* Tests: docker configuration for a test environment, to replace Vagrant.

Jelix 1.7.5
-----------

Released on august 17th, 2020

* have all bug fixes and improvements from Jelix 1.6.29.

Jelix 1.7.4
-----------

Released on june 9th, 2020

Bug fixed:

* All bug fixes and improvements from Jelix 1.6.28

Improvements:

* add `--profile` option on commands `module:create-class-dao` and `module:create-form`

Jelix 1.7.3
-----------

Released on march 28th, 2020

Bug fixed:

* Fix `setAttribute()` method in the mysqli driver and other little issues into plugins of jDb
* Fix a regression in `jDbPgsqlTools::getFieldList()`
* Fix help into the app:create-lang-package command
* Fix installer: `module:configure` did not honor `--no-local` correctly
* Fix installer: error in `jelix:migrate` when called when the app is already migrated
* Fix some unit tests
* Change the configuration parameter name `notfoundAct` to `notFoundAct`. The upgrader change it.
* All bug fixes and improvements from Jelix 1.6.26 and 1.6.27

Improvements:

* new web assets configuration for the new autocomplete and imageupload widgets (from Jelix 1.6.26)
* upgrade dependencies: PhpMailer 5.2.28, IniFile 3.2.4, FileUtilities 1.8.4, Symfony Console 3.2.14


Jelix 1.7.2
-----------

Released on october 20th, 2019 

Bug fixed:

* Fix install parameters given to installers. Given parameters list didn't contain parameters that have default values indicated into configurators objects.
* Fix the configurator: modules were not enabled when they didn't have a configurator class
* Fix auto-discovering of the plugins directory provided with Jelix
* All bug fixes and improvements from Jelix 1.6.25

Improvements:

* new class jHttpErrorException, jHttp401UnauthorizedException, jHttp403ForbiddenException and jHttp404NotFoundException to generate an HTTP error from controllers and coordinator plugins.
* new default response, having the id 'htmlerror', used to return an HTML content with an HTTP error, when the HTTP client accepts HTML content. This response is now used when the core router catch an jHttpErrorException or any of its child class.
* upgrade ckeditor5 to 12.4.0 with some additionnal plugins

Jelix 1.7.1
-----------

Released on september 11th, 2019 

* jForms: fix regression about CSRF token check
* webassets: fix url when using themes 


Jelix 1.7.0
------------

Release: 2019-09-09

  * [New features in Jelix 1.7.0](https://docs.jelix.org/en/manual-1.7/new-features)
  * [Tutorial to migrate to Jelix 1.7.0](https://docs.jelix.org/en/manual-1.7/installation/migrate)

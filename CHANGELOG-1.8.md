Changes into Jelix 1.8
======================

Next
----

- fix regeneration of session id: support of unstable network and keep session data during login
- fix jforms: form cache must not rely on session id, else forms could not be retrieved from cache
  after a regeneration of session id.

1.8.21
------

- Fix security issue with PHP session: session id must be changed after authentication
  to avoid session fixation attacks
- Fix security issue with the cookie for persistance: allow it only on https connections.

1.8.20
------

- Remove warning for php 8.4 about `E_STRICT` (contribution of Joel Kociolek)
- Fix PhpMailer version: 6.11 has unexpected breaking changes
- Fix a notice into jImageModifier
- Fix deprecation notice into jDbPDOConnection and tests for PHP 8.5

1.8.19
------

- fix the cache filename of configuration in the case where opcache is disabled

1.8.18
------

- Fix jAcl2: rights were not removed when removing a user who has not a private group.


1.8.17
------

- fix installer: module version into installer.ini.php may not be filled correctly after an upgrade
- improved output messages during an upgrade

1.8.16
------

- New datasource class for jForms: jFormsDynamicStaticDatasource
- Add `$httpCode` and `$httpMessage` in template for html errors
- Fix jForms linked list: loaded list containing "0" as value didn't show the corresponding item
- Fix jAcl2Db: group id can now contain upper case letter and dash character


1.8.15
------

- Fix jAcl2 : increase some id fields so we can use for example long email address for login names 
- unit-tests are ok to use Redis 7

1.8.14
------

- jForms: add new methods to TemplateController for some external template plugins
- jForms TemplateController: new parameter on outputControlValue for content when the value is empty
- new template plugin ifctrlactivated for jForms
- Fix compatibility with PHP 8.4


1.8.13
------

- Fix some notices into jImageModifier
- Fix autocomplete widget: missing semi-colon into the generated JS
- Fix password editor widget: allow more customization in HTML
- Fix password editor: generate less special characters
- Fix SQL request into the `acl2:rights-group-delete` command


1.8.12
------

- New: support of error page for any locales, into `app/responses/` : `error.fr_FR.php`, `error.de_DE.php`...
- Fix datetime picker: wrong selected value into the hour drop box
- Fix datetime picker: localization were missing
- Fix a javascript issue into the password widget: the value was not send correctly in some cases

1.8.11
------

- In urls.xml, entrypoint can now have an "alias" attribute, to indicate an alternate
  name, that could be used into the `declareUrls` of configurator. It is useful
  when your entrypoint name is not the name expected by external modules. For
  example, a module wants to be attached to the `admin` entrypoint, but the
  entrypoint corresponding to the administration interface is named `foo`, you
  can declare the alias `admin` on this entrypoint, and then the module can
  be installed.
- Fix installer: deconfigured modules were not uninstalled
- Fix installer: versions of dependencies of an already installed module
  were not checked when this module was not upgraded and its dependencies were upgraded.
- Fix column order into jControllerDaoCrud
- Fix jForms: php error when trying to process a file that is too big 
- Fix default charset of mysql table for jacl2db and jauthdb
- Fix jacl2db configurator: driver was not set correctly on EP config
- Fix jAuthdb installer: it should use the conf given by the installer
- tests: install xdebug and add `--xdebug` options on some commands
- Fix `app:create` command: create missing `var/db/sqlite3` directory.
- Fix debugbar: `'center'` value of defaultPosition config parameter was not taken account
- Fix configurator for sessions: some parameters were stored into the `session` 
  section of the configuration instead of `sessions`.

1.8.10
------

- New template plugin ctrl_value_assign for forms
- Fix installer: some upgraders may not be executed in some case
- Fix jDbConnection::lastIdInTable(): names should be enclosed
- Fix lastInsertId() on Sqlserver connector

1.8.9
-----

- Fix installer: installation parameter into mainconfig may not be taken account.
  In some cases, default values of installation parameter may be unexpectedly 
  written into localconfig.ini.php during the configuration, so
  installation parameters may not be taken account.
- jResponseHtmlFragment: remove the final attribut from output methods
- Fix jDbSchema: reload list of tables in some case
- Configurator: show warning when a bad path is given to getFilesToCopy
- fix pgsql drive: execute should return the status of pg_execute
- fix jacl2db_adminListener: url missing when only acl.user.view rights

1.8.8
-----

- Fix package and subpackage into doc comments
- Fix jacl2db_admin: the module should use the jAcl2 authentication adapter to retrieve the authenticated user.
- Fix Sqlite3 jDb driver: it must not free results if connection is already closed
- Fix regression: event listeners cache was never reused

1.8.7
-----

- jForms: in javascript, the form is now declared after the setup of all controls,
  not only after the setup of the jforms object. So the event `jformsready` is 
  triggered and the callbacks declared with `onFormReady` are executed after the full initialization of the javascript
  objects of jForms. If you want to keep the old behavior, you should indicate
  the option `'deprecatedDeclareFormBeforeControls'=>true` to the form builder.
- Fix dbcache driver of jAcl2: anonymous rights were not stored properly into
  the cache, and so rights were not taken account.
- Fix Composer package: remove the `assets/` directory, it is useless

1.8.6
-----

- Check compatibility with PHP 8.3: all seems ok
- ldap driver for jAuth: remove deprecation notice with PHP 8.3, about parameters on ldap_connect
- ldap driver for jAuth: support of TLS mode. Configuration parameter `tlsMode`, which can have values
  `""` (empty, no secured connection), `starttls` or `ldaps` (`ldaps` by default if port 636)
- jDb/mysqli: fix typo into getAttribute/setAttribute
- Fix Jelix 1.7/1.8 migrator: changes on url map were not saved
- Tests: upgrade PHPUnit to 9.6 

1.8.5
-----

- jacl2db_admin: add links to each user profile into the users list
- The application version is available into `jApp::config()->appVersion`
  and is set by default to the version stored into the `project.xml` file.
- new configuration parameter `sslmode` for Postgresql profiles. Possible
  values are `disable`, `allow`, `prefer`, `require`.

1.8.4
-----

* Fix redirections when there is an error, into the rights management interface (jacl2db_admin)
* new method `jResponseFormJQJson::setError()` to force to return an error message/url redirection to the form.
* New: Support of favicons into WebAssets (ex: `mygroup.icon=favicon-32x32.png|sizes=32x32`).
* new method `WebAssetsSelection::getIconLinks()`
* Fix issue into the password editor: the new value of the password changed by the button to regenerate one,
  was not taken account when submitted the form

1.8.3
-----

* New method `setHtmlAttributes()` on the `jResponseHtml` class, to set attributes on
the `<html>` element.
* New method `getFileResponse()` in `jController` to ease to return a file as a response
* jauthdb_admin module: 
  * New event `jauthdbAdminAfterUpdate` when properties of a user has changed.
  * Fix: uploaded files should be saved after the events `jauthdbAdminAfterCreate` and `jauthdbAdminAfterUpdate`,
    so listeners can save uploaded files into directories other than into the default one.
  * Use a jForms form to change a password 
  * Add a `formOptions` template variable in templates displaying forms of jauthdb_admin,
    so other modules can add options for jforms widgets.
  * new events `jauthdbAdminPasswordForm` and `jauthdbAdminCheckPasswordForm` for the password form
* jauthdb: 
  * possibility to authenticate with the email or the login, if there is a configuration parameter
    `authenticateWith=login-email`.
  * the section `auth_<driver>` is now merged with the `<driver>` section of `auth.coord.ini.php`, so
    we can redefine some configuration parameter of the `<driver>` section, into `localconfig.ini.php` for example.
  * new method `getDao()` on the jAuth `db` driver
* new class `jAuthPassword` to check the strength of a password or to generate a random password
* new jforms widget: `password_html` for `secret` controls. Adds a "view" button aside the input.
* new jforms widget: `passwordeditor_html` for `secret` controls. It checks the strength of the
  password, by calculating the entropy, and by comparing the edited password against a list of the most 
  used passwords. Adds also three buttons:  a "view" button, a "regenerate" button, and a "copy" button.
* jForms: fix generated JS into choice, upload2 and group widgets
* new method `jAcl2DbUserGroup::renameUser()`
* new configuration parameter to set default value for the `Return-Path` header into jMailer.
* Fix debugbar: elements at the same level of the debugbar were not clickable
* Fix jDb: support of double quotes around schema names into `search_path`
* Fix jDb: jDbSchema for Postgresql did not find table in schemas having upper case
  letters.

Improvements and bug fix from Jelix 1.7:

* Fix regression into `jFormsBase::getModifiedControls()`: some controls like submit were considered as modified  although it does not make sens
* Fix regression into the debugging of jMailer: the output was not made anymore into logs
* Fix error in create:dao command with nullable fields
* Fix jforms choice widget, display control value: add a space betwen label and value.
* Fix some PHP warning about passing null values to htmlspecialchars
* Fix the version into the JELIX_VERSION constant. It was not updated in the latest release.
* Fix the migration 1.6->1.7 of configuration file of entry points.

1.8.2
-----

* Fix the display of the debugbar, when having long lines

Improvements and bug fix from Jelix 1.7:

* Support of a default `Reply-To` header into jMailer
* new method `ConfigurationHelpers::updateEntryPointFile()`
* new method `InstallHelpers::updateEntryPointFile()` and `InstallHelpers::removeEntryPoint()`
* Update header of API documentation
* Fix `Jelix\Utilities\utf8_*` functions
* tests: fix error into the ldap docker image at startup
* tests: fix a warning in upgraderValidityTest with PHP 8.2


1.8.1
------

Released on may 30th, 2023

* jEvent: support of any classes for listeners
  The name of listener into the events.xml can be the full name of a class.
  The class must have a namespace and must be autoloadable.
* jDao: records can now extend from any classes. The `extends` attribute
  can now contain a class name instead of a record class selector.
  The class must have a namespace and must be autoloadable.
* jEvent: fix loading of listener. They were instantiated at each notification. 
* jacl2db: fix a bug when a right is forbidden. All rights were set to "forbid"
  when a 'view' rights was forbidden.
* jacl2db_admin: fix applying forbidden state on "view" rights.
* installer: fix default module configurator, it did not load installation parameters
  and then, installers didn't have calculated parameters.
* Upgrade PHPMailer to 6.8
* Upgrade jQuery to 3.7.0
* Upgrade Datatables to 1.13.4
* Upgrade Ckeditor to 38.0.1


Jelix 1.8.0
------------

Released on April 17th, 2023

### Features

* Jelix 1.8 is now compatible from PHP 7.4 to PHP 8.2.
* Core: 
  * new class `Jelix\Core\Services` that will allow to access to some services without using static methods of these service.
    The instance of this object is accessible from `\jApp::services()`.
    Warning: this is a work in progress.  
  * binary response: support of callback function to generate content. Can be used for streams, generator etc..
  * `jIncluder::incAll()`: it returns now the value returned by the including of the file generated by the compiler
  * Add an interface `jIActionSelector` on `jSelectorActFast` and `jSelectorAct`, to allow to provide other 
    implementation of action selector.
* Controllers:
  * new methods `redirect()` and `redirectUrl()` that are shortcuts to the creation of a redirection object
* jForms:
  * new method `BuilderBase::outputAllControlsValues()`
  * new method `BuilderBase::outputControlRawValue()`
  * new method `WidgetInterface::outputControlRawValue()`
  * image widget: add possibility to show the temporary new image
    New option for the image widget: showModeForNewImage.
    It indicates how the new image can be display.
  * builder option and JS API to ease the submit of a form with an XHR request.
* jAcl2Db admin UI: 
  * the user interface has been reworked to be more usable
  * possibility to hide some rights (`hiddenRights` in 
    the `acl2` configuration section)
  * It is not possible anymore to set some rights on the anonymous group (acl, or related to users)
  * rights are now dependent of the `view` right of the same branch.
* jEvent:
  * possibility to give an event object to `jEvent::notify()`. So you can have events having their own methods to manipulate
    information for the event.
  * Rework the implementation of the events dispatcher, to follows PSR-14. `jEvent::notify()`
    will be deprecated in futur versions, prefer to use `\jApp::services()->eventDispatcher()->dispatch($event)` for event objects. 
* Configurator:
  * The configurator is now able to declare automatically modules urls, and to remove
    all Urls of a module when it is uninstalled.
  * Module configurators can indicate a list of url to declare into the urls mapping 
  * more methods on XmlMapModifier to remove urls
  * new method `findProfile()` on helpers
* Installer:
  * The PreInstallHelpers class has now the database API to allows to check the
    content of the database before allowing the installation
  * new method `findProfile()` on helpers
  * new option to the installer command: `--no-clean-temp`. And the command verifies now that
    all content of the temp directory can be deleted.
  * Fix the selection of upgraders to execute: in some case, some upgraders may not be executed
* Unit tests: jUnitTestCase and jUnitTestCaseDb are deprecated. Use  
  `\Jelix\UnitTests\UnitTestCase` and `\Jelix\UnitTests\UnitTestCase` instead.
  Support of PHPUnit versions older than 6.0 is removed.
* the script runtests.php and the unit test mechanism for modules 
  (tests inside modules) are now deprecated.
  It is better to write tests outside modules, in order to not include them into
  Composer packages or other deployment system. It also allows you to use
  the PHPunit version you want, or to use other unit tests framework.
* Two new plugins for jTpl: `{ifacl2and}`, `{ifacl2or}`


### Removes
-----------

* the template plugins `swfjs`, `swfbiscuit` and the script `jquery.flash.js`. Flash is dead, so no reason to keep these files.
* `$GLOBALS['JELIX_EVENTS']` does not exists anymore

### Internal changes
---------------------

* Upgrade Symfony Console to 5.4
* Upgrade PHPUnit to 8.5 for our tests
* Upgrade PHPMailer to 6.6.*
* Upgrade Jquery to 3.6.1
* Upgrade Jquery UI to 1.13.2
* Upgrade CKEditor to 35.3.0
* Upgrade Datatables to 1.12.1
* Tests with CasperJs have been removed
* Some Javascript scripts like `jforms_jquery.js` are now generated with WebPack. See the `assets` directory.

* jForms: move code from template plugin to a new class TemplateController.
  It allows to control the display of the form in a single class
  instead of into several template plugins, and so, to use it into any or 
  outside a template system.

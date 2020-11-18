Changes into Jelix 1.7
======================

Jelix 1.7.6 (next)
------------------

* have all bug fixes and improvements from Jelix 1.6.30.
    * new command to test the mailer. To send an email to check mailer parameters, 
      execute `php console.php mailer:test my.email@example.com`
    * jForms: new control jControlTime, and support of a `<time>` element in jforms 
    * New method `jEvent::getParameters()`
    * New method `jAuth::setUserSession()`
    * jacl2db_admin interface: confirmation on groups delete button
    * Fix compat with php 7.4 in jCmdUtils

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

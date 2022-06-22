Changes into Jelix 1.8.0
========================

Not released yet.


Features
--------

* Jelix 1.8 requires PHP 7.4 and above.
* Unit tests: jUnitTestCase and jUnitTestCaseDb are deprecated. Use  
 `\Jelix\UnitTests\UnitTestCase` and `\Jelix\UnitTests\UnitTestCase` instead.
* jForms:
  * new method `BuilderBase::outputAllControlsValues()`
  * new method `BuilderBase::outputControlRawValue()`
  * new method `WidgetInterface::outputControlRawValue()`
* jAcl2Db admin UI: 
  * the user interface has been reworked to be more usable
  * possibility to hide some rights (`hiddenRights` in 
    the `acl2` configuration section)
  * It is not possible anymore to set some rights on the anonymous group (acl, or related to users)
  * rights are now dependent of the `view` right of the same branch.
* Configurator:
  * The configurator is now able to declare automatically modules urls, and to remove
    all Urls of a module when it is uninstalled.
  * Module configurators can indicate a list of url to declare into the urls mapping 
  * more methods on XmlMapModifier to remove urls
  * new method `findProfile()` on helpers
* Installer:
  * The PreInstallHelpers class has now the database API to allows to check the
    the content of the database before allowing the installation
  * new method `findProfile()` on helpers


Internal changes
----------------

* Upgrade Symfony Console to 5.2.1
* Upgrade PHPUnit to 8.5 for our tests
* Upgrade PHPMailer to 6.2.0
* Tests with CasperJs have been removed
* Some Javascript scripts like `jforms_jquery.js` are now generated with WebPack. See the `assets` directory.

* jForms: move code from template plugin to a new class TemplateController.
  It allows to control the display of the form in a single class
  instead of into several template plugins, and so, to use it into any or 
  outside a template system.

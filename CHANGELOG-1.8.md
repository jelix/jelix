Changes into Jelix 1.8.0
========================

Not released yet.


Features
--------

* Jelix 1.8 requires PHP 7.2.5 and above.
* Unit tests: jUnitTestCase and jUnitTestCaseDb are deprecated. Use  
 `\Jelix\UnitTests\UnitTestCase` and `\Jelix\UnitTests\UnitTestCase` instead.
* jForms:
  * new method BuilderBase::outputAllControlsValues()
  * new method BuilderBase::outputControlRawValue()
  * new method WidgetInterface::outputControlRawValue()
* jAcl2Db admin UI: the user interface has been reworked to be more usable
* jAcl2Db admin UI: possibility to hide some rights (`hiddenRights` in the `jacl2ui` configuration section)
* jAcl2Db: rights are now dependent of the `view` right of the same branch.
* The configurator is now able to declare automatically modules urls, and to remove
  all Urls of a module when it is uninstalled.
* Module configurators can indicate a list of url to declare into the urls mapping 
* more methods on XmlMapModifier to remove urls

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

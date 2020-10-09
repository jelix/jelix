Changes into Jelix 1.8.0
========================

Not released yet.


Features
--------

* Jelix 1.8 requires PHP 7.2 and above.
* Unit tests: jUnitTestCase and jUnitTestCaseDb are deprecated. Use  
 `\Jelix\UnitTests\UnitTestCase` and `\Jelix\UnitTests\UnitTestCase` instead.
* jForms:
  * new method BuilderBase::outputAllControlsValues()
  * new method BuilderBase::outputControlRawValue()
  * new method WidgetInterface::outputControlRawValue()


Internal changes
----------------

* Upgrade PHPUnit to 8.5 for our tests
* Tests with CasperJs have been removed

* jForms: move code from template plugin to a new class TemplateController.
  It allows to control the display of the form in a single class
  instead of into several template plugins, and so, to use it into any or 
  outside a template system.

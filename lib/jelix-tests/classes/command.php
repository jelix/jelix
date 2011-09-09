<?php
/**
 * PHPUnit command line execution controller.
 * 
 * This suppose that PHPUnit is installed and declared in include path
 * 
 * @package jelix-tests
 * @author Laurent Jouanneau
 * @contributor  Christophe Thiriot (for some code imported from his jphpunit module)
 */

require_once(dirname(__FILE__).'/JelixTestSuite.class.php');
require_once(dirname(__FILE__).'/junittestcase.class.php');
require_once(dirname(__FILE__).'/junittestcasedb.class.php');
require_once(JELIX_LIB_CORE_PATH.'jConfigCompiler.class.php');

PHP_CodeCoverage_Filter::getInstance()->addFileToBlacklist(__FILE__, 'PHPUNIT');

class jelix_TextUI_Command extends PHPUnit_TextUI_Command {

    protected $entryPoint = 'index';

    protected $epInfo = null;

    protected $testType = '';

    function __construct() {
        $this->longOptions['all-modules'] = null;
        $this->longOptions['module'] = null;
        $this->longOptions['entrypoint='] = null;
        $this->longOptions['testtype='] = null;
    }

    protected function handleCustomTestSuite() {

        $modulesTests = -1;

        /*
        $this->options[0] is an array of all options '--xxx'.
          each values is an array(0=>'optionname', 1=>'value if given')
        $this->options[1] is a list of parameters given after options
          it can be array(0=>'test name', 1=>'filename')
        */

        foreach ($this->options[0] as $option) {
            switch ($option[0]) {
                case '--entrypoint':
                    $this->entryPoint = $option[1];
                    break;
                case '--all-modules':
                    $modulesTests = 0;
                    break;
                case '--module':
                    $modulesTests = 1;
                    // test is the module name
                    // testFile is the test file inside the module
                    break;
                case '--testtype':
                    $this->testType = $option[1];
                    break;
            }
        }

        if (isset($this->options[1][1]) && $modulesTests != 0) { // a specifique test file
            $this->arguments['testFile'] = $this->options[1][1];
        } else {
            $this->arguments['testFile'] = '';
        }

        $appInstaller = new jInstallerApplication();
        $this->epInfo = $appInstaller->getEntryPointInfo($this->entryPoint);

        if ($modulesTests == 0) {
            // we add all modules in the test list
            $suite = $this->getAllModulesTestSuites();
            if (count($suite)) {
                $this->arguments['test'] = $suite;
                unset ($this->arguments['testFile']);
            }
            else {
                $this->showMessage("Error: no tests in modules\n");
                exit(PHPUnit_TextUI_TestRunner::FAILURE_EXIT);
            }
        }
        else if ($modulesTests == 1) {
            $suite = $this->getModuleTestSuite($this->options[1][0]);
            if (count($suite)) {
                $this->arguments['test'] = $suite;
                if (isset($this->options[1][1])) { // a specifique test file
                    $this->arguments['testFile'] = $this->options[1][1];
                } else {
                    $this->arguments['testFile'] = '';
                }
            }
            else {
                $this->showMessage("Error: no tests in the module\n");
                exit(PHPUnit_TextUI_TestRunner::FAILURE_EXIT);
            }
        }
        // it will initialize global variables $gJCoord $gJConfig. it could be needed by tests
        $coord = new jCoordinator($this->epInfo->configFile, false);
    }

    protected function getAllModulesTestSuites() {


        $moduleList = $this->epInfo->getModulesList();

        $topsuite = new PHPUnit_Framework_TestSuite();

        $type = ($this->testType?'.'.$this->testType: '').'.pu.php';

        foreach ($moduleList as $module=>$path) {
            $suite = new JelixTestSuite($module);
            $testCollector = new PHPUnit_Runner_IncludePathTestCollector(
                array($path),
                $type
            );

            $suite->addTestFiles($testCollector->collectTests());
            if (count($suite->tests()) > 0)
                $topsuite->addTestSuite($suite);
        }
        return $topsuite;
    }


    protected function getModuleTestSuite($module) {

        $moduleList = $this->epInfo->getModulesList();

        $topsuite = new PHPUnit_Framework_TestSuite();

        if (isset($moduleList[$module])) {
            $type = ($this->testType?'.'.$this->testType: '').'.pu.php';
            $suite = new JelixTestSuite($module);
            $testCollector = new PHPUnit_Runner_IncludePathTestCollector(
                array($moduleList[$module]),
                $type
            );

            $suite->addTestFiles($testCollector->collectTests());
            if (count($suite->tests()) > 0)
                $topsuite->addTestSuite($suite);
        }
        return $topsuite;
    }

    protected function showHelp() {
        parent::showHelp();
        echo "

Specific options for Jelix:

       phpunit [switches] --all-modules
       phpunit [switches] --module <modulename> [testfile.pu.php]

  --all-modules           Run tests of all installed modules.
  --module <module>       Run tests of a specific module. An optional filename can be indicated
                          to run a specific test of this module.

  --entrypoint <ep>       Run tests in the context (same configuration) of the given entry point. By default: 'index'
  --testtype <type>       Run only tests of the given type, ie. tests that have a filename suffix like '.<type>.pu.php'

        ";
    }
}
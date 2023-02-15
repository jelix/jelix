<?php
/**
 * PHPUnit command line execution controller.
 * 
 * This suppose that PHPUnit is installed and declared in include path
 *
 * @package     jelix
 * @subpackage  jelix-tests
 * @author Laurent Jouanneau
 * @contributor  Christophe Thiriot (for some code imported from his jphpunit module)
 */

require_once(__DIR__.'/JelixTestSuite.class.php');
require_once(__DIR__.'/junittestcase.class.php');
require_once(__DIR__.'/junittestcasedb.class.php');

use PHPUnit\TextUI\TestRunner;

/**
 * Command for PHPUnit, that load tests classes from the
 * `tests` directory of each modules.
 *
 * It is deprecated, prefer to put your unit tests classes into
 * a single directory for PHPUnit, outside modules.
 *
 * @deprecated
 */
class jelix_TextUI_Command extends PHPUnit\TextUI\Command {

    protected $entryPoint = 'index';

    protected $testType = '';


    function __construct() {
        $this->longOptions['all-modules'] = null;
        $this->longOptions['module'] = null;
        $this->longOptions['entrypoint='] = null;
        $this->longOptions['testtype='] = null;
    }


    /**
     * @param boolean $exit
     */
    public static function main(bool $exit = true): int
    {
        $command = new jelix_TextUI_Command;
        return $command->run($_SERVER['argv'], $exit);
    }

    protected function showMessage($message)
    {
        echo $message;
    }

    protected function createRunner(): TestRunner
    {
        $filter = new \SebastianBergmann\CodeCoverage\Filter();

        /*$filter->addFileToBlacklist(__FILE__, 'PHPUNIT');

        $filter->addFileToBlacklist(__DIR__.'/JelixTestSuite.class.php', 'PHPUNIT');
        $filter->addFileToBlacklist(__DIR__.'/junittestcase.class.php', 'PHPUNIT');
        $filter->addFileToBlacklist(__DIR__.'/junittestcasedb.class.php', 'PHPUNIT');
        $filter->addFileToBlacklist(dirname(__DIR__).'/phpunit.inc.php', 'PHPUNIT');
*/
        return new TestRunner($this->arguments['loader'], $filter);
    }

    protected function handleCustomTestSuite(): void {

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

        if (isset($this->options[1][1]) && $modulesTests != 0) { // a specific test file
            $this->arguments['testFile'] = $this->options[1][1];
        } else {
            $this->arguments['testFile'] = '';
        }

        $globalSetup = new \Jelix\Installer\GlobalSetup();
        $epInfo = $globalSetup->getMainEntryPoint();

        // let's load configuration now, and coordinator. it could be needed by tests
        // (during load of their php files or during execution)
        jApp::setConfig(jConfigCompiler::readAndCache($epInfo->getConfigFileName(), null, $this->entryPoint));
        jApp::setCoord(new jCoordinator('', false));

        if ($modulesTests == 0) {
            // we add all modules in the test list
            $suite = $this->getAllModulesTestSuites($globalSetup);
            if (count($suite)) {
                $this->arguments['test'] = $suite;
                unset ($this->arguments['testFile']);
            }
            else {
                $this->showMessage("Error: no tests in modules\n");
                exit(TestRunner::FAILURE_EXIT);
            }
        }
        else if ($modulesTests == 1) {
            if (isset($this->options[1][1])) { // a specific test file
                $suite = $this->getModuleTestSuite($globalSetup, $this->options[1][0], $this->options[1][1]);
            } else {
                $suite = $this->getModuleTestSuite($globalSetup, $this->options[1][0]);
            }
            if (count($suite)) {
                $this->arguments['test'] = $suite;
            }
            else {
                $this->showMessage("Error: no tests in the module\n");
                exit(TestRunner::FAILURE_EXIT);
            }
        }
    }

    protected function getAllModulesTestSuites(\Jelix\Installer\GlobalSetup $globalSetup)
    {
        $moduleList = $globalSetup->getModuleComponentsList();

        $topsuite = new PHPUnit\Framework\TestSuite();

        $type = ($this->testType?'.'.$this->testType: '').'.pu.php';

        foreach ($moduleList as $module=>$component) {
            $suite = new JelixTestSuite($module);

            $fileIteratorFacade = new SebastianBergmann\FileIterator\Facade();
            $files = $fileIteratorFacade->getFilesAsArray(
                $component->getPath(),
                $type
            );
            $suite->addTestFiles($files);

            if (count($suite->tests()) > 0) {
                $topsuite->addTestSuite($suite);
            }
        }
        return $topsuite;
    }


    protected function getModuleTestSuite(\Jelix\Installer\GlobalSetup $globalSetup, $module, $testFile = '')
    {

        $component = $globalSetup->getModuleComponent($module);

        $topsuite = new PHPUnit\Framework\TestSuite();

        if ($component) {
            $type = ($this->testType?'.'.$this->testType: '').'.pu.php';
            $suite = new JelixTestSuite($module);
            if ($testFile) {
                $suite->addTestFile($component->getPath().'tests/'.$testFile);
            }
            else {
                $fileIteratorFacade = new SebastianBergmann\FileIterator\Facade();
                $files = $fileIteratorFacade->getFilesAsArray(
                    $component->getPath(),
                  $type
                );
                $suite->addTestFiles($files);
            }

            if (count($suite->tests()) > 0)
                $topsuite->addTestSuite($suite);
        }
        return $topsuite;
    }

    protected function showHelp(): void
    {
        parent::showHelp();
        echo "

Specific options for Jelix (deprecated, will be removed into futur versions):

       phpunit [switches] --all-modules
       phpunit [switches] --module <modulename> [<testfile.pu.php>]

  --all-modules           Run tests of all installed modules.
  --module <module>       Run tests of a specific module. An optional filename can be indicated
                          to run a specific test of this module.

  --entrypoint <ep>       Run tests in the context (same configuration) of the given entry point. By default: 'index'
  --testtype <type>       Run only tests of the given type, ie. tests that have a filename suffix like '.<type>.pu.php'

";
    }
}

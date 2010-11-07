<?php
/**
* @package     jelix
* @subpackage  junittests
* @author      Laurent Jouanneau
* @contributor Christophe Thiriot
* @copyright   2008 Laurent Jouanneau
* @copyright   2008 Christophe Thiriot
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

// used by a usort function
// PHP5.1.2 generates a strict message if I put this function into defaultCtrl class, although it has the static keyword
function JUTcompareTestName($a,$b){ 
    return strcmp($a[0], $b[0]);
}

class defaultCtrl extends jControllerCmdLine {
    protected $allowed_options = array(
            'index' => array(),
            'help' => array(),
            'module' => array(),
            'single' => array(),
    );

    protected $allowed_parameters = array(
            'index' => array(),
            'help' => array(),
            'module' => array('mod'=>true),
            'single' => array('mod'=>true,'test'=>true),
    );



    protected $testsList = array();

    protected function _prepareResponse(){
        $rep = $this->getResponse();

        $rep->addContent('
Unit Tests        php version: '.phpversion().'   Jelix version: '.JELIX_VERSION.'
===========================================================================
');

        foreach($GLOBALS['gJConfig']->_modulesPathList as $module=>$path){
            if(file_exists($path.'tests/')){
                $dir = new DirectoryIterator($path.'tests/');
                foreach ($dir as $dirContent) {
                    if ($dirContent->isFile() && preg_match("/^(.+)\\.(html_)?cli\\.php$/", $dirContent->getFileName(), $m) ) {
                        $lib = str_replace('.',': ',$m[1]);
                        $lib = str_replace('_',' ',$lib);

                        $this->testsList[$module][] = array($dirContent->getFileName(), $m[1], $lib) ;
                    }
                }
                if(isset($this->testsList[$module])){
                    usort($this->testsList[$module], "JUTcompareTestName");
                }
            }
        }

        return $rep;
    }

    protected function _finishResponse($rep){
        return $rep;
    }

    /**
    *
    */
    function help() {
        $rep = $this->_prepareResponse();
        if(count($this->testsList)){
            foreach($this->testsList as $module=>$tests) {
                $rep->addContent('module "'.$module."\":\n", true);
                foreach($tests as $test){
                    $rep->addContent("\t".$test[2]."\t(".$test[1].")\n", true);
                }
            }
        }
        else {
            $rep->addContent('No availabled tests');
        }
        return $this->_finishResponse($rep);
    }

    function index() {

        $rep = $this->_prepareResponse();

        $reporter = jClasses::create("junittests~jtextrespreporter");
        jClasses::inc('junittests~junittestcase');
        jClasses::inc('junittests~junittestcasedb');
        $reporter->setResponse($rep);

        foreach($this->testsList as $module=>$tests){
            jContext::push($module);
            $group = new GroupTest('Tests on module '.$module);
            foreach($this->testsList[$module] as $test){
                $group->addTestFile($GLOBALS['gJConfig']->_modulesPathList[$module].'tests/'.$test[0]);
            }
            $result = $group->run($reporter);
            if (!$result) $rep->setExitCode(jResponseCmdline::EXIT_CODE_ERROR);
            jContext::pop();
        }
        return $this->_finishResponse($rep);
    }


    function module() {

        $rep = $this->_prepareResponse();

        $module = $this->param('mod');
        if(isset($this->testsList[$module])){
            $reporter = jClasses::create("junittests~jtextrespreporter");
            jClasses::inc('junittests~junittestcase');
            jClasses::inc('junittests~junittestcasedb');
            $reporter->setResponse($rep);

            $group = new GroupTest('All tests in "'.$module. '" module');
            foreach($this->testsList[$module] as $test){
                $group->addTestFile($GLOBALS['gJConfig']->_modulesPathList[$module].'tests/'.$test[0]);
            }
            jContext::push($module);
            $result = $group->run($reporter);
            if (!$result) $rep->setExitCode(jResponseCmdline::EXIT_CODE_ERROR);
            jContext::pop();
        }
        return $this->_finishResponse($rep);
    }


    function single() {
        $rep = $this->_prepareResponse();

        $module = $this->param('mod');
        $testname = $this->param('test');

        if(isset($this->testsList[$module])){
            $reporter = jClasses::create("junittests~jtextrespreporter");
            jClasses::inc('junittests~junittestcase');
            jClasses::inc('junittests~junittestcasedb');
            $reporter->setResponse($rep);

            foreach($this->testsList[$module] as $test){
                if($test[1] == $testname){
                    $group = new GroupTest('"'.$module. '" module , '.$test[2]);
                    $group->addTestFile($GLOBALS['gJConfig']->_modulesPathList[$module].'tests/'.$test[0]);
                    jContext::push($module);
                    $result = $group->run($reporter);
                    if (!$result) $rep->setExitCode(jResponseCmdline::EXIT_CODE_ERROR);
                    jContext::pop();
                    break;
                }
            }
        }else
            $rep->addContent("\n" . 'no tests for "'.$module.'" module.' . "\n");
        return $this->_finishResponse($rep);
    }
}
?>

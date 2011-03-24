<?php
/**
* @package     jelix
* @subpackage  junittests
* @author      Laurent Jouanneau
* @contributor Christophe Thiriot, Rahal Aboulfeth
* @copyright   2008 Laurent Jouanneau
* @copyright   2008 Christophe Thiriot, 2011 Rahal Aboulfeth
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class defaultCtrl extends jControllerCmdLine {
    protected $allowed_options = array(
            'index' => array('--categ' => true),
            'help' => array('--categ' => true),
            'module' => array('--categ' => true),
            'single' => array('--categ' => true ),
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
        
        $runnerPreparer = jClasses::create('junittests~jrunnerpreparer');
        $this->testsList = $runnerPreparer->getTestsList('cli');
        $this->category = $this->option('--categ' , false );
        $this->testsList = $runnerPreparer->filterTestsByCategory($this->category , $this->testsList );
        
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
        $category = $this->category ? ' '.$this->category : '';
        if(count($this->testsList)){
            foreach($this->testsList as $module=>$tests) {
                $rep->addContent('module "'.$module."\":\n", true);
                foreach($tests as $test){
                    $type = $test[3] ? "  ".$test[3] : "" ;
                    $rep->addContent("\t".$test[2]."\t(".$test[1].$type.")\n", true);
                }
            }
        }
        else {
            $rep->addContent('No available'.$category.' tests');
        }
        return $this->_finishResponse($rep);
    }

    function index() {

        $rep = $this->_prepareResponse();

        $reporter = jClasses::create("junittests~jtextrespreporter");
        jClasses::inc('junittests~junittestcase');
        jClasses::inc('junittests~junittestcasedb');
        $reporter->setResponse($rep);
        $category = $this->category ? ' '.$this->category : '';
        if (count($this->testsList)){
            foreach($this->testsList as $module=>$tests){
                jContext::push($module);
                $group = new TestSuite('Tests'.$category.' on module '.$module);
                foreach($this->testsList[$module] as $test){
                    $group->addFile($GLOBALS['gJConfig']->_modulesPathList[$module].'tests/'.$test[0]);
                }
                $result = $group->run($reporter);
                if (!$result) $rep->setExitCode(jResponseCmdline::EXIT_CODE_ERROR);
                jContext::pop();
            }
        } else {
            $rep->addContent('No available'.$category.' tests');
        }
        return $this->_finishResponse($rep);
    }


    function module() {

        $rep = $this->_prepareResponse();

        $module = $this->param('mod');
        $category = $this->category ? ' '.$this->category : '';
        if(isset($this->testsList[$module])){
            $reporter = jClasses::create("junittests~jtextrespreporter");
            jClasses::inc('junittests~junittestcase');
            jClasses::inc('junittests~junittestcasedb');
            $reporter->setResponse($rep);

            $group = new TestSuite('All'.$category.' tests in "'.$module. '" module');
            foreach($this->testsList[$module] as $test){
                $group->addFile($GLOBALS['gJConfig']->_modulesPathList[$module].'tests/'.$test[0]);
            }
            jContext::push($module);
            $result = $group->run($reporter);
            if (!$result) $rep->setExitCode(jResponseCmdline::EXIT_CODE_ERROR);
            jContext::pop();
        } else {
            $rep->addContent('No available'.$category.' tests for module '.$module);
        }
        return $this->_finishResponse($rep);
    }


    function single() {
        $rep = $this->_prepareResponse();

        $module = $this->param('mod');
        $testname = $this->param('test');
        $category = $this->category ? ' '.$this->category : '';
        if(isset($this->testsList[$module])){
            $reporter = jClasses::create("junittests~jtextrespreporter");
            jClasses::inc('junittests~junittestcase');
            jClasses::inc('junittests~junittestcasedb');
            $reporter->setResponse($rep);

            foreach($this->testsList[$module] as $test){
                if($test[1] == $testname){
                    $group = new TestSuite('"'.$module. '" module , '.$test[2]);
                    $group->addFile($GLOBALS['gJConfig']->_modulesPathList[$module].'tests/'.$test[0]);
                    jContext::push($module);
                    $result = $group->run($reporter);
                    if (!$result) $rep->setExitCode(jResponseCmdline::EXIT_CODE_ERROR);
                    jContext::pop();
                    break;
                }
            }
        }else
            $rep->addContent("\n" . 'no'.$category.' tests for "'.$module.'" module.' . "\n");
        return $this->_finishResponse($rep);
    }
}
?>

<?php
/**
* @package     jelix
* @subpackage  junittests
* @author      Laurent Jouanneau
* @contributor Rahal Aboulfeth
* @copyright   2007 Laurent Jouanneau, 2007 Rahal Aboulfeth
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

// used by a usort function
// PHP5.1.2 generates a strict message if I put this function into defaultCtrl class, although it has the static keyword
function JUTcompareTestName($a,$b){ 
    return strcmp($a[0], $b[0]);
}


class defaultCtrl extends jController {

    /**
    *
    */
    function index() {
        if(!isset($GLOBALS['gJConfig']->enableTests) || !$GLOBALS['gJConfig']->enableTests){
            // security
            $rep = $this->getResponse('html', true);
            $rep->title = 'Error';
            $rep->setHttpStatus('404', 'Not found');
            $rep->addContent('<p>404 Not Found</p>');
            return $rep;
        }

        $rep = $this->_prepareResponse();

        return $this->_finishResponse($rep);
    }

    function all() {
        if(!isset($GLOBALS['gJConfig']->enableTests) || !$GLOBALS['gJConfig']->enableTests){
            // security
            $rep = $this->getResponse('html', true);
            $rep->title = 'Error';
            $rep->setHttpStatus('404', 'Not found');
            $rep->addContent('<p>404 Not Found</p>');
            return $rep;
        }

        $rep = $this->_prepareResponse();
        jClasses::inc("junittests~jhtmlrespreporter");
        jClasses::inc('junittests~junittestcase');
        jClasses::inc('junittests~junittestcasedb');

        foreach($this->testsList as $module=>$tests){
            $reporter = new jhtmlrespreporter();
            $reporter->setResponse($rep);

            jContext::push($module);
            $group = new GroupTest('Tests on module '.$module);
            foreach($this->testsList[$module] as $test){
                $group->addTestFile($GLOBALS['gJConfig']->_modulesPathList[$module].'tests/'.$test[0]);
            }
            $group->run($reporter);
            jContext::pop();
        }
        return $this->_finishResponse($rep);
    }


    function module() {
        if(!isset($GLOBALS['gJConfig']->enableTests) || !$GLOBALS['gJConfig']->enableTests){
            // security
            $rep = $this->getResponse('html', true);
            $rep->title = 'Error';
            $rep->setHttpStatus('404', 'Not found');
            $rep->addContent('<p>404 Not Found</p>');
            return $rep;
        }
        $rep = $this->_prepareResponse();

        $module = $this->param('mod');
        if(isset($this->testsList[$module])){
            $reporter = jClasses::create("junittests~jhtmlrespreporter");
            jClasses::inc('junittests~junittestcase');
            jClasses::inc('junittests~junittestcasedb');
            $reporter->setResponse($rep);

            $group = new GroupTest('All tests in "'.$module. '" module');
            foreach($this->testsList[$module] as $test){
                $group->addTestFile($GLOBALS['gJConfig']->_modulesPathList[$module].'tests/'.$test[0]);
            }
            jContext::push($module);
            $group->run($reporter);
            jContext::pop();
        }
        return $this->_finishResponse($rep);
    }


    function single() {
        if(!isset($GLOBALS['gJConfig']->enableTests) || !$GLOBALS['gJConfig']->enableTests){
            // security
            $rep = $this->getResponse('html', true);
            $rep->title = 'Error';
            $rep->setHttpStatus('404', 'Not found');
            $rep->addContent('<p>404 Not Found</p>');
            return $rep;
        }
        $rep = $this->_prepareResponse();

        $module = $this->param('mod');
        $testname = $this->param('test');

        if(isset($this->testsList[$module])){
            $reporter = jClasses::create("junittests~jhtmlrespreporter");
            jClasses::inc('junittests~junittestcase');
            jClasses::inc('junittests~junittestcasedb');
            $reporter->setResponse($rep);

            foreach($this->testsList[$module] as $test){
                if($test[1] == $testname){
                    $group = new GroupTest('"'.$module. '" module , '.$test[2]);
                    $group->addTestFile($GLOBALS['gJConfig']->_modulesPathList[$module].'tests/'.$test[0]);
                    jContext::push($module);
                    $group->run($reporter);
                    jContext::pop();
                    break;
                }
            }
        }else
            $rep->body->assign ('MAIN','<p>no tests for "'.$module.'" module.</p>');
        return $this->_finishResponse($rep);
    }

    protected $testsList = array();

    protected function _prepareResponse(){
        $rep = $this->getResponse('html', true);
        $rep->bodyTpl = 'junittests~main';

        $rep->body->assign('page_title', 'Unit Tests');
        $rep->body->assign('versionphp',phpversion());
        $rep->body->assign('versionjelix',JELIX_VERSION);
        $rep->body->assign('basepath',$GLOBALS['gJConfig']->urlengine['basePath']);
        $rep->body->assign('isurlsig', $GLOBALS['gJConfig']->urlengine['engine'] == 'significant');

        $rep->addCSSLink($GLOBALS['gJConfig']->urlengine['basePath'].'tests/design.css');

        foreach($GLOBALS['gJConfig']->_modulesPathList as $module=>$path){
            if(file_exists($path.'tests/')){
                $dir = new DirectoryIterator($path.'tests/');
                foreach ($dir as $dirContent) {
                    if ($dirContent->isFile() && preg_match("/^(.+)\\.html(_cli)?\\.php$/", $dirContent->getFileName(), $m) ) {
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

        $rep->body->assign('modules', $this->testsList);

        return $rep;
    }

    protected function _finishResponse($rep){

        $rep->title .= ($rep->title !=''?' - ':'').' Unit Tests';
        $rep->body->assignIfNone('MAIN','<p>Welcome to unit tests</p>');
        return $rep;
    }
}
?>

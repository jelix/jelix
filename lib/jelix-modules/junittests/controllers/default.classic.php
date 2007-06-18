<?php
/**
* @package
* @subpackage 
* @author
* @copyright
* @link
* @licence  http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
*/


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


        $reporter = jClasses::create("jhtmlrespreporter");
        jClasses::inc('junittestcase');
        jClasses::inc('junittestcasedb');
        $reporter->setResponse($rep);

        $group = new GroupTest('Tests on all modules');

        foreach($this->testsList as $module=>$tests){

            foreach($this->testsList[$module] as $test){
                $group->addTestFile($GLOBALS['gJConfig']->_modulesPathList[$module].'tests/'.$test[0]);
            }
        }
        $group->run($reporter);

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

        $module = $this->param('module');
        if(isset($this->testsList[$module])){
            $reporter = jClasses::create("jhtmlrespreporter");
            jClasses::inc('junittestcase');
            jClasses::inc('junittestcasedb');
            $reporter->setResponse($rep);

            $group = new GroupTest('Tests on "'.$module. '" module');
            foreach($this->testsList[$module] as $test){
                $group->addTestFile($GLOBALS['gJConfig']->_modulesPathList[$module].'tests/'.$test[0]);
            }
            $group->run($reporter);

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

        $module = $this->param('module');
        $testname = $this->param('test');

        if(isset($this->testsList[$module])){
            $reporter = jClasses::create("jhtmlrespreporter");
            jClasses::inc('junittestcase');
            jClasses::inc('junittestcasedb');
            $reporter->setResponse($rep);

            
            foreach($this->testsList[$module] as $test){
                if($test[1] == $testname){
                    $group = new GroupTest('"'.$module. '" module : "'.$testname.'" Tests');
                    $group->addTestFile($GLOBALS['gJConfig']->_modulesPathList[$module].'tests/'.$test[0]);
                    $group->run($reporter);
                    break;
                }
            }
        }
        return $this->_finishResponse($rep);
    }



    protected $testsList = array();


    protected function _prepareResponse(){
        $rep = $this->getResponse('html', true);
        $rep->bodyTpl = 'main';

        $rep->body->assign('page_title', 'Unit Tests');
        $rep->body->assign('versionphp',phpversion());
        $rep->body->assign('versionjelix',JELIX_VERSION);
        $rep->body->assign('basepath',$GLOBALS['gJConfig']->urlengine['basePath']);
        $rep->body->assign('isurlsig', $GLOBALS['gJConfig']->urlengine['engine'] == 'significant');

        $rep->addCSSLink($GLOBALS['gJConfig']->urlengine['basePath'].'design/screen.css');

        foreach($GLOBALS['gJConfig']->_modulesPathList as $module=>$path){
            if(file_exists($path.'tests/')){
                $dir = new DirectoryIterator($path.'tests/');
                foreach ($dir as $dirContent) {
                    if ($dirContent->isFile() && preg_match("/^(.+)\\.html(_cli)?\\.php$/", $dirContent->getFileName(), $m) ) {
                        $this->testsList[$module][] = array($dirContent->getFileName(), $m[1]) ;
                    }
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

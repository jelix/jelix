<?php
/**
* @package     testapp
* @subpackage  testapp module
* @author      Laurent Jouanneau
* @contributor Julien Issler
* @copyright   2005-2023 Laurent Jouanneau
* @copyright   2009 Julien Issler
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jstestsCtrl extends jController {

    protected function getTestResponse()
    {
        jApp::config()->jResponseHtml['plugins'] = 'debugbar';
        $rep = $this->getResponse('html', true);
        $rep->setXhtmlOutput(false);
        $rep->addAssets('qunit');
        return $rep;
    }

    function jforms() {
        $rep = $this->getTestResponse();
        $rep->title = 'Unit tests on jforms';
        $rep->bodyTpl = 'jstest_jforms';
        $rep->addAssets('jforms_html');
        $rep->addAssets('jforms_htmleditor_default');
        $rep->addAssets('jforms_datepicker_default');
        $rep->addJsLink(jApp::urlBasePath().'tests/jforms/testjforms.js');

        return $rep;
    }

    function jsonrpc() {
        $rep = $this->getTestResponse();
        $rep->title = 'Unit tests for jsonrpc';
        $rep->bodyTpl = 'jstest_jsonrpc2';
        $rep->addJsLink(jApp::urlBasePath().'tests/test_jsonrpc2.js');
        return $rep;
    }

    function testinclude() {
        $rep = $this->getTestResponse();
        $rep->title = 'Unit tests for jquery include plugin';
        $rep->bodyTpl = 'jstest_include';
        $rep->addJsLink(jApp::urlJelixWWWPath().'jquery/include/jquery.include.js');
        $rep->addJsLink(jApp::urlBasePath().'tests/test_include.js');
        return $rep;
    }

    function testincludejsinc3() {
        $rep = $this->getResponse('text', true);
        $rep->addHttpHeader('Content-Type','application/javascript',true);
        $rep->content= '$("#includeresult").text($("#includeresult").text()+"INC3");';
        sleep(1);
        return $rep;
    }
}

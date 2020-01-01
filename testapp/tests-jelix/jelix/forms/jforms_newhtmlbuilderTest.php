<?php
/**
* @package     testapp
* @subpackage  unittest module
* @author      Laurent Jouanneau
* @copyright   2012 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(__DIR__.'/jforms_htmlbuilderTest.php');
require_once(JELIX_LIB_PATH.'plugins/formbuilder/html/html.formbuilder.php');
require_once(JELIX_LIB_PATH.'plugins/formwidget/html/html.formwidget.php');


class testHtmlRootWidget extends htmlFormWidget {

    function testGetJs() {
        $js = $this->js;
        $this->js = '';
        return $js;
    }

    function testGetFinalJs() {
        $js = $this->finalJs;
        $this->finalJs = '';
        return $js;
    }

}

class testHtmlFormsBuilder extends htmlFormBuilder {

    public function __construct($form){
        $this->_form = $form;
        $this->rootWidget = new testHtmlRootWidget();
    }

    function getJsContent() {
        return $this->rootWidget->testGetJs();
    }
    function clearJs() { $this->rootWidget->testGetJs(); }
    function getLastJsContent() { return $this->rootWidget->testGetFinalJs();}
}


class jforms_NewHTMLBuilderTest extends jforms_HTMLBuilderTest {

    function setUp() : void {
        self::initClassicRequest(TESTAPP_URL.'index.php');
        jApp::pushCurrentModule('jelix_tests');
        if (!self::$builder) {
            self::$container = new jFormsDataContainer('formtest','0');
            self::$form = new testHMLForm('formtest', self::$container, true );
            self::$form->securityLevel = 0;
            self::$builder = new testHtmlFormsBuilder(self::$form);
            $js0 = 'jFormsJQ.selectFillUrl=\''.jApp::urlBasePath().'index.php/jelix/forms/getdata\';
jFormsJQ.config = {locale:\''.jApp::config()->locale.'\',basePath:\''.jApp::urlBasePath().'\',jqueryPath:\''.jApp::config()->urlengine['jqueryPath'].'\',jqueryFile:\''.$this->getJQuery().'\',jelixWWWPath:\''.jApp::config()->urlengine['jelixWWWPath'].'\'};
jFormsJQ.tForm = new jFormsJQForm(\'jforms_formtest\',\'formtest\',\'0\');
jFormsJQ.tForm.setErrorDecorator(new jFormsJQErrorDecoratorHtml());
jFormsJQ.declareForm(jFormsJQ.tForm);
';
            self::$jsHeader0 = $js0;
            self::$htmlJsHeader0 = '';
            $js = 'jFormsJQ.selectFillUrl=\''.jApp::urlBasePath().'index.php/jelix/forms/getdata\';
jFormsJQ.config = {locale:\''.jApp::config()->locale.'\',basePath:\''.jApp::urlBasePath().'\',jqueryPath:\''.jApp::config()->urlengine['jqueryPath'].'\',jqueryFile:\''.$this->getJQuery().'\',jelixWWWPath:\''.jApp::config()->urlengine['jelixWWWPath'].'\'};
jFormsJQ.tForm = new jFormsJQForm(\'jforms_formtest1\',\'formtest\',\'0\');
jFormsJQ.tForm.setErrorDecorator(new jFormsJQErrorDecoratorHtml());
jFormsJQ.declareForm(jFormsJQ.tForm);
';
            self::$jsHeader = $js;
            self::$htmlJsHeader = '';
            self::$htmlJsFooter = '<script type="text/javascript" src="/index.php/jelix/forms/js/formtest/0.js"></script></form>';
            self::$jsFooter = '';
        }
    }

}

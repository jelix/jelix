<?php
/**
* @package     testapp
* @subpackage  unittest module
* @author      Laurent Jouanneau
* @copyright   2012 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(__DIR__.'/jforms.htmlbuilder.html_cli.php');
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


class UTjformsNewHTMLBuilder extends UTjformsHTMLBuilder {

    protected $form;
    protected $container;
    protected $builder;

    function setUpRun() {
        $this->container = new jFormsDataContainer('formtest','0');
        $this->form = new testHMLForm('formtest', $this->container, true );
        $this->form->securityLevel = 0;
        $this->builder = new testHtmlFormsBuilder($this->form);

    }
}

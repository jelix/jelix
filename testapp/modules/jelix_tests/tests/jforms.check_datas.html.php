<?php
/**
* @package     testapp
* @subpackage  unittest module
* @author      Jouanneau Laurent
* @contributor
* @copyright   2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(JELIX_LIB_FORMS_PATH.'jFormsBase.class.php');
require_once(JELIX_LIB_FORMS_PATH.'jFormsControl.class.php');
require_once(JELIX_LIB_FORMS_PATH.'jFormsDatasource.class.php');
require_once(JELIX_LIB_UTILS_PATH.'jDatatype.class.php');
require_once(JELIX_LIB_FORMS_PATH.'jFormsDataContainer.class.php');

class testCDForm extends jFormsBase {
    function addCtrl($control){
        $this->addControl($control);
    }
}

class UTjformsCheckDatas extends jUnitTestCaseDb {
    protected $form;
    protected $container;
    function testStart() {
        $this->container = new jFormsDataContainer('','');
        $this->form = new testCDForm($this->container);
    }


    function testInput() {
        $ctrl = new jFormsControlInput('nom');
        $ctrl->datatype=new jDatatypeString();
        $ctrl->required = false;
        //$ctrl->value='';
        $this->form->addCtrl($ctrl);

        // tests with null value
        $this->assertTrue($this->form->check());
        $ctrl->required = true;
        $this->assertFalse($this->form->check());
        $this->form->setData('nom','');
        $this->assertFalse($this->form->check());
        $ctrl->required = false;
        $this->assertTrue($this->form->check());

        $ctrl->datatype->addFacet('length',3);
        $this->assertFalse($this->form->check());
        $this->form->setData('nom','a');
        $this->assertFalse($this->form->check());
        $this->form->setData('nom','aa');
        $this->assertFalse($this->form->check());
        $this->form->setData('nom','aaa');
        $this->assertTrue($this->form->check());
        $this->form->setData('nom','aaqq');
        $this->assertFalse($this->form->check());


        $ctrl = new jFormsControlInput('nom');
        $ctrl->datatype=new jDatatypeBoolean();
        $ctrl->required = false;
        $this->form->addCtrl($ctrl);

        $this->form->setData('nom',null);
        $this->assertFalse($this->form->check());
        $this->form->setData('nom','');
        $this->assertFalse($this->form->check());
        $this->form->setData('nom','on');
        $this->assertTrue($this->form->check());
        $this->form->setData('nom','off');
        $this->assertTrue($this->form->check());
    }

    function testCheckbox() {
        $ctrl = new jFormsControlCheckbox('nom');
        $ctrl->datatype=new jDatatypeBoolean();
        $this->form->addCtrl($ctrl);

        $this->form->setData('nom',null);
        $this->assertTrue($this->form->check());
        $this->form->setData('nom','');
        $this->assertTrue($this->form->check());
        $this->form->setData('nom','on');
        $this->assertTrue($this->form->check());
    }

    function testCheckboxes() {
        $ctrl = new jFormsControlCheckboxes('nom');
        $ctrl->datatype=new jDatatypeString();
        $this->form->addCtrl($ctrl);

        $this->form->setData('nom',null);
        $this->assertTrue($this->form->check());
        $this->form->setData('nom','');
        $this->assertTrue($this->form->check());
        $this->form->setData('nom','on');
        $this->assertTrue($this->form->check());
        $this->form->setData('nom',array('toto','titi'));
        $this->assertTrue($this->form->check());

        $ctrl->required = true;

        $this->form->setData('nom',null);
        $this->assertFalse($this->form->check());
        $this->form->setData('nom','');
        $this->assertFalse($this->form->check());
        $this->form->setData('nom',array());
        $this->assertFalse($this->form->check());
        $this->form->setData('nom','on');
        $this->assertTrue($this->form->check());
        $this->form->setData('nom',array('toto','titi'));
        $this->assertTrue($this->form->check());
    }

}

?>
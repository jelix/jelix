<?php
/**
* @package     testapp
* @subpackage  unittest module
* @author      Jouanneau Laurent
* @contributor Julien Issler, Dominique Papin
* @copyright   2007-2008 Jouanneau laurent
* @copyright   2008 Julien Issler, 2008 Dominique Papin
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(JELIX_LIB_PATH.'forms/jFormsBase.class.php');
require_once(JELIX_LIB_PATH.'forms/jFormsControl.class.php');
require_once(JELIX_LIB_PATH.'forms/jFormsDatasource.class.php');
require_once(JELIX_LIB_UTILS_PATH.'jDatatype.class.php');
require_once(JELIX_LIB_PATH.'forms/jFormsDataContainer.class.php');

class testCDForm extends jFormsBase {
    function addCtrl($control, $reset=true){
        if($reset){
            $this->controls = array();
            $this->container->data = array();
        }
        $this->addControl($control);
    }
}

class UTjformsCheckDatas extends jUnitTestCaseDb {
    protected $form;
    protected $container;
    function setUp() {
        $this->container = new jFormsDataContainer('','');
        $this->form = new testCDForm('foo',$this->container);
    }

    function testInput() {
        $ctrl = new jFormsControlInput('nom');
        $ctrl->required = false;
        //$ctrl->value='';
        $this->form->addCtrl($ctrl);

        // tests with null value
        $this->assertTrue($this->form->check());
        $ctrl->required = true;
        $this->assertFalse($this->form->check());
        $this->form->setData('nom','');
        $this->assertFalse($this->form->check());
        $this->form->setData('nom','    ');
        $this->assertFalse($this->form->check());
        $ctrl->required = false;
        $this->assertTrue($this->form->check());

        $ctrl->datatype->addFacet('length',3);
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
        $this->assertTrue($this->form->check());
        $this->form->setData('nom','');
        $this->assertTrue($this->form->check());
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
        $this->form->setData('nom','0');
        $this->assertTrue($this->form->check());
        $this->form->setData('nom','1');
        $this->assertTrue($this->form->check());

        $ctrl->required = true;
        $this->form->setData('nom',null);
        $this->assertFalse($this->form->check());
        $this->form->setData('nom','');
        $this->assertFalse($this->form->check());
        $this->form->setData('nom','on');
        $this->assertTrue($this->form->check());
        $this->form->setData('nom','0');
        $this->assertFalse($this->form->check());
        $this->form->setData('nom','1');
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
        $this->assertFalse($this->form->check());
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
        $this->assertFalse($this->form->check());
        $this->form->setData('nom',array('toto','titi'));
        $this->assertTrue($this->form->check());
    }

    function testSecret(){
        $ctrl = new jFormsControlSecret('nom');
        $ctrl->required = false;
        $this->form->addCtrl($ctrl);

        $this->form->setData('nom',null);
        $this->assertTrue($this->form->check());
        $ctrl->required = true;
        $this->assertFalse($this->form->check());
        $this->form->setData('nom','');
        $this->assertFalse($this->form->check());
        $ctrl->required = false;
        $this->assertTrue($this->form->check());

        $ctrl2 = new jFormsControlSecretConfirm('nom_confirm');
        $ctrl2->primarySecret = 'nom';
        $this->form->addCtrl($ctrl2, false);

        $this->form->setData('nom_confirm','');
        $this->assertTrue($this->form->check());

        $this->form->setData('nom','aaa');
        $this->assertFalse($this->form->check());
        $this->form->setData('nom_confirm','aaa');
        $this->assertTrue($this->form->check());
        $this->form->setData('nom_confirm','aaaaaaa');
        $this->assertFalse($this->form->check());
    }

    function testDate(){
        $ctrl = new jFormsControlinput('datenaissance');
        $ctrl->datatype= new jDatatypelocaledate();
        $ctrl->hasHelp=true;
        $this->form->addCtrl($ctrl);
        $this->assertTrue($this->form->check());

    }

    function testCaptcha() {

        $ctrl = new jFormsControlCaptcha('captcha');
        $this->form->addCtrl($ctrl);

        // captcha required by default $ctrl->required = true;
        $this->assertFalse($this->form->check());
        $this->form->setData('captcha','');
        $this->assertFalse($this->form->check());
        $this->form->setData('captcha','    ');
        $this->assertFalse($this->form->check());

        $ctrl->initExpectedValue();

        $this->assertTrue(isset($this->form->getContainer()->privateData['captcha']));

        $expectedResponse = $this->form->getContainer()->privateData['captcha'];
        $this->assertFalse($this->form->check());

        if( $expectedResponse == '1234')
            $badresponse = '12345';
        else
            $badresponse = '1234';

        $this->form->setData('captcha',$badresponse);
        $this->assertFalse($this->form->check());

        $this->form->setData('captcha',$expectedResponse);
        $this->assertTrue($this->form->check());
    }

    function testGroup() {
        $group = new jFormsControlGroup('group');

        $ctrl = new jFormsControlInput('nom');
        $ctrl->required = false;
        $group->addChildControl($ctrl);

        $ctrl = new jFormsControlCheckboxes('categories');
        $ctrl->required = true;
        $group->addChildControl($ctrl);
        $this->form->addCtrl($group);

        $this->assertFalse($this->form->check());

        $this->form->setData('categories',array('toto','titi'));
        $this->assertTrue($this->form->check());

        $this->form->setData('nom', 'foo');
        $this->assertTrue($this->form->check());

    }


    function testChoice() {
        $choice = new jFormsControlChoice('choice');
        $choice->required = false;

        $choice->createItem('item1','labelitem1');
        $choice->createItem('item2','labelitem2');
        $choice->createItem('item3','labelitem3');

        $ctrl = new jFormsControlInput('nom');
        $ctrl->required = false;
        $choice->addChildControl($ctrl, 'item1');

        $ctrl = new jFormsControlCheckboxes('categories');
        $ctrl->required = true;
        $choice->addChildControl($ctrl, 'item1');

        $ctrl = new jFormsControlinput('datenaissance');
        $ctrl->datatype= new jDatatypelocaledate();
        $choice->addChildControl($ctrl, 'item2');

        $this->form->addCtrl($choice);

        $this->assertFalse($this->form->check());

        $this->form->setData('choice', 'foo');
        $this->assertFalse($this->form->check());

        $this->form->setData('choice', 'item3');
        $this->assertTrue($this->form->check());

        $this->form->setData('choice', 'item1');
        $this->assertFalse($this->form->check());

        $this->form->setData('categories','toto');
        $this->assertFalse($this->form->check());

        $this->form->setData('categories',array('toto'));
        $this->assertTrue($this->form->check());

        $this->form->setData('categories',array('toto','titi'));
        $this->assertTrue($this->form->check());

        $this->form->setData('choice', 'item2');
        $this->assertTrue($this->form->check());

        $this->form->setData('categories','');
        $this->assertTrue($this->form->check());

    }
}

?>
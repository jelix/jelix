<?php
/**
* @package     testapp
* @subpackage  unittest module
* @author      Bastien Jaillot
* @contributor Laurent Jouanneau, Steven Jehannet
* @copyright   2008 Bastien Jaillot, 2009-2020 Laurent Jouanneau, 2010 Steven Jehannet
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
 
class tesMForm extends jFormsBase {
    function addCtrl($control, $reset=true){
        if($reset){
            $this->controls = array();
            $this->container->data = array();
        }
        $this->addControl($control);
    }
}

class jforms_modified_ControlsTest extends \Jelix\UnitTests\UnitTestCaseDb {
    protected $form;
    protected $container;
    function setUp() : void {
        $this->container = new jFormsDataContainer('','');
        $this->form = new tesMForm('foo', $this->container);

        $ctrl = new jFormsControlInput('inputctrl');
        $ctrl->setForm($this->form);
        $ctrl->setData('toto');
        $this->form->addCtrl($ctrl);
        $this->form->setData('inputctrl', 'toto');

        $ctrl = new jFormsControlInput('emptyctrl');
        // no value
        $ctrl->setForm($this->form);
        $this->form->addControl($ctrl);

        $ctrl = new jFormsControlInput('integerctrl');
        $ctrl->datatype=new jDatatypeInteger();
        $ctrl->setForm($this->form);
        $ctrl->setData('');
        $this->form->addControl($ctrl);


        $ctrl = new jFormsControlCheckbox('chckbxctrl');
        $ctrl->datatype=new jDatatypeBoolean();
        $this->form->addControl($ctrl);
        $this->form->setData('chckbxctrl', '1');
        $ctrl= new jFormsControlCheckbox('chckbxctrl1');
        $ctrl->defaultValue='0';
        $ctrl->valueOnCheck='1';
        $ctrl->valueOnUncheck='0';
        $this->form->addControl($ctrl);
        $ctrl= new jFormsControlCheckbox('chckbxctrl2');
        $ctrl->defaultValue='1';
        $ctrl->valueOnCheck='1';
        $ctrl->valueOnUncheck='0';
        $this->form->addControl($ctrl);

        $ctrl= new jFormsControlListbox('list');
        $ctrl->multiple=true;
        $ctrl->datasource = new jFormsStaticDatasource();
        $ctrl->datasource->data = array('a'=>'aaa', 'b'=>'bbb', 'c'=>'123', 'd'=>'456');
        $this->form->addControl($ctrl);
        $this->form->setData('list', array('bbb','123'));
    }

    function testinitModifiedControlsList() {
        $this->form->initModifiedControlsList();

        $this->assertEquals($this->form->getContainer()->data, $this->form->getContainer()->originalData);

        $initForm = array (
          'inputctrl' => 'toto',
          'emptyctrl' => '',
          'integerctrl' => '',
          'chckbxctrl' => '1',
          'chckbxctrl1'=>'0',
          'chckbxctrl2'=>'1',
          'list'=>array('bbb','123'),
          );
        $this->assertEquals($initForm, $this->form->getContainer()->originalData);
    }

    function testModifiedControls1() {
        $this->form->initModifiedControlsList();

        $this->form->setData('chckbxctrl', '0');
        $newForm = array (
          'inputctrl' => 'toto',
          'emptyctrl' => '',
          'integerctrl' => '',
          'chckbxctrl' => '0',
          'chckbxctrl1'=>'0',
          'chckbxctrl2'=>'1',
          'list'=>array('bbb','123'),);
        $this->assertEquals($newForm, $this->form->getContainer()->data);

        $modifiedControls = array (
            'chckbxctrl' => '1');
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());
        $this->form->setData('inputctrl', 'tata');
        $newForm = array (
          'inputctrl' => 'tata',
          'emptyctrl' => '',
          'integerctrl' => '',
          'chckbxctrl' => '0',
          'chckbxctrl1'=>'0',
          'chckbxctrl2'=>'1',
          'list'=>array('bbb','123'),);
        $this->assertEquals($newForm, $this->form->getContainer()->data);
        $modifiedControls = array (
           'inputctrl' => 'toto' ,
            'chckbxctrl' => '1');
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());


        $this->form->setData('list', array('123'));
        $newForm = array (
          'inputctrl' => 'tata',
          'emptyctrl' => '',
          'integerctrl' => '',
          'chckbxctrl' => '0',
          'chckbxctrl1'=>'0',
          'chckbxctrl2'=>'1',
          'list'=>array('123'),);
        $this->assertEquals($newForm, $this->form->getContainer()->data);
        $modifiedControls = array (
           'inputctrl' => 'toto' ,
            'chckbxctrl' => '1',
            'list'=>array('bbb','123'));
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        $this->form->setData('list', array());
        $newForm = array (
          'inputctrl' => 'tata',
          'emptyctrl' => '',
          'integerctrl' => '',
          'chckbxctrl' => '0',
          'chckbxctrl1'=>'0',
          'chckbxctrl2'=>'1',
          'list'=>array(),);
        $this->assertEquals($newForm, $this->form->getContainer()->data);
        $modifiedControls = array (
           'inputctrl' => 'toto' ,
            'chckbxctrl' => '1',
            'list'=>array('bbb','123'));
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        // despite all changes : originalData hasn't changed
        $initForm = array (
          'inputctrl' => 'toto',
          'emptyctrl' => '',
          'integerctrl' => '',
          'chckbxctrl' => '1',
          'chckbxctrl1'=>'0',
          'chckbxctrl2'=>'1',
          'list'=>array('bbb','123'),);
        $this->assertEquals($initForm, $this->form->getContainer()->originalData);
    }


    function testModifiedInput() {
        $this->form->setData('inputctrl', null);
        $this->form->setData('integerctrl', '');
        $this->form->initModifiedControlsList();

        $this->form->setData('inputctrl', '');
        $modifiedControls = array ();
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        $this->form->setData('inputctrl', '0');
        $modifiedControls = array (
            'inputctrl' => null
        );
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        $this->form->setData('inputctrl', 'hey');
        $modifiedControls = array (
           'inputctrl' => null);
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());
    }

    function testModifiedIntegerInput() {
        // with initial empty value

        $this->form->setData('inputctrl', null);
        $this->form->setData('integerctrl', '');
        $this->form->initModifiedControlsList();

        $this->form->setData('integerctrl', '');
        $modifiedControls = array ();
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        $this->form->setData('integerctrl', '0');
        $modifiedControls = array (
            'integerctrl' => ''
        );
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        $this->form->setData('integerctrl', 0);
        $modifiedControls = array (
            'integerctrl' => ''
        );
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());


        $this->form->setData('integerctrl', '10');
        $modifiedControls = array (
            'integerctrl' => '');
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        $this->form->setData('integerctrl', 10);
        $modifiedControls = array (
            'integerctrl' => '');
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        // with initial 0 value

        $this->form->setData('integerctrl', 0);
        $this->form->initModifiedControlsList();

        $this->form->setData('integerctrl', '');
        $modifiedControls = array (
            'integerctrl' => 0
        );
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        $this->form->setData('integerctrl', '0');
        $modifiedControls = array ();
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        $this->form->setData('integerctrl', 0);
        $modifiedControls = array ();
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        $this->form->setData('integerctrl', '10');
        $modifiedControls = array (
            'integerctrl' => 0);
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        $this->form->setData('integerctrl', 10);
        $modifiedControls = array (
            'integerctrl' => 0);
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        // with initial '0' value

        $this->form->setData('integerctrl', '0');
        $this->form->initModifiedControlsList();

        $this->form->setData('integerctrl', '');
        $modifiedControls = array (
            'integerctrl' => '0'
        );
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        $this->form->setData('integerctrl', '0');
        $modifiedControls = array ();
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        $this->form->setData('integerctrl', 0);
        $modifiedControls = array ();
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        $this->form->setData('integerctrl', '10');
        $modifiedControls = array (
            'integerctrl' => '0');
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        $this->form->setData('integerctrl', 10);
        $modifiedControls = array (
            'integerctrl' => '0');
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        // with initial '10' value

        $this->form->setData('integerctrl', '10');
        $this->form->initModifiedControlsList();

        $this->form->setData('integerctrl', '');
        $modifiedControls = array (
            'integerctrl' => '10'
        );
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        $this->form->setData('integerctrl', '0');
        $modifiedControls = array (
            'integerctrl' => '10');
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        $this->form->setData('integerctrl', 0);
        $modifiedControls = array (
            'integerctrl' => '10');
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        $this->form->setData('integerctrl', '10');
        $modifiedControls = array ();
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        $this->form->setData('integerctrl', 10);
        $modifiedControls = array ();
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());
    }

    function testModifiedNumericalValue() {
        $this->form->setData('chckbxctrl', 0);
        $this->form->initModifiedControlsList();

        $this->form->setData('chckbxctrl', '0');
        $modifiedControls = array ();
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        $this->form->setData('chckbxctrl', 'on');
        $modifiedControls = array ('chckbxctrl'=> '0');
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        $this->form->setData('chckbxctrl', '');
        $modifiedControls = array ();
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        $this->form->setData('chckbxctrl', null);
        $modifiedControls = array ();
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        $this->form->setData('chckbxctrl', '1');
        $modifiedControls = array ('chckbxctrl'=> '0');
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        $this->form->setData('chckbxctrl', true);
        $modifiedControls = array ('chckbxctrl'=> '0');
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());


        $this->form->setData('chckbxctrl', null);
        $this->form->initModifiedControlsList();

        $this->form->setData('chckbxctrl', '0');
        $modifiedControls = array ();
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        $this->form->setData('chckbxctrl', 'on');
        $modifiedControls = array ('chckbxctrl'=>'0');
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());


        $c = $this->form->getControl('chckbxctrl');
        $c->valueOnCheck='0';
        $c->valueOnUncheck='1';

        $this->form->setData('chckbxctrl', 0);
        $this->form->initModifiedControlsList();

        $this->form->setData('chckbxctrl', '0');
        $modifiedControls = array ();
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        $this->form->setData('chckbxctrl', 0);
        $modifiedControls = array ();
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        $this->form->setData('chckbxctrl', 'on');
        $modifiedControls = array ();
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        $this->form->setData('chckbxctrl', '');
        $modifiedControls = array ('chckbxctrl'=> '0');
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        $this->form->setData('chckbxctrl', null);
        $modifiedControls = array ('chckbxctrl'=> '0');
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        $this->form->setData('chckbxctrl', '1');
        $modifiedControls = array ('chckbxctrl'=> '0');
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        $this->form->setData('chckbxctrl', true);
        $modifiedControls = array ('chckbxctrl'=> '0');
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

    }

    function testModifiedList() {
        $this->form->setData('list', array());
        $this->form->initModifiedControlsList();

        $this->form->setData('list', '');
        $modifiedControls = array ();
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        $this->form->setData('list', array('123'));
        $modifiedControls = array (
           'list' => array());
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());

        $this->form->setData('list', array('123','aaa'));
        $this->form->initModifiedControlsList();
        $this->form->setData('list', array('aaa', '123'));
        $modifiedControls = array ();
        $this->assertEquals($modifiedControls, $this->form->getModifiedControls());
    }

}

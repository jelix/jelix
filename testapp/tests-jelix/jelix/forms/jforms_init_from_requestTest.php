<?php
/**
* @package     testapp
* @subpackage  unittest module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2011 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class testIFRForm extends jFormsBase {
    function addCtrl($control, $reset=true){
        if($reset){
            $this->controls = array();
            $this->container->data = array();
        }
        $this->addControl($control);
    }
}

class jforms_init_from_requestTest extends \PHPUnit\Framework\TestCase {
    protected $form;
    protected $container;
    function setUp() : void {
        $this->container = new jFormsDataContainer('','');
        $this->form = new testIFRForm('bar',$this->container, true);
        $this->form->securityLevel = 0 ; // by default, we don't want to deal with a token in our test

        jApp::saveContext();
        jApp::setCoord(new stdClass());
        jApp::coord()->request = new jClassicRequest();
    }

    function tearDown() : void {
        jApp::restoreContext();
    }

    function testSimpleform() {
        $ctrl = new jFormsControlInput('name');
        $ctrl->required = false;
        $ctrl->defaultValue='namexxx';
        $this->form->addControl($ctrl);

        $ctrl2 = new jFormsControlInput('firstname');
        $ctrl2->required = false;
        $ctrl2->defaultValue='firstnamexxx';
        $this->form->addControl($ctrl2);

        // check if the container has default values
        $this->assertEquals('namexxx', $this->container->data['name']);
        $this->assertEquals('firstnamexxx', $this->container->data['firstname']);

        // prepare the request

        jApp::coord()->request->params = array('firstname'=>'robert', 'name'=>'dupont');

        // check if the container has new values
        $this->form->initFromRequest();
        $this->assertEquals('robert', $this->container->data['firstname']);
        $this->assertEquals('dupont', $this->container->data['name']);


        // Test with a deactivated control
        $this->form->deactivate('firstname');
        jApp::coord()->request->params = array('firstname'=>'jean', 'name'=>'durant');
        $this->form->initFromRequest();
        // only name should change
        $this->assertEquals('robert', $this->container->data['firstname']);
        $this->assertEquals('durant', $this->container->data['name']);

         // Test with a readonly control
        $this->form->deactivate('firstname', false);
        $this->form->setReadOnly('name');
        jApp::coord()->request->params = array('firstname'=>'alain', 'name'=>'smith');
        $this->form->initFromRequest();
        // only firstname should change
        $this->assertEquals('alain', $this->container->data['firstname']);
        $this->assertEquals('durant', $this->container->data['name']);

    }

/*
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

    }*/


    function testChoice() {
        /*
        <choice ref="choice">
            <item value="item1"><label>labelitem1</label>
                <input ref="nom" />
                <checkboxes ref="categories" required="true" />
            </item>
            <item value="item2"><label>labelitem2</label>
                <input ref="datenaissance" type="date" />
            </item>
            <item value="item3"><label>labelitem3</label>
            </item>
        </choice>
        */

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

        $ctrl = new jFormsControlInput('datenaissance');
        $ctrl->datatype= new jDatatypelocaledate();
        $choice->addChildControl($ctrl, 'item2');

        $this->form->addControl($choice);

        $origdata = $this->container->data;

        // check values in the container when the third item is selected
        jApp::coord()->request->params = array('choice'=>'item3',
                                          'nom'=>'dupont',
                                          'categories'=>array('foo','bar'),
                                          'datenaissance'=>'2000-01-01'
                                        );

        $this->form->initFromRequest();
        $this->assertEquals('item3', $this->container->data['choice']);
        $this->assertEquals('', $this->container->data['nom']);
        $this->assertEquals(array(), $this->container->data['categories']);
        $this->assertEquals('', $this->container->data['datenaissance']);

        // check values in the container when the second item is selected
        $this->container->data = $origdata;
        jApp::coord()->request->params = array('choice'=>'item2',
                                          'nom'=>'dupont',
                                          'categories'=>array('foo','bar'),
                                          'datenaissance'=>'2000-01-01'
                                        );
        $this->form->initFromRequest();
        $this->assertEquals('item2', $this->container->data['choice']);
        $this->assertEquals('', $this->container->data['nom']);
        $this->assertEquals(array(), $this->container->data['categories']);
        $this->assertEquals('2000-01-01', $this->container->data['datenaissance']);

        // check values in the container when the first item is selected
        $this->container->data = $origdata;
        jApp::coord()->request->params = array('choice'=>'item1',
                                          'nom'=>'dupont',
                                          'categories'=>array('foo','bar'),
                                          'datenaissance'=>'2000-01-01'
                                        );
        $this->form->initFromRequest();
        $this->assertEquals('item1', $this->container->data['choice']);
        $this->assertEquals('dupont', $this->container->data['nom']);
        $this->assertEquals(array('foo','bar'), $this->container->data['categories']);
        $this->assertEquals('', $this->container->data['datenaissance']);

        // let's deactivate an item
        $this->container->data = $origdata;
        $choice->deactivateItem('item1');
        jApp::coord()->request->params = array('choice'=>'item1',
                                          'nom'=>'dupont',
                                          'categories'=>array('foo','bar'),
                                          'datenaissance'=>'2000-01-01'
                                        );
        $this->form->initFromRequest();
        $this->assertEquals('', $this->container->data['choice']);
        $this->assertEquals('', $this->container->data['nom']);
        $this->assertEquals(array(), $this->container->data['categories']);
        $this->assertEquals('', $this->container->data['datenaissance']);
    }
}

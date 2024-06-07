<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2009-2024 Laurent Jouanneau
* @link        https://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
use Jelix\Forms\Forms;

class UTjformsDummyObject {
    public $name='';
    public $price=0;
    public $instock = true;
}


class jforms_sessionTest extends \Jelix\UnitTests\UnitTestCase {

    protected $backupGlobalsBlacklist = array('_SESSION');

    protected $form2;

    protected $form1Descriptor, $form2Descriptor, $formLabelDescriptor;

    static function setUpBeforeClass() : void {
        if (isset($_SESSION['JFORMS_SESSION'])) {
            unset($_SESSION['JFORMS_SESSION']);
        };
        jFile::removeDir(__DIR__.'/../../../temp/jelixtests/jforms');
    }
    
    function setUp() : void {
        self::initClassicRequest(TESTAPP_URL.'index.php');
        jApp::pushCurrentModule('jelix_tests');
        $this->form1Descriptor = '
<object class="\\Jelix\\BuiltComponents\\Forms\\Jelix_tests\\Product">
    <object method="getContainer()" class="\\Jelix\\Forms\\FormDataContainer">
        <integer property="formId" value="'.Forms::DEFAULT_ID.'" />
        <string property="formSelector" value="jelix_tests~product" />
        <array property="data">
            <string key="name" value="" />
            <string key="price" value="" />
        </array>
        <array property="errors">[]</array>
    </object>
    <array method="getAllData()">
        <string key="name" value="" />
        <string key="price" value="" />
    </array>
    <integer method="id()" value="'.Forms::DEFAULT_ID.'" />
    <array method="getControls()">
        <object key="name" class="\\Jelix\\Forms\\Controls\\InputControl">
            <string property="ref" value="name"/>
            <boolean property="required" value="true"/>
            <boolean method="isReadOnly()" value="false"/>
            <string property="label" value="product name"/>
            <string property="defaultValue" value=""/>
        </object>
        <object key="price" class="\\Jelix\\Forms\\Controls\\InputControl">
            <string property="ref" value="price"/>
            <boolean property="required" value="false"/>
            <boolean method="isReadOnly()" value="false"/>
            <string property="label" value="The price"/>
            <string property="defaultValue" value=""/>
        </object>
    </array>
</object>';


        $this->form2Descriptor ='
<object class="\\Jelix\\BuiltComponents\\Forms\\Jelix_tests\\Product">
    <object method="getContainer()" class="\\Jelix\\Forms\\FormDataContainer">
        <string property="formId" value="akey" />
        <string property="formSelector" value="jelix_tests~product" />
        <array property="data">
            <string key="name" value="" />
            <string key="price" value="" />
        </array>
        <array property="errors">[]</array>
        <integer property="refcount" value="0" />
    </object>
    <array method="getAllData()">
        <string key="name" value="" />
        <string key="price" value="" />
    </array>
    <string method="id()" value="akey" />
    <array method="getControls()">
        <object key="name" class="\\Jelix\\Forms\\Controls\\InputControl">
            <string property="ref" value="name"/>
            <boolean property="required" value="true"/>
            <boolean method="isReadOnly()" value="false"/>
            <string property="label" value="product name"/>
            <string property="defaultValue" value=""/>
        </object>
        <object key="price" class="\\Jelix\\Forms\\Controls\\InputControl">
            <string property="ref" value="price"/>
            <boolean property="required" value="false"/>
            <boolean method="isReadOnly()" value="false"/>
            <string property="label" value="The price"/>
            <string property="defaultValue" value=""/>
        </object>
    </array>
</object>';

        $this->formLabelDescriptor = '
<object class="\\Jelix\\BuiltComponents\\Forms\\Jelix_tests\\Label">
    <object method="getContainer()" class="\\Jelix\\Forms\\FormDataContainer">
        <array property="formId">[1,"fr"]</array>
        <string property="formSelector" value="jelix_tests~label" />
        <array property="data">
            <string key="label" value="" />
        </array>
        <array property="errors">[]</array>
        <integer property="refcount" value="0" />
    </object>
    <array method="getAllData()">
        <string key="label" value="" />
    </array>
    <array method="id()">[1,"fr"]</array>
    <array method="getControls()">
        <object key="label" class="\\Jelix\\Forms\\Controls\\InputControl">
            <string property="ref" value="label"/>
            <boolean property="required" value="true"/>
            <boolean method="isReadOnly()" value="false"/>
            <string property="label" value="The label"/>
            <string property="defaultValue" value=""/>
        </object>
    </array>
</object>';
    }

    function tearDown() : void {
        $_SESSION['JFORMS_SESSION']->save();
        jApp::popCurrentModule();
    }

    function testCreate(){
        $form1 = Forms::create('product');
        $this->assertComplexIdenticalStr($form1, $this->form1Descriptor);

        $this->assertTrue(isset($_SESSION['JFORMS_SESSION']));
        $this->assertInstanceOf('\\Jelix\\Forms\\FormsSession',$_SESSION['JFORMS_SESSION']);

        list($selector, $formId, $key1) = $_SESSION['JFORMS_SESSION']->getCacheKey('product', null);
        $this->assertEquals('jelix_tests~product', $selector->toString());
        $this->assertEquals(0, $formId);
        $this->assertFalse(jCache::get($key1, 'jforms'));

        $verifContainer1='
        <object class="\\Jelix\\Forms\\FormDataContainer">
            <integer property="formId" value="'.\Jelix\Forms\FormsSession::DEFAULT_ID.'" />
            <string property="formSelector" value="jelix_tests~product" />
            <array property="data">
                <string key="name" value="" />
                <string key="price" value="" />
            </array>
            <array property="errors">[]</array>
            <integer property="refcount" value="1" />
        </object>';
        $this->assertComplexIdenticalStr($form1->getContainer(), $verifContainer1);

        // second time
        $form1 = Forms::create('product');
        $this->assertEquals(2, $form1->getContainer()->refcount);

        $form2 = Forms::create('product', 'akey');
        $this->assertComplexIdenticalStr($form2, $this->form2Descriptor);

        list($selector, $formId, $key2) = $_SESSION['JFORMS_SESSION']->getCacheKey('product', 'akey');
        $this->assertEquals('jelix_tests~product', $selector->toString());
        $this->assertEquals('akey', $formId);
        $this->assertFalse(jCache::get($key2, 'jforms'));

        $verifContainer2='
        <object class="\\Jelix\\Forms\\FormDataContainer">
            <string property="formId" value="akey" />
            <string property="formSelector" value="jelix_tests~product" />
            <array property="data">
                <string key="name" value="" />
                <string key="price" value="" />
            </array>
            <array property="errors">[]</array>
            <integer property="refcount" value="0" />
        </object>';
        $this->assertComplexIdenticalStr($form2->getContainer(), $verifContainer2);

        // with a complex form id
        $formLabel = Forms::create('label', array(1,'fr'));
        $this->assertComplexIdenticalStr($formLabel, $this->formLabelDescriptor);

        list($selector, $formId, $key3) = $_SESSION['JFORMS_SESSION']->getCacheKey('label', array(1,'fr'));
        $this->assertEquals('jelix_tests~label', $selector->toString());
        $this->assertEquals(array(1,'fr'), $formId);
        $this->assertFalse(jCache::get($key3, 'jforms'));

        $verifContainer3='
        <object class="\\Jelix\\Forms\\FormDataContainer">
            <array property="formId">[1,"fr"]</array>
            <string property="formSelector" value="jelix_tests~label" />
            <array property="data">
                <string key="label" value="" />
            </array>
            <array property="errors">[]</array>
            <integer property="refcount" value="0" />
        </object>';
        $this->assertComplexIdenticalStr($formLabel->getContainer(), $verifContainer3);
        return array($key1, $key2, $key3);
    }

    /**
     * @depends testCreate
     */
    function testSessionSave($keys) {
        $container = jCache::get($keys[0], 'jforms');
        $this->assertTrue($container !== false);
        $this->assertTrue(is_string($container));
        $container = unserialize($container);
        $this->assertInstanceOf('\\Jelix\\Forms\\FormDataContainer', $container);
        $verifContainer1='
        <object class="\\Jelix\\Forms\\FormDataContainer">
            <integer property="formId" value="'.\Jelix\Forms\FormsSession::DEFAULT_ID.'" />
            <string property="formSelector" value="jelix_tests~product" />
            <array property="data">
                <string key="name" value="" />
                <string key="price" value="" />
            </array>
            <array property="errors">[]</array>
            <integer property="refcount" value="2" />
        </object>';
        $this->assertComplexIdenticalStr($container, $verifContainer1);

        $container = jCache::get($keys[1], 'jforms');
        $this->assertTrue($container !== false);
        $this->assertTrue(is_string($container));
        $container = unserialize($container);
        $this->assertInstanceOf('\\Jelix\\Forms\\FormDataContainer', $container);
        $verifContainer2='
        <object class="\\Jelix\\Forms\\FormDataContainer">
            <string property="formId" value="akey" />
            <string property="formSelector" value="jelix_tests~product" />
            <array property="data">
                <string key="name" value="" />
                <string key="price" value="" />
            </array>
            <array property="errors">[]</array>
            <integer property="refcount" value="0" />
        </object>';
        $this->assertComplexIdenticalStr($container, $verifContainer2);
        $container = jCache::get($keys[2], 'jforms');
        $this->assertTrue($container !== false);
        $this->assertTrue(is_string($container));
        $container = unserialize($container);
        $this->assertInstanceOf('\\Jelix\\Forms\\FormDataContainer', $container);

        $verifContainer3='
        <object class="\\Jelix\\Forms\\FormDataContainer">
            <array property="formId">[1,"fr"]</array>
            <string property="formSelector" value="jelix_tests~label" />
            <array property="data">
                <string key="label" value="" />
            </array>
            <array property="errors">[]</array>
            <integer property="refcount" value="0" />
        </object>';
        $this->assertComplexIdenticalStr($container, $verifContainer3);
    }

    /**
     * @depends testCreate
     */
    function testGet(){
        $f1 = Forms::get('product');
        $this->assertComplexIdenticalStr($f1, $this->form1Descriptor);

        $f2 = Forms::get('product', 'akey');
        $this->assertComplexIdenticalStr($f2, $this->form2Descriptor);

        $f3 = Forms::get('product', 'anUnknownKey');
        $this->assertNull($f3);

        $f4 = Forms::get('label', array(1,'fr'));
        $this->assertComplexIdenticalStr($f4, $this->formLabelDescriptor);
    }

    /**
     * @depends testGet
     */
    function testFill(){
        $req = jApp::coord()->request;
        $savedParams = $req->params;

        $form = Forms::fill('product');
        $this->assertComplexIdenticalStr($form, $this->form1Descriptor);

        $req->params['name'] = 'phone';
        $req->params['price'] = '45';

        $form = Forms::fill('product');
        $verif = '
<object class="\\Jelix\\BuiltComponents\\Forms\\Jelix_tests\\Product">
    <object method="getContainer()" class="\\Jelix\\Forms\\FormDataContainer">
        <integer property="formId" value="'.Forms::DEFAULT_ID.'" />
        <string property="formSelector" value="jelix_tests~product" />
        <array property="data">
            <string key="name" value="phone" />
            <string key="price" value="45" />
        </array>
        <array property="errors">[]</array>
        <integer property="refcount" value="2" />
    </object>
    <array method="getAllData()">
        <string key="name" value="phone" />
        <string key="price" value="45" />
    </array>
    <integer method="id()" value="'.Forms::DEFAULT_ID.'" />
    <array method="getControls()">
        <object key="name" class="\\Jelix\\Forms\\Controls\\InputControl">
            <string property="ref" value="name"/>
            <boolean property="required" value="true"/>
            <boolean method="isReadOnly()" value="false"/>
            <string property="label" value="product name"/>
            <string property="defaultValue" value=""/>
        </object>
        <object key="price" class="\\Jelix\\Forms\\Controls\\InputControl">
            <string property="ref" value="price"/>
            <boolean property="required" value="false"/>
            <boolean method="isReadOnly()" value="false"/>
            <string property="label" value="The price"/>
            <string property="defaultValue" value=""/>
        </object>
    </array>
</object>';
        $this->assertComplexIdenticalStr($form, $verif);

        // verify that the other form hasn't changed
        $form = Forms::get('product', 'akey');
        $this->assertComplexIdenticalStr($form, $this->form2Descriptor);

        $req->params['price'] = '23';
        $form = Forms::fill('product', 'akey');
        $verif = '
<object class="\\Jelix\\BuiltComponents\\Forms\\Jelix_tests\\Product">
    <object method="getContainer()" class="\\Jelix\\Forms\\FormDataContainer">
        <integer property="formId" value="akey" />
        <string property="formSelector" value="jelix_tests~product" />
        <array property="data">
            <string key="name" value="phone" />
            <string key="price" value="23" />
        </array>
        <array property="errors">[]</array>
        <integer property="refcount" value="0" />
    </object>
    <array method="getAllData()">
        <string key="name" value="phone" />
        <string key="price" value="45" />
    </array>
    <integer method="id()" value="akey" />
    <array method="getControls()">
        <object key="name" class="\\Jelix\\Forms\\Controls\\InputControl">
            <string property="ref" value="name"/>
            <boolean property="required" value="true"/>
            <boolean method="isReadOnly()" value="false"/>
            <string property="label" value="product name"/>
            <string property="defaultValue" value=""/>
        </object>
        <object key="price" class="\\Jelix\\Forms\\Controls\\InputControl">
            <string property="ref" value="price"/>
            <boolean property="required" value="false"/>
            <boolean method="isReadOnly()" value="false"/>
            <string property="label" value="The price"/>
            <string property="defaultValue" value=""/>
        </object>
    </array>
</object>';

        $req->params= $savedParams;
    }

    /**
     * @depends testFill
     */
    function testSessionSavedAfterFill($keys) {
        list($sel, $formId, $key)  = $_SESSION['JFORMS_SESSION']->getCacheKey('product', null);
        $container = jCache::get($key, 'jforms');
        $this->assertTrue($container !== false);
        $this->assertTrue(is_string($container));
        $container = unserialize($container);
        $this->assertInstanceOf('\\Jelix\\Forms\\FormDataContainer', $container);
        $verifContainer1='
        <object class="\\Jelix\\Forms\\FormDataContainer">
            <integer property="formId" value="'.\Jelix\Forms\FormsSession::DEFAULT_ID.'" />
            <string property="formSelector" value="jelix_tests~product" />
            <array property="data">
                <string key="name" value="phone" />
                <string key="price" value="45" />
            </array>
            <array property="errors">[]</array>
            <integer property="refcount" value="2" />
        </object>';
        $this->assertComplexIdenticalStr($container, $verifContainer1);

        list($sel, $formId, $key)  = $_SESSION['JFORMS_SESSION']->getCacheKey('product', 'akey');
        $container = jCache::get($key, 'jforms');
        $this->assertTrue($container !== false);
        $this->assertTrue(is_string($container));
        $container = unserialize($container);
        $this->assertInstanceOf('\\Jelix\\Forms\\FormDataContainer', $container);
        $verifContainer2='
        <object class="\\Jelix\\Forms\\FormDataContainer">
            <string property="formId" value="akey" />
            <string property="formSelector" value="jelix_tests~product" />
            <array property="data">
                <string key="name" value="phone" />
                <string key="price" value="23" />
            </array>
            <array property="errors">[]</array>
            <integer property="refcount" value="0" />
        </object>';
        $this->assertComplexIdenticalStr($container, $verifContainer2);

        list($sel, $formId, $key) = $_SESSION['JFORMS_SESSION']->getCacheKey('label', array(1,'fr'));
        $container = jCache::get($key, 'jforms');
        $this->assertTrue($container !== false);
        $this->assertTrue(is_string($container));
        $container = unserialize($container);
        $this->assertInstanceOf('\\Jelix\\Forms\\FormDataContainer', $container);

        $verifContainer3='
        <object class="\\Jelix\\Forms\\FormDataContainer">
            <array property="formId">[1,"fr"]</array>
            <string property="formSelector" value="jelix_tests~label" />
            <array property="data">
                <string key="label" value="" />
            </array>
            <array property="errors">[]</array>
            <integer property="refcount" value="0" />
        </object>';
        $this->assertComplexIdenticalStr($container, $verifContainer3);
    }

    /**
     * @depends testSessionSavedAfterFill
     */
    function testDestroy(){

        // first destroy of the default instance
        // since we called create() twice, we should still have
        // the instance
        Forms::destroy('product');

        $verifProduct0='
        <object class="\\Jelix\\Forms\\FormDataContainer">
            <integer property="formId" value="'.Forms::DEFAULT_ID.'" />
            <string property="formSelector" value="jelix_tests~product" />
            <array property="data">[]</array>
            <array property="errors">[]</array>
            <integer property="refcount" value="1" />
        </object>';
        list($container, $sel) = $_SESSION['JFORMS_SESSION']->getContainer('product', null, false);
        $this->assertComplexIdenticalStr($container, $verifProduct0);

        $verifProduct1='
        <object key="akey" class="\\Jelix\\Forms\\FormDataContainer">
            <string property="formId" value="akey" />
            <string property="formSelector" value="jelix_tests~product" />
            <array property="data">
                <string key="name" value="phone" />
                <string key="price" value="23" />
            </array>
            <array property="errors">[]</array>
            <integer property="refcount" value="0" />
        </object>';
        list($container, $sel) = $_SESSION['JFORMS_SESSION']->getContainer('product', 'akey', false);
        $this->assertComplexIdenticalStr($container, $verifProduct1);

        $verifProduct2='
        <object class="\\Jelix\\Forms\\FormDataContainer">
            <array property="formId">[1,"fr"]</array>
            <string property="formSelector" value="jelix_tests~label" />
            <array property="data">
                <string key="label" value="" />
            </array>
            <array property="errors">[]</array>
            <integer property="refcount" value="0" />
        </object>';
        list($container, $sel) = $_SESSION['JFORMS_SESSION']->getContainer('label', array(1,'fr'), false);
        $this->assertComplexIdenticalStr($container, $verifProduct2);

        // second destroy, we should have no more default instance
        Forms::destroy('product');
        list($container, $sel) = $_SESSION['JFORMS_SESSION']->getContainer('product', null, false);
        $this->assertNull($container);
        
        $verifProduct1='
        <object key="akey" class="\\Jelix\\Forms\\FormDataContainer">
            <string property="formId" value="akey" />
            <string property="formSelector" value="jelix_tests~product" />
            <array property="data">
                <string key="name" value="phone" />
                <string key="price" value="23" />
            </array>
            <array property="errors">[]</array>
            <integer property="refcount" value="0" />
        </object>';
        list($container, $sel) = $_SESSION['JFORMS_SESSION']->getContainer('product', 'akey', false);
        $this->assertComplexIdenticalStr($container, $verifProduct1);

        $verifProduct2='
        <object class="\\Jelix\\Forms\\FormDataContainer">
            <array property="formId">[1,"fr"]</array>
            <string property="formSelector" value="jelix_tests~label" />
            <array property="data">
                <string key="label" value="" />
            </array>
            <array property="errors">[]</array>
            <integer property="refcount" value="0" />
        </object>';
        list($container, $sel) = $_SESSION['JFORMS_SESSION']->getContainer('label', array(1,'fr'), false);
        $this->assertComplexIdenticalStr($container, $verifProduct2);

        // destroy other instance of product
        Forms::destroy('product','akey');

        list($container, $sel) = $_SESSION['JFORMS_SESSION']->getContainer('product', 'akey', false);
        $this->assertNull($container);

        $verifProduct2='
        <object class="\\Jelix\\Forms\\FormDataContainer">
            <array property="formId">[1,"fr"]</array>
            <string property="formSelector" value="jelix_tests~label" />
            <array property="data">
                <string key="label" value="" />
            </array>
            <array property="errors">[]</array>
            <integer property="refcount" value="0" />
        </object>';
        list($container, $sel) = $_SESSION['JFORMS_SESSION']->getContainer('label', array(1,'fr'), false);
        $this->assertComplexIdenticalStr($container, $verifProduct2);
    }

    /**
     * @depends testDestroy
     */
    function testSessionSavedAfterDestroy() {
        list($sel, $formId, $key)  = $_SESSION['JFORMS_SESSION']->getCacheKey('product', null);
        $container = jCache::get($key, 'jforms');
        $this->assertFalse($container);

        list($sel, $formId, $key)  = $_SESSION['JFORMS_SESSION']->getCacheKey('product', 'akey');
        $container = jCache::get($key, 'jforms');
        $this->assertFalse($container);

        list($sel, $formId, $key) = $_SESSION['JFORMS_SESSION']->getCacheKey('label', array(1,'fr'));
        $container = jCache::get($key, 'jforms');
        $this->assertTrue($container !== false);
        $this->assertTrue(is_string($container));
        $container = unserialize($container);
        $this->assertInstanceOf('\\Jelix\\Forms\\FormDataContainer', $container);

        $verifContainer3='
        <object class="\\Jelix\\Forms\\FormDataContainer">
            <array property="formId">[1,"fr"]</array>
            <string property="formSelector" value="jelix_tests~label" />
            <array property="data">
                <string key="label" value="" />
            </array>
            <array property="errors">[]</array>
            <integer property="refcount" value="0" />
        </object>';
        $this->assertComplexIdenticalStr($container, $verifContainer3);
    }

    /**
     * @depends testSessionSavedAfterDestroy
     */
    function testPrepareObjectFromControls() {
        $f = Forms::create('product');
        $ctrl= new \Jelix\Forms\Controls\CheckboxControl('instock');
        $ctrl->label='En stock?';
        $f->addControl($ctrl);
        
        $verif='
        <object class="\\Jelix\\Forms\\FormDataContainer">
            <integer property="formId" value="'.Forms::DEFAULT_ID.'" />
            <string property="formSelector" value="jelix_tests~product" />
            <array property="data">
                <string key="name" value="" />
                <string key="price" value="" />
                <string key="instock" value="0" />
            </array>
            <array property="errors">[]</array>
        </object>';
        list($container, $sel) = $_SESSION['JFORMS_SESSION']->getContainer('product', null, false);
        $this->assertComplexIdenticalStr($container, $verif);

        $f->setData('name', 'car');
        $f->setData('price', 56598);
        $f->setData('instock', true);

        $verif='
        <object  class="\\Jelix\\Forms\\FormDataContainer">
            <integer property="formId" value="'.Forms::DEFAULT_ID.'" />
            <string property="formSelector" value="jelix_tests~product" />
            <array property="data">
                <string key="name" value="car" />
                <integer key="price" value="56598" />
                <string key="instock" value="1" />
            </array>
            <array property="errors">[]</array>
        </object>';
        list($container, $sel) = $_SESSION['JFORMS_SESSION']->getContainer('product', null, false);
        $this->assertComplexIdenticalStr($container, $verif);

        $o = new UTjformsDummyObject();
        $f->prepareObjectFromControls($o);

        $verif='<object class="UTjformsDummyObject">
            <string property="name" value="car" />
            <integer property="price" value="56598" />
            <boolean property="instock" value="true" />
        </object>';
        $this->assertComplexIdenticalStr($o, $verif);

        Forms::destroy('product');
    } 

    /**
     * @depends testPrepareObjectFromControls
     */
    function testSessionSavedPrepareObjectFromControls() {
        list($sel, $formId, $key)  = $_SESSION['JFORMS_SESSION']->getCacheKey('product', null);
        $container = jCache::get($key, 'jforms');
        $this->assertFalse($container);

        list($sel, $formId, $key)  = $_SESSION['JFORMS_SESSION']->getCacheKey('product', 'akey');
        $container = jCache::get($key, 'jforms');
        $this->assertFalse($container);

        list($sel, $formId, $key) = $_SESSION['JFORMS_SESSION']->getCacheKey('label', array(1,'fr'));
        $container = jCache::get($key, 'jforms');
        $this->assertTrue($container !== false);
        $this->assertTrue(is_string($container));
        $container = unserialize($container);
        $this->assertInstanceOf('\\Jelix\\Forms\\FormDataContainer', $container);

        $verifContainer3='
        <object class="\\Jelix\\Forms\\FormDataContainer">
            <array property="formId">[1,"fr"]</array>
            <string property="formSelector" value="jelix_tests~label" />
            <array property="data">
                <string key="label" value="" />
            </array>
            <array property="errors">[]</array>
            <integer property="refcount" value="0" />
        </object>';
        $this->assertComplexIdenticalStr($container, $verifContainer3);
    }


    /**
     * @depends testSessionSavedPrepareObjectFromControls
     */
    public function testTokenGenerationDefaultId() {
        $f = Forms::create('product');
        $c = $f->getContainer();
        $this->assertEquals(0, $c->formId);
        
        $c->token = '';
        $this->assertNotEquals('', $f->createNewToken());

        sleep(1);
        $t = $c->token;
        $this->assertEquals($t, $f->createNewToken());
    }

    /**
     * @depends testTokenGenerationDefaultId
     */
    public function testTokenGenerationStringIntId() {
        $f = Forms::create('product', "8");
        $c = $f->getContainer();
        $this->assertEquals("8", $c->formId);
        
        $c->token = '';
        $this->assertNotEquals('', $f->createNewToken());

        sleep(1);
        $t = $c->token;
        $this->assertEquals($t, $f->createNewToken());
    }

    /**
     * @depends testTokenGenerationStringIntId
     */
    public function testTokenGenerationString0Id() {
        $f = Forms::create('product', "0");
        $c = $f->getContainer();
        $this->assertEquals("0", $c->formId);

        $c->token = '';
        $this->assertNotEquals('', $f->createNewToken());

        sleep(1);
        $t = $c->token;
        $this->assertEquals($t, $f->createNewToken());
    }

    /**
     * @depends testTokenGenerationString0Id
     */
    public function testTokenGenerationIntId() {
        $f = Forms::create('product', 8);
        $c = $f->getContainer();
        $this->assertEquals(8, $c->formId);

        $c->token = '';
        $this->assertNotEquals('', $f->createNewToken());

        sleep(1);
        $t = $c->token;
        $this->assertEquals($t, $f->createNewToken());
    }

    /**
     * @depends testTokenGenerationIntId
     */
    public function testTokenGeneration0Id() {
        $f = Forms::create('product', 0);
        $c = $f->getContainer();
        $this->assertEquals(0, $c->formId);

        $c->token = '';
        $this->assertNotEquals('', $f->createNewToken());

        sleep(1);
        $t = $c->token;
        $this->assertEquals($t, $f->createNewToken());
    }
}

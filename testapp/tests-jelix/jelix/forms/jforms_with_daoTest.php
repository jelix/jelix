<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2007-2024 Laurent Jouanneau
* @link        https://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
use Jelix\Forms\Forms;

class jforms_With_DaoTest extends \Jelix\UnitTests\UnitTestCaseDb {

    protected $backupGlobalsBlacklist = array('_SESSION');

    static function setUpBeforeClass() : void {
        if (isset($_SESSION['JFORMS_SESSION'])) {
            unset($_SESSION['JFORMS_SESSION']);
        };
        jFile::removeDir(__DIR__.'/../../../temp/jelixtests/jforms');
        self::initClassicRequest(TESTAPP_URL.'index.php');
        jApp::pushCurrentModule('jelix_tests');
        $form = Forms::create('product');
        $form = Forms::create('label', array(1,'fr'));
        $form = Forms::create('label', array(1,'en'));
    }

    function setUp() : void {
        self::initClassicRequest(TESTAPP_URL.'index.php');
        jApp::pushCurrentModule('jelix_tests');
    }

    function tearDown() : void {
        jApp::popCurrentModule();
        jApp::setCoord(null);
    }
    
    static function tearDownAfterClass() : void {
/*        jForms::destroy('product');
        jForms::destroy('label', array(1,'fr'));
        jForms::destroy('label', array(1,'en'));
*/    }

    static protected $id;
    static protected $id2;
    
    function testInsertDao(){
        $this->emptyTable('product_test');
        $this->emptyTable('product_tags_test');
        $this->emptyTable('labels_test');
        $req = jApp::coord()->request;

        $req->params['name'] = 'phone';
        $req->params['price'] = '45';
        $req->params['tag'] = array('professionnal','book');
        $form = Forms::fill('product');

        // save main data
        self::$id = $form->saveToDao('products');
        $this->assertEquals(1, preg_match("/^[0-9]+$/",self::$id));
        $records = array(
            array('id'=>self::$id, 'name'=>'phone', 'price'=>45),
        );
        $this->assertTableContainsRecords('product_test', $records);

        // save data of the tags control which is a container
        $form->saveControlToDao('tag','product_tags',self::$id);
        $records = array(
            array('product_id'=>self::$id, 'tag'=>'professionnal'),
            array('product_id'=>self::$id, 'tag'=>'book'),
        );
        $this->assertTableContainsRecords('product_tags_test', $records);

        //insert a second product
        $req->params['name'] = 'computer';
        $req->params['price'] = '590';
        $req->params['tag'] = array('professionnal','promotion');
        $form = Forms::fill('product');

        self::$id2 = $form->saveToDao('products');
        $this->assertEquals(1, preg_match("/^[0-9]+$/",self::$id2));
        $this->assertNotEquals(self::$id, self::$id2);
        $records = array(
            array('id'=>self::$id, 'name'=>'phone', 'price'=>45),
            array('id'=>self::$id2, 'name'=>'computer', 'price'=>590),
        );
        $this->assertTableContainsRecords('product_test', $records);

        // save data of the tags control which is a container
        $form->saveControlToDao('tag','product_tags',self::$id2);
        $records = array(
            array('product_id'=>self::$id, 'tag'=>'professionnal'),
            array('product_id'=>self::$id, 'tag'=>'book'),
            array('product_id'=>self::$id2,'tag'=>'professionnal'),
            array('product_id'=>self::$id2,'tag'=>'promotion'),
        );
        $this->assertTableContainsRecords('product_tags_test', $records);
    }

    /**
     * @depends testInsertDao
     */
    function testInsertDao2(){

        $req = jApp::coord()->request;

        $req->params['label'] = 'bonjour';
        $form = Forms::fill('label', array(1,'fr'));

        // save main data
        $id = $form->saveToDao('labels');
        $this->assertEquals(array(1,'fr'), $id);
        $records = array(
            array('key'=>1, 'lang'=>'fr', 'label'=>'bonjour'),
        );
        $this->assertTableContainsRecords('labels_test', $records);

        //insert a second label
        $req->params['label'] = 'Hello';
        $form = Forms::fill('label', array(1,'en'));

        $id2 = $form->saveToDao('labels');
        $this->assertEquals(array(1,'en'), $id2);
        $records = array(
            array('key'=>1, 'lang'=>'fr', 'label'=>'bonjour'),
            array('key'=>1, 'lang'=>'en', 'label'=>'Hello'),
        );
        $this->assertTableContainsRecords('labels_test', $records);
    }

    /**
     * @depends testInsertDao2
     */
    function testUpdateDao(){

        $req = jApp::coord()->request;

        $form = Forms::create('product',self::$id); // "fill" need an existing form

        $req->params['name'] = 'other phone';
        $req->params['price'] = '68';
        $req->params['tag'] = array('high tech','best seller');

        $form = Forms::fill('product',self::$id);
        $id = $form->saveToDao('products');

        $this->assertEquals(self::$id, $id);

        $form->saveToDao('products'); // try to update an unchanged record 

        $records = array(
            array('id'=>self::$id, 'name'=>'other phone', 'price'=>68),
            array('id'=>self::$id2,'name'=>'computer',    'price'=>590),
        );
        $this->assertTableContainsRecords('product_test', $records);

        // save data of the tags control which is a container
        $form->saveControlToDao('tag','product_tags',self::$id);
        $records = array(
            array('product_id'=>self::$id2, 'tag'=>'professionnal'),
            array('product_id'=>self::$id2, 'tag'=>'promotion'),
            array('product_id'=>self::$id,  'tag'=>'high tech'),
            array('product_id'=>self::$id,  'tag'=>'best seller'),
        );
        $this->assertTableContainsRecords('product_tags_test', $records);

    }

    /**
     * @depends testUpdateDao
     */
    function testLoadDao(){
        Forms::destroy('product');
        Forms::destroy('product', self::$id);

        list($container, $sel) = $_SESSION['JFORMS_SESSION']->getContainer('product', null, false);
        $this->assertNull($container);
        list($container, $sel) = $_SESSION['JFORMS_SESSION']->getContainer('product', self::$id, false);
        $this->assertNull($container);

        $form = Forms::create('product', self::$id);

        $verif='
        <object class="\\Jelix\\Forms\\FormDataContainer">
            <integer property="formId" value="'.self::$id.'" />
            <string property="formSelector" value="jelix_tests~product" />
            <array property="data">
                <string key="name" value="" />
                <string key="price" value="" />
                <array key="tag">[]</array>
            </array>
            <array property="errors">[]</array>
        </object>';
        list($container, $sel) = $_SESSION['JFORMS_SESSION']->getContainer('product', self::$id, false);
        $this->assertComplexIdenticalStr($container, $verif);

        $form->initFromDao('products');

        $verif='
        <object class="\\Jelix\\Forms\\FormDataContainer">
            <integer property="formId" value="'.self::$id.'" />
            <string property="formSelector" value="jelix_tests~product" />
            <array property="data">
                <string key="name" value="other phone" />
                <string key="price" value="68" />
                <array key="tag">[]</array>
            </array>
            <array property="errors">[]</array>
        </object>';
        list($container, $sel) = $_SESSION['JFORMS_SESSION']->getContainer('product', self::$id, false);
        $this->assertComplexIdenticalStr($container, $verif);

        $form->initControlFromDao('tag', 'product_tags');
        $verif='
        <object class="\\Jelix\\Forms\\FormDataContainer">
            <integer property="formId" value="'.self::$id.'" />
            <string property="formSelector" value="jelix_tests~product" />
            <array property="data">
                <string key="name" value="other phone" />
                <string key="price" value="68" />
                <array key="tag">["best seller", "high tech"]</array>
            </array>
            <array property="errors">[]</array>
        </object>';
        list($container, $sel) = $_SESSION['JFORMS_SESSION']->getContainer('product', self::$id, false);
        $this->assertComplexIdenticalStr($container, $verif);
    }

    /**
     * @depends testLoadDao
     */
    function testGetValue() {
        $this->emptyTable('labels1_test');
        $this->assertTrue(true);
    }
}
?>
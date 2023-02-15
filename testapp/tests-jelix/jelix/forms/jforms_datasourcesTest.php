<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2010 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class jforms_datasourcesTest extends \Jelix\UnitTests\UnitTestCaseDb {

    protected $savedParams;

    function setUp() : void {
        self::initClassicRequest(TESTAPP_URL.'index.php');
        jApp::pushCurrentModule('jelix_tests');
        if (isset($_SESSION['JFORMS_SESSION'])) {
            unset($_SESSION['JFORMS_SESSION']);
        };
        jFile::removeDir(__DIR__.'/../../../temp/jelixtests/jforms');
        $form = jForms::create('product');
        $this->savedParams = jApp::coord()->request->params;

        $labels = array(array('key'=>1, 'keyalias'=>'aa', 'lang'=>'fr', 'label'=>'aa-fr'),
                        array('key'=>2, 'keyalias'=>'bb', 'lang'=>'fr', 'label'=>'bb-fr'),
                        array('key'=>3, 'keyalias'=>'cc', 'lang'=>'fr', 'label'=>'cc-fr'),
                        array('key'=>4, 'keyalias'=>'dd', 'lang'=>'en', 'label'=>'dd-en'),
                        array('key'=>5, 'keyalias'=>'ee', 'lang'=>'en', 'label'=>'ee-en'),
        );
        $this->insertRecordsIntoTable('labels1_test', array('key','keyalias','lang','label'), $labels, true);

        $labels = array(array('key'=>1, 'keyalias'=>'aa', 'lang'=>'fr', 'label'=>'aa-fr'),
                        array('key'=>2, 'keyalias'=>'bb', 'lang'=>'fr', 'label'=>'bb-fr'),
                        array('key'=>3, 'keyalias'=>'cc', 'lang'=>'fr', 'label'=>'cc-fr'),
                        array('key'=>1, 'keyalias'=>'dd', 'lang'=>'en', 'label'=>'dd-en'),
                        array('key'=>2, 'keyalias'=>'ee', 'lang'=>'en', 'label'=>'ee-en'),
        );
        $this->insertRecordsIntoTable('labels_test', array('key','keyalias', 'lang','label'), $labels, true);
    }

    function tearDown() : void {
        
        jApp::coord()->request->params = $this->savedParams;
        jForms::destroy('product');
        jApp::popCurrentModule();
    }

    function testValueIsPkSimpleTable() {
        $form = jForms::get('product');
        // ============= The selected value is the primary key
        // ---- retrieve all data
        $ds = new jFormsDaoDatasource('jelix_tests~labels1' , "findAll" , 'label', 'key', '');
        $data = $ds->getData($form);
        $this->assertEquals(array('1'=>'aa-fr', '2'=>'bb-fr', '3'=>'cc-fr', '4'=>'dd-en', '5'=>'ee-en'), $data);
        $this->assertEquals('aa-fr', $ds->getLabel2('1', $form));
        $this->assertEquals('ee-en', $ds->getLabel2('5', $form));

        // ---- retrieve data with multiple label
        $ds = new jFormsDaoDatasource('jelix_tests~labels1' , "findAll" , 'lang,label', 'key', '', null, null, '#');
        $data = $ds->getData($form);
        $this->assertEquals(array('1'=>'fr#aa-fr', '2'=>'fr#bb-fr', '3'=>'fr#cc-fr', '4'=>'en#dd-en', '5'=>'en#ee-en'), $data);
        $this->assertEquals('fr#aa-fr', $ds->getLabel2('1', $form));
        $this->assertEquals('en#ee-en', $ds->getLabel2('5', $form));
    }

    function testValueIsPkSimpleTableStaticCriteria() {
        $form = jForms::get('product');
        // ============= The selected value is the primary key
        // ---- retrieve data with a static criteria
        $ds = new jFormsDaoDatasource('jelix_tests~labels1' , "findByLang" , 'label', 'key', '', "fr");
        $data = $ds->getData($form);
        $this->assertEquals(array('1'=>'aa-fr', '2'=>'bb-fr', '3'=>'cc-fr'), $data);
        $this->assertEquals('aa-fr', $ds->getLabel2('1', $form));
        // even if this record doesn't correspond to the criteria, we don't have choice
        // because the PK is a single field. And for some case, it could make sens
        $this->assertEquals('ee-en', $ds->getLabel2('5', $form));
    }

    function testValueIsPkSimpleTableDynamicCriteria() {
        $form = jForms::get('product');
        // ============= The selected value is the primary key
        // ---- retrieve data with a dynamic criteria
        $ds = new jFormsDaoDatasource('jelix_tests~labels1' , "findByLang" , 'label', 'key', '', null, 'name');

        $form->setData('name', 'fr');
        $data = $ds->getData($form);
        $this->assertEquals(array('1'=>'aa-fr', '2'=>'bb-fr', '3'=>'cc-fr'), $data);
        $this->assertEquals('aa-fr', $ds->getLabel2('1', $form));
        // even if this record doesn't correspond to the criteria, we don't have choice
        // because the PK is a single field. And for some case, it could make sens
        $this->assertEquals('ee-en', $ds->getLabel2('5', $form));

        $form->setData('name', 'en');
        $data = $ds->getData($form);
        $this->assertEquals(array('4'=>'dd-en', '5'=>'ee-en'), $data);
        $this->assertEquals('aa-fr', $ds->getLabel2('1', $form));
        $this->assertEquals('ee-en', $ds->getLabel2('5', $form));
    }

    function testValueNotPkSimpleTable() {
        $form = jForms::get('product');
        // ============= The selected value is not the primary key
        // ---- retrieve all data
            // method for the label is not given
        $ds = new jFormsDaoDatasource('jelix_tests~labels1' , "findAll" , 'label', 'keyalias', '');
        $data = $ds->getData($form);
        $this->assertEquals(array('aa'=>'aa-fr', 'bb'=>'bb-fr', 'cc'=>'cc-fr', 'dd'=>'dd-en', 'ee'=>'ee-en'), $data);
        $this->assertNull($ds->getLabel2('aa', $form));
        $this->assertNull($ds->getLabel2('ee', $form));

            // method for the label is given
        $ds = new jFormsDaoDatasource('jelix_tests~labels1' , "findAll" , 'label', 'keyalias', '', null, null);
        $ds->labelMethod = 'getByAlias';
        $data = $ds->getData($form);
        $this->assertEquals(array('aa'=>'aa-fr', 'bb'=>'bb-fr', 'cc'=>'cc-fr', 'dd'=>'dd-en', 'ee'=>'ee-en'), $data);
        $this->assertEquals('aa-fr', $ds->getLabel2('aa', $form));
        $this->assertEquals('ee-en', $ds->getLabel2('ee', $form));

    }

    function testValueNotPkSimpleTableMultipleLabels() {
        // ============= The selected value is not the primary key
        $form = jForms::get('product');
        // ---- retrieve data with multiple label
                // method for the label is not given
        $ds = new jFormsDaoDatasource('jelix_tests~labels1' , "findAll" , 'lang,label', 'keyalias', '', null, null, '#');
        $data = $ds->getData($form);
        $this->assertEquals(array('aa'=>'fr#aa-fr', 'bb'=>'fr#bb-fr', 'cc'=>'fr#cc-fr', 'dd'=>'en#dd-en', 'ee'=>'en#ee-en'), $data);
        $this->assertNull($ds->getLabel2('aa', $form));
        $this->assertNull($ds->getLabel2('ee', $form));

                // method for the label is given
        $ds = new jFormsDaoDatasource('jelix_tests~labels1' , "findAll" , 'lang,label', 'keyalias', '', null, null, '#');
        $ds->labelMethod = 'getByAlias';
        $data = $ds->getData($form);
        $this->assertEquals(array('aa'=>'fr#aa-fr', 'bb'=>'fr#bb-fr', 'cc'=>'fr#cc-fr', 'dd'=>'en#dd-en', 'ee'=>'en#ee-en'), $data);
        $this->assertEquals('fr#aa-fr', $ds->getLabel2('aa', $form));
        $this->assertEquals('en#ee-en', $ds->getLabel2('ee', $form));
    }

    function testValueNotPkSimpleTableStaticCriteria() {
        $form = jForms::get('product');
        // ============= The selected value is not the primary key
        // ---- retrieve data with a static criteria
                // method for the label is not given
        $ds = new jFormsDaoDatasource('jelix_tests~labels1' , "findByLang" , 'label', 'keyalias', '', "fr");
        $data = $ds->getData($form);
        $this->assertEquals(array('aa'=>'aa-fr', 'bb'=>'bb-fr', 'cc'=>'cc-fr'), $data);
        $this->assertNull($ds->getLabel2('aa', $form));
        $this->assertNull($ds->getLabel2('ee', $form));

                // method for the label is not given
        $ds = new jFormsDaoDatasource('jelix_tests~labels1' , "findByLang" , 'label', 'keyalias', '', "fr");
        $ds->labelMethod = 'getByAliasAndCriteria';
        $data = $ds->getData($form);
        $this->assertEquals(array('aa'=>'aa-fr', 'bb'=>'bb-fr', 'cc'=>'cc-fr'), $data);
        $this->assertEquals('aa-fr', $ds->getLabel2('aa', $form));
        $this->assertEquals('ee-en', $ds->getLabel2('ee', $form));
    }

    function testValueNotPkSimpleTableDynamicCriteriaWithoutMethod() {
        $form = jForms::get('product');
        // ============= The selected value is not the primary key
        // ---- retrieve data with a dynamic criteria
        $ds = new jFormsDaoDatasource('jelix_tests~labels1' , "findByLang" , 'label', 'keyalias', '', null, 'name');

                // method for the label is not given
        $form->setData('name', 'fr');
        $data = $ds->getData($form);
        $this->assertEquals(array('aa'=>'aa-fr', 'bb'=>'bb-fr', 'cc'=>'cc-fr'), $data);
        $this->assertNull($ds->getLabel2('aa', $form));
        $this->assertNull($ds->getLabel2('ee', $form));

                // method for the label is given
        $form->setData('name', 'fr');
        $ds->labelMethod = 'getByAliasAndCriteria';
        $data = $ds->getData($form);
        $this->assertEquals(array('aa'=>'aa-fr', 'bb'=>'bb-fr', 'cc'=>'cc-fr'), $data);
        $this->assertEquals('aa-fr', $ds->getLabel2('aa', $form));
        $this->assertEquals('dd-en', $ds->getLabel2('dd', $form));
    }

    function testValueNotPkSimpleTableDynamicCriteriaWithMethod() {
        $form = jForms::get('product');
        // ============= The selected value is not the primary key
        // ---- retrieve data with a dynamic criteria
        $ds = new jFormsDaoDatasource('jelix_tests~labels1' , "findByLang" , 'label', 'keyalias', '', null, 'name');
            // method for the label is not given
        $ds->labelMethod = 'get';
        $form->setData('name', 'en');
        $data = $ds->getData($form);
        $this->assertEquals(array('dd'=>'dd-en', 'ee'=>'ee-en'), $data);
        $this->assertNull($ds->getLabel2('aa', $form));
        $this->assertNull($ds->getLabel2('ee', $form));

            // method for the label is given
        $form->setData('name', 'en');
        $ds->labelMethod = 'getByAliasAndCriteria';
        $data = $ds->getData($form);
        $this->assertEquals(array('dd'=>'dd-en', 'ee'=>'ee-en'), $data);
        $this->assertEquals('bb-fr', $ds->getLabel2('bb', $form));
        $this->assertEquals('ee-en', $ds->getLabel2('ee', $form));
    }

    function testValueIsPkMultiKeyTable() {
        $form = jForms::get('product');

        // ---- retrieve data
        $ds = new jFormsDaoDatasource('jelix_tests~labels' , "findAllOrderByKeyalias" , 'label', 'key', '');
        $data = $ds->getData($form);
        $this->assertEquals(array('1'=>'dd-en', '2'=>'ee-en', '3'=>'cc-fr'), $data);
        try {
            $this->assertEquals('aa-fr', $ds->getLabel2('1', $form));
            $this->fail('An exception should be thrown since the primary key is not a unique field');
        }
        catch(Exception $e) {
            $this->assertTrue(true, 'Ok, exception is thrown since the primary key is not a unique field');
        }

        // ---- retrieve data with multiple label
        $ds = new jFormsDaoDatasource('jelix_tests~labels' , "findAllOrderByKeyalias" , 'lang,label', 'key', '', null, null, '#');
        $data = $ds->getData($form);
        $this->assertEquals(array('1'=>'en#dd-en', '2'=>'en#ee-en', '3'=>'fr#cc-fr'), $data);
    }
    function testValueIsPkMultiKeyTableStaticCriteria(){
        $form = jForms::get('product');

        // ---- retrieve data with a static criteria
        $ds = new jFormsDaoDatasource('jelix_tests~labels' , "findByLang" , 'label', 'key', '', "fr");
        $data = $ds->getData($form);
        $this->assertEquals(array('1'=>'aa-fr', '2'=>'bb-fr', '3'=>'cc-fr'), $data);
        $this->assertEquals('aa-fr', $ds->getLabel2('1', $form));
        $this->assertNull($ds->getLabel2('5', $form));
    }

    function testValueIsPkMultiKeyTableMutlipleStaticCriteria(){
        // ---- retrieve data with multiple static criteria
        $form = jForms::get('product');

        // should throw a warning. impossible
        //$ds = new jFormsDaoDatasource('jelix_tests~labels' , "findByLang" , 'label', 'key', '', "fr,en");
        //$data = $ds->getData($form);
        //$this->assertError();

        $ds = new jFormsDaoDatasource('jelix_tests~labels' , "findByLang2OrderByKeyalias" , 'label', 'key', '', "fr,en");
        $ds->labelMethod = 'getByLang2';
        $data = $ds->getData($form);
        $this->assertEquals(array('1'=>'dd-en', '2'=>'ee-en', '3'=>'cc-fr'), $data);
        $this->assertEquals('dd-en', $ds->getLabel2('1', $form));
        $this->assertNull($ds->getLabel2('5', $form));


    }
    
    function testValueIsPkMultiKeyTableDynamicCriteria(){
        $form = jForms::get('product');
        // ---- retrieve data with a dynamic criteria
        $ds = new jFormsDaoDatasource('jelix_tests~labels' , "findByLang" , 'label', 'key', '', null, 'name');

        $form->setData('name', 'fr');
        $data = $ds->getData($form);
        $this->assertEquals(array('1'=>'aa-fr', '2'=>'bb-fr', '3'=>'cc-fr'), $data);
        $this->assertEquals('aa-fr', $ds->getLabel2('1', $form));
        $this->assertNull($ds->getLabel2('5', $form));

        $form->setData('name', 'en');
        $data = $ds->getData($form);
        $this->assertEquals(array('1'=>'dd-en', '2'=>'ee-en'), $data);
        $this->assertEquals('dd-en', $ds->getLabel2('1', $form));
        $this->assertNull($ds->getLabel2('5', $form));

    }

    function testValueIsPkMultiKeyTableDynamicCriteriaNotPK(){
        $form = jForms::get('product');
        $ds = new jFormsDaoDatasource('jelix_tests~labels' , "findByLang" , 'label', 'key', '', null, 'price');
        $form->setData('price', '5');
        $data = $ds->getData($form);
        $this->assertEquals(array(), $data);
        $this->assertNull($ds->getLabel2('1', $form));
        $this->assertNull($ds->getLabel2('5', $form));
        
        $ds = new jFormsDaoDatasource('jelix_tests~labels' , "findAllFr" , 'label', 'key', '', null, 'price');
        // ok here, implementation of findAllFr doesn't take care about the price parameter, but well...
        $ds->labelMethod = 'getFr';
        $form->setData('price', '5');
        $data = $ds->getData($form);
        $this->assertEquals(array('1'=>'aa-fr', '2'=>'bb-fr', '3'=>'cc-fr'), $data);
        $this->assertEquals('aa-fr', $ds->getLabel2('1', $form));
        $this->assertNull($ds->getLabel2('5', $form));
    }

    function testValueIsPkMultiKeyTableMultipleDynamicCriteria(){
        $form = jForms::get('product');
        // ---- retrieve data with multiple dynamic criteria
        $ds = new jFormsDaoDatasource('jelix_tests~labels' , "findByLang3" , 'label', 'key', '', null, 'price,name');
        $ds->labelMethod = 'getByLang3';
        $form->setData('name', 'fr');
        $form->setData('price', '5');
        $data = $ds->getData($form);
        $this->assertEquals(array('1'=>'aa-fr', '2'=>'bb-fr', '3'=>'cc-fr'), $data);
        $this->assertEquals('aa-fr',$ds->getLabel2('1', $form));
        $this->assertNull($ds->getLabel2('5', $form));
    }
    
    function testValueNotPkMultiKeyTable(){
        $form = jForms::get('product');

        // ---- retrieve data with a value which is not part of the key
        // should not work
        //$ds = new jFormsDaoDatasource('jelix_tests~labels' , "findByAlias" , 'label', 'keyalias', '');
        //$ds->labelMethod = 'getByAlias';
        //$data = $ds->getData($form);
        //$this->assertError();

        // ---- retrieve data with a value which is not part of the key, + a criteria
        $ds = new jFormsDaoDatasource('jelix_tests~labels' , "findByLang" , 'label', 'keyalias', '', 'fr');
        $ds->labelMethod = 'getByAliasLang';
        $data = $ds->getData($form);
        $this->assertEquals(array('aa'=>'aa-fr', 'bb'=>'bb-fr', 'cc'=>'cc-fr'), $data);
        $this->assertEquals('aa-fr', $ds->getLabel2('aa', $form));
        $this->assertNull($ds->getLabel2('dd', $form));

    }
}

<?php
/**
* @package     jelix tests
* @author      Laurent Jouanneau
* @contributor Julien Issler, Dominique Papin
* @copyright   2006-2018 Laurent Jouanneau
* @copyright   2008 Julien Issler, 2008 Dominique Papin
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
use \Jelix\PropertiesFile\Properties;
use \Jelix\PropertiesFile\Parser;

class jLocaleTest extends \Jelix\UnitTests\UnitTestCase {

    protected $filePath;

    public static function setUpBeforeClass() : void  {
        self::initJelixConfig();
    }

    function setUp() : void  {
        jApp::saveContext();
        jApp::pushCurrentModule('jelix_tests');
        $this->filePath =  jApp::appPath().'modules/jelix_tests/locales/';
        parent::setUp();
    }

    function tearDown() : void  {
        jApp::restoreContext();
        parent::tearDown();
    }

    public function testBundleUnknownFile(){
        $this->assertTrue($this->filePath != '');
        try {
            $properties = new Properties();
            $reader = new Parser();
            $reader->parseFromFile($this->filePath.'unknownfile', $properties);
            self::fail('should throw an exception when trying reading unknownfile');
        }catch(Exception $e){
            $this->assertEquals('Cannot load the properties file '.$this->filePath.'unknownfile', $e->getMessage(),
            'should throw the right exception when trying reading unknownfile (wrong message: '.$e->getMessage().')');
        }
    }

    public function getPropertiesContent(){
        return array(
            array('test_A.properties', '<array> </array>'),
            array('test_B.properties', '<array>{"aaa":"bbb","ccc":""}</array>'),
            array('test_C.properties', '<array>{"aaa":"bbb","ccc":"ddd"}</array>'),
            array('test_D.properties', '<array>{"module.description":"Tests unitaires jelix"}</array>'),
            array('test_E.properties', '<array>{"module.description":"Tests unitaires jelix"}</array>'),
            array('test_F.properties', '<array><string key="module.description" value="Tests unitaires jelix" /></array>'),
            array('test_G.properties', '<array><string key="module.description" value="Tests unitaires jelix" />
                                    <string key="ooo" value="bbbb" />
                                    <string key="bbb" value=" " />
                                    <string key="ddd" value="lorem ipsum &amp;#65; &lt;html&gt; &amp;quote; test &amp;gt;" />
                                    <string key="ee" value=" "/>
                                    <string key="ff" value="  # other"/>
                                    <string key="hh" value="    "/>
                                    <string key="ii" value="   '.utf8_encode(chr(160)).' bidule"/>
                                    <string key="jj" value="truc"/>
                                </array>'),
            array('test_H.properties', '<array><string key="module.description" value="Tests unitaires # jelix" /><string key="ooo" value="bbbb" /></array>'),
            array('test_I.properties', '<array><string key="module.description" value="Tests unitaires # jelix" /><string key="ooo" value="bbbb" /></array>'),
            array('test_J.properties', '<array>
                <string key="text.key" value="bug 639 there shouldn\'t have a notice during the parsing of this property " />
                <string key="text.key2" value="same problem but with spaces at the end of the last line " />
                <string key="text.key3" value="youpa" /></array>'),
        );
    }

    /**
     * @dataProvider getPropertiesContent
     */
    public function testBundle($file, $content){
        try {
            $properties = new Properties();
            $reader = new Parser();
            $reader->parseFromFile($this->filePath.$file, $properties);
            $this->assertComplexIdenticalStr(
                $properties->getAllProperties(),
                "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n$content",
                $file);
        }
        catch(Exception $e){
            self::fail('test failed because of exception : ['.$e->getCode().'] '.$e->getMessage());
        }
    }
    
    function testSimpleLocale(){
        jApp::config()->locale = 'fr_FR';
        $this->assertEquals('ceci est une phrase fr_FR',jLocale::get('tests1.first.locale'));
        $this->assertEquals('ceci est une phrase fr_FR avec tiret',jLocale::get('tests1.first-dash-locale'));
        $this->assertEquals('ceci est une phrase fr_FR',jLocale::get('tests1.first.locale', null, 'fr_FR'));
        $this->assertEquals('ceci est une phrase fr_FR',jLocale::get('tests1.first.locale', null, 'de_DE'));
        $this->assertEquals('Chaîne à tester',jLocale::get('tests1.multiline.locale.with.accent'));
        $this->assertEquals('Chaîne à tester à foison',jLocale::get('tests1.multiline.locale.with.accent2'));
        $this->assertEquals(utf8_decode('Chaîne à tester'),jLocale::get('testunit.multiline.locale.with.accent',null,null,'ISO-8859-1'));
        $this->assertEquals(utf8_decode('Chaîne à tester à foison'),jLocale::get('testunit.multiline.locale.with.accent2',null,null,'ISO-8859-1'));
        $this->assertEquals('ceci est une phrase fr_CA',jLocale::get('tests1.first.locale', null, 'fr_CA'));
        $this->assertEquals('this is an en_US sentence',jLocale::get('tests1.first.locale', null, 'en_US'));
        $this->assertEquals('this is an en_EN sentence',jLocale::get('tests1.first.locale', null, 'en_EN'));
    }

    /**
     *
     */
    function testException() {
        jApp::config()->fallbackLocale = '';
        jApp::config()->locale = 'de_DE';
        try {
            $loc = jLocale::get('tests1.first.locale', null, 'de_DE');
            self::fail('no exception (found: "'.$loc.'")');
        }catch(jException $e) {
            self::fail('wrong exception type');
        }catch(Exception $e) {
            $this->assertEquals('(212)No locale file found for the given locale key "tests1.first.locale" in any other default languages (charset UTF-8)', $e->getMessage());
        }
        jApp::config()->fallbackLocale = 'en_US';
    }

    function testWithNoAskedLocale(){
        jApp::config()->fallbackLocale = '';
        // all this tests are made on an existing locale file
        jApp::config()->locale = 'fr_FR';
        $this->assertEquals('ceci est une phrase 2 fr_FR',jLocale::get('tests1.second.locale'));
        // no test1.second.locale in fr_CA, so we should have the fr_FR one
        //$this->assertEqual('ceci est une phrase 2 fr_FR',jLocale::get('tests1.second.locale', null, 'fr_CA'));

        // no test1.third.locale in fr_FR, so we should have the en_EN one
        jApp::config()->fallbackLocale = 'en_EN';
        $this->assertEquals('this is the 3th en_EN sentence',jLocale::get('tests1.third.locale', null, 'fr_FR'));

        try{
            // it doesn't exist, even in the fallback locale
            jLocale::get('tests1.fourth.locale', null, 'fr_FR');
            self::fail('no exception when trying to get tests1.fourth.locale locale');
        }catch(jException $e){
            self::fail('Bad exception when trying to get tests1.fourth.locale locale');
        }catch(Exception $e){
            $this->assertEquals('(213)The given locale key "jelix_tests~tests1.fourth.locale" does not exists in any default languages for the UTF-8 charset', $e->getMessage());
        }

        jApp::config()->fallbackLocale = '';

        try{
            // it doesn't exist
            jLocale::get('tests1.fourth.locale', null, 'fr_FR');
            self::fail('no exception when trying to get tests1.fourth.locale locale');
        }catch(jException $e){
            self::fail('Bad exception when trying to get tests1.fourth.locale locale');
        }catch(Exception $e){
            $this->assertEquals('(213)The given locale key "jelix_tests~tests1.fourth.locale" does not exists in any default languages for the UTF-8 charset', $e->getMessage());
        }
    }

    function testWithNoAskedLocaleFile(){
        // all this tests are made on an non existing locale file
        jApp::config()->locale = 'fr_FR';
        $this->assertEquals('ceci est une phrase fr_FR test2',jLocale::get('tests2.first.locale'));
        // no test2.properties file for fr_CA, so we should have the fr_FR one
        $this->assertEquals('ceci est une phrase fr_FR test2',jLocale::get('tests2.first.locale', null, 'fr_CA'));
        // no test3.properties file for fr_CA and fr_FR, so we should have the en_EN one
        jApp::config()->fallbackLocale = 'en_EN';
        $this->assertEquals('this is an en_EN sentence test3',jLocale::get('tests3.first.locale', null, 'fr_FR'));

        jApp::config()->fallbackLocale = '';
        try{
            // it doesn't exist
            jLocale::get('jelix_tests~tests3.first.locale', null, 'fr_FR');
            self::fail('no exception when trying to get tests3.first.locale');
        }catch(jException $e){
            self::fail('Bad exception when trying to get tests3.first.locale');
        }catch(Exception $e){
            $this->assertEquals('(212)No locale file found for the given locale key "jelix_tests~tests3.first.locale" in any other default languages (charset UTF-8)', $e->getMessage());
        }
    }

    function testWithBadCharset() {
        jApp::config()->fallbackLocale = '';
        try {
            // this locale exists, but only in UTF-8, not in ISO-8859-1
            jLocale::get('tests1.second.locale', null,'fr_FR','ISO-8859-1');
            self::fail();
        }catch(jException $e){
            self::fail('Bad exception when trying to get tests1.second.locale locale ('.$e->getMessage().')');
        } catch(Exception $e) {
           $this->assertEquals('(212)No locale file found for the given locale key "tests1.second.locale" in any other default languages (charset ISO-8859-1)', $e->getMessage());
        }

        try {
            // this locale don't exists, not in UTF-8 nor in ISO-8859-1
            jLocale::get('tests1.second.locale', null,'de_DE','ISO-8859-1');
            self::fail();
        }catch(jException $e){
            self::fail('Bad exception when trying to get tests1.second.locale locale ('.$e->getMessage().')');
        } catch(Exception $e) {
           $this->assertEquals('(212)No locale file found for the given locale key "tests1.second.locale" in any other default languages (charset ISO-8859-1)', $e->getMessage());
        }

        // ok now, we change to ISO-8859-11 : error message of jelix don't exists in this charset
        // it causes infinite loop in Jelix 1.0.2 and lower.
        jApp::config()->charset = 'ISO-8859-11';
        try {
            // this locale exists, but only in UTF-8, not in ISO-8859-11
            jLocale::get('tests1.second.locale', null,'fr_FR','ISO-8859-11');
            self::fail();
        }catch(jException $e){
            self::fail('Bad exception when trying to get tests1.second.locale locale ('.$e->getMessage().')');
        } catch(Exception $e) {
           $this->assertEquals('(212)No locale file found for the given locale key "tests1.second.locale" in any other default languages (charset ISO-8859-11)', $e->getMessage());
        }
    }

    function testLineBreak(){
        $this->assertEquals("This sentence has a line break\n after the word \"break\"",jLocale::get('tests4.string.with.line.break',null,'en_EN','UTF-8'));
    }

    function testLineBreakWithMultiLineString(){
        $this->assertEquals("This multiline sentence\n has two line breaks\n after the words \"sentence\" and \"breaks\"",jLocale::get('tests4.multiline.string.with.line.break',null,'en_EN','UTF-8'));
    }

    function testOverload(){
        jApp::config()->locale = 'fr_FR';
        $this->assertEquals('bonne valeur overload',jLocale::get('jelix_tests~overload.test'));
    }

    function testNewOverload(){
        jApp::config()->locale = 'fr_FR';
        $this->assertEquals('bonne valeur',jLocale::get('jelix_tests~newoverload.test'));
    }
}

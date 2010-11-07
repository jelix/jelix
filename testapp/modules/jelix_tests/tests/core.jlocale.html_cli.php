<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor Julien Issler, Dominique Papin
* @copyright   2006-2007 Laurent Jouanneau
* @copyright   2008 Julien Issler, 2008 Dominique Papin
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/



class bundleTest extends jBundle {

    public function readProperties($fichier){
        $this->_loadResources($fichier,'UTF-8');
        if(isset($this->_strings['UTF-8']))
            return $this->_strings['UTF-8'];
        else return null;
    }

}

class UTjlocale extends jUnitTestCase {

    protected $filePath;

    public function testBundleUnknownFile(){
        $this->filePath =  JELIX_APP_PATH.'modules/jelix_tests/locales/';
        $b = new bundleTest('','');
        try {
            $b->readProperties($this->filePath.'unknownfile');
            $this->fail('should throw an exception when trying reading unknownfile');
        }catch(Exception $e){
            $this->assertTrue($e->getMessage() == 'Cannot load the resource '.$this->filePath.'unknownfile',
            'should throw the right exception when trying reading unknownfile (wrong message: '.$e->getMessage().')');
        }

    }
    public function testBundle(){
        $firstlist = array(
        'test_A.properties' => '<null> </null>',
        'test_B.properties' => '<array>array("aaa"=>"bbb","ccc"=>"")</array>',
        'test_C.properties' => '<array>array("aaa"=>"bbb","ccc"=>"ddd")</array>',
        'test_D.properties' => '<array>array("module.description"=&gt;"Tests unitaires jelix")</array>',
        'test_E.properties' => '<array>array("module.description"=&gt;"Tests unitaires jelix")</array>',
        'test_F.properties' => '<array><string key="module.description" value="Tests unitaires jelix" /></array>',
        'test_G.properties' => '<array><string key="module.description" value="Tests unitaires jelix" />
                                    <string key="ooo" value="bbbb" />
                                    <string key="bbb" value=" " />
                                    <string key="ddd" value="lorem ipsum &amp;#65; &lt;html&gt; &amp;quote; test &amp;gt;" />
                                    <string key="ee" value=" "/>
                                    <string key="ff" value="  # other"/>
                                    <string key="hh" value="    "/>
                                    <string key="ii" value="   '.utf8_encode(chr(160)).' bidule"/>
                                    <string key="jj" value="truc"/>
                                </array>',
        'test_H.properties' => '<array><string key="module.description" value="Tests unitaires # jelix" /><string key="ooo" value="bbbb" /></array>',
        'test_I.properties' => '<array><string key="module.description" value="Tests unitaires # jelix" /><string key="ooo" value="bbbb" /></array>',
        'test_J.properties' => '<array>
                <string key="text.key" value="bug 639 there shouldn\'t have a notice during the parsing of this property " />
                <string key="text.key2" value="same problem but with spaces at the end of the last line " />
                <string key="text.key3" value="youpa" /></array>',
        );

        foreach($firstlist as $file=>$content){
            $b = new bundleTest('','');
            try{
                $strings = $b->readProperties($this->filePath.$file);
                $this->assertComplexIdenticalStr($strings,"<?xml version=\"1.0\" encoding=\"utf-8\"?>\n$content",$file );
            }catch(Exception $e){
                $this->fail('test failed because of exception : ['.$e->getCode().'] '.$e->getMessage());
            }
        }
    }


    function testSimpleLocale(){
        $GLOBALS['gJConfig']->locale = 'fr_FR';
        $this->assertEqual('ceci est une phrase fr_FR',jLocale::get('tests1.first.locale'));
        $this->assertEqual('ceci est une phrase fr_FR',jLocale::get('tests1.first.locale', null, 'fr_FR'));
        $this->assertEqualOrDiff('Chaîne à tester',jLocale::get('tests1.multiline.locale.with.accent'));
        $this->assertEqualOrDiff('Chaîne à tester à foison',jLocale::get('tests1.multiline.locale.with.accent2'));
        $this->assertEqualOrDiff(utf8_decode('Chaîne à tester'),jLocale::get('testunit.multiline.locale.with.accent',null,null,'ISO-8859-1'));
        $this->assertEqualOrDiff(utf8_decode('Chaîne à tester à foison'),jLocale::get('testunit.multiline.locale.with.accent2',null,null,'ISO-8859-1'));
        $this->assertEqual('ceci est une phrase fr_CA',jLocale::get('tests1.first.locale', null, 'fr_CA'));
        $this->assertEqual('this is an en_US sentence',jLocale::get('tests1.first.locale', null, 'en_US'));
        $this->assertEqual('this is an en_EN sentence',jLocale::get('tests1.first.locale', null, 'en_EN'));
        $GLOBALS['gJConfig']->locale = 'en_EN';
    }

    function testException() {
        $GLOBALS['gJConfig']->locale = 'fr_FR';
        try {
            $loc = jLocale::get('tests1.first.locale', null, 'de_DE');
            $this->fail('no exception');
        }catch(jException $e) {
            $this->fail('wrong exception type');
        }catch(Exception $e) {
            $this->pass();
            $this->assertEqual($e->getMessage(), '(200)The given locale key "tests1.first.locale" is invalid (for charset UTF-8, lang de_DE)');
        }

        $GLOBALS['gJConfig']->locale = 'de_DE';
        try {
            $loc = jLocale::get('tests1.first.locale', null, 'de_DE');
            $this->fail('no exception');
        }catch(jException $e) {
            $this->fail('wrong exception type');
        }catch(Exception $e) {
            $this->pass();
            $this->assertEqual($e->getMessage(), '(200)The given locale key "tests1.first.locale" is invalid (for charset UTF-8, lang de_DE)');
        }
        $GLOBALS['gJConfig']->locale = 'en_EN';
    }

    function testWithNoAskedLocale(){
        // all this tests are made on an existing locale file
        $GLOBALS['gJConfig']->locale = 'fr_FR';
        $this->assertEqual('ceci est une phrase 2 fr_FR',jLocale::get('tests1.second.locale'));
        // no test1.second.locale in fr_CA, so we should have the fr_FR one
        //$this->assertEqual('ceci est une phrase 2 fr_FR',jLocale::get('tests1.second.locale', null, 'fr_CA'));
        // no test1.third.locale in fr_CA, fr_FR, so we should have the en_EN one
        //$GLOBALS['gJConfig']->locale = 'en_EN';
        //$this->assertEqual('this is the 3th en_EN sentence',jLocale::get('tests1.third.locale', null, 'fr_CA'));
        //$this->assertEqual('this is the 3th en_EN sentence',jLocale::get('tests1.third.locale', null, 'fr_FR'));
        //$GLOBALS['gJConfig']->locale = 'fr_FR';
        try{
            jLocale::get('tests1.fourth.locale', null, 'fr_FR');
            $this->fail('no exception when trying to get tests1.fourth.locale locale');
        }catch(jException $e){
            $this->fail('Bad exception when trying to get tests1.fourth.locale locale');
        }catch(Exception $e){
            $this->pass();
            $this->assertEqual('(210)The given locale key "jelix_tests~tests1.fourth.locale" does not exists in the default lang for the UTF-8 charset', $e->getMessage());
        }
        $GLOBALS['gJConfig']->locale = 'en_EN';
    }

    function testWithNoAskedLocaleFile(){
        // all this tests are made on an non existing locale file
        $GLOBALS['gJConfig']->locale = 'fr_FR';
        $this->assertEqual('ceci est une phrase fr_FR test2',jLocale::get('tests2.first.locale'));
        // no test2.properties file for fr_CA, so we should have the fr_FR one
        $this->assertEqual('ceci est une phrase fr_FR test2',jLocale::get('tests2.first.locale', null, 'fr_CA'));
        // no test3.properties file for fr_CA and fr_FR, so we should have the en_EN one
        //$GLOBALS['gJConfig']->locale = 'en_EN';
        //$this->assertEqual('this is an en_EN sentence test3',jLocale::get('tests3.first.locale', null, 'fr_CA'));
        //$this->assertEqual('this is an en_EN sentence test3',jLocale::get('tests3.first.locale', null, 'fr_FR'));
        //$GLOBALS['gJConfig']->locale = 'fr_FR';
        $GLOBALS['gJConfig']->locale = 'en_EN';
    }

    function testWithBadCharset() {

        try {
            // this locale exists, but only in UTF-8, not in ISO-8859-1
            jLocale::get('tests1.second.locale', null,'fr_FR','ISO-8859-1');
            $this->fail();
        }catch(jException $e){
            $this->fail('Bad exception when trying to get tests1.second.locale locale ('.$e->getMessage().')');
        } catch(Exception $e) {
           $this->pass();
           $this->assertEqual('(200)The given locale key "tests1.second.locale" is invalid (for charset ISO-8859-1, lang fr_FR)', $e->getMessage());
        }

        try {
            // this locale don't exists, not in UTF-8 nor in ISO-8859-1
            jLocale::get('tests1.second.locale', null,'de_DE','ISO-8859-1');
            $this->fail();
        }catch(jException $e){
            $this->fail('Bad exception when trying to get tests1.second.locale locale ('.$e->getMessage().')');
        } catch(Exception $e) {
           $this->pass();
           $this->assertEqual('(200)The given locale key "tests1.second.locale" is invalid (for charset ISO-8859-1, lang de_DE)', $e->getMessage());
        }

        // ok now, we change to ISO-8859-11 : error message of jelix don't exists in this charset
        // it causes infinite loop in Jelix 1.0.2 and lower.
        $GLOBALS['gJConfig']->charset = 'ISO-8859-11';
        try {
            // this locale exists, but only in UTF-8, not in ISO-8859-11
            jLocale::get('tests1.second.locale', null,'fr_FR','ISO-8859-11');
            $this->fail();
        }catch(jException $e){
            $this->fail('Bad exception when trying to get tests1.second.locale locale ('.$e->getMessage().')');
        } catch(Exception $e) {
           $this->pass();
           $this->assertEqual('(200)The given locale key "tests1.second.locale" is invalid (for charset ISO-8859-11, lang fr_FR)', $e->getMessage());
        }
        $GLOBALS['gJConfig']->charset = 'UTF-8';
    }

    function testLineBreak(){
        $this->assertEqual("This sentence has a line break\n after the word \"break\"",jLocale::get('tests4.string.with.line.break',null,'en_EN','UTF-8'));
    }

    function testLineBreakWithMultiLineString(){
        $this->assertEqual("This multiline sentence\n has two line breaks\n after the words \"sentence\" and \"breaks\"",jLocale::get('tests4.multiline.string.with.line.break',null,'en_EN','UTF-8'));
    }

}

?>
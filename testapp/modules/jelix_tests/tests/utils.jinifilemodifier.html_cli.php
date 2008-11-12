<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Jouanneau Laurent
* @contributor
* @copyright   2008 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class testIniFileModifier extends jIniFileModifier {

    function __construct($filename) {
      if($filename !='') parent::__construct($filename);
    }

    function testParse($content) {
       $this->parse(explode("\n", $content));
    }
    function getContent() {
       return $this->content;
    }
    
    function generate(){ return $this->generateIni(); }

}


class UTjIniFileModifier extends jUnitTestCase {

    public function testParseFile(){
        $parser = new testIniFileModifier('');
        $content ='foo=bar';
        $expected=array(
            0 => array(
                    array(jIniFileModifier::TK_VALUE, 'foo','bar'),
                 ),
        );
        $parser->testParse($content);
        $this->assertEqual($parser->getContent(), $expected);

        $content ='
  ; a comment
  
foo=bar
';
        $expected=array(
            0 => array(
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_COMMENT, "  ; a comment"),
                array(jIniFileModifier::TK_WS, "  "),
                array(jIniFileModifier::TK_VALUE, 'foo','bar'),
                array(jIniFileModifier::TK_WS, ""),
            ),
        );

        $parser->testParse($content);
        $this->assertEqual($parser->getContent(), $expected);

        $content ='
  ; a comment
  
foo=bar

[aSection]
truc=machin
';
        $expected=array(
            0 => array(
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_COMMENT, "  ; a comment"),
                array(jIniFileModifier::TK_WS, "  "),
                array(jIniFileModifier::TK_VALUE, 'foo','bar'),
                array(jIniFileModifier::TK_WS, ""),
            ),
            'aSection'=>array(
                array(jIniFileModifier::TK_SECTION, "[aSection]"),
                array(jIniFileModifier::TK_VALUE, 'truc','machin'),
                array(jIniFileModifier::TK_WS, ""),
            ),
        );

        $parser->testParse($content);
        $this->assertEqual($parser->getContent(), $expected);

        $content ='
  ; a comment
  
foo=bar

[aSection]
truc=machin

[ot:her@section]
truc=machin2

';
        $expected=array(
            0 => array(
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_COMMENT, "  ; a comment"),
                array(jIniFileModifier::TK_WS, "  "),
                array(jIniFileModifier::TK_VALUE, 'foo','bar'),
                array(jIniFileModifier::TK_WS, ""),
            ),
            'aSection'=>array(
                array(jIniFileModifier::TK_SECTION, "[aSection]"),
                array(jIniFileModifier::TK_VALUE, 'truc','machin'),
                array(jIniFileModifier::TK_WS, ""),
            ),
            'ot:her@section'=>array(
                array(jIniFileModifier::TK_SECTION, "[ot:her@section]"),
                array(jIniFileModifier::TK_VALUE, 'truc','machin2'),
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_WS, ""),
            ),
        );

        $parser->testParse($content);
        $this->assertEqual($parser->getContent(), $expected);


        $content ='
foo[]=bar
example=1
foo[]=machine
';
        $expected=array(
            0 => array(
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_ARR_VALUE, 'foo','bar',''),
                 array(jIniFileModifier::TK_VALUE, 'example','1'),
                array(jIniFileModifier::TK_ARR_VALUE, 'foo','machine',''),
                array(jIniFileModifier::TK_WS, ""),
            ),
        );

        $parser->testParse($content);
        $this->assertEqual($parser->getContent(), $expected);
    }

    function testSetValue() {
        $parser = new testIniFileModifier('');
        $content = '
  ; a comment
  
foo=bar

[aSection]
truc=machin

[othersection]
truc=machin2

';
        $expected=array(
            0 => array(
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_COMMENT, "  ; a comment"),
                array(jIniFileModifier::TK_WS, "  "),
                array(jIniFileModifier::TK_VALUE, 'foo','bar'),
                array(jIniFileModifier::TK_WS, ""),
            ),
            'aSection'=>array(
                array(jIniFileModifier::TK_SECTION, "[aSection]"),
                array(jIniFileModifier::TK_VALUE, 'truc','machin'),
                array(jIniFileModifier::TK_WS, ""),
            ),
            'othersection'=>array(
                array(jIniFileModifier::TK_SECTION, "[othersection]"),
                array(jIniFileModifier::TK_VALUE, 'truc','machin2'),
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_WS, ""),
            ),
        );

        $parser->testParse($content);
        $this->assertEqual($parser->getContent(), $expected);

        $parser->setValue('foo','hello');
        $expected=array(
            0 => array(
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_COMMENT, "  ; a comment"),
                array(jIniFileModifier::TK_WS, "  "),
                array(jIniFileModifier::TK_VALUE, 'foo','hello'),
                array(jIniFileModifier::TK_WS, ""),
            ),
            'aSection'=>array(
                array(jIniFileModifier::TK_SECTION, "[aSection]"),
                array(jIniFileModifier::TK_VALUE, 'truc','machin'),
                array(jIniFileModifier::TK_WS, ""),
            ),
            'othersection'=>array(
                array(jIniFileModifier::TK_SECTION, "[othersection]"),
                array(jIniFileModifier::TK_VALUE, 'truc','machin2'),
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_WS, ""),
            ),
        );
        $this->assertEqual($parser->getContent(), $expected);

        $parser->setValue('truc','bidule', 'aSection');
        $expected=array(
            0 => array(
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_COMMENT, "  ; a comment"),
                array(jIniFileModifier::TK_WS, "  "),
                array(jIniFileModifier::TK_VALUE, 'foo','hello'),
                array(jIniFileModifier::TK_WS, ""),
            ),
            'aSection'=>array(
                array(jIniFileModifier::TK_SECTION, "[aSection]"),
                array(jIniFileModifier::TK_VALUE, 'truc','bidule'),
                array(jIniFileModifier::TK_WS, ""),
            ),
            'othersection'=>array(
                array(jIniFileModifier::TK_SECTION, "[othersection]"),
                array(jIniFileModifier::TK_VALUE, 'truc','machin2'),
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_WS, ""),
            ),
        );
        $this->assertEqual($parser->getContent(), $expected);

        $parser->setValue('truc','bidule2', 'othersection');
        $expected=array(
            0 => array(
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_COMMENT, "  ; a comment"),
                array(jIniFileModifier::TK_WS, "  "),
                array(jIniFileModifier::TK_VALUE, 'foo','hello'),
                array(jIniFileModifier::TK_WS, ""),
            ),
            'aSection'=>array(
                array(jIniFileModifier::TK_SECTION, "[aSection]"),
                array(jIniFileModifier::TK_VALUE, 'truc','bidule'),
                array(jIniFileModifier::TK_WS, ""),
            ),
            'othersection'=>array(
                array(jIniFileModifier::TK_SECTION, "[othersection]"),
                array(jIniFileModifier::TK_VALUE, 'truc','bidule2'),
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_WS, ""),
            ),
        );
        $this->assertEqual($parser->getContent(), $expected);

        $parser->setValue('name','toto', 'othersection');
        $expected=array(
            0 => array(
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_COMMENT, "  ; a comment"),
                array(jIniFileModifier::TK_WS, "  "),
                array(jIniFileModifier::TK_VALUE, 'foo','hello'),
                array(jIniFileModifier::TK_WS, ""),
            ),
            'aSection'=>array(
                array(jIniFileModifier::TK_SECTION, "[aSection]"),
                array(jIniFileModifier::TK_VALUE, 'truc','bidule'),
                array(jIniFileModifier::TK_WS, ""),
            ),
            'othersection'=>array(
                array(jIniFileModifier::TK_SECTION, "[othersection]"),
                array(jIniFileModifier::TK_VALUE, 'truc','bidule2'),
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_VALUE, 'name','toto'),
            ),
        );
        $this->assertEqual($parser->getContent(), $expected);

        $parser->setValue('name','toto', 'othersection','');
        $expected=array(
            0 => array(
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_COMMENT, "  ; a comment"),
                array(jIniFileModifier::TK_WS, "  "),
                array(jIniFileModifier::TK_VALUE, 'foo','hello'),
                array(jIniFileModifier::TK_WS, ""),
            ),
            'aSection'=>array(
                array(jIniFileModifier::TK_SECTION, "[aSection]"),
                array(jIniFileModifier::TK_VALUE, 'truc','bidule'),
                array(jIniFileModifier::TK_WS, ""),
            ),
            'othersection'=>array(
                array(jIniFileModifier::TK_SECTION, "[othersection]"),
                array(jIniFileModifier::TK_VALUE, 'truc','bidule2'),
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_ARR_VALUE, 'name','toto',''),
            ),
        );
        $this->assertEqual($parser->getContent(), $expected);
        //$this->dump($parser->getContent());
        $parser->setValue('theme','blue', 'aSection','0');
        $expected=array(
            0 => array(
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_COMMENT, "  ; a comment"),
                array(jIniFileModifier::TK_WS, "  "),
                array(jIniFileModifier::TK_VALUE, 'foo','hello'),
                array(jIniFileModifier::TK_WS, ""),
            ),
            'aSection'=>array(
                array(jIniFileModifier::TK_SECTION, "[aSection]"),
                array(jIniFileModifier::TK_VALUE, 'truc','bidule'),
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_ARR_VALUE, 'theme','blue','0'),
            ),
            'othersection'=>array(
                array(jIniFileModifier::TK_SECTION, "[othersection]"),
                array(jIniFileModifier::TK_VALUE, 'truc','bidule2'),
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_ARR_VALUE, 'name','toto',''),
            ),
        );
        $this->assertEqual($parser->getContent(), $expected);


        $content ='
foo[]=bar
example=1
foo[]=machine
';
        $expected=array(
            0 => array(
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_ARR_VALUE, 'foo','bar',''),
                 array(jIniFileModifier::TK_VALUE, 'example','1'),
                array(jIniFileModifier::TK_ARR_VALUE, 'foo','machine',''),
                array(jIniFileModifier::TK_WS, ""),
            ),
        );

        $parser->testParse($content);
        $this->assertEqual($parser->getContent(), $expected);

        $parser->setValue('theme','blue', 'aSection','0');
        $expected=array(
            0 => array(
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_ARR_VALUE, 'foo','bar',''),
                array(jIniFileModifier::TK_VALUE, 'example','1'),
                array(jIniFileModifier::TK_ARR_VALUE, 'foo','machine',''),
                array(jIniFileModifier::TK_WS, ""),
            ),
            'aSection'=>array(
                array(jIniFileModifier::TK_SECTION, "[aSection]"),
                array(jIniFileModifier::TK_ARR_VALUE, 'theme','blue','0'),
            ),
        );
        $this->assertEqual($parser->getContent(), $expected);

        $parser->setValue('foo','button');
        $expected=array(
            0 => array(
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_VALUE, 'foo','button'),
                array(jIniFileModifier::TK_VALUE, 'example','1'),
                array(jIniFileModifier::TK_WS,''),
                array(jIniFileModifier::TK_WS, ""),
            ),
            'aSection'=>array(
                array(jIniFileModifier::TK_SECTION, "[aSection]"),
                array(jIniFileModifier::TK_ARR_VALUE, 'theme','blue','0'),
            ),
        );
        $this->assertEqual($parser->getContent(), $expected);
    }
    
    
    function testGetValue() {
            $parser = new testIniFileModifier('');
        $content = '
  ; a comment
  
foo=bar
anumber=98
afloatnumber=   5.098  
[aSection]
truc= true
laurent=toto
isvalid = on

[othersection]
truc=machin2

';
        $parser->testParse($content);
        $this->assertEqual($parser->getValue('foo'), 'bar' );
        $this->assertEqual($parser->getValue('anumber'), 98 );
        $this->assertEqual($parser->getValue('afloatnumber'), 5.098 );
        $this->assertEqual($parser->getValue('truc','aSection'), true );
        $this->assertEqual($parser->getValue('laurent','aSection'), 'toto' );
        $this->assertEqual($parser->getValue('isvalid','aSection'), true );
    }
    
    function testSave() {
            $parser = new testIniFileModifier('');
        $content = '
  ; a comment
  
foo=bar
anumber=98
afloatnumber=   5.098  
[aSection]
truc= true
laurent=toto
isvalid = on

[othersection]
truc=machin2

';
        $result = '
  ; a comment
  
foo=bar
anumber=98
afloatnumber=5.098  
[aSection]
truc=true
laurent=toto
isvalid=on

[othersection]
truc=machin2


';
        $parser->testParse($content);
        $this->assertEqualOrDiff($parser->generate(), $result );

        file_put_contents(JELIX_APP_TEMP_PATH.'test_jinifilemodifier.html_cli.php', $content);
        $parser = new testIniFileModifier(JELIX_APP_TEMP_PATH.'test_jinifilemodifier.html_cli.php');
        $this->assertEqualOrDiff($parser->generate(), $result );
        
        $content = str_replace("\n", "\r", $content);
        file_put_contents(JELIX_APP_TEMP_PATH.'test_jinifilemodifier.html_cli.php', $content);
        $parser = new testIniFileModifier(JELIX_APP_TEMP_PATH.'test_jinifilemodifier.html_cli.php');
        $this->assertEqualOrDiff($parser->generate(), $result );
        
        $content = str_replace("\r", "\r\n", $content);
        file_put_contents(JELIX_APP_TEMP_PATH.'test_jinifilemodifier.html_cli.php', $content);
        $parser = new testIniFileModifier(JELIX_APP_TEMP_PATH.'test_jinifilemodifier.html_cli.php');
        $this->assertEqualOrDiff($parser->generate(), $result );

    }
}

?>
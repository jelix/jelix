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
                array(jIniFileModifier::TK_ARR_VALUE, 'foo','bar',0),
                 array(jIniFileModifier::TK_VALUE, 'example','1'),
                array(jIniFileModifier::TK_ARR_VALUE, 'foo','machine',1),
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
                array(jIniFileModifier::TK_ARR_VALUE, 'name','toto',0),
            ),
        );
        $this->assertEqual($parser->getContent(), $expected);
        //$this->dump($parser->getContent());
        $parser->setValue('theme','blue', 'aSection',0);
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
                array(jIniFileModifier::TK_ARR_VALUE, 'theme','blue', 0),
            ),
            'othersection'=>array(
                array(jIniFileModifier::TK_SECTION, "[othersection]"),
                array(jIniFileModifier::TK_VALUE, 'truc','bidule2'),
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_ARR_VALUE, 'name','toto', 0),
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
                array(jIniFileModifier::TK_ARR_VALUE, 'foo','bar', 0),
                 array(jIniFileModifier::TK_VALUE, 'example','1'),
                array(jIniFileModifier::TK_ARR_VALUE, 'foo','machine',1),
                array(jIniFileModifier::TK_WS, ""),
            ),
        );

        $parser->testParse($content);
        $this->assertEqual($parser->getContent(), $expected);

        $parser->setValue('theme','blue', 'aSection','0');
        $expected=array(
            0 => array(
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_ARR_VALUE, 'foo','bar',0),
                array(jIniFileModifier::TK_VALUE, 'example','1'),
                array(jIniFileModifier::TK_ARR_VALUE, 'foo','machine',1),
                array(jIniFileModifier::TK_WS, ""),
            ),
            'aSection'=>array(
                array(jIniFileModifier::TK_SECTION, "[aSection]"),
                array(jIniFileModifier::TK_ARR_VALUE, 'theme','blue',0),
            ),
        );
        $this->assertEqual($parser->getContent(), $expected);

        $parser->setValue('foo','button');
        $expected=array(
            0 => array(
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_VALUE, 'foo','button'),
                array(jIniFileModifier::TK_VALUE, 'example','1'),
                array(jIniFileModifier::TK_WS,'--'),
                array(jIniFileModifier::TK_WS, ""),
            ),
            'aSection'=>array(
                array(jIniFileModifier::TK_SECTION, "[aSection]"),
                array(jIniFileModifier::TK_ARR_VALUE, 'theme','blue',0),
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
string= "uuuuu"
string2= "aaa
bbb"
afloatnumber=   5.098  

[aSection]
truc= true
laurent=toto
isvalid = on

[othersection]
truc=machin2

[vla]
foo[]=aaa
foo[]=bbb
foo[]=ccc

';
        $parser->testParse($content);
        $this->assertEqual($parser->getValue('foo'), 'bar' );
        $this->assertEqual($parser->getValue('anumber'), 98 );
        $this->assertEqual($parser->getValue('string'), 'uuuuu' );
        $this->assertEqual($parser->getValue('string2'), 'aaa
bbb');
        $this->assertEqual($parser->getValue('afloatnumber'), 5.098 );
        $this->assertEqual($parser->getValue('truc','aSection'), true );
        $this->assertEqual($parser->getValue('laurent','aSection'), 'toto' );
        $this->assertEqual($parser->getValue('isvalid','aSection'), true );
        $this->assertEqual($parser->getValue('foo','vla',2), 'ccc' );
        
    }
    
    function testSave() {
            $parser = new testIniFileModifier('');
        $content = '
  ; a comment
  
foo=bar
job= foo.b-a_r
messageLogFormat = "%date%\t[%code%]\t%msg%\t%file%\t%line%\n"
anumber=98
afloatnumber=   5.098  
[aSection]
truc= true
laurent=toto
isvalid = on

[othersection]
truc=machin2

[vla]
foo[]=aaa
foo[]=bbb
foo[]=ccc

';
        $result = '
  ; a comment
  
foo=bar
job=foo.b-a_r
messageLogFormat="%date%\t[%code%]\t%msg%\t%file%\t%line%\n"
anumber=98
afloatnumber=5.098  
[aSection]
truc=true
laurent=toto
isvalid=on

[othersection]
truc=machin2

[vla]
foo[]=aaa
foo[]=bbb
foo[]=ccc


';
        $parser->testParse($content);
        $this->assertEqualOrDiff($result, $parser->generate() );

        file_put_contents(JELIX_APP_TEMP_PATH.'test_jinifilemodifier.html_cli.php', $content);
        $parser = new testIniFileModifier(JELIX_APP_TEMP_PATH.'test_jinifilemodifier.html_cli.php');
        $this->assertEqualOrDiff($result, $parser->generate() );
        
        $content = str_replace("\n", "\r", $content);
        file_put_contents(JELIX_APP_TEMP_PATH.'test_jinifilemodifier.html_cli.php', $content);
        $parser = new testIniFileModifier(JELIX_APP_TEMP_PATH.'test_jinifilemodifier.html_cli.php');
        $this->assertEqualOrDiff($result, $parser->generate() );
        
        $content = str_replace("\r", "\r\n", $content);
        file_put_contents(JELIX_APP_TEMP_PATH.'test_jinifilemodifier.html_cli.php', $content);
        $parser = new testIniFileModifier(JELIX_APP_TEMP_PATH.'test_jinifilemodifier.html_cli.php');
        $this->assertEqualOrDiff($result, $parser->generate());

    }
    
    
    function testRemove() {
        $parser = new testIniFileModifier('');
        $content = '
  ; a comment
  
foo=bar
;bla bla
anumber=98
string= "uuuuu"
string2= "aaa
bbb"
afloatnumber=   5.098  

[aSection]
truc= true
laurent=toto
isvalid = on

[othersection]
truc=machin2

[vla]
foo[]=aaa
foo[]=bbb
foo[]=ccc


';
        $parser->testParse($content);
        $parser->removeValue('anumber', 0, null, false);
        $this->assertNull($parser->getValue('anumber'));
        
        $parser->removeValue('laurent','aSection', null, false);
        $this->assertNull($parser->getValue('laurent','aSection'));

        $parser->removeValue('foo','vla', 1, false);
        $this->assertNull($parser->getValue('foo','vla', 1));

        $parser->removeValue('', 'aSection', null, false);
        $this->assertNull($parser->getValue('truc','aSection'));
        $this->assertEqual($parser->getSectionList(), array('othersection', 'vla'));

$result = '
  ; a comment
  
foo=bar
;bla bla
string=uuuuu
string2="aaa
bbb"
afloatnumber=5.098  

[othersection]
truc=machin2

[vla]
foo[]=aaa
foo[]=ccc



';
        $this->assertEqualOrDiff($result, $parser->generate());
    }

    function testRemoveWithComment() {
        $parser = new testIniFileModifier('');
        $content = '
  ; a comment <?php die()
  
foo=bar
anumber=98
string= "uuuuu"
string2= "aaa
bbb"
afloatnumber=   5.098  

; section comment
[aSection]
truc= true

; a comment

laurent=toto
isvalid = on

; super section
[othersection]
truc=machin2

[vla]
foo[]=aaa
; key comment
foo[]=bbb
foo[]=ccc


';
        $parser->testParse($content);
        $parser->removeValue('anumber', 0, null, true);
        $parser->removeValue('laurent','aSection', null, true);
        $parser->removeValue('foo','vla', 1, true);
        $parser->removeValue('', 'othersection', null, true);
        $parser->removeValue('foo',0, null, true);

$result = '
  ; a comment <?php die()
  
string=uuuuu
string2="aaa
bbb"
afloatnumber=5.098  

; section comment
[aSection]
truc=true


isvalid=on

[vla]
foo[]=aaa
foo[]=ccc



';
        $this->assertEqualOrDiff($result, $parser->generate());





        $parser = new testIniFileModifier('');
        $content = '
string=uuuuu

; bla bla

; bli bli
;blo blo

string2=aaa
afloatnumber=5.098  

';
        $parser->testParse($content);
        $parser->removeValue('string2', 0, null, true);

$result = '
string=uuuuu

; bla bla


afloatnumber=5.098  


';
        $this->assertEqualOrDiff($result, $parser->generate());

    }

}

?>
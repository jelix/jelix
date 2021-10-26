<?php
/**
* @package     testapp
* @subpackage  testsjelix
* @author      Laurent Jouanneau
* @contributor
* @copyright   2008-2012 Laurent Jouanneau
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


class jIniFileModifierTest extends PHPUnit_Framework_TestCase {

    public function testParseFile(){
        $parser = new testIniFileModifier('');
        $content ='foo=bar';
        $expected=array(
            0 => array(
                    array(jIniFileModifier::TK_VALUE, 'foo','bar'),
                 ),
        );
        $parser->testParse($content);
        $this->assertEquals($expected, $parser->getContent());

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
        $this->assertEquals($expected, $parser->getContent());

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
        $this->assertEquals($expected, $parser->getContent());

        $content ='
  ; a comment
  
foo=bar

[aSection]
truc=machin

[ot:her@sec-tion]
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
            'ot:her@sec-tion'=>array(
                array(jIniFileModifier::TK_SECTION, "[ot:her@sec-tion]"),
                array(jIniFileModifier::TK_VALUE, 'truc','machin2'),
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_WS, ""),
            ),
        );

        $parser->testParse($content);
        $this->assertEquals($expected, $parser->getContent());


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
        $this->assertEquals($expected, $parser->getContent());
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
        $this->assertEquals($expected, $parser->getContent());

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
        $this->assertEquals($expected, $parser->getContent());

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
        $this->assertEquals($expected, $parser->getContent());

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
        $this->assertEquals($expected, $parser->getContent());

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
        $this->assertEquals($expected, $parser->getContent());

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
        $this->assertEquals($expected, $parser->getContent());
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
        $this->assertEquals($expected, $parser->getContent());


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
        $this->assertEquals($expected, $parser->getContent());

        $parser->setValue('foo','bla', 0,'');
        $expected=array(
            0 => array(
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_ARR_VALUE, 'foo','bar', 0),
                array(jIniFileModifier::TK_VALUE, 'example','1'),
                array(jIniFileModifier::TK_ARR_VALUE, 'foo','machine',1),
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_ARR_VALUE, 'foo','bla',2),
            ),
        );
        $this->assertEquals($expected, $parser->getContent());

        $parser->setValue('theme','blue', 'aSection','0');
        $expected=array(
            0 => array(
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_ARR_VALUE, 'foo','bar',0),
                array(jIniFileModifier::TK_VALUE, 'example','1'),
                array(jIniFileModifier::TK_ARR_VALUE, 'foo','machine',1),
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_ARR_VALUE, 'foo','bla',2),
            ),
            'aSection'=>array(
                array(jIniFileModifier::TK_SECTION, "[aSection]"),
                array(jIniFileModifier::TK_ARR_VALUE, 'theme','blue',0),
            ),
        );
        $this->assertEquals($expected, $parser->getContent());

        $parser->setValue('foo','button');
        $expected=array(
            0 => array(
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_VALUE, 'foo','button'),
                array(jIniFileModifier::TK_VALUE, 'example','1'),
                array(jIniFileModifier::TK_WS,'--'),
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_WS,'--'),
            ),
            'aSection'=>array(
                array(jIniFileModifier::TK_SECTION, "[aSection]"),
                array(jIniFileModifier::TK_ARR_VALUE, 'theme','blue',0),
            ),
        );
        $this->assertEquals($expected, $parser->getContent());

        $parser->setValue('assocarray', array('hello'=>'world', 'james' => 'bond'));
        $expected=array(
            0 => array(
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_VALUE, 'foo','button'),
                array(jIniFileModifier::TK_VALUE, 'example','1'),
                array(jIniFileModifier::TK_WS,'--'),
                array(jIniFileModifier::TK_WS, ""),
                array(jIniFileModifier::TK_WS,'--'),
                array(jIniFileModifier::TK_ARR_VALUE, 'assocarray','world','hello'),
                array(jIniFileModifier::TK_ARR_VALUE, 'assocarray','bond','james'),
            ),
            'aSection'=>array(
                array(jIniFileModifier::TK_SECTION, "[aSection]"),
                array(jIniFileModifier::TK_ARR_VALUE, 'theme','blue',0),
            ),
        );
        $this->assertEquals($expected, $parser->getContent());
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
string3= "aaa
  multiline
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
        $this->assertEquals($parser->getValue('foo'), 'bar' );
        $this->assertEquals($parser->getValue('anumber'), 98 );
        $this->assertEquals($parser->getValue('string'), 'uuuuu' );
        $this->assertEquals($parser->getValue('string2'), 'aaa
bbb');
        $this->assertEquals($parser->getValue('string3'), 'aaa
  multiline
bbb');
        $this->assertEquals($parser->getValue('afloatnumber'), 5.098 );
        $this->assertEquals($parser->getValue('truc','aSection'), true );
        $this->assertEquals($parser->getValue('laurent','aSection'), 'toto' );
        $this->assertEquals($parser->getValue('isvalid','aSection'), true );
        $this->assertEquals($parser->getValue('foo','vla',2), 'ccc' );
        $this->assertEquals($parser->getValue('foo','vla'), array('aaa', 'bbb', 'ccc'));
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
isnotvalid = off

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
isnotvalid=off

[othersection]
truc=machin2

[vla]
foo[]=aaa
foo[]=bbb
foo[]=ccc

';
        $parser->testParse($content);
        $parser->setValue('isnotvalid', false, 'aSection');
        $this->assertEquals($result, $parser->generate() );

        file_put_contents(jApp::tempPath().'test_jinifilemodifier.html_cli.php', $content);
        $parser = new testIniFileModifier(jApp::tempPath().'test_jinifilemodifier.html_cli.php');
        $this->assertEquals($result, $parser->generate() );
        
        $content = str_replace("\n", "\r", $content);
        file_put_contents(jApp::tempPath().'test_jinifilemodifier.html_cli.php', $content);
        $parser = new testIniFileModifier(jApp::tempPath().'test_jinifilemodifier.html_cli.php');
        $this->assertEquals($result, $parser->generate() );
        
        $content = str_replace("\r", "\r\n", $content);
        file_put_contents(jApp::tempPath().'test_jinifilemodifier.html_cli.php', $content);
        $parser = new testIniFileModifier(jApp::tempPath().'test_jinifilemodifier.html_cli.php');
        $this->assertEquals($result, $parser->generate());

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
        $this->assertEquals($parser->getSectionList(), array('othersection', 'vla'));

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
        $this->assertEquals($result, $parser->generate());
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
        $this->assertEquals($result, $parser->generate());





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
        $this->assertEquals($result, $parser->generate());

    }


    function testImport() {
        $ini = new testIniFileModifier('');
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
        $ini->testParse($content);

        $ini2 = new testIniFileModifier('');
        $content2 = '

; my comment
toto = truc
;bla
anumber=100

; section comment
[aSection]

newlaurent=hello
; a new comment
isvalid = on
truc= false

supercar=ferrari

[newsection]
truc=machin2

foo[]=aaa
; key comment
foo[]=bbb
foo[]=ccc


';
        $ini2->testParse($content2);

        $ini->import($ini2);


$result = '
  ; a comment <?php die()
  
foo=bar
anumber=100
string=uuuuu
string2="aaa
bbb"
afloatnumber=5.098


; my comment
toto=truc

; section comment
[aSection]
truc=false

; a comment

laurent=toto
isvalid=on

newlaurent=hello

supercar=ferrari

; super section
[othersection]
truc=machin2

[vla]
foo[]=aaa
; key comment
foo[]=bbb
foo[]=ccc



[newsection]
truc=machin2

foo[]=aaa
; key comment
foo[]=bbb
foo[]=ccc


';
        $this->assertEquals($result, $ini->generate());

    }



    function testImportRename() {
        $ini = new testIniFileModifier('');
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
[blob_thesection]
truc=machin2
bidule = 1
[vla]
foo[]=aaa
; key comment
foo[]=bbb
foo[]=ccc


';
        $ini->testParse($content);

        $ini2 = new testIniFileModifier('');
        $content2 = '

; my comment
toto = truc
;bla
anumber=100

; section comment
[mySection]

newlaurent=hello
; a new comment
isvalid = on
truc= false

supercar=ferrari

[thesection]
truc=machin3
truck=on



';
        $ini2->testParse($content2);

        $ini->import($ini2, 'blob');


$result = '
  ; a comment <?php die()
  
foo=bar
anumber=98
string=uuuuu
string2="aaa
bbb"
afloatnumber=5.098

; section comment
[aSection]
truc=true

; a comment

laurent=toto
isvalid=on

; super section
[blob_thesection]
truc=machin3
bidule=1
truck=on
[vla]
foo[]=aaa
; key comment
foo[]=bbb
foo[]=ccc



[blob]


; my comment
toto=truc
;bla
anumber=100

; section comment
[blob_mySection]

newlaurent=hello
; a new comment
isvalid=on
truc=false

supercar=ferrari
';
        $this->assertEquals($result, $ini->generate());



        $ini = new testIniFileModifier('');
        $ini->testParse($content);

        $ini2 = new testIniFileModifier('');
        $ini2->testParse($content2);

        $ini->import($ini2, 'blob', ':');
$result = '
  ; a comment <?php die()
  
foo=bar
anumber=98
string=uuuuu
string2="aaa
bbb"
afloatnumber=5.098

; section comment
[aSection]
truc=true

; a comment

laurent=toto
isvalid=on

; super section
[blob_thesection]
truc=machin2
bidule=1
[vla]
foo[]=aaa
; key comment
foo[]=bbb
foo[]=ccc



[blob]


; my comment
toto=truc
;bla
anumber=100

; section comment
[blob:mySection]

newlaurent=hello
; a new comment
isvalid=on
truc=false

supercar=ferrari

[blob:thesection]
truc=machin3
truck=on



';
        $this->assertEquals($result, $ini->generate());

    }

    public function testRenameSection() {
        $ini = new testIniFileModifier('');
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
[thesection]
truc=machin2
bidule = 1
[vla]
foo[]=aaa
; key comment
foo[]=bbb
foo[]=ccc


';
        $ini->testParse($content);
        $ini->renameValue('string', 'vuvuzela');
        $ini->renameSection('aSection', 'beautiful');
        $result = '
  ; a comment <?php die()
  
foo=bar
anumber=98
vuvuzela=uuuuu
string2="aaa
bbb"
afloatnumber=5.098

; section comment
[beautiful]
truc=true

; a comment

laurent=toto
isvalid=on

; super section
[thesection]
truc=machin2
bidule=1
[vla]
foo[]=aaa
; key comment
foo[]=bbb
foo[]=ccc


';
        $this->assertEquals($result, $ini->generate());

        $ini->renameSection('0', 'zipo');
        $result = '[zipo]

  ; a comment <?php die()
  
foo=bar
anumber=98
vuvuzela=uuuuu
string2="aaa
bbb"
afloatnumber=5.098

; section comment
[beautiful]
truc=true

; a comment

laurent=toto
isvalid=on

; super section
[thesection]
truc=machin2
bidule=1
[vla]
foo[]=aaa
; key comment
foo[]=bbb
foo[]=ccc


';
        $this->assertEquals($result, $ini->generate());

        $ini->renameValue('truc', 'system', 'thesection');

        $result = '[zipo]

  ; a comment <?php die()
  
foo=bar
anumber=98
vuvuzela=uuuuu
string2="aaa
bbb"
afloatnumber=5.098

; section comment
[beautiful]
truc=true

; a comment

laurent=toto
isvalid=on

; super section
[thesection]
system=machin2
bidule=1
[vla]
foo[]=aaa
; key comment
foo[]=bbb
foo[]=ccc


';
        $this->assertEquals($result, $ini->generate());
    }

    
    public function testSetValues() {
        $content = '
; a comment <?php die()
  
foo=bar

; section comment
[aSection]
truc=true

; super section
[the_section]
foo= z
';
        $ini = new testIniFileModifier('');
        $ini->testParse($content);
        $ini->setValues(array('truc'=>'machin', 'bidule'=>1, 'truck'=>true, 'foo'=>array('aaa', 'bbb', 'ccc')), 'the_section');
        $expected = '
; a comment <?php die()
  
foo=bar

; section comment
[aSection]
truc=true

; super section
[the_section]

truc=machin
bidule=1
truck=on
foo[]=aaa
foo[]=bbb
foo[]=ccc
';
        $this->assertEquals($expected, $ini->generate());
    }

    public function testGetValues() {
        $content = '
; a comment <?php die()
  
foo=bar

; section comment
[aSection]
truc=true

; super section
[the_section]
truc=machin
bidule=1
truck=on
foo[]=aaa
; key comment
foo[]=bbb
foo[]=ccc
';

        $ini = new testIniFileModifier('');
        $ini->testParse($content);

        $values = $ini->getValues('the_section');
        $expected = array('truc'=>'machin', 'bidule'=>1, 'truck'=>true, 'foo'=>array('aaa', 'bbb', 'ccc'));
        $this->assertEquals($expected, $values);

        $values = $ini->getValues(0);
        $expected = array('foo'=>'bar');
        $this->assertEquals($expected, $values);
    }

}

?>

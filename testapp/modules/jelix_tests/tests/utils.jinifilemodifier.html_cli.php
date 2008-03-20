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

    }

    function testParse($content) {
       $this->parse(explode("\n", $content));
       return $this->content;
    }

}


class UTjIniFileModifier extends UnitTestCase {

    public function testParseFile(){
        $parser = new testIniFileModifier('');
        $content ='foo=bar';
        $expected=array(
            0 => array(
                    array(jIniFileModifier::TK_VALUE, 'foo','bar'),
                 ),
        );
        $this->assertEqual($parser->testParse($content), $expected);

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

        $this->assertEqual($parser->testParse($content), $expected);

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

        $this->assertEqual($parser->testParse($content), $expected);
    }

}

?>
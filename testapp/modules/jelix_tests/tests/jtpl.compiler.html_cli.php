<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Jouanneau Laurent
* @contributor
* @copyright   2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(JELIX_LIB_TPL_PATH.'jTplCompiler.class.php');

class testJtplContentCompiler extends jTplCompiler {

   public function setTrusted($trusted){
        $this->_trusted = $trusted;
   }

   public function compileContent2($content){
        return $this->compileContent($content);
   }
}




class UTjtplcontent extends jUnitTestCase {

    protected $content = array(
0=>array(
        '',
        '',
        ),
1=>array(
        '<p>ok</p>',
        '<p>ok</p>',
        ),
2=>array(
        '<p>ok<?php echo $toto ?></p>',
        '<p>ok</p>',
        ),
3=>array(
        '<p>ok</p>
<script>{literal}
function toto() {
}
{/literal}
</script>
<p>ko</p>',
        '<p>ok</p>
<script>
function toto() {
}

</script>
<p>ko</p>',
        ),
4=>array(
        '<p>ok {* toto $toto *}</p>',
        '<p>ok </p>',
        ),

5=>array(
        '<p>ok {* toto

 $toto *}</p>',
        '<p>ok </p>',
        ),

6=>array(
        '<p>ok {* toto
{$toto} *}</p>',
        '<p>ok </p>',
        ),
7=>array(
        '<p>ok {* toto
{$toto} *}</p> {* hello *}',
        '<p>ok </p> ',
        ),
    );

    function testCompileContent() {
        $compil = new testJtplContentCompiler();
        $compil->setTrusted(true);

        foreach($this->content as $k=>$t){
            try{
                $this->assertEqualOrDiff($t[1], $compil->compileContent2($t[0]));
            }catch(jException $e){
                $this->fail("Test '$k', Unknown Jelix Exception: ".$e->getMessage().' ('.$e->getLocaleKey().')');
            }catch(Exception $e){
                $this->fail("Test '$k', Unknown Exception: ".$e->getMessage());
            }
        }
    }
}

?>
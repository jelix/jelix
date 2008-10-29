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

require_once(JELIX_LIB_PATH.'tpl/jTplCompiler.class.php');

class testJtplContentCompiler extends jTplCompiler {

    public function setUserPlugins($userModifiers, $userFunctions) {
        $this->_modifier = array_merge($this->_modifier, $userModifiers);
        $this->_userFunctions = $userFunctions;
    }

    public function compileContent2($content){
        return $this->compileContent($content);
    }
}

function testjtplcontentUserFunction($t,$a,$b) {

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
8=>array(
        '<p>ok {* {if $a == "a"}aaa{/if} *}</p>',
        '<p>ok </p>',
        ),
9=>array(
        '<p>ok<? echo $toto ?></p>',
        '<p>ok</p>',
        ),
10=>array(
        '<p>ok<?= $toto ?></p>',
        '<p>ok</p>',
        ),
11=>array(
        '<p>ok{if $foo} {/if}</p>',
        '<p>ok<?php if($t->_vars[\'foo\']):?> <?php endif;?></p>',
        ),
12=>array(
        '<p>ok{if ($foo)} {/if}</p>',
        '<p>ok<?php if(($t->_vars[\'foo\'])):?> <?php endif;?></p>',
        ),
13=>array(
        '<p>ok{while ($foo)} {/while}</p>',
        '<p>ok<?php while(($t->_vars[\'foo\'])):?> <?php endwhile;?></p>',
        ),
14=>array(
        '<p>ok{while $foo} {/while}</p>',
        '<p>ok<?php while($t->_vars[\'foo\']):?> <?php endwhile;?></p>',
        ),
15=>array(
        '<p>ok{jurl ($foo)}</p>',
        '<p>ok<?php jtpl_function_html_jurl( $t,($t->_vars[\'foo\']));?></p>',
        ),
16=>array(
        '<p>ok{jurl ($foo,$params)}</p>',
        '<p>ok<?php jtpl_function_html_jurl( $t,($t->_vars[\'foo\'],$t->_vars[\'params\']));?></p>',
        ),
17=>array(
        '<p>ok{$foo.($truc.$bbb)}</p>',
        '<p>ok<?php echo $t->_vars[\'foo\'].($t->_vars[\'truc\'].$t->_vars[\'bbb\']); ?></p>',
        ),
18=>array(
        '<p>ok{if ($foo || $bar) && $baz} {/if}</p>',
        '<p>ok<?php if(($t->_vars[\'foo\'] || $t->_vars[\'bar\']) && $t->_vars[\'baz\']):?> <?php endif;?></p>',
        ),
19=>array(
        '<p>ok{bla $foo, $params}</p>',
        '<p>ok<?php testjtplcontentUserFunction( $t,$t->_vars[\'foo\'], $t->_vars[\'params\']);?></p>',
        ),
20=>array('{for ($i=0;$i<$p;$i++)} A {/for}',
          '<?php for($t->_vars[\'i\']=0;$t->_vars[\'i\']<$t->_vars[\'p\'];$t->_vars[\'i\']++):?> A <?php endfor;?>'
         ),
21=>array('{for $i=0;$i<$p;$i++} A {/for}',
          '<?php for($t->_vars[\'i\']=0;$t->_vars[\'i\']<$t->_vars[\'p\'];$t->_vars[\'i\']++):?> A <?php endfor;?>'
         ),
22=>array('{for $i=count($o);$i<$p;$i++} A {/for}',
          '<?php for($t->_vars[\'i\']=count($t->_vars[\'o\']);$t->_vars[\'i\']<$t->_vars[\'p\'];$t->_vars[\'i\']++):?> A <?php endfor;?>'
         ),
23=>array(
        '<p>ok {const $foo}</p>',
        '<p>ok <?php echo htmlspecialchars(constant($t->_vars[\'foo\']));?></p>',
        ),
    );

    function testCompileContent() {
        $compil = new testJtplContentCompiler();
        $compil->outputType = 'html';
        $compil->trusted = true;
        $compil->setUserPlugins(array(), array('bla'=>'testjtplcontentUserFunction'));
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

    protected $contentUntrusted = array(
0=>array('{for ($i=0;$i<$p;$i++)} A {/for}',
          '<?php for($t->_vars[\'i\']=0;$t->_vars[\'i\']<$t->_vars[\'p\'];$t->_vars[\'i\']++):?> A <?php endfor;?>'
         ),
1=>array('{for $i=0;$i<$p;$i++} A {/for}',
          '<?php for($t->_vars[\'i\']=0;$t->_vars[\'i\']<$t->_vars[\'p\'];$t->_vars[\'i\']++):?> A <?php endfor;?>'
         ),
    );
    
    function testCompileContentUntrusted() {
        $compil = new testJtplContentCompiler();
        $compil->outputType = 'html';
        $compil->trusted = false;
        $compil->setUserPlugins(array(), array('bla'=>'testjtplcontentUserFunction'));
        foreach($this->contentUntrusted as $k=>$t){
            try{
                $this->assertEqualOrDiff($t[1], $compil->compileContent2($t[0]));
            }catch(jException $e){
                $this->fail("Test '$k', Unknown Jelix Exception: ".$e->getMessage().' ('.$e->getLocaleKey().')');
            }catch(Exception $e){
                $this->fail("Test '$k', Unknown Exception: ".$e->getMessage());
            }
        }
    }


   protected $contentPlugins = array(
1=>array(
        '<p>ok {zone \'toto\'}</p>',
        '<p>ok <?php echo jZone::get(\'toto\');?></p>',
        ),
2=>array(
        '<p>ok {zone $truc,array(\'toto\'=>4,\'bla\'=>\'foo\')}</p>',
        '<p>ok <?php echo jZone::get($t->_vars[\'truc\'],array(\'toto\'=>4,\'bla\'=>\'foo\'));?></p>',
        ),

3=>array(
        '<p>ok {ifuserconnected} connected {/ifuserconnected}</p>',
        '<p>ok <?php  if(jAuth::isConnected()):?> connected <?php  endif; ?></p>',
        ),
4=>array(
        '<p>ok {ifuserconnected} connected {else} not connected {/ifuserconnected}</p>',
        '<p>ok <?php  if(jAuth::isConnected()):?> connected <?php else:?> not connected <?php  endif; ?></p>',
        ),
5=>array(
        '<p>ok {zone $truc,
                     array(\'toto\'=>4,
                      \'bla\'=>\'foo\')
                }</p>',
        '<p>ok <?php echo jZone::get($t->_vars[\'truc\'], array(\'toto\'=>4, \'bla\'=>\'foo\') );?></p>',
        ),
6=>array(
        '<p>ok {zone $truc,
                     array(\'toto\'=>4,
                      \'bla\'=>\'foo\')
                }</p><div>{counter_init \'name\', \'0\', 1, 1}</div>',
        '<p>ok <?php echo jZone::get($t->_vars[\'truc\'], array(\'toto\'=>4, \'bla\'=>\'foo\') );?></p><div><?php jtpl_function_common_counter_init( $t,\'name\', \'0\', 1, 1);?></div>',
        ),

);

    function testCompilePlugins() {
        $compil = new testJtplContentCompiler();
        $compil->outputType = 'html';
        $compil->trusted = true;

        foreach($this->contentPlugins as $k=>$t){
            try{
                $this->assertEqualOrDiff($t[1], $compil->compileContent2($t[0]));
            }catch(jException $e){
                $this->fail("Test '$k', Unknown Jelix Exception: ".$e->getMessage().' ('.$e->getLocaleKey().')');
            }catch(Exception $e){
                $this->fail("Test '$k', Unknown Exception: ".$e->getMessage());
            }
        }
    }

    protected $tplerrors = array(
         0=>array('{if $foo}',
                  'jelix~errors.tpl.tag.block.end.missing',array('if',null) ),
         1=>array('{ifuserconnected} {if $foo}  {/if} ',
                  'jelix~errors.tpl.tag.block.end.missing',array('ifuserconnected',null) ),
         2=>array('{foreach ($t=>$a)} A {/foreach}',
                  'jelix~errors.tpl.tag.character.invalid',array('foreach ($t=>$a)', '(', NULL) ),
         3=>array('{for ($i=0;$i<$p;$i++} A {/for}',
                  'jelix~errors.tpl.tag.bracket.error',array('for ($i=0;$i<$p;$i++',null) ),
         4=>array('{form ($foo,$params)} aa {/form}',
                  'jelix~errors.tplplugin.block.bad.argument.number',array('form','2-5',null) ),
         5=>array('{($aaa)}',
                  'jelix~errors.tpl.tag.syntax.invalid',array('($aaa)',null) ),
         );

    function testCompileErrors() {

        foreach($this->tplerrors as $k=>$t){
            $compil = new testJtplContentCompiler();
            $compil->outputType = 'html';
            $compil->trusted = true;
            try{
                $compil->compileContent2($t[0]);
                $this->fail("Test '$k', exception didn't happen");
            }catch(jException $e){
                $this->assertEqual($e->getLocaleKey(), $t[1], "Test '$k': %s  (local parameters: ".var_export($e->getLocaleParameters(), true).")");
                $this->assertEqualOrDiff($e->getLocaleParameters(), $t[2], "Test '$k': %s");
            }catch(Exception $e){
                $this->fail("Test '$k', Unknown Exception: ".$e->getMessage());
            }
        }
    }

    protected $tplerrors2 = array(
         0=>array('{for $i=count($a);$i<$p;$i++} A {/for}',
                  'jelix~errors.tpl.tag.character.invalid',array('for $i=count($a);$i<$p;$i++','(',null) ),
         1=>array('{const \'fff\'}',
                  'jelix~errors.tplplugin.untrusted.not.available',array('const',null) ),
    );
    function testCompileErrorsUntrusted() {

        foreach($this->tplerrors2 as $k=>$t){
            $compil = new testJtplContentCompiler();
            $compil->outputType = 'html';
            $compil->trusted = false;
            try{
                $compil->compileContent2($t[0]);
                $this->fail("Test '$k', exception didn't happen");
            }catch(jException $e){
                $this->assertEqual($e->getLocaleKey(), $t[1], "Test '$k': %s  (local parameters: ".var_export($e->getLocaleParameters(), true).")");
                $this->assertEqualOrDiff($e->getLocaleParameters(), $t[2], "Test '$k': %s");
            }catch(Exception $e){
                $this->fail("Test '$k', Unknown Exception: ".$e->getMessage());
            }
        }
    }

}

?>
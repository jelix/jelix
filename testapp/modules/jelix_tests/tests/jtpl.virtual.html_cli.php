<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Thiriot Christophe
* @contributor
* @copyright   2009 Thiriot Christophe
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class UTjtplVirtual extends jUnitTestCase {

    public function testSimpleTemplate() {

        $template = ' hello
{$value}
world
{for $i=1;$i<=5;$i++}
  {$i}
{/for}
';

        $expected = ' hello
test
world
  1
  2
  3
  4
  5
';

        $tpl = new jTpl();
        $tpl->assign('value', 'test');
        $result = $tpl->fetchFromString($template, 'text');
        $this->assertEqual($expected, $result);
    }

    private function testTemplateWithLocale() {

        $GLOBALS['gJConfig']->locale = 'en_EN';
        $template = 'hello
{@jelix_tests~tests1.first.locale@}
{assign $value="third"}

{@jelix_tests~tests1.$value.locale@}';

        $expected = 'hello
this is an en_EN sentence

this is the 3th en_EN sentence';

        $tpl = new jTpl();
        $result = $tpl->fetchFromString($template, 'text');
        $this->assertEqual($expected, $result);
    }

    private function testTemplateWithModifier() {

        $template = 'hello
this is a sentence with a {$value|upper} with a modifier
{$up|lower}';

        $expected = 'hello
this is a sentence with a VALUE with a modifier
value';

        $tpl = new jTpl();
        $tpl->assign('value', 'value');
        $tpl->assign('up', 'VALUE');
        $result = $tpl->fetchFromString($template, 'text');
        $this->assertEqual($expected, $result);
    }
}

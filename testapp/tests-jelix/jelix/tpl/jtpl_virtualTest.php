<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Thiriot Christophe
* @contributor Laurent Jouanneau
* @copyright   2009 Thiriot Christophe, 2012 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jtpl_virtualTest extends \Jelix\UnitTests\UnitTestCase {

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
        $this->assertEquals($expected, $result);
    }

    public function testTemplateWithLocale() {

        jApp::config()->locale = 'en_US';
        $template = 'hello
{@jelix_tests~tests1.first.locale@}
{assign $value="third"}

{@jelix_tests~tests1.$value.locale@}';

        $expected = 'hello
this is an en_US sentence

this is the 3th en_US sentence';

        $tpl = new jTpl();
        $result = $tpl->fetchFromString($template, 'text');
        $this->assertEquals($expected, $result);
    }

    public function testTemplateWithModifier() {

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
        $this->assertEquals($expected, $result);
    }
}

<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2010 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class UTjtplfetch extends jUnitTestCase {

    function testSimpleFetch() {
        
        $tpl = new jTpl();
        $tpl->assign(array('toto'=>'firefox', 'titi'=>'chrome'));
        $tpl->assign('foo', 'hello');
        $tpl->assign('list', array('aaa','bbb','ccc'));
        $content = $tpl->fetch('jelix_tests~test_tpl_fetch');
        
        $expected =
'firefoxchrome
foo=hello
value=aaa
value=bbb
value=ccc
end';
        $this->assertEqualOrDiff($expected, $content);
    }


}

<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Jouanneau Laurent
* @contributor Thibault PIRONT < nuKs >
* @copyright   2007 Jouanneau laurent
* @copyright   2007 Thibault PIRONT
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class UTjtplplugins extends jUnitTestCase {

    protected $templates = array(
        0=>array(
            'test_plugin_jurl', // selecteur du template
            '<p><a href="%BASEPATH%index.php?module=jelix_tests&amp;action=urlsig:url1">aaa</a></p>', // contenu généré
        ),
        1=>array(
            'test_plugin_counter',
            '1-2-3-4-5,1-2-3-4-5,6-7-8-9-10,----,11-12-13-14-15',
        ),
        2=>array(
            'test_plugin_counter_reset',
            '1-2-3-4-5,1-2-3-4-5,1-2-3-4-5,1-2-3-4-5',
        ),
        3=>array(
            'test_plugin_counter_reset_all',
            '1-2-3-4-5,1-2-3-4-5,1-2-3-4-5,1-2-3-4-5',
        ),
        4=>array(
            'test_plugin_counter_init_allarg_noexeption',
            '2-0--2--4--6,03-06-09-12-15,g-f-e-d-c,E-J-O-T-Y',
        ),
        5=>array(
            'test_plugin_counter_init_noexeption',
            '1-2-3-4-5,01-02-03-04-05,e-f-g-h-i',
        ),
        6=>array(
            'test_plugin_counter_init_exeption',
            'y-z-1-2-3',
        ),
   );

    function testPlugin() {

        foreach($this->templates as $k=>$t) {

            // we delete the cache because it won't be updated 
            // if changes are made in jTpl itself or plugins
            $sel = new jSelectorTpl($t[0]); //, $outputtype='', $trusted = true
            $cache = $sel->getCompiledFilePath();
            if(file_exists($cache))
                unlink($cache);

            $tpl = new jTpl();
            $tpl->assign('i', 0); // Pour les boucles for.
            $output = $tpl->fetch ($t[0]); //, $outputtype='', $trusted = true, $callMeta=true
            $expected = $t[1];
            if(strpos($t[1],'%BASEPATH%') !== false){
                $expected = str_replace('%BASEPATH%', $GLOBALS['gJConfig']->urlengine['basePath'], $expected);
            }
            $this->assertEqualOrDiff($output, $expected, 'testplugin['.$k.'], %s');

        }
    }

}

?>
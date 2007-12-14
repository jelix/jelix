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


class UTjtplplugins extends jUnitTestCase {

    protected $templates = array(
0=>array(
        'test_plugin_jurl', // selecteur du template
        '<p><a href="qsd%BASEPATH%index.php?module=jelix_tests&amp;action=urlsig:url1">aaa</a></p>', // contenu généré
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
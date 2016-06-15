<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2016 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
require(LIB_PATH.'/jelix-legacy/core-modules/jelix/install/UrlEngineUpgrader.php');

class urlUpgradeTest extends jUnitTestCase {

    public function setUp() {
        copy(__DIR__.'/urls/urls_empty.xml', jApp::tempPath('urls.xml'));
        copy(__DIR__.'/urls/configurl.ini', jApp::tempPath('config.ini'));
        parent::setUp();
    }

    function tearDown() {
    }

    function testSimpleUpgrade() {
        $config = new \Jelix\IniFile\MultiIniModifier(__DIR__.'/app1/var/config/mainconfig.ini.php', jApp::tempPath('config.ini') );
        $modifier = new \Jelix\Routing\UrlMapping\XmlMapModifier(jApp::tempPath('urls.xml'));
        $xmlEp = $modifier->addEntryPoint('index', 'classic', null);
        $upgraderUrl = new UrlEngineUpgrader($config, 'index', $xmlEp);
        $upgraderUrl->upgrade();
        $modifier->save();
        $config->save();
        $this->assertEquals(file_get_contents(__DIR__.'/urls/res_config_simple_1.ini'),
                            file_get_contents(jApp::tempPath('config.ini')));
        $this->assertEquals(file_get_contents(__DIR__.'/urls/res_upgrade_simple_1.xml'),
                            file_get_contents(jApp::tempPath('urls.xml')));
    }

    function testSimple2Upgrade() {
        $config = new \Jelix\IniFile\MultiIniModifier(__DIR__.'/app1/var/config/mainconfig.ini.php', jApp::tempPath('config.ini') );
        $config->setValue('index','jauth~*@classic', 'simple_urlengine_entrypoints');
        $config->setValue('admin',"jacl2db~*@classic, jacl2db_admin~*@classic, jauthdb_admin~*@classic, master_admin~*@classic, admin~*@classic, jauth~*@classic", 'simple_urlengine_entrypoints');
        $config->setValue('startModule', 'view');
        $config->setValue('startAction', 'default:index');
        $config->save();
        copy(jApp::tempPath('config.ini'), jApp::tempPath('config2.ini'));
 
        $modifier = new \Jelix\Routing\UrlMapping\XmlMapModifier(jApp::tempPath('urls.xml'));
        $xmlEp = $modifier->addEntryPoint('index', 'classic', null);
        $upgraderUrl = new UrlEngineUpgrader($config, 'index', $xmlEp);
        $upgraderUrl->upgrade();
        $config->save();
        $this->assertEquals(file_get_contents(__DIR__.'/urls/res_config_simple_2.ini'),
                            file_get_contents(jApp::tempPath('config.ini')));

        $config = new \Jelix\IniFile\MultiIniModifier(__DIR__.'/app1/var/config/mainconfig.ini.php', jApp::tempPath('config2.ini') );
        $config->setValue('startModule', 'master_admin');
        $config->setValue('startAction', 'default:index');
        $xmlEp = $modifier->addEntryPoint('admin', 'classic', null);
        $upgraderUrl = new UrlEngineUpgrader($config, 'admin', $xmlEp);
        $upgraderUrl->upgrade();

        $config->save();
        $this->assertEquals(file_get_contents(__DIR__.'/urls/res_config_simple_2.ini'),
                            file_get_contents(jApp::tempPath('config2.ini')));

        $modifier->save();
        $this->assertEquals(file_get_contents(__DIR__.'/urls/res_upgrade_simple_2.xml'),
                            file_get_contents(jApp::tempPath('urls.xml')));
    }

    function testBasicSignificantUpgrade() {
        $config = new \Jelix\IniFile\MultiIniModifier(__DIR__.'/app1/var/config/mainconfig.ini.php', jApp::tempPath('config.ini') );
        $config->setValue('engine','basic_significant', 'urlengine');
        $config->setValue('index','jauth~*@classic', 'simple_urlengine_entrypoints');
        $config->setValue('admin',"jacl2db~*@classic, jacl2db_admin~*@classic, jauthdb_admin~*@classic, master_admin~*@classic, admin~*@classic, jauth~*@classic", 'simple_urlengine_entrypoints');
        $config->setValue('startModule', 'view');
        $config->setValue('startAction', 'default:index');
        $config->setValue('index', 'on', 'basic_significant_urlengine_entrypoints');
        $config->setValue('admin', 'off', 'basic_significant_urlengine_entrypoints');

        $config->save();
        copy(jApp::tempPath('config.ini'), jApp::tempPath('config2.ini'));
 
        $modifier = new \Jelix\Routing\UrlMapping\XmlMapModifier(jApp::tempPath('urls.xml'));
        $xmlEp = $modifier->addEntryPoint('index', 'classic', null);
        $upgraderUrl = new UrlEngineUpgrader($config, 'index', $xmlEp);
        $upgraderUrl->upgrade();
        $config->save();
        $this->assertEquals(file_get_contents(__DIR__.'/urls/res_config_simple_2.ini'),
                            file_get_contents(jApp::tempPath('config.ini')));

        $config = new \Jelix\IniFile\MultiIniModifier(__DIR__.'/app1/var/config/mainconfig.ini.php', jApp::tempPath('config2.ini') );
        $config->setValue('startModule', 'master_admin');
        $config->setValue('startAction', 'default:index');
        $xmlEp = $modifier->addEntryPoint('admin', 'classic', null);
        $upgraderUrl = new UrlEngineUpgrader($config, 'admin', $xmlEp);
        $upgraderUrl->upgrade();

        $config->save();
        $this->assertEquals(file_get_contents(__DIR__.'/urls/res_config_simple_2.ini'),
                            file_get_contents(jApp::tempPath('config2.ini')));

        $modifier->save();
        $this->assertEquals(file_get_contents(__DIR__.'/urls/res_upgrade_basicsig_1.xml'),
                            file_get_contents(jApp::tempPath('urls.xml')));
    }

}
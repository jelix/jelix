<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2009-2011 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
* @since 1.2
*/


require_once(dirname(__FILE__).'/installer.lib.php');
require_once(LIB_PATH.'/simpletest/mock_objects.php');

class testInstallerComponentModule2 extends jInstallerComponentModule {

    function setSourceVersionDate($version, $date) {
        $this->sourceDate = $date;
        $this->sourceVersion = $version;
    }

}



class testInstallerComponentForDependencies extends jInstallerComponentBase {
    
    protected $identityNamespace = 'http://jelix.org/ns/module/1.0';
    protected $rootName = 'module';
    protected $identityFile = 'module.xml';
    
    function getInstaller($ep, $installWholeApp) {
        return null;
    }

    function getUpgraders($ep) {
        return null;
    }
    
    function readDependenciesFromString($xmlcontent) {
        $xml = simplexml_load_string($xmlcontent);
        //$this->sourceVersion = (string) $xml->info[0]->version[0];   
        $this->readDependencies($xml);
    }
    
}

class UTjInstallerComponent extends UnitTestCase {

    protected $defaultIni;


    public function setUp() {
    }

    public function testDependenciesReading() {
        $this->defaultIni = new jIniFileModifier(jApp::configPath().'defaultconfig.ini.php');
        $comp = new testInstallerComponentForDependencies("test","", null);

        $str = '<?xml version="1.0" encoding="UTF-8"?>
<module xmlns="http://jelix.org/ns/module/1.0">
</module>';
        $comp->readDependenciesFromString($str);
        $this->assertEqual($comp->dependencies, array());
        $this->assertEqual($comp->getJelixVersion(), array('*','*'));

        $str = '<?xml version="1.0" encoding="UTF-8"?>
<module xmlns="http://jelix.org/ns/module/1.0">
    <dependencies>
    </dependencies>
</module>';
        $comp->readDependenciesFromString($str);
        $this->assertEqual($comp->dependencies, array());
        $this->assertEqual($comp->getJelixVersion(), array('*','*'));

        $str = '<?xml version="1.0" encoding="UTF-8"?>
<module xmlns="http://jelix.org/ns/module/1.0">
    <dependencies>
        <jelix minversion="1.0" maxversion="1.1" />
    </dependencies>
</module>';

        $comp->readDependenciesFromString($str);
        $this->assertEqual($comp->dependencies, array(
            array(
                'type'=> 'module',
                'id' => 'jelix@jelix.org',
                'name' => 'jelix',
                'minversion' => '1.0',
                'maxversion' => '1.1',
                ''
            )
            ));
        $this->assertEqual($comp->getJelixVersion(), array('1.0', '1.1'));


        $str = '<?xml version="1.0" encoding="UTF-8"?>
<module xmlns="http://jelix.org/ns/module/1.0">
    <dependencies>
        <jelix minversion="1.0" maxversion="1.1" />
        <module name="jauthdb" />
        <module name="jacl2db" id="jacl2db@jelix.org"  />
        <module name="jacldb"  id="jacldb@jelix.org"  minversion="1.0"/>
    </dependencies>
</module>';

        $comp->readDependenciesFromString($str);
        $this->assertEqual($comp->dependencies, array(
            array(
                'type'=> 'module',
                'id' => 'jelix@jelix.org',
                'name' => 'jelix',
                'minversion' => '1.0',
                'maxversion' => '1.1',
                ''
            ),
            array(
                'type'=> 'module',
                'id' => '',
                'name' => 'jauthdb',
                'minversion' => '*',
                'maxversion' => '*',
                ''
            ),
            array(
                'type'=> 'module',
                'id' => 'jacl2db@jelix.org',
                'name' => 'jacl2db',
                'minversion' => '*',
                'maxversion' => '*',
                ''
            ),
            array(
                'type'=> 'module',
                'id' => 'jacldb@jelix.org',
                'name' => 'jacldb',
                'minversion' => '1.0',
                'maxversion' => '*',
                ''
            ),
            ));
        $this->assertEqual($comp->getJelixVersion(), array('1.0', '1.1'));
    }


    function testGetInstallerWithNoInstaller() {
        try {
            // dummy ini file modifier. not used by installer of tested modules
            $ini = new testInstallerIniFileModifier("test.ini.php");

            // testinstall1 has no install.php file
            $component = new jInstallerComponentModule('testinstall1', jApp::appPath().'modules/testinstall1/', null);
            $component->init();
            $conf =(object) array( 'modules'=>array(
               'testinstall1.access'=>2, 
               'testinstall1.dbprofile'=>'default', 
               'testinstall1.installed'=>false, 
               'testinstall1.version'=>JELIX_VERSION,
            ));

            $EPindex = new testInstallerEntryPoint($this->defaultIni, $ini, 'index.php', 'classic', $conf);
            $component->addModuleInfos($EPindex->getEpId(), new jInstallerModuleInfos('testinstall1', $conf->modules));

            $installer = $component->getInstaller($EPindex, true);
            $this->assertNull($installer);
        }
        catch(jInstallerException $e) {
            $this->fail("Unexpected exception : ".$e->getMessage()." (".var_export($e->getLocaleParameters(),true).")");
        }
        
    }

    function testGetInstallerWithInstaller() {
        try {
            // dummy ini file modifier. not used by installer of tested modules
            $iniIndex = new testInstallerIniFileModifier('index/config.ini.php');
            $iniFoo = new testInstallerIniFileModifier('foo/config.ini.php');

            // testinstall2 has an install.php file
            $component = new jInstallerComponentModule('testinstall2', jApp::appPath().'modules/testinstall2/', null);
            $component->init();

            $conf =(object) array( 'modules'=>array(
               'testinstall2.access'=>2,
               'testinstall2.dbprofile'=>'default',
               'testinstall2.installed'=>false,
               'testinstall2.version'=>JELIX_VERSION,
            ));

            $EPindex = new testInstallerEntryPoint($this->defaultIni, $iniIndex, 'index.php', 'classic', $conf);
            $component->addModuleInfos($EPindex->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules));

            $EPfoo = new testInstallerEntryPoint($this->defaultIni, $iniFoo, 'foo.php', 'classic', $conf);
            $component->addModuleInfos($EPfoo->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules));

            $installer = $component->getInstaller($EPindex, true);
            $this->assertTrue (is_object($installer));

            $installer = $component->getInstaller($EPfoo, true);
            $this->assertTrue (is_object($installer));

        }
        catch(jInstallerException $e) {
            $this->fail("Unexpected exception : ".$e->getMessage()." (".var_export($e->getLocaleParameters(),true).")");
        }
    }


    function testGetUpgradersWithNoUpgraders() {
        try {

            // dummy ini file modifier. not used by installer of tested modules
            $ini = new testInstallerIniFileModifier("index/config.ini.php");

            // testinstall1 has no upgrade scripts
            $component = new jInstallerComponentModule('testinstall1', jApp::appPath().'modules/testinstall1/', null);
            $component->init();
            $conf =(object) array( 'modules'=>array(
               'testinstall1.access'=>2, 
               'testinstall1.dbprofile'=>'default', 
               'testinstall1.installed'=>false, 
               'testinstall1.version'=>JELIX_VERSION,
            ));
            $EPindex = new testInstallerEntryPoint($this->defaultIni, $ini, 'index.php', 'classic', $conf);
            $component->addModuleInfos($EPindex->getEpId(), new jInstallerModuleInfos('testinstall1', $conf->modules) );

            $upgraders = $component->getUpgraders($EPindex);
            $this->assertTrue(is_array($upgraders));
            $this->assertEqual(count($upgraders), 0);
        }
        catch(jInstallerException $e) {
            $this->fail("Unexpected exception : ".$e->getMessage()." (".var_export($e->getLocaleParameters(),true).")");
        }
    }

    function testGetUpgradersWithNoValidUpgrader() {

        try {
            // dummy ini file modifier. not used by installer of tested modules
            $ini = new testInstallerIniFileModifier("index/config.ini.php");

            //------------ testinstall2 has some upgraders file
            $component = new jInstallerComponentModule('testinstall2', jApp::appPath().'modules/testinstall2/', null);
            $component->init();

            // the current version is the latest one : no updaters
            $conf =(object) array( 'modules'=>array(
               'testinstall2.access'=>2, 
               'testinstall2.dbprofile'=>'default', 
               'testinstall2.installed'=>false, 
               'testinstall2.version'=>JELIX_VERSION, 
            ));

            $EPindex = new testInstallerEntryPoint($this->defaultIni, $ini, 'index.php', 'classic', $conf);
            $component->addModuleInfos($EPindex->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules) );

            $upgraders = $component->getUpgraders($EPindex);
            $this->assertTrue (is_array($upgraders));
            $this->assertEqual(count($upgraders), 0);
        }
        catch(jInstallerException $e) {
            $this->fail("Unexpected exception : ".$e->getMessage()." (".var_export($e->getLocaleParameters(),true).")");
        }
    }

    function testGetUpgradersWithOneValidUpgrader() {
        try {
            // dummy ini file modifier. not used by installer of tested modules
            $iniIndex = new testInstallerIniFileModifier("index/config.ini.php");
            $iniFoo = new testInstallerIniFileModifier("foo/config.ini.php");

            // the current version is the previous one : one updater
            $component = new jInstallerComponentModule('testinstall2', jApp::appPath().'modules/testinstall2/', null);
            $component->init();

            $conf =(object) array( 'modules'=>array(
               'testinstall2.access'=>2, 
               'testinstall2.dbprofile'=>'default', 
               'testinstall2.installed'=>false, 
               'testinstall2.version'=>"1.2.3", 
            ));

            $EPindex = new testInstallerEntryPoint($this->defaultIni, $iniIndex, 'index.php', 'classic', $conf);
            $component->addModuleInfos($EPindex->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules) );


            $upgraders = $component->getUpgraders($EPindex);
            if ($this->assertTrue (is_array($upgraders))) {
                if ($this->assertEqual(count($upgraders), 1)) {
                    $this->assertEqual(get_class($upgraders[0]), 'testinstall2ModuleUpgrader_newupgraderfilename');
                }
            }

            $EPfoo = new testInstallerEntryPoint($this->defaultIni, $iniFoo, 'foo.php', 'classic', $conf);
            $component->addModuleInfos($EPfoo->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules) );

            $upgraders = $component->getUpgraders($EPfoo);
            if ($this->assertTrue (is_array($upgraders))) {
                if ($this->assertEqual(count($upgraders), 1)) {
                    $this->assertEqual(get_class($upgraders[0]), 'testinstall2ModuleUpgrader_newupgraderfilename');
                }
            }
        }
        catch(jInstallerException $e) {
            $this->fail("Unexpected exception : ".$e->getMessage()." (".var_export($e->getLocaleParameters(),true).")");
        }
    }


    function testGetUpgradersWithTwoValidUpgrader() {
        try {
            // dummy ini file modifier. not used by installer of tested modules
            $iniIndex = new testInstallerIniFileModifier("index/config.ini.php");
            $iniFoo = new testInstallerIniFileModifier("foo/config.ini.php");

            // the current version is the previous one : one updater
            $component = new jInstallerComponentModule('testinstall2', JELIX_APP_PATH.'modules/testinstall2/', null);
            $component->init();

            $conf =(object) array( 'modules'=>array(
               'testinstall2.access'=>2, 
               'testinstall2.dbprofile'=>'default', 
               'testinstall2.installed'=>false, 
               'testinstall2.version'=>"1.1.2", 
            ));

            $EPindex = new testInstallerEntryPoint($this->defaultIni, $iniIndex, 'index.php', 'classic', $conf);
            $component->addModuleInfos($EPindex->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules) );

            // since newupgraderfilename targets '1.1.2' and '1.2.4', we should have second then newupgraderfilename
            $upgraders = $component->getUpgraders($EPindex);
            if ($this->assertTrue (is_array($upgraders))) {
                if ($this->assertEqual(count($upgraders), 3)) {
                    $this->assertEqual(get_class($upgraders[0]), 'testinstall2ModuleUpgrader_newupgraderfilenamedate');
                    $this->assertEqual(get_class($upgraders[1]), 'testinstall2ModuleUpgrader_second');
                    $this->assertEqual(get_class($upgraders[2]), 'testinstall2ModuleUpgrader_newupgraderfilename');
                }
            }

            $EPfoo = new testInstallerEntryPoint($this->defaultIni, $iniFoo, 'foo.php', 'classic', $conf);
            $component->addModuleInfos($EPfoo->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules) );

            $upgraders = $component->getUpgraders($EPfoo);
            if ($this->assertTrue (is_array($upgraders))) {
                if ($this->assertEqual(count($upgraders), 3)) {
                    $this->assertEqual(get_class($upgraders[0]), 'testinstall2ModuleUpgrader_newupgraderfilenamedate');
                    $this->assertEqual(get_class($upgraders[1]), 'testinstall2ModuleUpgrader_second');
                    $this->assertEqual(get_class($upgraders[2]), 'testinstall2ModuleUpgrader_newupgraderfilename');
                }
            }
        }
        catch(jInstallerException $e) {
            $this->fail("Unexpected exception : ".$e->getMessage()." (".var_export($e->getLocaleParameters(),true).")");
        }
    }

    function testGetUpgradersWithTwoValidUpgrader2() {
        try {
            // dummy ini file modifier. not used by installer of tested modules
            $iniIndex = new testInstallerIniFileModifier("index/config.ini.php");
            $iniFoo = new testInstallerIniFileModifier("foo/config.ini.php");

            $component = new jInstallerComponentModule('testinstall2', JELIX_APP_PATH.'modules/testinstall2/', null);
            $component->init();

            $conf =(object) array( 'modules'=>array(
               'testinstall2.access'=>2, 
               'testinstall2.dbprofile'=>'default', 
               'testinstall2.installed'=>false, 
               'testinstall2.version'=>"1.1.1", 
            ));

            $EPindex = new testInstallerEntryPoint($this->defaultIni, $iniIndex, 'index.php', 'classic', $conf);
            $component->addModuleInfos($EPindex->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules) );

            // since newupgraderfilename targets '1.1.2' and '1.2.4', we should have newupgraderfilename then second

            $upgraders = $component->getUpgraders($EPindex);
            if ($this->assertTrue (is_array($upgraders))) {
                if ($this->assertEqual(count($upgraders), 3)) {
                    $this->assertEqual(get_class($upgraders[0]), 'testinstall2ModuleUpgrader_newupgraderfilename');
                    $this->assertEqual(get_class($upgraders[1]), 'testinstall2ModuleUpgrader_newupgraderfilenamedate');
                    $this->assertEqual(get_class($upgraders[2]), 'testinstall2ModuleUpgrader_second');
                }
            }

            $EPfoo = new testInstallerEntryPoint($this->defaultIni, $iniFoo, 'foo.php', 'classic', $conf);
            $component->addModuleInfos($EPfoo->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules) );

            $upgraders = $component->getUpgraders($EPfoo);
            if ($this->assertTrue (is_array($upgraders))) {
                if ($this->assertEqual(count($upgraders), 3)) {
                    $this->assertEqual(get_class($upgraders[0]), 'testinstall2ModuleUpgrader_newupgraderfilename');
                    $this->assertEqual(get_class($upgraders[1]), 'testinstall2ModuleUpgrader_newupgraderfilenamedate');
                    $this->assertEqual(get_class($upgraders[2]), 'testinstall2ModuleUpgrader_second');
                }
            }
        }
        catch(jInstallerException $e) {
            $this->fail("Unexpected exception : ".$e->getMessage()." (".var_export($e->getLocaleParameters(),true).")");
        }
    }

    function testGetUpgradersWithTwoValidUpgraderWithDate() {
        try {
            // dummy ini file modifier. not used by installer of tested modules
            $iniIndex = new testInstallerIniFileModifier("index/config.ini.php");
            $iniFoo = new testInstallerIniFileModifier("foo/config.ini.php");

            Mock::generate('jInstaller');
            file_put_contents(JELIX_APP_TEMP_PATH.'dummyInstaller.ini', '');
            $installer = new MockjInstaller();
            $installer->installerIni = new jIniFileModifier(JELIX_APP_TEMP_PATH.'dummyInstaller.ini');

            $component = new testInstallerComponentModule2('testinstall2', JELIX_APP_PATH.'modules/testinstall2/', $installer);
            $component->init();

            // 1.1  1.1.2* 1.1.3** 1.1.5 1.2.2** 1.2.4*

            $installer->installerIni->setValue('testinstall2.firstversion', '1.1' , 'index');
            $installer->installerIni->setValue('testinstall2.firstversion.date', '2011-01-10' , 'index');
            $installer->installerIni->setValue('testinstall2.version', '1.1.2' , 'index');
            $installer->installerIni->setValue('testinstall2.version.date', '2011-01-12' , 'index');
            $component->setSourceVersionDate('1.1.5','2011-01-15');
            $conf =(object) array( 'modules'=>array(
               'testinstall2.access'=>2, 
               'testinstall2.dbprofile'=>'default', 
               'testinstall2.installed'=>false, 
               'testinstall2.version'=>"1.1", 
            ));

            $EPindex = new testInstallerEntryPoint($this->defaultIni, $iniIndex, 'index.php', 'classic', $conf);
            $component->addModuleInfos($EPindex->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules) );

            $upgraders = $component->getUpgraders($EPindex);
            if ($this->assertTrue (is_array($upgraders))) {
                if ($this->assertEqual(count($upgraders), 3)) {
                    $this->assertEqual(get_class($upgraders[0]), 'testinstall2ModuleUpgrader_newupgraderfilename');
                    $this->assertEqual(get_class($upgraders[1]), 'testinstall2ModuleUpgrader_newupgraderfilenamedate');
                    $this->assertEqual(get_class($upgraders[2]), 'testinstall2ModuleUpgrader_second');
                }
            }

            $installer->installerIni->setValue('testinstall2.firstversion', '1.1.3' , 'index');
            $installer->installerIni->setValue('testinstall2.firstversion.date', '2011-01-13' , 'index');
            $installer->installerIni->setValue('testinstall2.version', '1.1.5' , 'index');
            $installer->installerIni->setValue('testinstall2.version.date', '2011-01-15' , 'index');
            $component->setSourceVersionDate('1.2.5','2011-01-25');
            $conf =(object) array( 'modules'=>array(
               'testinstall2.access'=>2,
               'testinstall2.dbprofile'=>'default', 
               'testinstall2.installed'=>false, 
               'testinstall2.version'=>"1.1.5", 
            ));

            $EPindex = new testInstallerEntryPoint($this->defaultIni, $iniIndex, 'index.php', 'classic', $conf);
            $component->addModuleInfos($EPindex->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules) );

            $upgraders = $component->getUpgraders($EPindex);
            if ($this->assertTrue (is_array($upgraders))) {
                if ($this->assertEqual(count($upgraders), 1)) {
                    $this->assertEqual(get_class($upgraders[0]), 'testinstall2ModuleUpgrader_newupgraderfilename');
                }
            }
        }
        catch(jInstallerException $e) {
            $this->fail("Unexpected exception : ".$e->getMessage()." (".var_export($e->getLocaleParameters(),true).")");
        }
    }

    function testGetUpgradersWithAllUpgraders() {
        try {
            // dummy ini file modifier. not used by installer of tested modules
            $iniIndex = new testInstallerIniFileModifier("index/config.ini.php");
            $iniFoo = new testInstallerIniFileModifier("foo/config.ini.php");

            // the current version is a very old one : all updaters
            $component = new jInstallerComponentModule('testinstall2', jApp::appPath().'modules/testinstall2/', null);
            $component->init();

            $conf =(object) array( 'modules'=>array(
               'testinstall2.access'=>2,
               'testinstall2.dbprofile'=>'default',
               'testinstall2.installed'=>false,
               'testinstall2.version'=>"0.9",
            ));

            $EPindex = new testInstallerEntryPoint($this->defaultIni, $iniIndex, 'index.php', 'classic', $conf);
            $component->addModuleInfos($EPindex->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules) );

            $upgraders = $component->getUpgraders($EPindex);
            if ($this->assertTrue (is_array($upgraders))) {
                if ($this->assertEqual(count($upgraders), 4)) {
                    $this->assertEqual(get_class($upgraders[0]), 'testinstall2ModuleUpgrader_first');
                    $this->assertEqual(get_class($upgraders[1]), 'testinstall2ModuleUpgrader_newupgraderfilename');
                    $this->assertEqual(get_class($upgraders[2]), 'testinstall2ModuleUpgrader_newupgraderfilenamedate');
                    $this->assertEqual(get_class($upgraders[3]), 'testinstall2ModuleUpgrader_second');
                }
            }

            $EPfoo = new testInstallerEntryPoint($this->defaultIni, $iniFoo, 'foo.php', 'classic', $conf);
            $component->addModuleInfos($EPfoo->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules) );

            $upgraders = $component->getUpgraders($EPfoo);
            if ($this->assertTrue (is_array($upgraders))) {
                if ($this->assertEqual(count($upgraders), 4)) {
                    $this->assertEqual(get_class($upgraders[0]), 'testinstall2ModuleUpgrader_first');
                    $this->assertEqual(get_class($upgraders[1]), 'testinstall2ModuleUpgrader_newupgraderfilename');
                    $this->assertEqual(get_class($upgraders[2]), 'testinstall2ModuleUpgrader_newupgraderfilenamedate');
                    $this->assertEqual(get_class($upgraders[3]), 'testinstall2ModuleUpgrader_second');
                }
            }
        }
        catch(jInstallerException $e) {
            $this->fail("Unexpected exception : ".$e->getMessage()." (".var_export($e->getLocaleParameters(),true).")");
        }
    }
}


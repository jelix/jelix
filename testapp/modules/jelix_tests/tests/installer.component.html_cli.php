<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2009 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
* @since 1.2
*/


require_once(dirname(__FILE__).'/installer.lib.php');


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
        $this->defaultIni = new jIniFileModifier(JELIX_APP_CONFIG_PATH.'defaultconfig.ini.php');
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
            $component = new jInstallerComponentModule('testinstall1', JELIX_APP_PATH.'modules/testinstall1/', null);
            $component->init();
            $conf =(object) array( 'modules'=>array(
               'testinstall1.access'=>2, 
               'testinstall1.dbprofile'=>'dbprofils.ini.php', 
               'testinstall1.installed'=>false, 
               'testinstall1.version'=>JELIX_VERSION,
               'testinstall1.sessionid'=>'',
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
            $component = new jInstallerComponentModule('testinstall2', JELIX_APP_PATH.'modules/testinstall2/', null);
            $component->init();

            $conf =(object) array( 'modules'=>array(
               'testinstall2.access'=>2, 
               'testinstall2.dbprofile'=>'dbprofils.ini.php', 
               'testinstall2.installed'=>false, 
               'testinstall2.version'=>JELIX_VERSION, 
               'testinstall2.sessionid'=>'',
            ));

            $EPindex = new testInstallerEntryPoint($this->defaultIni, $iniIndex, 'index.php', 'classic', $conf);
            $component->addModuleInfos($EPindex->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules));

            $EPfoo = new testInstallerEntryPoint($this->defaultIni, $iniFoo, 'foo.php', 'classic', $conf);
            $component->addModuleInfos($EPfoo->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules));

            $installer = $component->getInstaller($EPindex, true);
            $this->assertTrue (is_object($installer));

            // no discriminant id, so we don't have a new installer for an other entry point
            $installer = $component->getInstaller($EPfoo, true);
            $this->assertFalse($installer);

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
            $component = new jInstallerComponentModule('testinstall1', JELIX_APP_PATH.'modules/testinstall1/', null);
            $component->init();
            $conf =(object) array( 'modules'=>array(
               'testinstall1.access'=>2, 
               'testinstall1.dbprofile'=>'dbprofils.ini.php', 
               'testinstall1.installed'=>false, 
               'testinstall1.version'=>JELIX_VERSION,
               'testinstall1.sessionid'=>'',
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
            $component = new jInstallerComponentModule('testinstall2', JELIX_APP_PATH.'modules/testinstall2/', null);
            $component->init();

            // the current version is the latest one : no updaters
            $conf =(object) array( 'modules'=>array(
               'testinstall2.access'=>2, 
               'testinstall2.dbprofile'=>'dbprofils.ini.php', 
               'testinstall2.installed'=>false, 
               'testinstall2.version'=>JELIX_VERSION, 
               'testinstall2.sessionid'=>'',
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
            $component = new jInstallerComponentModule('testinstall2', JELIX_APP_PATH.'modules/testinstall2/', null);
            $component->init();

            $conf =(object) array( 'modules'=>array(
               'testinstall2.access'=>2, 
               'testinstall2.dbprofile'=>'dbprofils.ini.php', 
               'testinstall2.installed'=>false, 
               'testinstall2.version'=>"1.1.2", 
               'testinstall2.sessionid'=>'',
            ));

            $EPindex = new testInstallerEntryPoint($this->defaultIni, $iniIndex, 'index.php', 'classic', $conf);
            $component->addModuleInfos($EPindex->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules) );

            $upgraders = $component->getUpgraders($EPindex);
            if ($this->assertTrue (is_array($upgraders))) {
                if ($this->assertEqual(count($upgraders), 1))
                    $this->assertEqual(get_class($upgraders[0]), 'testinstall2ModuleUpgrader_second');
            }

            $EPfoo = new testInstallerEntryPoint($this->defaultIni, $iniFoo, 'foo.php', 'classic', $conf);
            $component->addModuleInfos($EPfoo->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules) );

            $upgraders = $component->getUpgraders($EPfoo);

            if ($this->assertTrue (is_array($upgraders))) {
                $this->assertEqual(count($upgraders), 0);
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
            $component = new jInstallerComponentModule('testinstall2', JELIX_APP_PATH.'modules/testinstall2/', null);
            $component->init();

            $conf =(object) array( 'modules'=>array(
               'testinstall2.access'=>2, 
               'testinstall2.dbprofile'=>'dbprofils.ini.php', 
               'testinstall2.installed'=>false, 
               'testinstall2.version'=>"0.9",
               'testinstall2.sessionid'=>'',
            ));

            $EPindex = new testInstallerEntryPoint($this->defaultIni, $iniIndex, 'index.php', 'classic', $conf);
            $component->addModuleInfos($EPindex->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules) );

            $upgraders = $component->getUpgraders($EPindex);
            if ($this->assertTrue (is_array($upgraders))) {
                if ($this->assertEqual(count($upgraders), 2)) {
                    $this->assertEqual(get_class($upgraders[0]), 'testinstall2ModuleUpgrader_first');
                    $this->assertEqual(get_class($upgraders[1]), 'testinstall2ModuleUpgrader_second');
                }
            }

            $EPfoo = new testInstallerEntryPoint($this->defaultIni, $iniFoo, 'foo.php', 'classic', $conf);
            $component->addModuleInfos($EPfoo->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules) );

            $upgraders = $component->getUpgraders($EPfoo);
            if ($this->assertTrue (is_array($upgraders))) {
                $this->assertEqual(count($upgraders), 0);
            }

        }
        catch(jInstallerException $e) {
            $this->fail("Unexpected exception : ".$e->getMessage()." (".var_export($e->getLocaleParameters(),true).")");
        }
    }


    function testGetUpgradersWithDifferentUpgradersOnEntryPoint() {

        try {
            // dummy ini file modifier. not used by installer of tested modules
            $iniIndex = new testInstallerIniFileModifier("index/config.ini.php");
            $iniFoo = new testInstallerIniFileModifier("foo/config.ini.php");

            $component = new jInstallerComponentModule('testinstall2', JELIX_APP_PATH.'modules/testinstall2/', null);
            $component->init();

            $conf =(object) array( 'modules'=>array(
               'testinstall2.access'=>2, 
               'testinstall2.dbprofile'=>'dbprofils.ini.php', 
               'testinstall2.installed'=>false, 
               'testinstall2.version'=>"0.9",
               'testinstall2.sessionid'=>'',
            ));

            $EPindex = new testInstallerEntryPoint($this->defaultIni, $iniIndex, 'index.php', 'classic', $conf);
            $component->addModuleInfos($EPindex->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules) );

            $upgraders = $component->getUpgraders($EPindex);
            if ($this->assertTrue (is_array($upgraders))) {
                if ($this->assertEqual(count($upgraders), 2)) {
                    $this->assertEqual(get_class($upgraders[0]), 'testinstall2ModuleUpgrader_first');
                    $this->assertEqual(get_class($upgraders[1]), 'testinstall2ModuleUpgrader_second');
                }
            }

            $EPfoo = new testInstallerEntryPoint($this->defaultIni, $iniFoo, 'foo.php', 'classic', $conf);
            $component->addModuleInfos($EPfoo->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules) );
            
            $upgraders2 = $component->getUpgraders($EPfoo);

            if ($this->assertTrue (is_array($upgraders2))) {
                $this->assertEqual(count($upgraders2), 0);
            }
 
            $upgraders[1]->testUseCommonId = false;
            $iniBar = new testInstallerIniFileModifier("bar/config.ini.php");
            $EPindex = new testInstallerEntryPoint($this->defaultIni, $iniBar, 'bar.php', 'classic', $conf);
            $component->addModuleInfos($EPindex->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules) );

            $upgraders2 = $component->getUpgraders($EPindex);

            if ($this->assertTrue (is_array($upgraders2))) {
                if ($this->assertEqual(count($upgraders2), 1)) {
                    $this->assertEqual(get_class($upgraders2[0]), 'testinstall2ModuleUpgrader_second');
                }
            }
        }
        catch(jInstallerException $e) {
            $this->fail("Unexpected exception : ".$e->getMessage()." (".var_export($e->getLocaleParameters(),true).")");
        }
    }
}


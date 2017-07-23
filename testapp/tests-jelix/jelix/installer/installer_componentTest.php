<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2009-2012 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
* @since 1.2
*/


require_once(__DIR__.'/installer.lib.php');

class testInstallerComponentModule2 extends jInstallerComponentModule {

    function setSourceVersionDate($version, $date) {
        $this->moduleInfos->versionDate = $date;
        $this->moduleInfos->version = $version;
    }

}



class testInstallerComponentForDependencies extends jInstallerComponentModule {
    
    function getInstaller(\Jelix\Installer\EntryPoint $ep, $installWholeApp) {
        return null;
    }

    function getUpgraders(\Jelix\Installer\EntryPoint $ep) {
        return null;
    }
}

class jInstaller_ComponentTest extends jUnitTestCase {

    protected $globalSetup;

    function setUp() {
        jApp::saveContext();
        self::initJelixConfig();
        $this->globalSetup = new testInstallerGlobalSetup();
        jApp::saveContext();
    }

    function tearDown() {
        jApp::restoreContext();
    }

    function testGetInstallerWithNoInstaller() {
        try {
            // dummy ini file modifier. not used by installer of tested modules
            $ini = new testInstallerIniFileModifier("test.ini.php");
            $moduleInfo = new \Jelix\Core\Infos\ModuleInfos(jApp::appPath().'modules/testinstall1/');
            // testinstall1 has no install.php file
            $component = new jInstallerComponentModule($moduleInfo , null);
            $conf =(object) array( 'modules'=>array(
               'testinstall1.access'=>2, 
               'testinstall1.dbprofile'=>'default', 
               'testinstall1.installed'=>false, 
               'testinstall1.version'=>JELIX_VERSION,
            ));

            $EPindex = new testInstallerEntryPoint($this->globalSetup,
                                                   $ini, 'index.php', 'classic', $conf);
            $component->addModuleStatus($EPindex->getEpId(), new jInstallerModuleInfos('testinstall1', $conf->modules));

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
            $moduleInfo = new \Jelix\Core\Infos\ModuleInfos(jApp::appPath().'modules/testinstall2/');
            // testinstall2 has an install.php file

            $component = new jInstallerComponentModule($moduleInfo, $this->globalSetup);
            $conf =(object) array( 'modules'=>array(
               'testinstall2.access'=>2,
               'testinstall2.dbprofile'=>'default',
               'testinstall2.installed'=>false,
               'testinstall2.version'=>JELIX_VERSION,
            ));

            $EPindex = new testInstallerEntryPoint($this->globalSetup, $iniIndex, 'index.php', 'classic', $conf);
            $component->addModuleStatus($EPindex->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules));

            $EPfoo = new testInstallerEntryPoint($this->globalSetup, $iniFoo, 'foo.php', 'classic', $conf);
            $component->addModuleStatus($EPfoo->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules));

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
            $moduleInfo = new \Jelix\Core\Infos\ModuleInfos(jApp::appPath().'modules/testinstall1/');

            // testinstall1 has no upgrade scripts
            $component = new jInstallerComponentModule($moduleInfo, $this->globalSetup);

            $conf =(object) array( 'modules'=>array(
               'testinstall1.access'=>2, 
               'testinstall1.dbprofile'=>'default', 
               'testinstall1.installed'=>false, 
               'testinstall1.version'=>JELIX_VERSION,
            ));
            $EPindex = new testInstallerEntryPoint($this->globalSetup, $ini, 'index.php', 'classic', $conf);
            $component->addModuleStatus($EPindex->getEpId(), new jInstallerModuleInfos('testinstall1', $conf->modules) );

            $upgraders = $component->getUpgraders($EPindex);
            $this->assertTrue(is_array($upgraders));
            $this->assertEquals(0, count($upgraders));
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
            $moduleInfo = new \Jelix\Core\Infos\ModuleInfos(jApp::appPath().'modules/testinstall2/');
            $component = new jInstallerComponentModule($moduleInfo, $this->globalSetup);

            // the current version is the latest one : no updaters
            $conf =(object) array( 'modules'=>array(
               'testinstall2.access'=>2, 
               'testinstall2.dbprofile'=>'default', 
               'testinstall2.installed'=>false, 
               'testinstall2.version'=>JELIX_VERSION, 
            ));

            $EPindex = new testInstallerEntryPoint($this->globalSetup, $ini, 'index.php', 'classic', $conf);
            $component->addModuleStatus($EPindex->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules) );

            $upgraders = $component->getUpgraders($EPindex);
            $this->assertTrue (is_array($upgraders));
            $this->assertEquals(0, count($upgraders));
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

            $moduleInfo = new \Jelix\Core\Infos\ModuleInfos(jApp::appPath().'modules/testinstall2/');
            // the current version is the previous one : one updater

            $component = new jInstallerComponentModule($moduleInfo, $this->globalSetup);

            $conf =(object) array( 'modules'=>array(
               'testinstall2.access'=>2, 
               'testinstall2.dbprofile'=>'default', 
               'testinstall2.installed'=>false, 
               'testinstall2.version'=>"1.2.3", 
            ));

            $EPindex = new testInstallerEntryPoint($this->globalSetup, $iniIndex, 'index.php', 'classic', $conf);
            $component->addModuleStatus($EPindex->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules) );

            $upgraders = $component->getUpgraders($EPindex);
            $this->assertTrue (is_array($upgraders));
            $this->assertEquals(1, count($upgraders));
            $this->assertEquals('testinstall2ModuleUpgrader_newupgraderfilename', get_class($upgraders[0]));

            $EPfoo = new testInstallerEntryPoint($this->globalSetup, $iniFoo, 'foo.php', 'classic', $conf);
            $component->addModuleStatus($EPfoo->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules) );

            $upgraders = $component->getUpgraders($EPfoo);
            $this->assertTrue (is_array($upgraders));
            $this->assertEquals(1, count($upgraders));
            $this->assertEquals('testinstall2ModuleUpgrader_newupgraderfilename', get_class($upgraders[0]));
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
            $moduleInfo = new \Jelix\Core\Infos\ModuleInfos(jApp::appPath().'modules/testinstall2/');
            $component = new jInstallerComponentModule($moduleInfo, $this->globalSetup);

            $conf =(object) array( 'modules'=>array(
               'testinstall2.access'=>2, 
               'testinstall2.dbprofile'=>'default', 
               'testinstall2.installed'=>false, 
               'testinstall2.version'=>"1.1.2", 
            ));

            $EPindex = new testInstallerEntryPoint($this->globalSetup, $iniIndex, 'index.php', 'classic', $conf);
            $component->addModuleStatus($EPindex->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules) );

            // since newupgraderfilename targets '1.1.2' and '1.2.4', we should have second then newupgraderfilename
            $upgraders = $component->getUpgraders($EPindex);
            $this->assertTrue (is_array($upgraders));
            $this->assertEquals(3, count($upgraders));
            $this->assertEquals('testinstall2ModuleUpgrader_newupgraderfilenamedate', get_class($upgraders[0]));
            $this->assertEquals('testinstall2ModuleUpgrader_second', get_class($upgraders[1]));
            $this->assertEquals('testinstall2ModuleUpgrader_newupgraderfilename', get_class($upgraders[2]));

            $EPfoo = new testInstallerEntryPoint($this->globalSetup, $iniFoo, 'foo.php', 'classic', $conf);
            $component->addModuleStatus($EPfoo->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules) );

            $upgraders = $component->getUpgraders($EPfoo);
            $this->assertTrue (is_array($upgraders));
            $this->assertEquals(3, count($upgraders));
            $this->assertEquals('testinstall2ModuleUpgrader_newupgraderfilenamedate', get_class($upgraders[0]));
            $this->assertEquals('testinstall2ModuleUpgrader_second', get_class($upgraders[1]));
            $this->assertEquals('testinstall2ModuleUpgrader_newupgraderfilename', get_class($upgraders[2]));
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
            $moduleInfo = new \Jelix\Core\Infos\ModuleInfos(jApp::appPath().'modules/testinstall2/');
            $component = new jInstallerComponentModule($moduleInfo, $this->globalSetup);

            $conf =(object) array( 'modules'=>array(
               'testinstall2.access'=>2, 
               'testinstall2.dbprofile'=>'default', 
               'testinstall2.installed'=>false, 
               'testinstall2.version'=>"1.1.1", 
            ));

            $EPindex = new testInstallerEntryPoint($this->globalSetup, $iniIndex, 'index.php', 'classic', $conf);
            $component->addModuleStatus($EPindex->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules) );

            // since newupgraderfilename targets '1.1.2' and '1.2.4', we should have newupgraderfilename then second

            $upgraders = $component->getUpgraders($EPindex);
            $this->assertTrue (is_array($upgraders));
            $this->assertEquals(3, count($upgraders));
            $this->assertEquals('testinstall2ModuleUpgrader_newupgraderfilename', get_class($upgraders[0]));
            $this->assertEquals('testinstall2ModuleUpgrader_newupgraderfilenamedate', get_class($upgraders[1]));
            $this->assertEquals('testinstall2ModuleUpgrader_second', get_class($upgraders[2]));

            $EPfoo = new testInstallerEntryPoint($this->globalSetup, $iniFoo, 'foo.php', 'classic', $conf);
            $component->addModuleStatus($EPfoo->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules) );

            $upgraders = $component->getUpgraders($EPfoo);
            $this->assertTrue (is_array($upgraders));
            $this->assertEquals(3, count($upgraders));
            $this->assertEquals('testinstall2ModuleUpgrader_newupgraderfilename', get_class($upgraders[0]));
            $this->assertEquals('testinstall2ModuleUpgrader_newupgraderfilenamedate', get_class($upgraders[1]));
            $this->assertEquals('testinstall2ModuleUpgrader_second', get_class($upgraders[2]));
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

            file_put_contents(jApp::tempPath('dummyInstaller.ini'), '');
            $installerIni = new \Jelix\IniFile\IniModifier(jApp::tempPath('dummyInstaller.ini'));
            $this->globalSetup->setInstallerIni($installerIni);

            $moduleInfo = new \Jelix\Core\Infos\ModuleInfos(jApp::appPath().'modules/testinstall2/');
            $component = new testInstallerComponentModule2($moduleInfo, $this->globalSetup);

            // 1.1  1.1.2* 1.1.3** 1.1.5 1.2.2** 1.2.4*

            $installerIni->setValue('testinstall2.firstversion', '1.1' , 'index');
            $installerIni->setValue('testinstall2.firstversion.date', '2011-01-10' , 'index');
            $installerIni->setValue('testinstall2.version', '1.1.2' , 'index');
            $installerIni->setValue('testinstall2.version.date', '2011-01-12' , 'index');
            $component->setSourceVersionDate('1.1.5','2011-01-15');
            $conf =(object) array( 'modules'=>array(
               'testinstall2.access'=>2, 
               'testinstall2.dbprofile'=>'default', 
               'testinstall2.installed'=>false, 
               'testinstall2.version'=>"1.1", 
            ));

            $EPindex = new testInstallerEntryPoint($this->globalSetup, $iniIndex, 'index.php', 'classic', $conf);
            $component->addModuleStatus($EPindex->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules) );

            $upgraders = $component->getUpgraders($EPindex);
            $this->assertTrue (is_array($upgraders));
            $this->assertEquals(3, count($upgraders));
            $this->assertEquals('testinstall2ModuleUpgrader_newupgraderfilename', get_class($upgraders[0]));
            $this->assertEquals('testinstall2ModuleUpgrader_newupgraderfilenamedate', get_class($upgraders[1]));
            $this->assertEquals('testinstall2ModuleUpgrader_second', get_class($upgraders[2]));

            /*
            testinstall2ModuleUpgrader_newupgraderfilename : B
            testinstall2ModuleUpgrader_newupgraderfilenamedate: C 2011-01-13
            testinstall2ModuleUpgrader_second : D
            testinstall2ModuleUpgrader_first : A
                                       01-13     01-15             01-25
                        /- B:1.1.2 - C:1.1.3 - D:1.1.5
              A:1.1 ---/-            C:1.2.2 -         B:1.2.4     1.2.5

             */
            $installerIni->setValue('testinstall2.firstversion', '1.1.3' , 'index');
            $installerIni->setValue('testinstall2.firstversion.date', '2011-01-13' , 'index');
            $installerIni->setValue('testinstall2.version', '1.1.5' , 'index');
            $installerIni->setValue('testinstall2.version.date', '2011-01-15' , 'index');

            $component->setSourceVersionDate('1.2.5','2011-01-25');
            $conf =(object) array( 'modules'=>array(
               'testinstall2.access'=>2,
               'testinstall2.dbprofile'=>'default', 
               'testinstall2.installed'=>false, 
               'testinstall2.version'=>"1.1.5", 
            ));

            $EPindex = new testInstallerEntryPoint($this->globalSetup, $iniIndex, 'index.php', 'classic', $conf);
            $component->addModuleStatus($EPindex->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules) );

            $upgraders = $component->getUpgraders($EPindex);
            $this->assertTrue (is_array($upgraders));
            $this->assertEquals(1, count($upgraders));
            $this->assertEquals('testinstall2ModuleUpgrader_newupgraderfilename', get_class($upgraders[0]));
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
            $moduleInfo = new \Jelix\Core\Infos\ModuleInfos(jApp::appPath().'modules/testinstall2/');
            $component = new jInstallerComponentModule($moduleInfo, $this->globalSetup);

            $conf =(object) array( 'modules'=>array(
               'testinstall2.access'=>2,
               'testinstall2.dbprofile'=>'default',
               'testinstall2.installed'=>false,
               'testinstall2.version'=>"0.9",
            ));

            $EPindex = new testInstallerEntryPoint($this->globalSetup, $iniIndex, 'index.php', 'classic', $conf);
            $component->addModuleStatus($EPindex->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules) );

            $upgraders = $component->getUpgraders($EPindex);
            $this->assertTrue (is_array($upgraders));
            $this->assertEquals(4, count($upgraders));
            $this->assertEquals('testinstall2ModuleUpgrader_first', get_class($upgraders[0]));
            $this->assertEquals('testinstall2ModuleUpgrader_newupgraderfilename', get_class($upgraders[1]));
            $this->assertEquals('testinstall2ModuleUpgrader_newupgraderfilenamedate', get_class($upgraders[2]));
            $this->assertEquals('testinstall2ModuleUpgrader_second', get_class($upgraders[3]));

            $EPfoo = new testInstallerEntryPoint($this->globalSetup, $iniFoo, 'foo.php', 'classic', $conf);
            $component->addModuleStatus($EPfoo->getEpId(), new jInstallerModuleInfos('testinstall2', $conf->modules) );

            $upgraders = $component->getUpgraders($EPfoo);
            $this->assertTrue (is_array($upgraders));
            $this->assertEquals(4, count($upgraders));
            $this->assertEquals('testinstall2ModuleUpgrader_first', get_class($upgraders[0]));
            $this->assertEquals('testinstall2ModuleUpgrader_newupgraderfilename', get_class($upgraders[1]));
            $this->assertEquals('testinstall2ModuleUpgrader_newupgraderfilenamedate', get_class($upgraders[2]));
            $this->assertEquals('testinstall2ModuleUpgrader_second', get_class($upgraders[3]));
        }
        catch(jInstallerException $e) {
            $this->fail("Unexpected exception : ".$e->getMessage()." (".var_export($e->getLocaleParameters(),true).")");
        }
    }
}


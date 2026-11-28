<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2009-2023 Laurent Jouanneau
* @link        https://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
* @since 1.2
*/


use Jelix\Core\Infos\ModuleInfos;

require_once(__DIR__.'/installer.lib.php');

class testInstallerComponentModule2 extends \Jelix\Installer\ModuleInstallerLauncher {

    function setSourceVersionDate($version, $date)
    {

        $this->moduleInfos->versionDate = $date;
        $this->moduleInfos->version = $version;
    }
}

class testInstallerComponentForDependencies extends \Jelix\Installer\ModuleInstallerLauncher {

    function getConfigurator($actionMode = true, $forLocalConfiguration = null, $installParameters = null) {
        return null;
    }

    function getInstaller() {
        return null;
    }

    function getUpgraders() {
        return null;
    }


    public function setModuleXmlContent ($xmlContent) {

        $modulexml = $this->moduleStatus->getPath().'module.xml';
        $parser = new testInstallerModuleParser($modulexml);
        $this->moduleInfos = $parser->parseFromString($xmlContent);

        foreach($this->moduleInfos->dependencies as $dep) {
            if ($dep['type'] == 'module' && $dep['name'] == 'jelix') {
                $this->jelixMinVersion = $dep['minversion'];
                $this->jelixMaxVersion = $dep['maxversion'];
                break;
            }
        }
    }
}

class jInstaller_ComponentTest extends \Jelix\UnitTests\UnitTestCase {

    /**
     * @var testInstallerGlobalSetup
     */
    protected $globalSetup;

    function setUp() : void {
        jApp::saveContext();
        self::initJelixConfig();
        $this->globalSetup = new testInstallerGlobalSetup();
        jApp::saveContext();
    }

    function tearDown() : void {
        jApp::restoreContext();
    }

    public function testDependenciesReading()
    {
        $moduleInfos = new \Jelix\Installer\ModuleStatus('test',
            jApp::appPath().'modules/test/', array(
                'enabled'=>true,
                'dbprofile'=>'default',
                'installed'=>false,
                'version'=>jFramework::version(),
            ), true);

        $comp = new testInstallerComponentForDependencies($moduleInfos, $this->globalSetup);


        $str = '<?xml version="1.0" encoding="UTF-8"?>
<module xmlns="http://jelix.org/ns/module/1.0">
</module>';
        $comp->setModuleXmlContent($str);
        $this->assertEquals(array(), $comp->getDependencies());
        $this->assertEquals(array('*','*'), $comp->getJelixVersion());

        $str = '<?xml version="1.0" encoding="UTF-8"?>
<module xmlns="http://jelix.org/ns/module/1.0">
    <dependencies>
    </dependencies>
</module>';
        $comp->setModuleXmlContent($str);
        $this->assertEquals(array(), $comp->getDependencies());
        $this->assertEquals(array('*','*'), $comp->getJelixVersion());

        $str = '<?xml version="1.0" encoding="UTF-8"?>
<module xmlns="http://jelix.org/ns/module/1.0">
    <dependencies>
        <jelix minversion="1.0" maxversion="1.1" />
    </dependencies>
</module>';

        $comp->setModuleXmlContent($str);
        $this->assertEquals(array(
            array(
                'type'=> 'module',
                'id' => 'jelix@jelix.org',
                'name' => 'jelix',
                'minversion' => '1.0',
                'maxversion' => '1.1',
                'version' => '>=1.0,<=1.1',
                'optional' => false
            )
            ), $comp->getDependencies());
        $this->assertEquals(array('1.0', '1.1'), $comp->getJelixVersion());


        $str = '<?xml version="1.0" encoding="UTF-8"?>
<module xmlns="http://jelix.org/ns/module/1.0">
    <dependencies>
        <jelix minversion="1.0" maxversion="1.1" />
        <module name="jauthdb" />
        <module name="jacl2db" id="jacl2db@jelix.org"  />
        <module name="news"  id="news@testapp.jelix.org"  minversion="1.0" optional="true"/>
    </dependencies>
</module>';

        $comp->setModuleXmlContent($str);
        $this->assertEquals(array(
            array(
                'type'=> 'module',
                'id' => 'jelix@jelix.org',
                'name' => 'jelix',
                'minversion' => '1.0',
                'maxversion' => '1.1',
                'version' => '>=1.0,<=1.1',
                'optional' => false,
            ),
            array(
                'type'=> 'module',
                'id' => '',
                'name' => 'jauthdb',
                'minversion' => '0',
                'maxversion' => '*',
                'version' => '*',
                'optional' => false,
            ),
            array(
                'type'=> 'module',
                'id' => 'jacl2db@jelix.org',
                'name' => 'jacl2db',
                'minversion' => '0',
                'maxversion' => '*',
                'version' => '*',
                'optional' => false,
            ),
            array(
                'type'=> 'module',
                'id' => 'news@testapp.jelix.org',
                'name' => 'news',
                'minversion' => '1.0',
                'maxversion' => '*',
                'version' => '>=1.0',
                'optional' => true,
            ),
            ), $comp->getDependencies());
        $this->assertEquals(array('1.0', '1.1'), $comp->getJelixVersion());
    }

    function testGetInstallerWithNoInstaller() {
        try {
            $moduleInfos = new \Jelix\Installer\ModuleStatus('testinstall1',
                jApp::appPath() . 'modules/testinstall1/', array(
                    'enabled'=>true,
                    'dbprofile'=>'default',
                    'installed'=>false,
                    'version'=>jFramework::version(),
                ), true);
            // testinstall1 has no install.php file
            $component = new \Jelix\Installer\ModuleInstallerLauncher($moduleInfos, $this->globalSetup);
            $this->globalSetup->addModuleComponent($component);

            $installer = $component->getInstaller();
            $this->assertNull($installer);
            $configurator = $component->getConfigurator($component::CONFIGURATOR_TO_CONFIGURE);
            $this->assertNotNull($configurator);
        }
        catch(\Jelix\Installer\Exception $e) {
            $this->fail("Unexpected exception : ".$e->getMessage()." (".var_export($e->getLocaleParameters(),true).")");
        }

    }

    function testGetInstallerWithInstaller() {
        try {
            // dummy ini file modifier. not used by installer of tested modules
            $moduleInfos = new \Jelix\Installer\ModuleStatus('testinstall2',
                jApp::appPath().'modules/testinstall2/', array(
                    'enabled'=>true,
                    'dbprofile'=>'default',
                    'installed'=>false,
                    'version'=>jFramework::version(),
                ), true);

            // testinstall2 has an install.php and configure.php file
            $component = new \Jelix\Installer\ModuleInstallerLauncher($moduleInfos, $this->globalSetup);
            $this->globalSetup->addModuleComponent($component);

            $installer = $component->getInstaller();
            $this->assertTrue (is_object($installer));

            $configurator = $component->getConfigurator($component::CONFIGURATOR_TO_CONFIGURE);
            $this->assertTrue (is_object($configurator));
        }
        catch(\Jelix\Installer\Exception $e) {
            $this->fail("Unexpected exception : ".$e->getMessage()." (".var_export($e->getLocaleParameters(),true).")");
        }
    }

    function testGetUpgradersWithNoUpgraders() {
        try {
            $moduleInfos = new \Jelix\Installer\ModuleStatus('testinstall1',
                jApp::appPath().'modules/testinstall1/', array(
                    'enabled'=>true,
                    'dbprofile'=>'default',
                    'installed'=>false,
                    'version'=>jFramework::version(),
                ), true);

            // testinstall1 has no upgrade scripts
            $component = new \Jelix\Installer\ModuleInstallerLauncher($moduleInfos, $this->globalSetup);
            $this->globalSetup->addModuleComponent($component);

            $upgraders = $component->getUpgraders();
            $this->assertTrue(is_array($upgraders));
            $this->assertEquals(0, count($upgraders));
        }
        catch(\Jelix\Installer\Exception $e) {
            $this->fail("Unexpected exception : ".$e->getMessage()." (".var_export($e->getLocaleParameters(),true).")");
        }
    }

    function testGetUpgradersWithNoValidUpgrader() {

        try {
            // the current version is the latest one : no updaters
            //------------ testinstall2 has some upgraders file
            $moduleInfos = new \Jelix\Installer\ModuleStatus('testinstall2',
                jApp::appPath().'modules/testinstall2/', array(
                    'enabled'=>true,
                    'dbprofile'=>'default',
                    'installed'=>false,
                    'version'=>jFramework::version(),
                ), true);

            // testinstall2 has an install.php file
            $component = new \Jelix\Installer\ModuleInstallerLauncher($moduleInfos, $this->globalSetup);
            $this->globalSetup->addModuleComponent($component);

            $upgraders = $component->getUpgraders();
            $this->assertTrue (is_array($upgraders));
            $this->assertEquals(0, count($upgraders));
        }
        catch(\Jelix\Installer\Exception $e) {
            $this->fail("Unexpected exception : ".$e->getMessage()." (".var_export($e->getLocaleParameters(),true).")");
        }
    }

    function testGetUpgradersWithOneValidUpgrader() {
        try {
            $moduleInfos = new \Jelix\Installer\ModuleStatus('testinstall2',
                jApp::appPath().'modules/testinstall2/', array(
                    'enabled'=>true,
                    'dbprofile'=>'default',
                    'installed'=>false,
                    'version'=>"1.2.3",
                ), true);

            // the current version is the previous one : one updater
            $component = new \Jelix\Installer\ModuleInstallerLauncher($moduleInfos, $this->globalSetup);
            $this->globalSetup->addModuleComponent($component);

            $upgraders = $component->getUpgraders();
            $this->assertTrue (is_array($upgraders));
            $this->assertEquals(2, count($upgraders));
            $this->assertEquals('testinstall2ModuleUpgrader_newupgraderfilename', get_class($upgraders[0]));
            $this->assertEquals('testinstall2ModuleUpgrader', get_class($upgraders[1]));

        }
        catch(\Jelix\Installer\Exception $e) {
            $this->fail("Unexpected exception : ".$e->getMessage()." (".var_export($e->getLocaleParameters(),true).")");
        }
    }


    function testGetUpgradersWithTwoValidUpgrader() {
        try {
            $moduleInfos = new \Jelix\Installer\ModuleStatus('testinstall2',
                jApp::appPath().'modules/testinstall2/', array(
                    'enabled'=>true,
                    'dbprofile'=>'default',
                    'installed'=>false,
                    'version'=>"1.1.2",
                ), true);

            // the current version is the previous one : one updater
            $component = new \Jelix\Installer\ModuleInstallerLauncher($moduleInfos, $this->globalSetup);
            $this->globalSetup->addModuleComponent($component);

           $upgraders = $component->getUpgraders();

            $this->assertTrue (is_array($upgraders));
            $this->assertEquals(3, count($upgraders));
            $this->assertEquals('testinstall2ModuleUpgrader_newupgraderfilenamedate', get_class($upgraders[0]));
            $this->assertEquals('testinstall2ModuleUpgrader_second', get_class($upgraders[1]));
            //$this->assertEquals('testinstall2ModuleUpgrader_newupgraderfilename', get_class($upgraders[2]));
            $this->assertEquals('testinstall2ModuleUpgrader', get_class($upgraders[2]));
        }
        catch(\Jelix\Installer\Exception $e) {
            $this->fail("Unexpected exception : ".$e->getMessage()." (".var_export($e->getLocaleParameters(),true).")");
        }
    }

    function testGetUpgradersWithTwoValidUpgrader2() {
        try {
            $moduleInfos = new \Jelix\Installer\ModuleStatus('testinstall2',
                jApp::appPath().'modules/testinstall2/', array(
                    'enabled'=>true,
                    'dbprofile'=>'default',
                    'installed'=>false,
                    'version'=>"1.1.1",
                ), true);


            // the current version is the previous one : one updater
            $component = new \Jelix\Installer\ModuleInstallerLauncher($moduleInfos, $this->globalSetup);
            $this->globalSetup->addModuleComponent($component);

            // since newupgraderfilename targets '1.1.2' and '1.2.4', we should have newupgraderfilename then second

            $upgraders = $component->getUpgraders();
            $this->assertTrue (is_array($upgraders));
            $this->assertEquals(4, count($upgraders));
            $this->assertEquals('testinstall2ModuleUpgrader_newupgraderfilename', get_class($upgraders[0]));
            $this->assertEquals('testinstall2ModuleUpgrader_newupgraderfilenamedate', get_class($upgraders[1]));
            $this->assertEquals('testinstall2ModuleUpgrader_second', get_class($upgraders[2]));
            $this->assertEquals('testinstall2ModuleUpgrader', get_class($upgraders[3]));
        }
        catch(\Jelix\Installer\Exception $e) {
            $this->fail("Unexpected exception : ".$e->getMessage()." (".var_export($e->getLocaleParameters(),true).")");
        }
    }

    function testGetUpgradersWithTwoValidUpgraderWithDate() {
        try {
            // dummy ini file modifier. not used by installer of tested modules
            file_put_contents(jApp::tempPath('dummyInstaller.ini'), '');
            $installerIni = new \Jelix\IniFile\IniModifier(jApp::tempPath('dummyInstaller.ini'));
            $this->globalSetup->setInstallerIni($installerIni);

            // 1.1  1.1.2* 1.1.3** 1.1.5 1.2.2** 1.2.4*
            $installerIni->setValue('testinstall2.firstversion', '1.1' , 'modules');
            $installerIni->setValue('testinstall2.firstversion.date', '2011-01-10' , 'modules');
            $installerIni->setValue('testinstall2.version', '1.1' , 'modules');
            $installerIni->setValue('testinstall2.version.date', '2011-01-10' , 'modules');

            $moduleStatus = new \Jelix\Installer\ModuleStatus('testinstall2',
                jApp::appPath('modules/testinstall2/'), array(
                    'enabled'=>true,
                    'dbprofile'=>'default',
                    'installed'=>false,
                    'version'=>"1.1",
                ), true);
            $component = new testInstallerComponentModule2($moduleStatus, $this->globalSetup);
            $component->init();
            $component->setSourceVersionDate('1.1.5','2011-01-15');
            $this->globalSetup->addModuleComponent($component);
            $component->setSourceVersionDate('1.1.5','2011-01-15');

            $upgraders = $component->getUpgraders();
            $this->assertTrue (is_array($upgraders));
            $this->assertEquals(4, count($upgraders));
            $this->assertEquals('testinstall2ModuleUpgrader_newupgraderfilename', get_class($upgraders[0]));
            $this->assertEquals('testinstall2ModuleUpgrader_newupgraderfilenamedate', get_class($upgraders[1]));
            $this->assertEquals('testinstall2ModuleUpgrader_second', get_class($upgraders[2]));
            $this->assertEquals('testinstall2ModuleUpgrader', get_class($upgraders[3]));


            $installerIni->setValue('testinstall2.firstversion', '1.1.3' , 'modules');
            $installerIni->setValue('testinstall2.firstversion.date', '2011-01-13' , 'modules');
            $installerIni->setValue('testinstall2.version', '1.1.5' , 'modules');
            $installerIni->setValue('testinstall2.version.date', '2011-01-15' , 'modules');
            $moduleInfos = new \Jelix\Installer\ModuleStatus('testinstall2',
                jApp::appPath('modules/testinstall2/'), array(
                    'enabled'=>true,
                    'dbprofile'=>'default',
                    'installed'=>false,
                    'version'=>"1.1.5",
                ), true);
            $component = new testInstallerComponentModule2($moduleInfos, $this->globalSetup);
            $component->init();
            $component->setSourceVersionDate('1.2.5','2011-01-25');
            $this->globalSetup->addModuleComponent($component);

            $upgraders = $component->getUpgraders();
            $this->assertTrue (is_array($upgraders));
            $this->assertEquals(2, count($upgraders));
            $this->assertEquals('testinstall2ModuleUpgrader_newupgraderfilename', get_class($upgraders[0]));
            $this->assertEquals('testinstall2ModuleUpgrader', get_class($upgraders[1]));
        }
        catch(\Jelix\Installer\Exception $e) {
            $this->fail("Unexpected exception : ".$e->getMessage()." (".var_export($e->getLocaleParameters(),true).")");
        }
    }

    function testGetUpgradersWithAllUpgraders() {
        try {
            $moduleInfos = new \Jelix\Installer\ModuleStatus('testinstall2',
                jApp::appPath().'modules/testinstall2/', array(
                    'enabled'=>true,
                    'dbprofile'=>'default',
                    'installed'=>false,
                    'version'=>"0.9",
                ), true);

            // the current version is a very old one : all updaters
            $component = new \Jelix\Installer\ModuleInstallerLauncher($moduleInfos, $this->globalSetup);
            $this->globalSetup->addModuleComponent($component);

            $upgraders = $component->getUpgraders();
            $this->assertTrue (is_array($upgraders));
            $this->assertEquals(5, count($upgraders));
            $this->assertEquals('testinstall2ModuleUpgrader_first', get_class($upgraders[0]));
            $this->assertEquals('testinstall2ModuleUpgrader_newupgraderfilename', get_class($upgraders[1]));
            $this->assertEquals('testinstall2ModuleUpgrader_newupgraderfilenamedate', get_class($upgraders[2]));
            $this->assertEquals('testinstall2ModuleUpgrader_second', get_class($upgraders[3]));
            $this->assertEquals('testinstall2ModuleUpgrader', get_class($upgraders[4]));

        }
        catch(\Jelix\Installer\Exception $e) {
            $this->fail("Unexpected exception : ".$e->getMessage()." (".var_export($e->getLocaleParameters(),true).")");
        }
    }

    function testGetUpgradersWithMainUpgrader() {
        try {
            $moduleInfos = new \Jelix\Installer\ModuleStatus('testinstall3',
                jApp::appPath().'modules/testinstall3/', array(
                    'enabled'=>true,
                    'dbprofile'=>'default',
                    'installed'=>false,
                    'version'=>"1.5.0",
                ), true);

            // the current version is the previous one : one updater
            $component = new \Jelix\Installer\ModuleInstallerLauncher($moduleInfos, $this->globalSetup);
            $this->globalSetup->addModuleComponent($component);

            $upgraders = $component->getUpgraders();
            $this->assertTrue (is_array($upgraders));
            $this->assertEquals(2, count($upgraders));
            $this->assertEquals('testinstall3ModuleUpgrader_newcomp', get_class($upgraders[0]));
            $this->assertEquals('testinstall3ModuleUpgrader', get_class($upgraders[1]));

        }
        catch(\Jelix\Installer\Exception $e) {
            $this->fail("Unexpected exception : ".$e->getMessage()." (".var_export($e->getLocaleParameters(),true).")");
        }
    }

    function testGetUpgradersWithMainUpgraderAlreadyUpgraded() {
        try {
            // the current installed version is the current one: no upgraded
            $path = jApp::appPath().'modules/testinstall3/';
            $infos = ModuleInfos::load($path);
            $moduleInfos = new \Jelix\Installer\ModuleStatus('testinstall3',
                $path, array(
                    'enabled'=>true,
                    'dbprofile'=>'default',
                    'installed'=>false,
                    'version'=>$infos->version,
                ), true);

            $component = new \Jelix\Installer\ModuleInstallerLauncher($moduleInfos, $this->globalSetup);
            $this->globalSetup->addModuleComponent($component);

            $upgraders = $component->getUpgraders();
            $this->assertTrue (is_array($upgraders));
            $this->assertEquals(0, count($upgraders));

        }
        catch(\Jelix\Installer\Exception $e) {
            $this->fail("Unexpected exception : ".$e->getMessage()." (".var_export($e->getLocaleParameters(),true).")");
        }
    }

    function testGetUpgradersWithMainUpgraderNoUpgrader() {
        try {
            $moduleInfos = new \Jelix\Installer\ModuleStatus('testinstall3',
                jApp::appPath().'modules/testinstall3/', array(
                    'enabled'=>true,
                    'dbprofile'=>'default',
                    'installed'=>false,
                    'version'=>"1.6.3",
                ), true);

            // the current version is the previous one : one updater
            $component = new \Jelix\Installer\ModuleInstallerLauncher($moduleInfos, $this->globalSetup);
            $this->globalSetup->addModuleComponent($component);

            $upgraders = $component->getUpgraders();
            $this->assertTrue (is_array($upgraders));
            $this->assertEquals(1, count($upgraders));
            $this->assertEquals('testinstall3ModuleUpgrader', get_class($upgraders[0]));

        }
        catch(\Jelix\Installer\Exception $e) {
            $this->fail("Unexpected exception : ".$e->getMessage()." (".var_export($e->getLocaleParameters(),true).")");
        }
    }
}

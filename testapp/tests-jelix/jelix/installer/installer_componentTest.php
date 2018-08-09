<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2009-2018 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
* @since 1.2
*/


require_once(__DIR__.'/installer.lib.php');

class testInstallerComponentModule2 extends jInstallerComponentModule {

    function setSourceVersionDate($version, $date) {
        $this->sourceDate = $date;
        $this->sourceVersion = $version;
    }

}



class testInstallerComponentForDependencies extends jInstallerComponentModule {
    
    protected $identityNamespace = 'http://jelix.org/ns/module/1.0';
    protected $rootName = 'module';
    protected $identityFile = 'module.xml';
    
    function getInstaller($installWholeApp) {
        return null;
    }

    function getUpgraders() {
        return null;
    }
    
    function readDependenciesFromString($xmlcontent) {
        $xml = simplexml_load_string($xmlcontent);
        //$this->sourceVersion = (string) $xml->info[0]->version[0];   
        $this->readDependencies($xml);
    }
    
}

class jInstaller_ComponentTest extends jUnitTestCase {

    /**
     * @var testInstallerGlobalSetup
     */
    protected $globalSetup;

    function setUp() {
        self::initJelixConfig();
        $this->globalSetup = new testInstallerGlobalSetup();
        jApp::saveContext();
    }

    function tearDown() {
        jApp::restoreContext();
    }

    public function testDependenciesReading() {
        $conf =(object) array( 'modules'=>array(
            'test.access'=>2,
            'test.dbprofile'=>'default',
            'test.installed'=>false,
            'test.version'=>jFramework::version(),
        ));

        $moduleInfos = new jInstallerModuleInfos('test',
            jApp::appPath().'modules/test/', $conf->modules);

        $comp = new testInstallerComponentForDependencies($moduleInfos, $this->globalSetup);

        $str = '<?xml version="1.0" encoding="UTF-8"?>
<module xmlns="http://jelix.org/ns/module/1.0">
</module>';
        $comp->readDependenciesFromString($str);
        $this->assertEquals(array(), $comp->getDependencies());
        $this->assertEquals(array('*','*'), $comp->getJelixVersion());

        $str = '<?xml version="1.0" encoding="UTF-8"?>
<module xmlns="http://jelix.org/ns/module/1.0">
    <dependencies>
    </dependencies>
</module>';
        $comp->readDependenciesFromString($str);
        $this->assertEquals(array(), $comp->getDependencies());
        $this->assertEquals(array('*','*'), $comp->getJelixVersion());

        $str = '<?xml version="1.0" encoding="UTF-8"?>
<module xmlns="http://jelix.org/ns/module/1.0">
    <dependencies>
        <jelix minversion="1.0" maxversion="1.1" />
    </dependencies>
</module>';

        $comp->readDependenciesFromString($str);
        $this->assertEquals(array(
            array(
                'type'=> 'module',
                'id' => 'jelix@jelix.org',
                'name' => 'jelix',
                'minversion' => '1.0',
                'maxversion' => '1.1',
                'version' => '>=1.0,<=1.1'
            )
            ), $comp->getDependencies());
        $this->assertEquals(array('1.0', '1.1'), $comp->getJelixVersion());


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
        $this->assertEquals(array(
            array(
                'type'=> 'module',
                'id' => 'jelix@jelix.org',
                'name' => 'jelix',
                'minversion' => '1.0',
                'maxversion' => '1.1',
                'version' => '>=1.0,<=1.1'
            ),
            array(
                'type'=> 'module',
                'id' => '',
                'name' => 'jauthdb',
                'minversion' => '0',
                'maxversion' => '*',
                'version' => '*'
            ),
            array(
                'type'=> 'module',
                'id' => 'jacl2db@jelix.org',
                'name' => 'jacl2db',
                'minversion' => '0',
                'maxversion' => '*',
                'version' => '*'
            ),
            array(
                'type'=> 'module',
                'id' => 'jacldb@jelix.org',
                'name' => 'jacldb',
                'minversion' => '1.0',
                'maxversion' => '*',
                'version' => '>=1.0'
            ),
            ), $comp->getDependencies());
        $this->assertEquals(array('1.0', '1.1'), $comp->getJelixVersion());
    }


    function testGetInstallerWithNoInstaller() {
        try {
            $conf =(object) array( 'modules'=>array(
                'testinstall1.access'=>2,
                'testinstall1.dbprofile'=>'default',
                'testinstall1.installed'=>false,
                'testinstall1.version'=>jFramework::version(),
            ));

            $moduleInfos = new jInstallerModuleInfos('testinstall1',
                jApp::appPath().'modules/testinstall1/', $conf->modules);
            // testinstall1 has no install.php file
            $component = new jInstallerComponentModule($moduleInfos, $this->globalSetup);
            $this->globalSetup->addModuleComponent($component);

            $installer = $component->getInstaller(true);
            $this->assertNull($installer);
        }
        catch(jInstallerException $e) {
            $this->fail("Unexpected exception : ".$e->getMessage()." (".var_export($e->getLocaleParameters(),true).")");
        }
        
    }

    function testGetInstallerWithInstaller() {
        try {
            // dummy ini file modifier. not used by installer of tested modules
            $conf =(object) array( 'modules'=>array(
                'testinstall2.access'=>2,
                'testinstall2.dbprofile'=>'default',
                'testinstall2.installed'=>false,
                'testinstall2.version'=>jFramework::version(),
            ));

            $moduleInfos = new jInstallerModuleInfos('testinstall2',
                jApp::appPath().'modules/testinstall2/', $conf->modules);

            // testinstall2 has an install.php file
            $component = new jInstallerComponentModule($moduleInfos, $this->globalSetup);
            $this->globalSetup->addModuleComponent($component);

            $installer = $component->getInstaller(true);
            $this->assertTrue (is_object($installer));

        }
        catch(jInstallerException $e) {
            $this->fail("Unexpected exception : ".$e->getMessage()." (".var_export($e->getLocaleParameters(),true).")");
        }
    }


    function testGetUpgradersWithNoUpgraders() {
        try {
            $conf =(object) array( 'modules'=>array(
               'testinstall1.access'=>2,
               'testinstall1.dbprofile'=>'default',
               'testinstall1.installed'=>false,
               'testinstall1.version'=>jFramework::version(),
            ));
            $moduleInfos = new jInstallerModuleInfos('testinstall1',
                jApp::appPath().'modules/testinstall1/', $conf->modules);

            // testinstall1 has no upgrade scripts
            $component = new jInstallerComponentModule($moduleInfos, $this->globalSetup);
            $this->globalSetup->addModuleComponent($component);

            $upgraders = $component->getUpgraders();
            $this->assertTrue(is_array($upgraders));
            $this->assertEquals(0, count($upgraders));
        }
        catch(jInstallerException $e) {
            $this->fail("Unexpected exception : ".$e->getMessage()." (".var_export($e->getLocaleParameters(),true).")");
        }
    }

    function testGetUpgradersWithNoValidUpgrader() {

        try {
            // the current version is the latest one : no updaters
            $conf =(object) array( 'modules'=>array(
               'testinstall2.access'=>2, 
               'testinstall2.dbprofile'=>'default', 
               'testinstall2.installed'=>false, 
               'testinstall2.version'=>jFramework::version(),
            ));

            //------------ testinstall2 has some upgraders file
            $moduleInfos = new jInstallerModuleInfos('testinstall2',
                jApp::appPath().'modules/testinstall2/', $conf->modules);

            // testinstall2 has an install.php file
            $component = new jInstallerComponentModule($moduleInfos, $this->globalSetup);
            $this->globalSetup->addModuleComponent($component);

            $upgraders = $component->getUpgraders();
            $this->assertTrue (is_array($upgraders));
            $this->assertEquals(0, count($upgraders));
        }
        catch(jInstallerException $e) {
            $this->fail("Unexpected exception : ".$e->getMessage()." (".var_export($e->getLocaleParameters(),true).")");
        }
    }

    function testGetUpgradersWithOneValidUpgrader() {
        try {
            $conf =(object) array( 'modules'=>array(
               'testinstall2.access'=>2, 
               'testinstall2.dbprofile'=>'default', 
               'testinstall2.installed'=>false, 
               'testinstall2.version'=>"1.2.3", 
            ));
            $moduleInfos = new jInstallerModuleInfos('testinstall2',
                jApp::appPath().'modules/testinstall2/', $conf->modules);

            // the current version is the previous one : one updater
            $component = new jInstallerComponentModule($moduleInfos, $this->globalSetup);
            $this->globalSetup->addModuleComponent($component);

            $upgraders = $component->getUpgraders();
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
            $conf =(object) array( 'modules'=>array(
               'testinstall2.access'=>2, 
               'testinstall2.dbprofile'=>'default', 
               'testinstall2.installed'=>false, 
               'testinstall2.version'=>"1.1.2", 
            ));
            $moduleInfos = new jInstallerModuleInfos('testinstall2',
                jApp::appPath().'modules/testinstall2/', $conf->modules);

            // the current version is the previous one : one updater
            $component = new jInstallerComponentModule($moduleInfos, $this->globalSetup);
            $this->globalSetup->addModuleComponent($component);

            // since newupgraderfilename targets '1.1.2' and '1.2.4', we should have second then newupgraderfilename
            $upgraders = $component->getUpgraders();
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
            $conf =(object) array( 'modules'=>array(
               'testinstall2.access'=>2, 
               'testinstall2.dbprofile'=>'default', 
               'testinstall2.installed'=>false, 
               'testinstall2.version'=>"1.1.1", 
            ));
            $moduleInfos = new jInstallerModuleInfos('testinstall2',
                jApp::appPath().'modules/testinstall2/', $conf->modules);

            // the current version is the previous one : one updater
            $component = new jInstallerComponentModule($moduleInfos, $this->globalSetup);
            $this->globalSetup->addModuleComponent($component);

            // since newupgraderfilename targets '1.1.2' and '1.2.4', we should have newupgraderfilename then second

            $upgraders = $component->getUpgraders();
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
            file_put_contents(jApp::tempPath('dummyInstaller.ini'), '');
            $installerIni = new \Jelix\IniFile\IniModifier(jApp::tempPath('dummyInstaller.ini'));
            $this->globalSetup->setInstallerIni($installerIni);

            // 1.1  1.1.2* 1.1.3** 1.1.5 1.2.2** 1.2.4*
            $installerIni->setValue('testinstall2.firstversion', '1.1' , 'modules');
            $installerIni->setValue('testinstall2.firstversion.date', '2011-01-10' , 'modules');
            $installerIni->setValue('testinstall2.version', '1.1.2' , 'modules');
            $installerIni->setValue('testinstall2.version.date', '2011-01-12' , 'modules');

            $conf =(object) array( 'modules'=>array(
               'testinstall2.access'=>2, 
               'testinstall2.dbprofile'=>'default', 
               'testinstall2.installed'=>false, 
               'testinstall2.version'=>"1.1", 
            ));

            $moduleInfos = new jInstallerModuleInfos('testinstall2', jApp::appPath('modules/testinstall2/'), $conf->modules);
            $component = new testInstallerComponentModule2($moduleInfos, $this->globalSetup);
            $component->setSourceVersionDate('1.1.5','2011-01-15');
            $this->globalSetup->addModuleComponent($component);

            $upgraders = $component->getUpgraders();
            $this->assertTrue (is_array($upgraders));
            $this->assertEquals(3, count($upgraders));
            $this->assertEquals('testinstall2ModuleUpgrader_newupgraderfilename', get_class($upgraders[0]));
            $this->assertEquals('testinstall2ModuleUpgrader_newupgraderfilenamedate', get_class($upgraders[1]));
            $this->assertEquals('testinstall2ModuleUpgrader_second', get_class($upgraders[2]));


            $installerIni->setValue('testinstall2.firstversion', '1.1.3' , 'modules');
            $installerIni->setValue('testinstall2.firstversion.date', '2011-01-13' , 'modules');
            $installerIni->setValue('testinstall2.version', '1.1.5' , 'modules');
            $installerIni->setValue('testinstall2.version.date', '2011-01-15' , 'modules');
            $conf =(object) array( 'modules'=>array(
               'testinstall2.access'=>2,
               'testinstall2.dbprofile'=>'default', 
               'testinstall2.installed'=>false, 
               'testinstall2.version'=>"1.1.5", 
            ));
            $moduleInfos = new jInstallerModuleInfos('testinstall2', jApp::appPath('modules/testinstall2/'), $conf->modules);
            $component = new testInstallerComponentModule2($moduleInfos, $this->globalSetup);
            $component->setSourceVersionDate('1.2.5','2011-01-25');
            $this->globalSetup->addModuleComponent($component);

            $upgraders = $component->getUpgraders();
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
            $conf =(object) array( 'modules'=>array(
               'testinstall2.access'=>2,
               'testinstall2.dbprofile'=>'default',
               'testinstall2.installed'=>false,
               'testinstall2.version'=>"0.9",
            ));
            $moduleInfos = new jInstallerModuleInfos('testinstall2',
                jApp::appPath().'modules/testinstall2/', $conf->modules);

            // the current version is a very old one : all updaters
            $component = new jInstallerComponentModule($moduleInfos, $this->globalSetup);
            $this->globalSetup->addModuleComponent($component);

            $upgraders = $component->getUpgraders();
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

    function testGetUpgradersWithMainUpgrader() {
        try {
            $conf =(object) array( 'modules'=>array(
                'testinstall3.access'=>2,
                'testinstall3.dbprofile'=>'default',
                'testinstall3.installed'=>false,
                'testinstall3.version'=>"1.5.0",
            ));
            $moduleInfos = new jInstallerModuleInfos('testinstall3',
                jApp::appPath().'modules/testinstall3/', $conf->modules);

            // the current version is the previous one : one updater
            $component = new jInstallerComponentModule($moduleInfos, $this->globalSetup);
            $this->globalSetup->addModuleComponent($component);

            $upgraders = $component->getUpgraders();
            $this->assertTrue (is_array($upgraders));
            $this->assertEquals(2, count($upgraders));
            $this->assertEquals('testinstall3ModuleUpgrader_newcomp', get_class($upgraders[0]));
            $this->assertEquals('testinstall3ModuleUpgrader', get_class($upgraders[1]));

        }
        catch(jInstallerException $e) {
            $this->fail("Unexpected exception : ".$e->getMessage()." (".var_export($e->getLocaleParameters(),true).")");
        }
    }

    function testGetUpgradersWithMainUpgraderAlreadyUpgraded() {
        try {
            $conf =(object) array( 'modules'=>array(
                'testinstall3.access'=>2,
                'testinstall3.dbprofile'=>'default',
                'testinstall3.installed'=>false,
                'testinstall3.version'=>"1.7.0-beta.3",
            ));
            $moduleInfos = new jInstallerModuleInfos('testinstall3',
                jApp::appPath().'modules/testinstall3/', $conf->modules);

            // the current version is the previous one : one updater
            $component = new jInstallerComponentModule($moduleInfos, $this->globalSetup);
            $this->globalSetup->addModuleComponent($component);

            $upgraders = $component->getUpgraders();
            $this->assertTrue (is_array($upgraders));
            $this->assertEquals(0, count($upgraders));

        }
        catch(jInstallerException $e) {
            $this->fail("Unexpected exception : ".$e->getMessage()." (".var_export($e->getLocaleParameters(),true).")");
        }
    }

    function testGetUpgradersWithMainUpgraderNoUpgrader() {
        try {
            $conf =(object) array( 'modules'=>array(
                'testinstall3.access'=>2,
                'testinstall3.dbprofile'=>'default',
                'testinstall3.installed'=>false,
                'testinstall3.version'=>"1.6.3",
            ));
            $moduleInfos = new jInstallerModuleInfos('testinstall3',
                jApp::appPath().'modules/testinstall3/', $conf->modules);

            // the current version is the previous one : one updater
            $component = new jInstallerComponentModule($moduleInfos, $this->globalSetup);
            $this->globalSetup->addModuleComponent($component);

            $upgraders = $component->getUpgraders();
            $this->assertTrue (is_array($upgraders));
            $this->assertEquals(1, count($upgraders));
            $this->assertEquals('testinstall3ModuleUpgrader', get_class($upgraders[0]));

        }
        catch(jInstallerException $e) {
            $this->fail("Unexpected exception : ".$e->getMessage()." (".var_export($e->getLocaleParameters(),true).")");
        }
    }
}


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


class jInstaller_DependenciesTest extends jUnitTestCase {

    protected $defaultIni;

    public function setUp() {
        jApp::saveContext();
        self::initJelixConfig();
        $this->defaultIni = new \Jelix\IniFile\IniModifier(jApp::configPath().'mainconfig.ini.php');
    }

    public function tearDown() {
        jApp::restoreContext();
    }

    public function testOneModuleNoDeps() {
        $ini = new testInstallerIniFileModifier("test.ini.php");
        $conf =(object) array( 'modules'=>array(
            'testA.access'=>2,
            'testA.dbprofile'=>'default',
            'testA.installed'=>false,
            'testA.version'=>'1.0',
        ));
        $ep = new testInstallerEntryPoint($this->defaultIni, $ini, 'index.php', 'classic', $conf);

        $modInfos = new testInstallerModuleInfos('/testA', '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testA@modules.jelix.org" name="testA">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix version="'.JELIX_VERSION.'" />
                        </dependencies>
                    </module>');
        $ep->setModuleData('testA', $modInfos);
        $ep->createInstallLaunchers(function($moduleStatus, $moduleInfos){
            return new \Jelix\Installer\ModuleInstallLauncher($moduleInfos, null);
        });
        $result = $ep->getOrderedDependencies(array('testA'=>$ep->getLauncher('testA')));

        $this->assertTrue(is_array($result));

        $expected = '<?xml version="1.0"?>
    <array>
        <!--<array>
            <object class="\Jelix\Installer\ModuleInstallLauncher">
                <string method="getName()" value="jelix" />
            </object>
            <boolean value="true" />
        </array>-->
        <array>
            <object class="\Jelix\Installer\ModuleInstallLauncher">
                <string method="getName()" value="testA" />
            </object>
            <boolean value="true" />
        </array>
    </array>';
        $this->assertComplexIdenticalStr($result, $expected);
    }

    public function test2Modules() {
        $ini = new testInstallerIniFileModifier("test.ini.php");
        $conf =(object) array( 'modules'=>array(
            'testA.access'=>2,
            'testA.dbprofile'=>'default',
            'testA.installed'=>false,
            'testA.version'=>"1.0",
            'testB.access'=>2,
            'testB.dbprofile'=>'default',
            'testB.installed'=>false,
            'testB.version'=>"1.0",
        ));
        $ep = new testInstallerEntryPoint($this->defaultIni, $ini, 'index.php', 'classic', $conf);

        $modInfos = new testInstallerModuleInfos('/testA', '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testA@modules.jelix.org" name="testA">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix version="'.JELIX_VERSION.'" />
                        </dependencies>
                    </module>');
        $ep->setModuleData('testA', $modInfos);
        $modInfos = new testInstallerModuleInfos('/testB',
                    '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testB@modules.jelix.org" name="testB">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix version="'.JELIX_VERSION.'" />
                            <module name="testA" />
                        </dependencies>
                    </module>'
                    );
        $ep->setModuleData('testB', $modInfos);

        $ep->createInstallLaunchers(function($moduleStatus, $moduleInfos){
            return new \Jelix\Installer\ModuleInstallLauncher($moduleInfos, null);
        });

        $result = $ep->getOrderedDependencies(array('testB'=>$ep->getLauncher('testB')));

        $this->assertTrue(is_array($result));

        $expected = '<?xml version="1.0"?>
    <array>
        <!--<array>
            <object class="\Jelix\Installer\ModuleInstallLauncher">
                <string method="getName()" value="jelix" />
            </object>
            <boolean value="true" />
        </array>-->
        <array>
            <object class="\Jelix\Installer\ModuleInstallLauncher">
                <string method="getName()" value="testA" />
            </object>
            <boolean value="true" />
        </array>
        <array>
            <object class="\Jelix\Installer\ModuleInstallLauncher">
                <string method="getName()" value="testB" />
            </object>
            <boolean value="true" />
        </array>
    </array>';
        $this->assertComplexIdenticalStr($result, $expected);
    }

    public function testComplexDependencies1() {

        //        A->B
        //        A->C
        //        D->B
        //        D->E

        $ini = new testInstallerIniFileModifier("test.ini.php");
        $conf =(object) array( 'modules'=>array(
            'testA.access'=>2,
            'testA.dbprofile'=>'default',
            'testA.installed'=>false,
            'testA.version'=>"1.0",
            'testB.access'=>2,
            'testB.dbprofile'=>'default',
            'testB.installed'=>false,
            'testB.version'=>"1.0",
            'testC.access'=>2,
            'testC.dbprofile'=>'default',
            'testC.installed'=>false,
            'testC.version'=>"1.0",
            'testD.access'=>2,
            'testD.dbprofile'=>'default',
            'testD.installed'=>false,
            'testD.version'=>"1.0",
            'testE.access'=>2,
            'testE.dbprofile'=>'default',
            'testE.installed'=>false,
            'testE.version'=>"1.0",
        ));
        $ep = new testInstallerEntryPoint($this->defaultIni, $ini, 'index.php', 'classic', $conf);

        $modInfos = new testInstallerModuleInfos('/testA', '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testA@modules.jelix.org" name="testA">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix version="'.JELIX_VERSION.'" />
                            <module name="testB" />
                            <module name="testC" />
                        </dependencies>
                    </module>');
        $ep->setModuleData('testA', $modInfos);
        $modInfos = new testInstallerModuleInfos('/testB', '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testB@modules.jelix.org" name="testB">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix version="'.JELIX_VERSION.'" />
                        </dependencies>
                    </module>');
        $ep->setModuleData('testB', $modInfos);
        $modInfos = new testInstallerModuleInfos('/testC', '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testC@modules.jelix.org" name="testC">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix version="'.JELIX_VERSION.'" />
                        </dependencies>
                    </module>');
        $ep->setModuleData('testC', $modInfos);
        $modInfos = new testInstallerModuleInfos('/testD', '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testD@modules.jelix.org" name="testD">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix version="'.JELIX_VERSION.'" />
                            <module name="testB" />
                            <module name="testE" />
                        </dependencies>
                    </module>');
        $ep->setModuleData('testD', $modInfos);
        $modInfos = new testInstallerModuleInfos('/testE', '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testE@modules.jelix.org" name="testE">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix version="'.JELIX_VERSION.'" />
                        </dependencies>
                    </module>');
        $ep->setModuleData('testE', $modInfos);

        $ep->createInstallLaunchers(function($moduleStatus, $moduleInfos){
            return new \Jelix\Installer\ModuleInstallLauncher($moduleInfos, null);
        });

        $result = $ep->getOrderedDependencies(array('testA'=>$ep->getLauncher('testA'), 'testD'=>$ep->getLauncher('testD')));

        $this->assertTrue(is_array($result));

        $expected = '<?xml version="1.0"?>
    <array>
        <!--<array>
            <object class="\Jelix\Installer\ModuleInstallLauncher">
                <string method="getName()" value="jelix" />
            </object>
            <boolean value="true" />
        </array>-->
        <array>
            <object class="\Jelix\Installer\ModuleInstallLauncher">
                <string method="getName()" value="testB" />
            </object>
            <boolean value="true" />
        </array>
        <array>
            <object class="\Jelix\Installer\ModuleInstallLauncher">
                <string method="getName()" value="testC" />
            </object>
            <boolean value="true" />
        </array>
        <array>
            <object class="\Jelix\Installer\ModuleInstallLauncher">
                <string method="getName()" value="testA" />
            </object>
            <boolean value="true" />
        </array>
        <array>
            <object class="\Jelix\Installer\ModuleInstallLauncher">
                <string method="getName()" value="testE" />
            </object>
            <boolean value="true" />
        </array>
        <array>
            <object class="\Jelix\Installer\ModuleInstallLauncher">
                <string method="getName()" value="testD" />
            </object>
            <boolean value="true" />
        </array>
    </array>';
        $this->assertComplexIdenticalStr($result, $expected);
    }


    public function testComplexDependencies2() {

        //  A->B
        //  A->C
        //  D->E
        //  D->F
        //  B->F

        $ini = new testInstallerIniFileModifier("test.ini.php");
        $conf =(object) array( 'modules'=>array(
            'testA.access'=>2,
            'testA.dbprofile'=>'default',
            'testA.installed'=>false,
            'testA.version'=>"1.0",
            'testB.access'=>2,
            'testB.dbprofile'=>'default',
            'testB.installed'=>false,
            'testB.version'=>"1.0",
            'testC.access'=>2,
            'testC.dbprofile'=>'default',
            'testC.installed'=>false,
            'testC.version'=>"1.0",
            'testD.access'=>2,
            'testD.dbprofile'=>'default',
            'testD.installed'=>false,
            'testD.version'=>"1.0",
            'testE.access'=>2,
            'testE.dbprofile'=>'default',
            'testE.installed'=>false,
            'testE.version'=>"1.0",
            'testF.access'=>2,
            'testF.dbprofile'=>'default',
            'testF.installed'=>false,
            'testF.version'=>"1.0",
        ));
        $ep = new testInstallerEntryPoint($this->defaultIni, $ini, 'index.php', 'classic', $conf);

        $modInfos = new testInstallerModuleInfos('/testA', '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testA@modules.jelix.org" name="testA">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix version="'.JELIX_VERSION.'" />
                            <module name="testB" />
                            <module name="testC" />
                        </dependencies>
                    </module>');
        $ep->setModuleData('testA', $modInfos);

        $modInfos = new testInstallerModuleInfos('/testB', '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testB@modules.jelix.org" name="testB">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix version="'.JELIX_VERSION.'" />
                            <module name="testF" />
                        </dependencies>
                    </module>');
        $ep->setModuleData('testB', $modInfos);

        $modInfos = new testInstallerModuleInfos('/testC', '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testC@modules.jelix.org" name="testC">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix version="'.JELIX_VERSION.'" />
                        </dependencies>
                    </module>');
        $ep->setModuleData('testC', $modInfos);
        $modInfos = new testInstallerModuleInfos('/testD', '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testD@modules.jelix.org" name="testD">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix version="'.JELIX_VERSION.'" />
                            <module name="testE" />
                            <module name="testF" />
                        </dependencies>
                    </module>');
        $ep->setModuleData('testD', $modInfos);
        $modInfos = new testInstallerModuleInfos('/testE', '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testE@modules.jelix.org" name="testE">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix version="'.JELIX_VERSION.'" />
                        </dependencies>
                    </module>');
        $ep->setModuleData('testE', $modInfos);
        $modInfos = new testInstallerModuleInfos('/testF', '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testF@modules.jelix.org" name="testF">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix version="'.JELIX_VERSION.'" />
                        </dependencies>
                    </module>');
        $ep->setModuleData('testF', $modInfos);

        $ep->createInstallLaunchers(function($moduleStatus, $moduleInfos){
            return new \Jelix\Installer\ModuleInstallLauncher($moduleInfos, null);
        });

        $result = $ep->getOrderedDependencies(array('testA'=>$ep->getLauncher('testA'), 'testD'=>$ep->getLauncher('testD')));

        $this->assertTrue(is_array($result));

        $expected = '<?xml version="1.0"?>
    <array>
        <!--<array>
            <object class="\Jelix\Installer\ModuleInstallLauncher">
                <string method="getName()" value="jelix" />
            </object>
            <boolean value="true" />
        </array>-->
        <array>
            <object class="\Jelix\Installer\ModuleInstallLauncher">
                <string method="getName()" value="testF" />
            </object>
            <boolean value="true" />
        </array>
        <array>
            <object class="\Jelix\Installer\ModuleInstallLauncher">
                <string method="getName()" value="testB" />
            </object>
            <boolean value="true" />
        </array>
        <array>
            <object class="\Jelix\Installer\ModuleInstallLauncher">
                <string method="getName()" value="testC" />
            </object>
            <boolean value="true" />
        </array>
        <array>
            <object class="\Jelix\Installer\ModuleInstallLauncher">
                <string method="getName()" value="testA" />
            </object>
            <boolean value="true" />
        </array>
        <array>
            <object class="\Jelix\Installer\ModuleInstallLauncher">
                <string method="getName()" value="testE" />
            </object>
            <boolean value="true" />
        </array>
        <array>
            <object class="\Jelix\Installer\ModuleInstallLauncher">
                <string method="getName()" value="testD" />
            </object>
            <boolean value="true" />
        </array>
    </array>';
        $this->assertComplexIdenticalStr($result, $expected);
    }



   public function testCircularDependency() {

        // A->B->C->A

        $ini = new testInstallerIniFileModifier("test.ini.php");
        $conf =(object) array( 'modules'=>array(
            'testA.access'=>2,
            'testA.dbprofile'=>'default',
            'testA.installed'=>false,
            'testA.version'=>"1.0",
            'testB.access'=>2,
            'testB.dbprofile'=>'default',
            'testB.installed'=>false,
            'testB.version'=>"1.0",
            'testC.access'=>2,
            'testC.dbprofile'=>'default',
            'testC.installed'=>false,
            'testC.version'=>"1.0",
        ));
        $ep = new testInstallerEntryPoint($this->defaultIni, $ini, 'index.php', 'classic', $conf);

        $modInfos = new testInstallerModuleInfos('/testA', '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testA@modules.jelix.org" name="testA">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix version="'.JELIX_VERSION.'" />
                            <module name="testB" />
                        </dependencies>
                    </module>');
        $ep->setModuleData('testA', $modInfos);

        $modInfos = new testInstallerModuleInfos('/testB', '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testB@modules.jelix.org" name="testB">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix version="'.JELIX_VERSION.'" />
                            <module name="testC" />
                        </dependencies>
                    </module>');
        $ep->setModuleData('testB', $modInfos);

        $modInfos = new testInstallerModuleInfos('/testC', '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testC@modules.jelix.org" name="testC">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix version="'.JELIX_VERSION.'" />
                            <module name="testA" />
                        </dependencies>
                    </module>');
        $ep->setModuleData('testC', $modInfos);


        $ep->createInstallLaunchers(function($moduleStatus, $moduleInfos){
            return new \Jelix\Installer\ModuleInstallLauncher($moduleInfos, null);
        });

        try {
            $ep->getOrderedDependencies(array('testA'=>$ep->getLauncher('testA')));
            $this->assertFalse(true);
        }
        catch(\Jelix\Installer\Exception $e) {
            $this->assertEquals($e->getMessage(), 'module.circular.dependency');
        }

        try {
            $ep->getOrderedDependencies(array('testB'=>$ep->getLauncher('testB')));
            $this->assertFalse(true);
        }
        catch(\Jelix\Installer\Exception $e) {
            $this->assertEquals($e->getMessage(), 'module.circular.dependency');
        }
    }
}


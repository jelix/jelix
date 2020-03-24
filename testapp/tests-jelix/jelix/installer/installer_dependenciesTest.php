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

    protected $installer;
    protected $instReport;

    public function setUp() {
        self::initJelixConfig();
        jApp::saveContext();
        $this->instReport = new testInstallReporter();
        $this->installer = new testInstallerMain($this->instReport);
    }

    public function tearDown() {
        jApp::restoreContext();
        $this->instReport = null;
        $this->installer = null;
    }

    public function testOneModuleNoDeps() {

        $this->installer->testAddModule('testA',
                                       '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testA@modules.jelix.org" name="testA">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix minversion="'.JELIX_VERSION.'" maxversion="'.JELIX_VERSION.'" />
                        </dependencies>
                    </module>'
                    );
        $this->installer->initForTest();

        $this->assertTrue($this->installer->doCheckDependencies(array('testA')));

        $result = $this->installer->getComponentsToInstall();
        $expected = '<?xml version="1.0"?>
    <array>
        <array>
            <object class="jInstallerComponentModule">
                <string method="getName()" value="jelix" />
            </object>
            <boolean value="true" />
        </array>
        <array>
            <object class="testInstallerComponentModule">
                <string method="getName()" value="testA" />
            </object>
            <boolean value="true" />
        </array>
    </array>';
        $this->assertComplexIdenticalStr($result, $expected);
    }

    public function test2Modules() {

        $this->installer->testAddModule('testA',
                                       '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testA@modules.jelix.org" name="testA">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix minversion="'.JELIX_VERSION.'" maxversion="'.JELIX_VERSION.'" />
                        </dependencies>
                    </module>'
                    );
        $this->installer->testAddModule('testB',
                                       '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testB@modules.jelix.org" name="testB">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix minversion="'.JELIX_VERSION.'" maxversion="'.JELIX_VERSION.'" />
                            <module name="testA" />
                        </dependencies>
                    </module>'
                    );
        $this->installer->initForTest();

        $this->assertTrue($this->installer->doCheckDependencies(array('testB')));

        $result = $this->installer->getComponentsToInstall();
        $expected = '<?xml version="1.0"?>
    <array>
        <array>
            <object class="jInstallerComponentModule">
                <string method="getName()" value="jelix" />
            </object>
            <boolean value="true" />
        </array>
        <array>
            <object class="testInstallerComponentModule">
                <string method="getName()" value="testA" />
            </object>
            <boolean value="true" />
        </array>
        <array>
            <object class="testInstallerComponentModule">
                <string method="getName()" value="testB" />
            </object>
            <boolean value="true" />
        </array>
    </array>';
        $this->assertComplexIdenticalStr($result, $expected);
    }

    public function testComplexDependencies1() {
        /*
                A->B
                A->C
                D->B
                D->E
        */
        $this->installer->testAddModule('testA',
                                       '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testA@modules.jelix.org" name="testA">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix minversion="'.JELIX_VERSION.'" maxversion="'.JELIX_VERSION.'" />
                            <module name="testB" />
                            <module name="testC" />
                        </dependencies>
                    </module>'
                    );
        $this->installer->testAddModule('testB',
                                       '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testB@modules.jelix.org" name="testB">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix minversion="'.JELIX_VERSION.'" maxversion="'.JELIX_VERSION.'" />
                        </dependencies>
                    </module>'
                    );
        $this->installer->testAddModule('testC',
                                       '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testC@modules.jelix.org" name="testC">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix minversion="'.JELIX_VERSION.'" maxversion="'.JELIX_VERSION.'" />
                        </dependencies>
                    </module>'
                    );
        $this->installer->testAddModule('testD',
                                       '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testD@modules.jelix.org" name="testD">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix minversion="'.JELIX_VERSION.'" maxversion="'.JELIX_VERSION.'" />
                            <module name="testB" />
                            <module name="testE" />
                        </dependencies>
                    </module>'
                    );
        $this->installer->testAddModule('testE',
                                       '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testE@modules.jelix.org" name="testE">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix minversion="'.JELIX_VERSION.'" maxversion="'.JELIX_VERSION.'" />
                        </dependencies>
                    </module>'
                    );

        $this->installer->initForTest();

        $this->assertTrue($this->installer->doCheckDependencies(array('testA', 'testD')));

        $result = $this->installer->getComponentsToInstall();
        $expected = '<?xml version="1.0"?>
    <array>
        <array>
            <object class="jInstallerComponentModule">
                <string method="getName()" value="jelix" />
            </object>
            <boolean value="true" />
        </array>
        <array>
            <object class="testInstallerComponentModule">
                <string method="getName()" value="testB" />
            </object>
            <boolean value="true" />
        </array>
        <array>
            <object class="testInstallerComponentModule">
                <string method="getName()" value="testC" />
            </object>
            <boolean value="true" />
        </array>
        <array>
            <object class="testInstallerComponentModule">
                <string method="getName()" value="testA" />
            </object>
            <boolean value="true" />
        </array>
        <array>
            <object class="testInstallerComponentModule">
                <string method="getName()" value="testE" />
            </object>
            <boolean value="true" />
        </array>
        <array>
            <object class="testInstallerComponentModule">
                <string method="getName()" value="testD" />
            </object>
            <boolean value="true" />
        </array>
    </array>';
        $this->assertComplexIdenticalStr($result, $expected);
    }


    public function testComplexDependencies2() {
        /*
                A->B
                A->C
                D->E
                D->F
                B->F
        */
        $this->installer->testAddModule('testA',
                                       '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testA@modules.jelix.org" name="testA">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix minversion="'.JELIX_VERSION.'" maxversion="'.JELIX_VERSION.'" />
                            <module name="testB" />
                            <module name="testC" />
                        </dependencies>
                    </module>'
                    );
        $this->installer->testAddModule('testB',
                                       '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testB@modules.jelix.org" name="testB">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix minversion="'.JELIX_VERSION.'" maxversion="'.JELIX_VERSION.'" />
                            <module name="testF" />
                        </dependencies>
                    </module>'
                    );
        $this->installer->testAddModule('testC',
                                       '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testC@modules.jelix.org" name="testC">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix minversion="'.JELIX_VERSION.'" maxversion="'.JELIX_VERSION.'" />
                        </dependencies>
                    </module>'
                    );
        $this->installer->testAddModule('testD',
                                       '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testD@modules.jelix.org" name="testD">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix minversion="'.JELIX_VERSION.'" maxversion="'.JELIX_VERSION.'" />
                            <module name="testE" />
                            <module name="testF" />
                        </dependencies>
                    </module>'
                    );
        $this->installer->testAddModule('testE',
                                       '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testE@modules.jelix.org" name="testE">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix minversion="'.JELIX_VERSION.'" maxversion="'.JELIX_VERSION.'" />
                        </dependencies>
                    </module>'
                    );
        $this->installer->testAddModule('testF',
                                       '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testF@modules.jelix.org" name="testF">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix minversion="'.JELIX_VERSION.'" maxversion="'.JELIX_VERSION.'" />
                        </dependencies>
                    </module>'
                    );

        $this->installer->initForTest();

        $this->assertTrue($this->installer->doCheckDependencies(array('testA', 'testD')));

        $result = $this->installer->getComponentsToInstall();
        $expected = '<?xml version="1.0"?>
    <array>
        <array>
            <object class="jInstallerComponentModule">
                <string method="getName()" value="jelix" />
            </object>
            <boolean value="true" />
        </array>
        <array>
            <object class="testInstallerComponentModule">
                <string method="getName()" value="testF" />
            </object>
            <boolean value="true" />
        </array>
        <array>
            <object class="testInstallerComponentModule">
                <string method="getName()" value="testB" />
            </object>
            <boolean value="true" />
        </array>
        <array>
            <object class="testInstallerComponentModule">
                <string method="getName()" value="testC" />
            </object>
            <boolean value="true" />
        </array>
        <array>
            <object class="testInstallerComponentModule">
                <string method="getName()" value="testA" />
            </object>
            <boolean value="true" />
        </array>
        <array>
            <object class="testInstallerComponentModule">
                <string method="getName()" value="testE" />
            </object>
            <boolean value="true" />
        </array>
        <array>
            <object class="testInstallerComponentModule">
                <string method="getName()" value="testD" />
            </object>
            <boolean value="true" />
        </array>
    </array>';
        $this->assertComplexIdenticalStr($result, $expected);
    }



   public function testCircularDependency() {
        /*
                A->B->C->A
        */
        $this->installer->testAddModule('testA',
                                       '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testA@modules.jelix.org" name="testA">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix minversion="'.JELIX_VERSION.'" maxversion="'.JELIX_VERSION.'" />
                            <module name="testB" />
                        </dependencies>
                    </module>'
                    );

        $this->installer->testAddModule('testB',
                                       '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testB@modules.jelix.org" name="testB">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix minversion="'.JELIX_VERSION.'" maxversion="'.JELIX_VERSION.'" />
                            <module name="testC" />
                        </dependencies>
                    </module>'
                    );

        $this->installer->testAddModule('testC',
                                       '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testC@modules.jelix.org" name="testC">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix minversion="'.JELIX_VERSION.'" maxversion="'.JELIX_VERSION.'" />
                            <module name="testA" />
                        </dependencies>
                    </module>'
                    );

        $this->installer->initForTest();

        $this->assertFalse($this->installer->doCheckDependencies(array('testA')));
        $max = count($this->instReport->messages);
        $this->assertTrue($max > 0);
        $error = $this->instReport->messages[$max-1];
        $this->assertEquals('Circular dependency ! Cannot install the component testA', $error[0]);
        $this->assertEquals('error', $error[1]);

        $this->instReport->messages = array();

        $this->assertFalse($this->installer->doCheckDependencies(array('testB')));
        $max = count($this->instReport->messages);
        $this->assertTrue($max > 0);
        $error = $this->instReport->messages[$max-1];
        $this->assertEquals('Circular dependency ! Cannot install the component testB', $error[0]);
        $this->assertEquals('error', $error[1]);
    }


    public function testOptionalDependencies() {
        /*
                A->B
                A->C
                D->B
                D->E optional
        */
        $this->installer->testAddModule('testA',
            '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testA@modules.jelix.org" name="testA">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix minversion="'.JELIX_VERSION.'" maxversion="'.JELIX_VERSION.'" />
                            <module name="testB" />
                            <module name="testC" />
                        </dependencies>
                    </module>'
        );
        $this->installer->testAddModule('testB',
            '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testB@modules.jelix.org" name="testB">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix minversion="'.JELIX_VERSION.'" maxversion="'.JELIX_VERSION.'" />
                        </dependencies>
                    </module>'
        );
        $this->installer->testAddModule('testC',
            '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testC@modules.jelix.org" name="testC">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix minversion="'.JELIX_VERSION.'" maxversion="'.JELIX_VERSION.'" />
                        </dependencies>
                    </module>'
        );
        $this->installer->testAddModule('testD',
            '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testD@modules.jelix.org" name="testD">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix minversion="'.JELIX_VERSION.'" maxversion="'.JELIX_VERSION.'" />
                            <module name="testB" />
                            <module name="testE" optional="true"/>
                        </dependencies>
                    </module>'
        );

        $this->installer->initForTest();

        $this->assertTrue($this->installer->doCheckDependencies(array('testA', 'testD')));

        $result = $this->installer->getComponentsToInstall();
        $this->assertEquals(5, count($result));
        $expected = '<?xml version="1.0"?>
    <array>
        <array>
            <object class="jInstallerComponentModule">
                <string method="getName()" value="jelix" />
            </object>
            <boolean value="true" />
        </array>
        <array>
            <object class="testInstallerComponentModule">
                <string method="getName()" value="testB" />
            </object>
            <boolean value="true" />
        </array>
        <array>
            <object class="testInstallerComponentModule">
                <string method="getName()" value="testC" />
            </object>
            <boolean value="true" />
        </array>
        <array>
            <object class="testInstallerComponentModule">
                <string method="getName()" value="testA" />
            </object>
            <boolean value="true" />
        </array>
        <array>
            <object class="testInstallerComponentModule">
                <string method="getName()" value="testD" />
            </object>
            <boolean value="true" />
        </array>
    </array>';
        $this->assertComplexIdenticalStr($result, $expected);
    }

    public function testOptionalDependenciesWithMissingDependency() {
        /*
                A->B optional and missing
                A->C
                D->B
                D->E optional
        */
        $this->installer->testAddModule('testA',
            '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testA@modules.jelix.org" name="testA">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix minversion="'.JELIX_VERSION.'" maxversion="'.JELIX_VERSION.'" />
                            <module name="testB" optional="true"/>
                            <module name="testC" />
                        </dependencies>
                    </module>'
        );
        $this->installer->testAddModule('testC',
            '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testC@modules.jelix.org" name="testC">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix minversion="'.JELIX_VERSION.'" maxversion="'.JELIX_VERSION.'" />
                        </dependencies>
                    </module>'
        );
        $this->installer->testAddModule('testD',
            '<module xmlns="http://jelix.org/ns/module/1.0">
                        <info id="testD@modules.jelix.org" name="testD">
                            <version stability="stable">1.0</version>
                        </info>
                        <dependencies>
                            <jelix minversion="'.JELIX_VERSION.'" maxversion="'.JELIX_VERSION.'" />
                            <module name="testE" optional="true"/>
                            <module name="testB" />
                        </dependencies>
                    </module>'
        );

        $this->installer->initForTest();
        $this->instReport->messages = array();
        $this->assertTrue($this->installer->doCheckDependencies(array('testA')));

        $this->instReport->messages = array();
        $this->assertFalse($this->installer->doCheckDependencies(array('testD')));
        $max = count($this->instReport->messages);
        $this->assertTrue($max > 0);
        $error = $this->instReport->messages[$max-1];
        $this->assertEquals('To install testD these modules are needed: testB, ', $error[0]);
        $this->assertEquals('error', $error[1]);

    }


}


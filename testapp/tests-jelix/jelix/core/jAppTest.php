<?php


class jAppTest extends \PHPUnit\Framework\TestCase {


    
    function testModulesDirPath() {
        $modules = jApp::getAllModulesPath();

         // first save
        jApp::saveContext();
    
        // verify that we still have the current path
        $this->assertEquals($modules, jApp::getAllModulesPath());
 
        jApp::clearModulesPluginsPath();

        // verify that we have only jelix as modules
        $this->assertEquals(array('jelix'=> JELIX_LIB_PATH.'core-modules/jelix/'), jApp::getAllModulesPath());

        jApp::declareModulesDir(array(
            __DIR__.'/../installer/app1/modules/'
        ));

        $this->assertEquals(
            array(
                  'jelix'=> JELIX_LIB_PATH.'core-modules/jelix/',
                  'aaa'=>realpath(__DIR__.'/../installer/app1/modules/aaa/').'/'
            ),
            jApp::getAllModulesPath());

        // pop the first save, we should be with initial paths
        jApp::restoreContext();
        $this->assertEquals($modules, jApp::getAllModulesPath());
    }

    function testModulePath() {
        jApp::saveContext();
        jApp::clearModulesPluginsPath();

        // verify that we have only jelix as modules
        $this->assertEquals(array('jelix'=> JELIX_LIB_PATH.'core-modules/jelix/'), jApp::getAllModulesPath());

        jApp::declareModule(array(
            __DIR__.'/../installer/app1/modules/aaa/',
            jApp::appPath('modules/testapp')
        ));

        $this->assertEquals(
            array(
                  'jelix'=> JELIX_LIB_PATH.'core-modules/jelix/',
                  'aaa'=>realpath(__DIR__.'/../installer/app1/modules/aaa/').'/',
                  'testapp'=>realpath(jApp::appPath('modules/testapp')).'/'
            ),
            jApp::getAllModulesPath());

        // pop the first save, we should be with initial paths
        jApp::restoreContext();
    }

    function testModuleConfigPath()
    {
        jApp::saveContext();
        jApp::clearModulesPluginsPath();

        // verify that we have only jelix as modules
        $this->assertEquals(array('jelix'=> JELIX_LIB_PATH.'core-modules/jelix/'), jApp::getAllModulesPath());

        jApp::declareModulesFromConfig((object) array(
            'modules' => array(
                'aaa.path' => 'app:tests-jelix/jelix/installer/app1/modules/aaa',
                'aaa.enabled' => true
            )
        ));

        jApp::declareModule(jApp::appPath('modules/testapp'));

        $this->assertEquals(
            array(
                 'jelix'=> JELIX_LIB_PATH.'core-modules/jelix/',
                 'aaa'=>realpath(__DIR__.'/../installer/app1/modules/aaa/').'/',
                'testapp'=>realpath(jApp::appPath('modules/testapp')).'/'
            ),
            jApp::getAllModulesPath());

        // pop the first save, we should be with initial paths
        jApp::restoreContext();
    }

    function testPluginPath() {
        $expected = array(
            JELIX_LIB_PATH.'plugins/',
            jApp::appPath('vendor/jelix/php-redis-plugin/plugins/'),
            jApp::appPath('vendor/jelix/wikirenderer-plugin/plugins/'),
            jApp::appPath('plugins/'),
            LIB_PATH.'jelix-plugins/',
            jApp::appPath('vendor/jelix/minify-module/jminify/plugins/'),
            jApp::appPath('vendor/jelix/jacl-module/jacl/plugins/'),
            jApp::appPath('vendor/jelix/jacl-module/jacldb/plugins/'),
            LIB_PATH.'jelix-modules/jacl2/plugins/',
            LIB_PATH.'jelix-modules/jacl2db/plugins/',
        );
        sort($expected);
        
        $plugins = jApp::getAllPluginsPath();
        sort($plugins);

        $this->assertEquals($expected, $plugins, print_r($plugins,true)." does not equal to ".print_r($expected,true));

        jApp::saveContext();
        $plugins2 = jApp::getAllPluginsPath();
        sort($plugins2);
        $this->assertEquals($plugins, $plugins2);

        jApp::clearModulesPluginsPath();
        $this->assertEquals(array(JELIX_LIB_PATH.'plugins/'), jApp::getAllPluginsPath());

        jApp::restoreContext();
        $plugins2 = jApp::getAllPluginsPath();
        sort($plugins2);
        $this->assertEquals($plugins, $plugins2);
    }
}

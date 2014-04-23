<?php
require_once(JELIX_LIB_PATH.'plugins/configcompiler/nsautoloader/nsautoloader.configcompiler.php');

class configCompilerAutoloadInfoTest extends PHPUnit_Framework_TestCase {
/*
    function testNamespacesModuleXml() {
        $modulexml = '
<?xml version="1.0" encoding="UTF-8"?>
<module xmlns="http://jelix.org/ns/module/1.0">
    <autoload>
        <class name="footbat.php" file="autoload/footbat.php" />
        <classPattern pattern="/^bat/" dir="autoloadtest/some/" />
        <namespace name="jelixTests\foo" dir="autoloadtest" />
        <namespacePathMap name="jelixTests\bar" dir="autoloadtest/barns" suffix=".class.php" />
        <includePath dir="autoloadtest/incpath" suffix=".php" />
        <autoloader file="autoloadtest/myautoloader.php" />
    </autoload>
</module>';
        $moduleInfo = simplexml_load_string($modulexml);
        $plugin = new nsautoloaderConfigCompilerPlugin();
        $config = new stdClass();
        $path = __DIR__;
        
        $plugin->atStart($config);
        $plugin->onModule($config, $moduleName, $path, $moduleInfo, true);

    }
*/
    function testNamespacesComposer() {
        $composerjson = '{
            "name":"tests",
            "autoload": {
                "psr-4": {
                    "foo\\\\": [ "autoload/ns/bar/foo", "autoload/some/foo"]
                },
                "psr-0": {
                    "foo\\\\":"autoload/ns/bar",
                    "blo_u\\\\bl_i":"autoload/ns/other",
                    "":"autoload/some"
                },
                "include-path" : [ "autoload/some/bateau" ],
                "files": [
                    "autoload/some/batman.php"
                ],
                "classmap": [
                    "autoload/some/foo/", "autoload/foobat.php"
                ]
            }
        }';
        $moduleInfo = json_decode($composerjson);
        $plugin = new nsautoloaderConfigCompilerPlugin();
        $config = new stdClass();
        $path = __DIR__."/";
        
        $plugin->atStart($config);
        $plugin->onModule($config, "tests", $path, $moduleInfo, false);
        $this->assertEquals(array(
            'footbat' => $path.'autoload/foobat.php',
            'foo_bateau'=> $path.'autoload/some/foo/bateau.php',
                                  ), $config->_autoload_class);
        $this->assertEquals(array(
                'blo_u\\bl_i' => $path.'autoload/ns/other|.php',
                'foo\\' => $path.'autoload/ns/bar|.php',
            ), $config->_autoload_namespace);
        $this->assertEquals(array(
            'foo\\' => array(
                $path."autoload/ns/bar/foo|.php",
                $path."autoload/some/foo|.php"
            )), $config->_autoload_namespacepathmap);
        $this->assertEquals(array(), $config->_autoload_classpattern);
        $this->assertEquals(array(), $config->_autoload_includepathmap);
        $this->assertEquals(array(
            'path'=>array($path."autoload/some/bateau|.php"
                )), $config->_autoload_includepath);
        $this->assertEquals(array(
            "psr4"=>array(),
            "psr0"=>array($path.'autoload/some|.php'),
            ), $config->_autoload_fallback);
    }
}
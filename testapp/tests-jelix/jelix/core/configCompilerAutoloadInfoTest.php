<?php
require_once(JELIX_LIB_PATH.'plugins/configcompiler/nsautoloader/nsautoloader.configcompiler.php');

class testComposerJsonParser extends \Jelix\Core\Infos\ComposerJsonParser {
    public function __construct($path, $locale, $jsonContent){
        $this->path = $path;
        $this->locale = substr($locale, 0, 2);
        $this->json = json_decode($jsonContent, true);
        if (!is_array($this->json)) {
            throw new \Exception(" given content is not JSON");
        }
    }
}


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
                    "foo":"autoload/ns/bar",
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

        $path = __DIR__."/";
        $jsonParser = new testComposerJsonParser($path, 'en', $composerjson);
        $moduleInfo = new \Jelix\Core\Infos\ModuleInfos($path);
        $jsonParser->parse($moduleInfo);
        $config = new stdClass();
        $plugin = new nsautoloaderConfigCompilerPlugin();
        $plugin->atStart($config);
        $plugin->onModule($config, $moduleInfo);
        $this->assertEquals(array(
            'footbat' => $path.'autoload/foobat.php',
            'foo_bateau'=> $path.'autoload/some/foo/bateau.php',
                                  ), $config->_autoload_class);
        $this->assertEquals(array(
                'blo_u\\bl_i' => array($path.'autoload/ns/other|.php'),
                'foo' => array($path.'autoload/ns/bar|.php'),
            ), $config->_autoload_namespacepsr0);
        $this->assertEquals(array(
            'foo' => array(
                $path."autoload/ns/bar/foo|.php",
                $path."autoload/some/foo|.php"
            )), $config->_autoload_namespacepsr4);
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
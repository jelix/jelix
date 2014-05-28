<?php


class infosreaderTest extends jUnitTestCase {

    function testReadModuleXmlInfo() {

        $path = __DIR__.'/app/modules/simple/';
        $result = new \Jelix\Core\Infos\ModuleInfos($path);
        $expected = '<?xml version="1.0"?>
    <object>
        <string property="id" value="simple@testapp.jelix.org" />
        <string property="name" value="simple" />
        <string property="createDate" value="" />
        <string property="version" value="1.0" />
        <string property="versionDate" value="" />
        <string property="versionStability" value="" />
        <string property="label" value="simple" />
        <string property="description" value="" />
        <array property="creators"></array>
        <array property="contributors"></array>
        <string property="notes" value="" />
        <string property="homepageURL" value="" />
        <string property="updateURL" value="" />
        <string property="license" value="" />
        <string property="licenseURL" value="" />
        <string property="copyright" value="" />
        <array property="dependencies">
            <array>
                <string key="type" value="jelix" />
                <string key="maxversion" value="2.0" />
                <string key="minversion" value="1.6" />
            </array>
            <!--<array>
                <string key="type" value="" />
                <string key="maxversion" value="" />
                <string key="minversion" value="" />
                <string key="edition" value="" />
                <string key="id" value="" />
                <string key="name" value="" />
            </array>-->
        </array>
        <string property="type" value="library" />
        <array property="autoloaders">
            <!--<array>
            </array>-->
        </array>
        <array property="autoloadClasses">
            <!--<array>
            </array>-->
        </array>
        <array property="autoloadClassPatterns">
            <!--<array>
            </array>-->
        </array>
        <array property="autoloadNamespaces">
            <!--<array>
            </array>-->
        </array>
        <array property="autoloadPsr4Namespaces">
            <!--<array>
            </array>-->
        </array>
        <array property="autoloadIncludePath">
            <!--<array>
            </array>-->
        </array>    
    </object>';
//var_export($result);
        $this->assertComplexIdenticalStr($result, $expected);

    }

    function testReadModuleXmlInfoAutoload() {

        $path = __DIR__.'/../../../modules/jelix_tests/';
        $result = new \Jelix\Core\Infos\ModuleInfos($path);
        $expected = '<?xml version="1.0"?>
    <object>
        <string property="id" value="jelix_tests@testapp.jelix.org" />
        <string property="name" value="jelix_tests" />
        <string property="createDate" value="" />
        <string property="label" value="Jelix tests" />
        <string property="description" value="unit tests for jelix" />
        <array property="creators">
            <array>
                <string key="name">Laurent Jouanneau</string>
                <string key="email">laurent@jelix.org</string>
                <string key="active">true</string>
            </array>
        </array>
        <array property="contributors"></array>
        <string property="notes" value="" />
        <string property="homepageURL" value="http://jelix.org" />
        <string property="updateURL" value="" />
        <string property="license" value="" />
        <string property="licenseURL" value="" />
        <string property="copyright" value="Copyright 2006-2011 jelix.org" />
        <array property="dependencies">
            <array>
                <string key="type" value="jelix" />
            </array>
            <array>
                <string key="type" value="module" />
                <string key="name" value="testurls" />
            </array>
            <array>
                <string key="type" value="module" />
                <string key="name" value="jauthdb" />
            </array>
            <array>
                <string key="type" value="module" />
                <string key="name" value="jacl2db" />
            </array>
            <array>
                <string key="type" value="module" />
                <string key="name" value="jacldb" />
            </array>
            <!--<array>
                <string key="type" value="" />
                <string key="maxversion" value="" />
                <string key="minversion" value="" />
                <string key="edition" value="" />
                <string key="id" value="" />
                <string key="name" value="" />
            </array>-->
        </array>
        <string property="type" value="library" />
        <array property="autoloaders">
            <string>autoloadtest/myautoloader.php</string>
        </array>
        <array property="autoloadClasses">
            <!--<array>
            </array>-->
        </array>
        <array property="autoloadClassPatterns">
            <!--<array>
            </array>-->
        </array>
        <array property="autoloadNamespaces">
            <!--<array>
            </array>-->
        </array>
        <array property="autoloadPsr4Namespaces">
            <!--<array>
            </array>-->
        </array>
        <array property="autoloadIncludePath">
            <array>
                <string key="dir">autoloadtest/incpath</string>
                <string key="suffix">.php</string>
            </array>
        </array>    
    </object>';
        $this->assertComplexIdenticalStr($result, $expected);
    }

    function testReadModuleJsonInfo() {

        $path = __DIR__.'/app/modules/package/';
        $result = new \Jelix\Core\Infos\ModuleInfos($path);
        $expected = '<?xml version="1.0"?>
    <object>
        <string property="id" value="jelixtest/composerpackage" />
        <string property="name" value="jelixtest/composerpackage" />
        <string property="createDate" value="" />
        <string property="version" value="1.0" />
        <string property="versionDate" value="" />
        <string property="versionStability" value="" />
        <string property="label" value="jelixtest/composerpackage" />
        <string property="description" value="A jelix module that is a composer package" />
        <array property="creators"></array>
        <array property="contributors"></array>
        <string property="notes" value="" />
        <string property="homepageURL" value="" />
        <string property="updateURL" value="" />
        <string property="license" value="" />
        <string property="licenseURL" value="" />
        <string property="copyright" value="" />
        <array property="dependencies">
            <array>
                <string key="type" value="php" />
                <string key="maxversion" value="" />
                <string key="minversion" value="&gt;=5.3.3" />
                <string key="id" value="php" />
                <string key="name" value="php" />
            </array>
            <array>
                <string key="type" value="module" />
                <string key="maxversion" value="" />
                <string key="minversion" value="self:version" />
                <string key="id" value="jelix/core" />
                <string key="name" value="jelix/core" />
            </array>
            <!--<array>
                <string key="type" value="" />
                <string key="maxversion" value="" />
                <string key="minversion" value="" />
                <string key="id" value="" />
                <string key="name" value="" />
            </array>-->
        </array>
        <string property="type" value="jelix-module" />
        <array property="autoloaders">
            <!--<array>
            </array>-->
        </array>
        <array property="autoloadClasses">
            <!--<array>
            </array>-->
        </array>
        <array property="autoloadClassPatterns">
            <!--<array>
            </array>-->
        </array>
        <array property="autoloadNamespaces">
            <!--<array>
            </array>-->
        </array>
        <array property="autoloadPsr4Namespaces">
            <!--<array>
            </array>-->
        </array>
        <array property="autoloadIncludePath">
            <!--<array>
            </array>-->
        </array>    
    </object>';
        $this->assertComplexIdenticalStr($result, $expected);
    }
}
<?php


class infosreaderTest extends jUnitTestCase {

    function tearDown() {
        if (file_exists(__DIR__.'/tmp/project.xml')) {
            unlink (__DIR__.'/tmp/project.xml');
        }
        if (file_exists(__DIR__.'/tmp/jelix-app.json')) {
            unlink (__DIR__.'/tmp/jelix-app.json');
        }
        if (file_exists(__DIR__.'/tmp')) {
            rmdir(__DIR__.'/tmp');
        }
    }

    function testReadModuleXmlInfo() {

        $path = __DIR__.'/app/modules/simple/';
        $result = new \Jelix\Core\Infos\ModuleInfos($path);
        $this->assertTrue($result->exists());
        $expected = '<?xml version="1.0"?>
    <object>
        <string property="name" value="simple" />
        <string property="createDate" value="" />
        <string property="version" value="1.0" />
        <string property="versionDate" value="" />
        <string property="versionStability" value="" />
        <string property="label" value="simple" />
        <string property="description" value="" />
        <array property="authors"></array>
        <string property="notes" value="" />
        <string property="homepageURL" value="" />
        <string property="updateURL" value="" />
        <string property="license" value="" />
        <string property="licenseURL" value="" />
        <string property="copyright" value="" />
        <array property="dependencies">
            <array>
                <string key="name" value="jelix" />
                <string key="version" value="&gt;=1.6,&lt;=2.0" />
            </array>
        </array>
        <string property="type" value="module" />
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
        <array property="autoloadPsr0Namespaces">
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
        $this->assertTrue($result->exists());
        $expected = '<?xml version="1.0"?>
    <object>
        <string property="name" value="jelix_tests" />
        <string property="createDate" value="" />
        <string property="label" value="Jelix tests" />
        <string property="description" value="unit tests for jelix" />
        <array property="authors">
            <array>
                <string key="name">Laurent Jouanneau</string>
                <string key="email">laurent@jelix.org</string>
                <string key="active">true</string>
            </array>
        </array>
        <string property="notes" value="" />
        <string property="homepageURL" value="http://jelix.org" />
        <string property="updateURL" value="" />
        <string property="license" value="" />
        <string property="licenseURL" value="" />
        <string property="copyright" value="Copyright 2006-2011 jelix.org" />
        <array property="dependencies">
            <array>
                <string key="name" value="jelix" />
            </array>
            <array>
                <string key="name" value="testurls" />
            </array>
            <array>
                <string key="name" value="jauthdb" />
            </array>
            <array>
                <string key="name" value="jacl2db" />
            </array>
            <array>
                <string key="name" value="jacldb" />
            </array>
        </array>
        <string property="type" value="module" />
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
        <array property="autoloadPsr0Namespaces">
            <!--<array>
            </array>-->
        </array>
        <array property="autoloadPsr4Namespaces">
            <!--<array>
            </array>-->
        </array>
        <array property="autoloadIncludePath">
            <array>
                <string key="0">autoloadtest/incpath</string>
                <string key="1">.php</string>
            </array>
        </array>    
    </object>';
        $this->assertComplexIdenticalStr($result, $expected);
    }

    function testReadModuleJsonInfo() {

        $path = __DIR__.'/app/modules/package/';
        $result = new \Jelix\Core\Infos\ModuleInfos($path);
        $this->assertTrue($result->exists());
        $expected = '<?xml version="1.0"?>
    <object>
        <string property="name" value="thepackage" />
        <string property="createDate" value="" />
        <string property="version" value="1.0" />
        <string property="versionDate" value="" />
        <string property="versionStability" value="" />
        <string property="label" value="thepackage" />
        <string property="description" value="A jelix module using new jelix-module.json" />
        <array property="authors"></array>
        <string property="notes" value="" />
        <string property="homepageURL" value="" />
        <string property="updateURL" value="" />
        <string property="license" value="" />
        <string property="licenseURL" value="" />
        <string property="copyright" value="" />
        <array property="dependencies">
        </array>
        <string property="type" value="module" />
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
        <array property="autoloadPsr0Namespaces">
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

    function testReadAppJsonInfo() {
        $path = __DIR__.'/app/';
        $result = new \Jelix\Core\Infos\AppInfos($path);
        $this->assertTrue($result->exists());
        $expected = '<?xml version="1.0"?>
    <object>
        <string property="name" value="myappname" />
        <string property="createDate" value="" />
        <string property="version" value="1.0" />
        <string property="versionDate" value="2015-04-14" />
        <string property="versionStability" value="" />
        <string property="label" value="a label" />
        <string property="description" value="a description" />
        <array property="authors">
           <array>
               <string key="name" value="me" />
               <string key="email" value="me@example.com" />
           </array>
        </array>
        <string property="homepageURL" value="http://jelix.org" />
        <string property="updateURL" value="" />
        <string property="license" value="MIT" />
        <string property="licenseURL" value="" />
        <string property="copyright" value="2015 somebody" />
        <string property="type" value="application" />
        <string property="configPath" value="var/config" />
        <string property="logPath" value="var/log" />
        <string property="varPath" value="var" />
        <string property="wwwPath" value="www" />
        <string property="tempPath" value="temp/" />
        <array property="entrypoints">
            <array key="entrypoint.php">
                <string key="file" value="entrypoint.php" />
                <string key="config" value="config.ini.php" />
                <string key="type" value="classic" />
            </array>
        </array>
    </object>';
        $this->assertComplexIdenticalStr($result, $expected);
    }

    function testReadAppXmlInfo() {
        $path = __DIR__.'/app2/';
        $result = new \Jelix\Core\Infos\AppInfos($path);
        $this->assertTrue($result->exists());
        $expected = '<?xml version="1.0"?>
    <object>
        <string property="name" value="testapp" />
        <string property="createDate" value="2005-01-01" />
        <string property="version" value="2.0" />
        <string property="versionDate" value="2015-05-14" />
        <string property="versionStability" value="stable" />
        <string property="label" value="Testapp" />
        <string property="description" value="Application to test Jelix" />
        <array property="authors">
           <array>
               <string key="name" value="Laurent Jouanneau" />
               <string key="email" value="laurent@jelix.org" />
           </array>
        </array>
        <string property="homepageURL" value="http://jelix.org" />
        <string property="updateURL" value="" />
        <string property="license" value="GPL" />
        <string property="licenseURL" value="http://www.gnu.org/licenses/gpl.html" />
        <string property="copyright" value="2005-2011 Laurent Jouanneau and other contributors" />
        <string property="type" value="application" />
        <string property="configPath" value="var/config" />
        <string property="logPath" value="var/log" />
        <string property="varPath" value="var" />
        <string property="wwwPath" value="www" />
        <string property="tempPath" value="temp" />
        <array property="entrypoints">
            <array key="index.php">
                <string key="file" value="index.php" />
                <string key="config" value="index/config.ini.php" />
                <string key="type" value="classic" />
            </array>
            <array key="rest.php">
                <string key="file" value="rest.php" />
                <string key="config" value="rest/config.ini.php" />
                <string key="type" value="classic" />
            </array>
            <array key="cmdline.php">
                <string key="file" value="cmdline.php" />
                <string key="config" value="cmdline/config.ini.php" />
                <string key="type" value="cmdline" />
            </array>
        </array>
    </object>';
        $this->assertComplexIdenticalStr($result, $expected);
    }

    function testAddEntrypointToJelixAppJson() {
        $path = __DIR__.'/tmp';
        if (!file_exists($path)) {
            mkdir($path);
        }
        copy(__DIR__.'/app/jelix-app.json', $path.'/jelix-app.json');
        $this->assertTrue(file_exists( $path.'/jelix-app.json'));
        $result = new \Jelix\Core\Infos\AppInfos($path);
        $this->assertTrue($result->exists());
        $expected = '<?xml version="1.0"?>
    <object>
        <string property="name" value="myappname" />
        <string property="createDate" value="" />
        <string property="version" value="1.0" />
        <string property="versionDate" value="2015-04-14" />
        <string property="versionStability" value="" />
        <string property="label" value="a label" />
        <string property="description" value="a description" />
        <array property="authors">
           <array>
               <string key="name" value="me" />
               <string key="email" value="me@example.com" />
           </array>
        </array>
        <string property="homepageURL" value="http://jelix.org" />
        <string property="updateURL" value="" />
        <string property="license" value="MIT" />
        <string property="licenseURL" value="" />
        <string property="copyright" value="2015 somebody" />
        <string property="type" value="application" />
        <string property="configPath" value="var/config" />
        <string property="logPath" value="var/log" />
        <string property="varPath" value="var" />
        <string property="wwwPath" value="www" />
        <string property="tempPath" value="temp/" />
        <array property="entrypoints">
            <array key="entrypoint.php">
                <string key="file" value="entrypoint.php" />
                <string key="config" value="config.ini.php" />
                <string key="type" value="classic" />
            </array>
        </array>
    </object>';
        $this->assertComplexIdenticalStr($result, $expected);
        
        $result->addEntryPointInfo('foo.php', 'foo/config.ini.php', 'classic');
        $expected = '<?xml version="1.0"?>
    <object>
        <string property="name" value="myappname" />
        <string property="createDate" value="" />
        <string property="version" value="1.0" />
        <string property="versionDate" value="2015-04-14" />
        <string property="versionStability" value="" />
        <string property="label" value="a label" />
        <string property="description" value="a description" />
        <array property="authors">
           <array>
               <string key="name" value="me" />
               <string key="email" value="me@example.com" />
           </array>
        </array>
        <string property="homepageURL" value="http://jelix.org" />
        <string property="updateURL" value="" />
        <string property="license" value="MIT" />
        <string property="licenseURL" value="" />
        <string property="copyright" value="2015 somebody" />
        <string property="type" value="application" />
        <string property="configPath" value="var/config" />
        <string property="logPath" value="var/log" />
        <string property="varPath" value="var" />
        <string property="wwwPath" value="www" />
        <string property="tempPath" value="temp/" />
        <array property="entrypoints">
            <array key="entrypoint.php">
                <string key="file" value="entrypoint.php" />
                <string key="config" value="config.ini.php" />
                <string key="type" value="classic" />
            </array>
            <array key="foo.php">
                <string key="file" value="foo.php" />
                <string key="config" value="foo/config.ini.php" />
                <string key="type" value="classic" />
            </array>
        </array>
    </object>';
        $this->assertComplexIdenticalStr($result, $expected);
        $result = new \Jelix\Core\Infos\AppInfos($path);
        $this->assertComplexIdenticalStr($result, $expected);
    }

    function testAddEntrypointToProjectXml() {
        $path = __DIR__.'/tmp';
        if (!file_exists($path)) {
            mkdir($path);
        }
        copy(__DIR__.'/app2/project.xml', $path.'/project.xml');
        
        $result = new \Jelix\Core\Infos\AppInfos($path);
        $this->assertTrue($result->exists());
        $this->assertTrue($result->isXmlFile());
        $expected = '<?xml version="1.0"?>
    <object>
        <string property="name" value="testapp" />
        <string property="createDate" value="2005-01-01" />
        <string property="version" value="2.0" />
        <string property="versionDate" value="2015-05-14" />
        <string property="versionStability" value="stable" />
        <string property="label" value="Testapp" />
        <string property="description" value="Application to test Jelix" />
        <array property="authors">
           <array>
               <string key="name" value="Laurent Jouanneau" />
               <string key="email" value="laurent@jelix.org" />
           </array>
        </array>
        <string property="homepageURL" value="http://jelix.org" />
        <string property="updateURL" value="" />
        <string property="license" value="GPL" />
        <string property="licenseURL" value="http://www.gnu.org/licenses/gpl.html" />
        <string property="copyright" value="2005-2011 Laurent Jouanneau and other contributors" />
        <string property="type" value="application" />
        <string property="configPath" value="var/config" />
        <string property="logPath" value="var/log" />
        <string property="varPath" value="var" />
        <string property="wwwPath" value="www" />
        <string property="tempPath" value="temp" />
        <array property="entrypoints">
            <array key="index.php">
                <string key="file" value="index.php" />
                <string key="config" value="index/config.ini.php" />
                <string key="type" value="classic" />
            </array>
            <array key="rest.php">
                <string key="file" value="rest.php" />
                <string key="config" value="rest/config.ini.php" />
                <string key="type" value="classic" />
            </array>
            <array key="cmdline.php">
                <string key="file" value="cmdline.php" />
                <string key="config" value="cmdline/config.ini.php" />
                <string key="type" value="cmdline" />
            </array>
        </array>
    </object>';
        $this->assertComplexIdenticalStr($result, $expected);
        $result->addEntryPointInfo('foo.php', 'foo/config.ini.php', 'classic');
        $expected = '<?xml version="1.0"?>
    <object>
        <string property="name" value="testapp" />
        <string property="createDate" value="2005-01-01" />
        <string property="version" value="2.0" />
        <string property="versionDate" value="2015-05-14" />
        <string property="versionStability" value="stable" />
        <string property="label" value="Testapp" />
        <string property="description" value="Application to test Jelix" />
        <array property="authors">
           <array>
               <string key="name" value="Laurent Jouanneau" />
               <string key="email" value="laurent@jelix.org" />
           </array>
        </array>
        <string property="homepageURL" value="http://jelix.org" />
        <string property="updateURL" value="" />
        <string property="license" value="GPL" />
        <string property="licenseURL" value="http://www.gnu.org/licenses/gpl.html" />
        <string property="copyright" value="2005-2011 Laurent Jouanneau and other contributors" />
        <string property="type" value="application" />
        <string property="configPath" value="var/config" />
        <string property="logPath" value="var/log" />
        <string property="varPath" value="var" />
        <string property="wwwPath" value="www" />
        <string property="tempPath" value="temp" />
        <array property="entrypoints">
            <array key="index.php">
                <string key="file" value="index.php" />
                <string key="config" value="index/config.ini.php" />
                <string key="type" value="classic" />
            </array>
            <array key="rest.php">
                <string key="file" value="rest.php" />
                <string key="config" value="rest/config.ini.php" />
                <string key="type" value="classic" />
            </array>
            <array key="cmdline.php">
                <string key="file" value="cmdline.php" />
                <string key="config" value="cmdline/config.ini.php" />
                <string key="type" value="cmdline" />
            </array>
            <array key="foo.php">
                <string key="file" value="foo.php" />
                <string key="config" value="foo/config.ini.php" />
                <string key="type" value="classic" />
            </array>
        </array>
    </object>';
        $this->assertComplexIdenticalStr($result, $expected);
        $result = new \Jelix\Core\Infos\AppInfos($path);
        $this->assertComplexIdenticalStr($result, $expected);
    }
}
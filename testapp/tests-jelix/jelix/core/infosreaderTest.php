<?php


class infosreaderTest extends jUnitTestCase {

    function tearDown() {
        if (file_exists(__DIR__.'/../../../temp/testframework.ini')) {
            unlink (__DIR__.'/../../../temp/testframework.ini');
        }
        if (file_exists(__DIR__.'/../../../temp/testlocalframework.ini')) {
            unlink (__DIR__.'/../../../temp/testlocalframework.ini');
        }
    }

    function setUp() {
        if (file_exists(__DIR__.'/../../../temp/testframework.ini')) {
            unlink (__DIR__.'/../../../temp/testframework.ini');
        }
        if (file_exists(__DIR__.'/../../../temp/testlocalframework.ini')) {
            unlink (__DIR__.'/../../../temp/testlocalframework.ini');
        }
    }

    function testReadModuleXmlInfo() {

        $path = __DIR__.'/app/modules/simple/module.xml';
        $parser = new \Jelix\Core\Infos\ModuleXmlParser($path);
        $result = $parser->parse();
        $this->assertTrue($result->exists());
        $expected = '<?xml version="1.0"?>
    <object>
        <string property="name" value="simple" />
        <string property="createDate" value="" />
        <string property="version" value="1.0" />
        <string property="versionDate" value="" />
        <string property="versionStability" value="" />
        <array property="label">
            <string key="en" value="simplé" />
        </array>
        <array property="description"></array>
        <array property="author"></array>
        <string property="homepageURL" value="" />
        <string property="updateURL" value="" />
        <string property="license" value="" />
        <string property="licenseURL" value="" />
        <string property="copyright" value="" />
        <array property="dependencies">
            <array>
                <string key="name" value="jelix" />
                <string key="minversion" value="1.6" />
                <string key="maxversion" value="2.0" />
                <string key="version" value="&gt;=1.6,&lt;=2.0" />
            </array>
        </array>
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

        $writer = new \Jelix\Core\Infos\ModuleXmlWriter($result->getFilePath());
        $result = $writer->write($result, false);
        $this->assertEqualOrDiff(
            "<?xml version=\"1.0\" encoding=\"utf-8\"?>
<module xmlns=\"http://jelix.org/ns/module/1.0\">
  <info id=\"simple@testapp.jelix.org\" name=\"simple\">
    <version>1.0</version>
    <label lang=\"en\">simplé</label>
  </info>
  <dependencies>
    <module id=\"jelix@jelix.org\" name=\"jelix\" minversion=\"1.6\" maxversion=\"2.0\"/>
  </dependencies>
</module>
", $result);

    }

    function testReadModuleXmlInfoAutoload() {

        $path = __DIR__.'/app/modules/complex/module.xml';
        $parser = new \Jelix\Core\Infos\ModuleXmlParser($path);
        $result = $parser->parse();
        $this->assertTrue($result->exists());
        $expected = '<?xml version="1.0"?>
    <object>
        <string property="name" value="jelix_tests" />
        <string property="createDate" value="" />
        <array property="label">
            <string key="en" value="Jelix tests" />
        </array>
        <array property="description">
            <string key="en" value="unit tests for jelix" />
        </array>
        <array property="author">
            <object>
                <string property="name">Laurent Jouanneau</string>
                <string property="email">laurent@jelix.org</string>
            </object>
        </array>
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
        <array property="autoloaders">
            <string>autoloadtest/myautoloader.php</string>
        </array>
        <array property="autoloadClasses">
            <string key="myautoloadedclass">autoloadtest/autoloadtestclass.php</string>
        </array>
        <array property="autoloadClassPatterns">
            <array key="/^myalclass/">
                <string>autoloadtest/withpattern/</string>
                <string>.cl.php</string>
            </array>
        </array>
        <array property="autoloadPsr0Namespaces">
            <array key="jelixTests\foo">
                <array><string>autoloadtest</string><string>.php</string></array>
            </array>
        </array>
        <array property="autoloadPsr4Namespaces">
            <array key="jelixTests\bar">
                <array><string>autoloadtest/barns</string>
                <string>.class.php</string></array>
            </array>
        </array>
        <array property="autoloadIncludePath">
            <array>
                <string >autoloadtest/incpath</string>
            </array>
        </array>    
    </object>';
        $this->assertComplexIdenticalStr($result, $expected);

        $this->assertEquals('Jelix tests', $result->getLabel());
        $this->assertEquals('unit tests for jelix', $result->getDescription());

        $writer = new \Jelix\Core\Infos\ModuleXmlWriter($result->getFilePath());
        $result = $writer->write($result, false);
        $this->assertEqualOrDiff(
            "<?xml version=\"1.0\" encoding=\"utf-8\"?>
<module xmlns=\"http://jelix.org/ns/module/1.0\">
  <info id=\"jelix_tests@testapp.jelix.org\" name=\"jelix_tests\">
    <version date=\"2019-01-02 15:11\">1.7.0-beta.5</version>
    <label lang=\"en\">Jelix tests</label>
    <description lang=\"en\">unit tests for jelix</description>
    <copyright>Copyright 2006-2011 jelix.org</copyright>
    <author name=\"Laurent Jouanneau\" email=\"laurent@jelix.org\" role=\"creator\"/>
    <homepageURL>http://jelix.org</homepageURL>
  </info>
  <dependencies>
    <module id=\"jelix@jelix.org\" name=\"jelix\" minversion=\"1.7.0-beta.3\" maxversion=\"1.7.0\"/>
    <module name=\"testurls\" minversion=\"1.7.0-beta.5\" maxversion=\"1.7.0-beta.5\"/>
    <module name=\"jauthdb\"/>
    <module name=\"jacl2db\"/>
    <module name=\"jacldb\"/>
  </dependencies>
  <autoload>
    <autoloader file=\"autoloadtest/myautoloader.php\"/>
    <class name=\"myautoloadedclass\" file=\"autoloadtest/autoloadtestclass.php\"/>
    <classPattern pattern=\"/^myalclass/\" dir=\"autoloadtest/withpattern/\" suffix=\".cl.php\"/>
    <psr0 namespace=\"jelixTests\\foo\" dir=\"autoloadtest\"/>
    <psr4 namespace=\"jelixTests\bar\" dir=\"autoloadtest/barns\" suffix=\".class.php\"/>
    <includePath dir=\"autoloadtest/incpath\"/>
  </autoload>
</module>
", $result);
    }

    function testReadAppXmlInfo() {
        $path = __DIR__.'/app/project.xml';
        $parser = new \Jelix\Core\Infos\ProjectXmlParser($path);
        $result = $parser->parse();
        $this->assertTrue($result->exists());
        $expected = '<?xml version="1.0"?>
    <object>
        <string property="name" value="testapp" />
        <string property="createDate" value="2005-01-01" />
        <string property="version" value="2.0" />
        <string property="versionDate" value="2015-05-14" />
        <string property="versionStability" value="stable" />
        <array property="label">
            <string key="en" value="Testapp" />
        </array>
        <array property="description">
            <string key="en" value="Application to test Jelix" />
        </array>
        <array property="author">
           <object>
               <string property="name" value="Laurent Jouanneau" />
               <string property="email" value="laurent@jelix.org" />
           </object>
        </array>
        <string property="homepageURL" value="http://jelix.org" />
        <string property="updateURL" value="" />
        <string property="license" value="GPL" />
        <string property="licenseURL" value="http://www.gnu.org/licenses/gpl.html" />
        <string property="copyright" value="2005-2011 Laurent Jouanneau and other contributors" />
    </object>';
        $this->assertComplexIdenticalStr($result, $expected);

        $writer = new \Jelix\Core\Infos\ProjectXmlWriter($result->getFilePath());
        $result = $writer->write($result, false);
        $this->assertEqualOrDiff(
            "<?xml version=\"1.0\" encoding=\"utf-8\"?>
<project xmlns=\"http://jelix.org/ns/project/1.0\">
  <info name=\"testapp\" createdate=\"2005-01-01\">
    <version date=\"2015-05-14\" stability=\"stable\">2.0</version>
    <label lang=\"en\">Testapp</label>
    <description lang=\"en\">Application to test Jelix</description>
    <licence URL=\"http://www.gnu.org/licenses/gpl.html\">GPL</licence>
    <copyright>2005-2011 Laurent Jouanneau and other contributors</copyright>
    <author name=\"Laurent Jouanneau\" email=\"laurent@jelix.org\" role=\"creator\"/>
    <homepageURL>http://jelix.org</homepageURL>
  </info>
</project>
", $result);

    }


    function testFrameworkInfo() {
        $path = __DIR__.'/app/app/system/framework.ini.php';
        $fmkInfos = new \Jelix\Core\Infos\FrameworkInfos($path);
        $result = $fmkInfos->getEntryPoints();
        $expected = '<?xml version="1.0"?>
        <array>
            <object key="index">
                <string method="getId()" value="index" />
                <string method="getConfigFile()" value="index/config.ini.php" />
                <string method="getType()" value="classic" />
            </object>
            <object key="rest">
                <string method="getId()" value="rest" />
                <string method="getConfigFile()" value="rest/config.ini.php" />
                <string method="getType()" value="classic" />
            </object>
            <object key="cmdline">
                <string method="getId()" value="cmdline" />
                <string method="getConfigFile()" value="cmdline/config.ini.php" />
                <string method="getType()" value="cmdline" />
            </object>
        </array>';
        $this->assertComplexIdenticalStr($result, $expected);
    }


    function testLocalFrameworkInfo() {
        copy(__DIR__.'/app/app/system/framework.ini.php', __DIR__.'/../../../temp/testframework.ini');
        $fmkInfos = new \Jelix\Core\Infos\FrameworkInfos(
            __DIR__.'/../../../temp/testframework.ini',
            __DIR__.'/../../../temp/testlocalframework.ini'
        );
        $result = $fmkInfos->getEntryPoints();
        $expected = '<?xml version="1.0"?>
        <array>
            <object key="index">
                <string method="getId()" value="index" />
                <string method="getConfigFile()" value="index/config.ini.php" />
                <string method="getType()" value="classic" />
            </object>
            <object key="rest">
                <string method="getId()" value="rest" />
                <string method="getConfigFile()" value="rest/config.ini.php" />
                <string method="getType()" value="classic" />
            </object>
            <object key="cmdline">
                <string method="getId()" value="cmdline" />
                <string method="getConfigFile()" value="cmdline/config.ini.php" />
                <string method="getType()" value="cmdline" />
            </object>
        </array>';
        $this->assertComplexIdenticalStr($result, $expected);


        $fmkInfos->addEntryPointInfo('foo.php', 'foo/config.ini');
        $fmkInfos->addLocalEntryPointInfo('localfoo.php', 'localfoo/config.ini', "soap");
        $fmkInfos->removeEntryPointInfo("rest.php");
        $fmkInfos->save();

        $ini= new \Jelix\IniFile\IniModifier(__DIR__.'/../../../temp/testframework.ini');
        $this->assertEquals(array(
            'entrypoint:index.php', 'entrypoint:cmdline.php', 'entrypoint:foo.php',
            ), $ini->getSectionList()
        );
        $this->assertEquals(array('config'=>'foo/config.ini', 'type'=>'classic'), $ini->getValues('entrypoint:foo.php'));

        $ini= new \Jelix\IniFile\IniModifier(__DIR__.'/../../../temp/testlocalframework.ini');
        $this->assertEquals(array(
            'entrypoint:localfoo.php'
            ), $ini->getSectionList()
        );
        $this->assertEquals(array('config'=>'localfoo/config.ini', 'type'=>'soap'), $ini->getValues('entrypoint:localfoo.php'));

    }


}
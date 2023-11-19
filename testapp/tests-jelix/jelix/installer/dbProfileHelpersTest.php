<?php

class dbProfileHelpersForTests
{
    use Jelix\Installer\Module\API\DbProfileHelpersTrait;

    function getProfilesIni() {
        return new \Jelix\IniFile\IniModifier(__DIR__.'/app1/var/config/profiles-test.ini.php');
    }
}



class dbProfileHelpersTest extends \PHPUnit\Framework\TestCase
{

    protected $profileFilename;

    function setUp() : void
    {
        $this->profileFilename = __DIR__.'/app1/var/config/profiles-test.ini.php';
        if (file_exists($this->profileFilename)) {
            unlink($this->profileFilename);
        }
    }

    function tearDown() : void
    {
        if (file_exists($this->profileFilename)) {
            unlink($this->profileFilename);
        }
    }

    static function getFindProfileData()
    {
        return array(
            array('', array(), 'foo'),
            array('
[jdb:foo]
bar = baz
', array('bar'=>'baz'), 'foo'),
            array('
[jdb]
foo=default
[jdb:default]
bar = baz
', array('bar'=>'baz'), 'default'),

            array('
[jdb]
default=myapp
foo=default
[jdb:myapp]
bar = baz
', array('bar'=>'baz'), 'myapp'),

            array('
[jdb]
default=myapp
foo=default
myapp=truc
[jdb:truc]
bar = baz
', array('bar'=>'baz'), 'truc'),

            array('
[jdb]
default=myapp

[jdb:myapp]
bar = baz
', array(), 'foo'),

        );
    }

    /**
     * @dataProvider getFindProfileData
     */
    function testFindProfile($iniContent, $expectedProfile, $expectedName)
    {
        file_put_contents($this->profileFilename, $iniContent);
        $profileHelpers = new dbProfileHelpersForTests();
        list($profile, $realName) = $profileHelpers->findDbProfile('foo');
        $this->assertEquals($expectedProfile, $profile);
        $this->assertEquals($expectedName, $realName);
    }
}

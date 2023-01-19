<?php

class testjProfilesCompiler extends jProfilesCompiler {
    function __construct($sources) {
        $this->sources = $sources;
    }
}



class testJProfiles extends jProfiles {

    static function testSetProfiles($profiles) {
        $compil = new testjProfilesCompiler($profiles);
        self::$_profiles = $compil->compile();
    }

    static function testGetProfiles() {
        return self::$_profiles;
    }
}


class profilesTest extends \Jelix\UnitTests\UnitTestCase
{
    public static function setUpBeforeClass() : void {
        self::initJelixConfig();
    }

    function testDefaultProfile () {
        $defaultProfile = array('wsdl'=>'books.wsdl', 'option'=>'foo');
        $readedDefaultProfile = array('_name'=>'default', 'wsdl'=>'books.wsdl', 'option'=>'foo');

        testJProfiles::testSetProfiles(array(
            'foo'=>array(),
            'foo:default'=>  $defaultProfile
            )
        );

        $profile = testJProfiles::get('foo');
        $this->assertEquals($readedDefaultProfile, $profile);

        $profile = testJProfiles::get('foo','default');
        $this->assertEquals($readedDefaultProfile, $profile);

        $profile = testJProfiles::get('foo','toto');
        $this->assertEquals($readedDefaultProfile, $profile);

        try {
            $profile = testJProfiles::get('foo','toto', true);
            $this->fail();
        } catch(jException $e) {
            $this->assertEquals(500, $e->getCode(), "Exception: ".$e->getMessage());
        } catch (Exception $e) {
            $this->fail('bad expected exception');
        }
    }

    function testAliasDefaultProfile () {
        $defaultProfile = array('wsdl'=>'books.wsdl', 'option'=>'foo');
        $readedDefaultProfile = array('wsdl'=>'books.wsdl', 'option'=>'foo','_name'=>'server1');

        $allProfiles = array(
            'foo'=>array('default'=>'server1'),
            'foo:server1'=>  $defaultProfile
        );

        testJProfiles::testSetProfiles($allProfiles);

        $profile = testJProfiles::get('foo');
        $this->assertEquals($readedDefaultProfile, $profile);

        $profile = testJProfiles::get('foo','default');
        $this->assertEquals($readedDefaultProfile, $profile);

        $profile = testJProfiles::get('foo','server1');
        $this->assertEquals($readedDefaultProfile, $profile);

        $profile = testJProfiles::get('foo','toto');
        $this->assertEquals($readedDefaultProfile, $profile);

        $this->assertEquals( array(
            'foo' => array(
                'server1'=> $readedDefaultProfile,
                'default'=> $readedDefaultProfile
            )
            ), testJProfiles::testGetProfiles());

        try {
            $profile = testJProfiles::get('foo','toto', true);
            $this->fail();
        } catch(jException $e) {
            $this->assertEquals(500, $e->getCode(), "Exception: ".$e->getMessage());
        } catch (Exception $e) {
            $this->fail('bad expected exception');
        }
    }

    function testAliasProfile () {
        $myProfile = array('wsdl'=>'books.wsdl', 'option'=>'foo');
        $readedProfile = array('wsdl'=>'books.wsdl', 'option'=>'foo', '_name'=>'server1');

        $allProfiles = array(
            'foo'=>array(
                'default'=>'server1',
                'myserver'=>'server1'
            ),
            'foo:server1'=>  $myProfile
        );

        testJProfiles::testSetProfiles($allProfiles);

        $profile = testJProfiles::get('foo');
        $this->assertEquals($readedProfile, $profile);

        $profile = testJProfiles::get('foo','default');
        $this->assertEquals($readedProfile, $profile);

        $profile = testJProfiles::get('foo','server1');
        $this->assertEquals($readedProfile, $profile);

        $profile = testJProfiles::get('foo','myserver');
        $this->assertEquals($readedProfile, $profile);

        $profile = testJProfiles::get('foo','toto');
        $this->assertEquals($readedProfile, $profile);

        $this->assertEquals( array(
            'foo' => array(
                'server1'=>  $readedProfile,
                'default'=>  $readedProfile,
                'myserver'=>  $readedProfile,
            )
            ), testJProfiles::testGetProfiles());

        try {
            $profile = testJProfiles::get('foo','toto', true);
            $this->fail();
        } catch(jException $e) {
            $this->assertEquals(500, $e->getCode(), "Exception: ".$e->getMessage());
        } catch (Exception $e) {
            $this->fail('bad expected exception');
        }
    }

    function testVirtualProfile()
    {
        $readedProfile = array('wsdl'=>'books.wsdl', 'option'=>'foo', '_name'=>'server1');

        testJProfiles::createVirtualProfile('foo', 'myalias', 'server1');
        $this->assertEquals( array(
            'foo' => array(
                'server1'=>  $readedProfile,
                'default'=>  $readedProfile,
                'myserver'=>  $readedProfile,
                'myalias'=>  $readedProfile,
            )
            ), testJProfiles::testGetProfiles());

        testJProfiles::createVirtualProfile('foo', 'new', array('bla'=>'ok'));
        $this->assertEquals( array(
            'foo' => array(
                'server1'=>  $readedProfile,
                'default'=>  $readedProfile,
                'myserver'=>  $readedProfile,
                'myalias'=>  $readedProfile,
                'new'=>array('bla'=>'ok', '_name'=>'new')
            )
            ), testJProfiles::testGetProfiles());
    }

    function testPool() {
        $this->assertNull(testJProfiles::getFromPool('foo', 'bar'));

        testJProfiles::storeInPool('foo', 'bar', 'a value');
        $this->assertEquals('a value', testJProfiles::getFromPool('foo', 'bar'));

        testJProfiles::clear();
        $this->assertNull(testJProfiles::getFromPool('foo', 'bar'));

    }

    function testGetStorePool() {
        testJProfiles::clear();
        try {
            $this->assertEquals('result:array:foo',
                            testJProfiles::getOrStoreInPool('foo', 'new', array($this, '_getObj')));
            $this->fail();
        } catch(Exception $e) {
            $this->assertEquals('Unknown profile "new" for "foo"', $e->getMessage());
        }
        testJProfiles::createVirtualProfile('foo', 'new', array('bla'=>'ok'));
        $this->assertEquals('result:array:new',
                            testJProfiles::getOrStoreInPool('foo', 'new', array($this, '_getObj')));

        $this->assertEquals('result:array:new', testJProfiles::getFromPool('foo', 'new'));
    }

    public function _getObj($profile){
        $value = 'result:';
        if (is_array($profile))
            $value.='array:';
        if (isset($profile['_name']))
            $value.= $profile['_name'];
        return $value;
    }

    function getDuplicateProfileData()
    {
        return array(
            // test 1 : alias to a pgsql profile
            array(
                array(
                    'jdb' => array(
                        'foo' => 'default'
                    ),
                    'jdb:default' => array(
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass'
                    ),
                    'jdb:maria' => array(
                        'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass'
                    )
                ),
                array('jdb'=> array(
                    'default' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                    ),
                    'foo' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                    ),
                    'maria' => array(
                        '_name' => 'maria',
                        'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'mysql', 'phpext' => 'mysqli', 'pdoext' => 'pdo_mysql', 'pdodriver' => 'mysql', 'pdooptions' => '',
                    )
                )
                )
            ),
            // test 2 : alias to a pgsql profile, and a second identical profile, which should become an alias
            array(
                array(
                    'jdb' => array(
                        'foo' => 'default'
                    ),
                    'jdb:default' => array(
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass'
                    ),
                    'jdb:identical' => array(
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass'
                    ),
                    'jdb:maria' => array(
                        'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass'
                    )
                ),
                array(
                    'jdb'=> array(
                    'default' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                    ),
                    'foo' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                    ),
                    'identical' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                    ),
                    'maria' => array(
                        '_name' => 'maria',
                        'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'mysql', 'phpext' => 'mysqli', 'pdoext' => 'pdo_mysql', 'pdodriver' => 'mysql', 'pdooptions' => '',
                    )
                    )
                )
            ),
            // test 3 : alias to a pgsql profile, and a second identical profile with a timeout=0, which should become an alias
            array(
                array(
                    'jdb' => array(
                        'foo' => 'default'
                    ),
                    'jdb:default' => array(
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass'
                    ),
                    'jdb:identical' => array(
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'timeout'=>0
                    ),
                    'jdb:maria' => array(
                        'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass'
                    )
                ),
                array('jdb'=> array(
                    'default' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                    ),
                    'foo' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                    ),
                    'identical' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => ''
                    ),
                    'maria' => array(
                        '_name' => 'maria',
                        'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'mysql', 'phpext' => 'mysqli', 'pdoext' => 'pdo_mysql', 'pdodriver' => 'mysql', 'pdooptions' => '',
                    )
                )
                )
            ),
            // test 4 : alias to a pgsql profile, and a second identical profile with a timeout=5, which stay a different profile
            array(
                array(
                    'jdb' => array(
                        'foo' => 'default'
                    ),
                    'jdb:default' => array(
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass'
                    ),
                    'jdb:identical' => array(
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'timeout'=>5
                    ),
                    'jdb:maria' => array(
                        'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass'
                    )
                ),
                array('jdb'=> array(
                    'default' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                    ),
                    'foo' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                    ),
                    'identical' => array(
                        '_name' => 'identical',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'timeout',
                        'timeout' => 5
                    ),
                    'maria' => array(
                        '_name' => 'maria',
                        'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'mysql', 'phpext' => 'mysqli', 'pdoext' => 'pdo_mysql', 'pdodriver' => 'mysql', 'pdooptions' => '',
                    )
                )
                )
            ),
            // test 5 : alias to a pgsql profile, and a second almost identical profile with a different search_path, so it stays a different profile
            array(
                array(
                    'jdb' => array(
                        'foo' => 'default'
                    ),
                    'jdb:default' => array(
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass'
                    ),
                    'jdb:identical' => array(
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'search_path'=>'bar,public'
                    ),
                    'jdb:maria' => array(
                        'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass'
                    )
                ),
                array('jdb'=> array(
                    'default' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                    ),
                    'foo' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                    ),
                    'identical' => array(
                        '_name' => 'identical',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path,timeout',
                        'timeout' => 180, 'search_path' => 'bar,public'
                    ),
                    'maria' => array(
                        '_name' => 'maria',
                        'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'mysql', 'phpext' => 'mysqli', 'pdoext' => 'pdo_mysql', 'pdodriver' => 'mysql', 'pdooptions' => '',
                    )
                )
                )
            ),
            // test 6 : alias to a pgsql profile, and two identical profile except for search path, one will be an
            // alias, an other stays a different profile
            array(
                array(
                    'jdb' => array(
                        'foo' => 'default'
                    ),
                    'jdb:almostidentical' => array(
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'search_path'=>'genius,public'
                    ),
                    'jdb:default' => array(
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'search_path'=>'bar,public'
                    ),
                    'jdb:identical' => array(
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'search_path'=>'bar,public'
                    ),
                    'jdb:maria' => array(
                        'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass'
                    ),
                    'jdb:other' => array(
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'gerard', 'password' => 'pass',
                        'search_path'=>'bar,public'
                    )
                ),
                array('jdb'=> array(
                    'almostidentical' => array(
                        '_name' => 'almostidentical',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path',
                        'search_path' => 'genius,public'
                    ),
                    'default' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path,timeout',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'foo' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path,timeout',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'identical' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path,timeout',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'maria' => array(
                        '_name' => 'maria',
                        'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'mysql', 'phpext' => 'mysqli', 'pdoext' => 'pdo_mysql', 'pdodriver' => 'mysql', 'pdooptions' => '',
                    ),
                    'other' => array(
                        '_name' => 'other',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'gerard', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path',
                        'search_path' => 'bar,public'
                    ),
                )
                )
            ),
            // test 7 : alias to a pgsql profile, and two identical profile except for search path, one will be an
            // alias, an other stays a different profile. An other profile is identical but has a timeout with the
            // same values as an other one with changed timeout
            array(
                array(
                    'jdb' => array(
                        'foo' => 'default'
                    ),
                    'jdb:almostidentical' => array(
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'search_path'=>'genius,public'
                    ),
                    'jdb:default' => array(
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'search_path'=>'bar,public'
                    ),
                    'jdb:futchball' => array(
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'search_path'=>'bar,public', 'timeout'=>180
                    ),
                    'jdb:identical' => array(
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'search_path'=>'bar,public'
                    ),
                    'jdb:maria' => array(
                        'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass'
                    ),
                    'jdb:other' => array(
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'gerard', 'password' => 'pass',
                        'search_path'=>'bar,public'
                    )
                ),
                array('jdb'=> array(
                    'almostidentical' => array(
                        '_name' => 'almostidentical',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path',
                        'search_path' => 'genius,public'
                    ),
                    'default' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path,timeout',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'foo' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path,timeout',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'futchball' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path,timeout',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'identical' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path,timeout',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'maria' => array(
                        '_name' => 'maria',
                        'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'mysql', 'phpext' => 'mysqli', 'pdoext' => 'pdo_mysql', 'pdodriver' => 'mysql', 'pdooptions' => '',
                    ),
                    'other' => array(
                        '_name' => 'other',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'gerard', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path',
                        'search_path' => 'bar,public'
                    ),
                )
                )
            ),
            // test 8 : we reinject the result of test 7 into the compiler, to be sure
            // we have the same result. e.g., we simulate createVirtualProfile does.
            array(
                array(
                    'jdb:almostidentical' => array(
                        '_name' => 'almostidentical',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path',
                        'search_path' => 'genius,public'
                    ),
                    'jdb:default' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path,timeout',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'jdb:foo' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path,timeout',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'jdb:futchball' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path,timeout',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'jdb:identical' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path,timeout',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'jdb:maria' => array(
                        '_name' => 'maria',
                        'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'mysql', 'phpext' => 'mysqli', 'pdoext' => 'pdo_mysql', 'pdodriver' => 'mysql', 'pdooptions' => '',
                    ),
                    'jdb:other' => array(
                        '_name' => 'other',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'gerard', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path',
                        'search_path' => 'bar,public'
                    ),
                ),
                array('jdb'=> array(
                    'almostidentical' => array(
                        '_name' => 'almostidentical',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path',
                        'search_path' => 'genius,public'
                    ),
                    'default' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'timeout,search_path',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'foo' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'timeout,search_path',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'futchball' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'timeout,search_path',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'identical' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'timeout,search_path',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'maria' => array(
                        '_name' => 'maria',
                        'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'mysql', 'phpext' => 'mysqli', 'pdoext' => 'pdo_mysql', 'pdodriver' => 'mysql', 'pdooptions' => '',
                    ),
                    'other' => array(
                        '_name' => 'other',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'gerard', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path',
                        'search_path' => 'bar,public'
                    ),
                )
                )
            ),
            // test 9 : alias to a pgsql profile, and two identical profile except for search path, one will be an
            // alias, an other stays a different profile. And add a virtual profile that will be not an alias
            // we simulate what it is done in createVirtualProfile
            array(
                array(

                    'jdb:almostidentical' => array(
                        '_name' => 'almostidentical',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path',
                        'search_path' => 'genius,public'
                    ),
                    'jdb:default' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path,timeout',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'jdb:foo' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path,timeout',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'jdb:identical' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path,timeout',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'jdb:maria' => array(
                        '_name' => 'maria',
                        'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'mysql', 'phpext' => 'mysqli', 'pdoext' => 'pdo_mysql', 'pdodriver' => 'mysql', 'pdooptions' => '',
                    ),
                    'jdb:other' => array(
                        '_name' => 'other',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'gerard', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path',
                        'search_path' => 'bar,public'
                    ),
                    'jdb:myvirtual' => array(
                        'driver' => 'pgsql', 'host' => 'otherhost', 'port' => 5432, 'user' => 'patrick', 'password' => 'pass',
                        'search_path'=>'bar,public'
                    )
                ),
                array('jdb'=> array(
                    'almostidentical' => array(
                        '_name' => 'almostidentical',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path',
                        'search_path' => 'genius,public'
                    ),
                    'default' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'timeout,search_path',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'foo' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'timeout,search_path',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'identical' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'timeout,search_path',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'myvirtual' => array(
                        '_name' => 'myvirtual',
                        'driver' => 'pgsql', 'host' => 'otherhost', 'port' => 5432, 'user' => 'patrick', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path',
                        'search_path' => 'bar,public'
                    ),
                    'maria' => array(
                        '_name' => 'maria',
                        'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'mysql', 'phpext' => 'mysqli', 'pdoext' => 'pdo_mysql', 'pdodriver' => 'mysql', 'pdooptions' => '',
                    ),
                    'other' => array(
                        '_name' => 'other',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'gerard', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path',
                        'search_path' => 'bar,public'
                    ),
                )
                )
            ),
            // test 10 : alias to a pgsql profile, and two identical profile except for search path, one will be an
            // alias, an other stays a different profile.
            // And add a virtual profile that will be not an alias because it has no the same timeout
            // we simulate what it is done in createVirtualProfile
            array(
                array(

                    'jdb:almostidentical' => array(
                        '_name' => 'almostidentical',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path',
                        'search_path' => 'genius,public'
                    ),
                    'jdb:default' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path,timeout',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'jdb:foo' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path,timeout',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'jdb:identical' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path,timeout',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'jdb:maria' => array(
                        '_name' => 'maria',
                        'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'mysql', 'phpext' => 'mysqli', 'pdoext' => 'pdo_mysql', 'pdodriver' => 'mysql', 'pdooptions' => '',
                    ),
                    'jdb:other' => array(
                        '_name' => 'other',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'gerard', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path',
                        'search_path' => 'bar,public'
                    ),
                    'jdb:myvirtual' => array(
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'search_path'=>'bar,public'
                    )
                ),
                array('jdb'=> array(
                    'almostidentical' => array(
                        '_name' => 'almostidentical',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path',
                        'search_path' => 'genius,public'
                    ),
                    'default' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'timeout,search_path',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'foo' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'timeout,search_path',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'identical' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'timeout,search_path',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'maria' => array(
                        '_name' => 'maria',
                        'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'mysql', 'phpext' => 'mysqli', 'pdoext' => 'pdo_mysql', 'pdodriver' => 'mysql', 'pdooptions' => '',
                    ),
                    'other' => array(
                        '_name' => 'other',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'gerard', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path',
                        'search_path' => 'bar,public'
                    ),
                    'myvirtual' => array(
                        '_name' => 'myvirtual',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path,timeout',
                        'timeout'=>181, 'search_path' => 'bar,public'
                    ),
                )
                )
            ),
            // test 11 : alias to a pgsql profile, and two identical profile except for search path, one will be an
            // alias, an other stays a different profile.
            // And add a virtual profile that will be an alias because it has the same changed timeout
            // we simulate what it is done in createVirtualProfile
            array(
                array(

                    'jdb:almostidentical' => array(
                        '_name' => 'almostidentical',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path',
                        'search_path' => 'genius,public'
                    ),
                    'jdb:default' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path,timeout',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'jdb:foo' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path,timeout',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'jdb:identical' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path,timeout',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'jdb:maria' => array(
                        '_name' => 'maria',
                        'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'mysql', 'phpext' => 'mysqli', 'pdoext' => 'pdo_mysql', 'pdodriver' => 'mysql', 'pdooptions' => '',
                    ),
                    'jdb:other' => array(
                        '_name' => 'other',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'gerard', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path',
                        'search_path' => 'bar,public'
                    ),
                    'jdb:myvirtual' => array(
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'search_path'=>'bar,public', 'timeout' => 180
                    )
                ),
                array('jdb'=> array(
                    'almostidentical' => array(
                        '_name' => 'almostidentical',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path',
                        'search_path' => 'genius,public'
                    ),
                    'default' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'timeout,search_path',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'foo' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'timeout,search_path',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'identical' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'timeout,search_path',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'maria' => array(
                        '_name' => 'maria',
                        'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'mysql', 'phpext' => 'mysqli', 'pdoext' => 'pdo_mysql', 'pdodriver' => 'mysql', 'pdooptions' => '',
                    ),
                    'other' => array(
                        '_name' => 'other',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'gerard', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'search_path',
                        'search_path' => 'bar,public'
                    ),
                    'myvirtual' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => 'timeout,search_path',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                )
                )
            ),
        );
    }

    /**
     * @dataProvider getDuplicateProfileData
     * @param $sourceProfiles
     * @param $expectedProfiles
     * @return void
     */
    function testDuplicateProfile($sourceProfiles, $expectedProfiles)
    {
        $compil = new testjProfilesCompiler($sourceProfiles);
        $profiles = $compil->compile();
        ksort($profiles);
        $this->assertEquals($expectedProfiles, $profiles);
    }
}
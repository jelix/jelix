<?php

use Jelix\Core\App;
use Jelix\Profiles\ReaderPlugin;
use Jelix\Services\Database\DbProfilePlugin;

/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2007-2025 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
use Jelix\Core\Profiles;

class ProfilesReaderForTest extends \Jelix\Profiles\ProfilesReader {

    public function readFromTestArray($iniContent)
    {
        return $this->compile($iniContent);
    }
}


class jDb_profilesTest  extends \Jelix\UnitTests\UnitTestCase
{
    public function setUp() : void  {
        self::initJelixConfig();
    }

    public function tearDown() : void  {
        Profiles::clear();
        parent::tearDown();
    }

    function testProfile() {
        $p = Profiles::get('jdb', 'jelix_tests_mysql');
        $expected = array(
            'driver'=>"mysqli",
            'database'=>"testapp_mysql",
            'host'=> "localhost_mysql",
            'user'=> "plop_mysql",
            'password'=> "futchball_mysql",
            'persistent'=> true,
            'force_encoding'=>true,
            '_name'=>'jelix_tests_mysql',
            'usepdo' => false,
            'dbtype' => 'mysql',
            'phpext' => 'mysqli',
            'pdoext' => 'pdo_mysql',
            'pdodriver' => 'mysql',
            'table_prefix' => '',
            'pdooptions' => '',
            'charset' => 'UTF-8',
            'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath'
        );

        $this->assertEquals($expected, $p);

        $p = Profiles::get('jdb', 'forward');
        $expected= array(
            'driver'=>"mysqli",
            'database'=>"jelix_tests_forward",
            'host'=> "localhost_forward",
            'user'=> "plop_forward",
            'password'=> "futchball_forward",
            'persistent'=> true,
            'force_encoding'=>false,
            '_name'=>'jelix_tests_forward',
            'usepdo' => false,
            'dbtype' => 'mysql',
            'phpext' => 'mysqli',
            'pdoext' => 'pdo_mysql',
            'pdodriver' => 'mysql',
            'table_prefix' => '',
            'pdooptions' => '',
            'charset' => 'UTF-8',
            'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath'
        );

        $this->assertEquals($expected, $p);

        $p = Profiles::get('jdb', 'testapp');
        $this->assertEquals('testapp', $p['_name']);
        $p2 = Profiles::get('jdb');
        $this->assertEquals('testapp', $p2['_name']);
        $this->assertEquals($p, $p2);
        $p = Profiles::get('jdb', 'testapppdo');
        $this->assertEquals('testapppdo', $p['_name']);
    }

    function testVirtualProfile() {
        $profile = array(
            'driver'=>'mysql',
            'database'=>"virtual_mysql",
            'host'=> "localhostv_mysql",
            'user'=> "v_mysql",
            'password'=> "vir_mysql",
            'persistent'=> '1',
            'force_encoding'=>1,
            'charset' => 'UTF-8',
            'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath'
        );

        Profiles::createVirtualProfile('jdb', 'foobar', $profile);

        $p = Profiles::get('jdb', 'foobar');
        $profile['_name'] = 'foobar';
        $profile['usepdo'] = false;
        $profile['dbtype'] = 'mysql';
        $profile['phpext'] = 'mysql';
        $profile['pdoext'] = 'pdo_mysql';
        $profile['pdodriver'] = 'mysql';
        $profile['persistent'] = true;
        $profile['force_encoding'] = true;
        $profile['table_prefix'] = '';
        $profile['pdooptions'] = '';
        $profile['charset'] = 'UTF-8';
        $profile['filePathParser'] = '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath';

        $this->assertEquals($profile, $p);
    }

    function testCommonValues() {

        $profile = array(
            'trace'=> 1,
            'connection_timeout'=> 10,
            'exceptions'=> 1
        );

        Profiles::createVirtualProfile('acme', '__common__', $profile);

        $profile = array(
            'wsdl'=> "http://example.com/wsdl1",
            'connection_timeout'=>25
        );

        Profiles::createVirtualProfile('acme', 'foo', $profile);

        $p = Profiles::get('acme', 'foo');
        $expected = array(
            'trace'=> 1,
            'exceptions'=> 1,
            'connection_timeout'=>25,
            'wsdl'=> "http://example.com/wsdl1",
            '_name'=>'foo',
        );

        $this->assertEquals($expected, $p);
    }


    static function getDuplicateProfileData()
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
                        'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'foo' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'maria' => array(
                        '_name' => 'maria',
                        'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'mysql', 'phpext' => 'mysqli', 'pdoext' => 'pdo_mysql', 'pdodriver' => 'mysql', 'pdooptions' => '',
                        'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
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
                            'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                        ),
                        'foo' => array(
                            '_name' => 'default',
                            'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                            'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                            'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                            'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                        ),
                        'identical' => array(
                            '_name' => 'default',
                            'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                            'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                            'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                            'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                        ),
                        'maria' => array(
                            '_name' => 'maria',
                            'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass',
                            'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                            'dbtype' => 'mysql', 'phpext' => 'mysqli', 'pdoext' => 'pdo_mysql', 'pdodriver' => 'mysql', 'pdooptions' => '',
                            'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
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
                        'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'foo' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'identical' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'maria' => array(
                        '_name' => 'maria',
                        'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'mysql', 'phpext' => 'mysqli', 'pdoext' => 'pdo_mysql', 'pdodriver' => 'mysql', 'pdooptions' => '',
                        'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
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
                        'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'foo' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'identical' => array(
                        '_name' => 'identical',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout' => 5, 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'maria' => array(
                        '_name' => 'maria',
                        'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'mysql', 'phpext' => 'mysqli', 'pdoext' => 'pdo_mysql', 'pdodriver' => 'mysql', 'pdooptions' => '',
                        'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
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
                        'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'foo' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'identical' => array(
                        '_name' => 'identical',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout' => 180, 'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'maria' => array(
                        '_name' => 'maria',
                        'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'mysql', 'phpext' => 'mysqli', 'pdoext' => 'pdo_mysql', 'pdodriver' => 'mysql', 'pdooptions' => '',
                        'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
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
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'search_path' => 'genius,public','charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'default' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout'=>180, 'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'foo' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout'=>180, 'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'identical' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout'=>180, 'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'maria' => array(
                        '_name' => 'maria',
                        'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'mysql', 'phpext' => 'mysqli', 'pdoext' => 'pdo_mysql', 'pdodriver' => 'mysql', 'pdooptions' => '',
                        'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'other' => array(
                        '_name' => 'other',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'gerard', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
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
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'search_path' => 'genius,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'default' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout'=>180, 'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'foo' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout'=>180, 'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'futchball' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout'=>180, 'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'identical' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout'=>180, 'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'maria' => array(
                        '_name' => 'maria',
                        'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'mysql', 'phpext' => 'mysqli', 'pdoext' => 'pdo_mysql', 'pdodriver' => 'mysql', 'pdooptions' => '',
                        'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'other' => array(
                        '_name' => 'other',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'gerard', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
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
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'search_path' => 'genius,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'jdb:default' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout'=>180, 'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'jdb:foo' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout'=>180, 'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'jdb:futchball' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout'=>180, 'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'jdb:identical' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout'=>180, 'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'jdb:maria' => array(
                        '_name' => 'maria',
                        'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'mysql', 'phpext' => 'mysqli', 'pdoext' => 'pdo_mysql', 'pdodriver' => 'mysql', 'pdooptions' => '',
                        'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'jdb:other' => array(
                        '_name' => 'other',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'gerard', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                ),
                array('jdb'=> array(
                    'almostidentical' => array(
                        '_name' => 'almostidentical',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'search_path' => 'genius,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'default' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout'=>180, 'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'foo' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout'=>180, 'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'futchball' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout'=>180, 'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'identical' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout'=>180, 'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'maria' => array(
                        '_name' => 'maria',
                        'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'mysql', 'phpext' => 'mysqli', 'pdoext' => 'pdo_mysql', 'pdodriver' => 'mysql', 'pdooptions' => '',
                        'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'other' => array(
                        '_name' => 'other',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'gerard', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
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
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'search_path' => 'genius,public'
                    ),
                    'jdb:default' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'jdb:foo' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'jdb:identical' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
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
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
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
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'search_path' => 'genius,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'default' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout'=>180, 'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'foo' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout'=>180, 'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'identical' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout'=>180, 'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'myvirtual' => array(
                        '_name' => 'myvirtual',
                        'driver' => 'pgsql', 'host' => 'otherhost', 'port' => 5432, 'user' => 'patrick', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'maria' => array(
                        '_name' => 'maria',
                        'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'mysql', 'phpext' => 'mysqli', 'pdoext' => 'pdo_mysql', 'pdodriver' => 'mysql', 'pdooptions' => '',
                        'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'other' => array(
                        '_name' => 'other',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'gerard', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
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
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'search_path' => 'genius,public'
                    ),
                    'jdb:default' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'jdb:foo' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout'=>180, 'search_path' => 'bar,public'
                    ),
                    'jdb:identical' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
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
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
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
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'search_path' => 'genius,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'default' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout'=>180, 'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'foo' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout'=>180, 'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'identical' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout'=>180, 'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'maria' => array(
                        '_name' => 'maria',
                        'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'mysql', 'phpext' => 'mysqli', 'pdoext' => 'pdo_mysql', 'pdodriver' => 'mysql', 'pdooptions' => '',
                        'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'other' => array(
                        '_name' => 'other',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'gerard', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'myvirtual' => array(
                        '_name' => 'myvirtual',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout'=>181, 'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
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
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'search_path' => 'genius,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'jdb:default' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout'=>180, 'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'jdb:foo' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout'=>180, 'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'jdb:identical' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout'=>180, 'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'jdb:maria' => array(
                        '_name' => 'maria',
                        'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'mysql', 'phpext' => 'mysqli', 'pdoext' => 'pdo_mysql', 'pdodriver' => 'mysql', 'pdooptions' => '',
                        'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'jdb:other' => array(
                        '_name' => 'other',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'gerard', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
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
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'search_path' => 'genius,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'default' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout'=>180, 'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'foo' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout'=>180, 'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'identical' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout'=>180, 'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'maria' => array(
                        '_name' => 'maria',
                        'driver' => 'mysqli', 'host' => 'localhost', 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'mysql', 'phpext' => 'mysqli', 'pdoext' => 'pdo_mysql', 'pdodriver' => 'mysql', 'pdooptions' => '',
                        'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'other' => array(
                        '_name' => 'other',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'gerard', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
                    ),
                    'myvirtual' => array(
                        '_name' => 'default',
                        'driver' => 'pgsql', 'host' => 'localhost', 'port' => 5432, 'user' => 'toto', 'password' => 'pass',
                        'usepdo' => false, 'persistent' => false, 'force_encoding' => false, 'table_prefix' => '',
                        'dbtype' => 'pgsql', 'phpext' => 'pgsql', 'pdoext' => 'pdo_pgsql', 'pdodriver' => 'pgsql', 'pdooptions' => '',
                        'timeout'=>180, 'search_path' => 'bar,public', 'charset' => 'UTF-8', 'filePathParser' => '\Jelix\Services\Database\DbProfilePlugin::parseSqlitePath',
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
        $compiler = new ProfilesReaderForTest(function($name) {
            if ($name == 'jdb') {
                return new DbProfilePlugin('jdb');
            }
            return new ReaderPlugin($name);
        });

        $profiles = $compiler->readFromTestArray($sourceProfiles);

        ksort($profiles);
        $this->assertEquals($expectedProfiles, $profiles);
    }
}

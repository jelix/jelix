<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2009 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

include_once (JELIX_LIB_PATH.'plugins/db/mysql/mysql.dbtools.php');
include_once (JELIX_LIB_PATH.'plugins/db/pgsql/pgsql.dbtools.php');
include_once (JELIX_LIB_PATH.'plugins/db/oci/oci.dbtools.php');
include_once (JELIX_LIB_PATH.'plugins/db/sqlite/sqlite.dbtools.php');


class jDbToolsTest extends jUnitTestCaseDb {

    public static function setUpBeforeClass() {
        self::initJelixConfig();
    }

    function testEncloseName(){

        $tools= new mysqlDbTools(null);
        $result = $tools->encloseName('foo');
        $this->assertEquals('`foo`',$result);

        $tools= new pgsqlDbTools(null);
        $result = $tools->encloseName('foo');
        $this->assertEquals('"foo"',$result);

        $tools= new ociDbTools(null);
        $result = $tools->encloseName('foo');
        $this->assertEquals('foo',$result);

        $tools= new sqliteDbTools(null);
        $result = $tools->encloseName('foo');
        $this->assertEquals('foo',$result);
    }

    function testFloatToStr() {
        $this->assertEquals('12', jDb::floatToStr(12));
        $this->assertEquals('12.56', jDb::floatToStr(12.56));
        $this->assertEquals('12', jDb::floatToStr("12"));
        $this->assertEquals('12.56', jDb::floatToStr("12.56"));
        $this->assertEquals('65.78E6', jDb::floatToStr("65.78E6"));
        $this->assertEquals('65780000.0', jDb::floatToStr(65.78E6));
        $this->assertEquals('6.578E+43', jDb::floatToStr(65.78E42));

        // not very good behavior, but this is the behavior in old stable version of jelix
        $this->assertEquals('65', jDb::floatToStr("65,650.98"));
        $this->assertEquals('12', jDb::floatToStr("12,589")); // ',' no allowed as decimal separator
        $this->assertEquals('96', jDb::floatToStr("96 000,98"));

        // some test to detect if the behavior of PHP change
        $this->assertFalse(is_numeric("65,650.98"));
        $this->assertFalse(is_float("65,650.98"));
        $this->assertFalse(is_integer("65,650.98"));
        $this->assertEquals('65', floatval("65,650.98"));
    }

    function testStringToPhpValue(){
    
        $tools= new mysqlDbTools(null);

        try {
            $tools->stringToPhpValue('int','5', false);
            $this->fail("stringToPhpValue accepts int !!");
        } catch(Exception $e) {
            $this->assertTrue(true);
        }
        try {
            $tools->stringToPhpValue( 'string','$foo',false);
            $this->fail("stringToPhpValue accepts string !!");
        } catch(Exception $e) {
            $this->assertTrue(true);
        }

        try {
            $tools->stringToPhpValue( 'autoincrement','5',false);
            $this->fail("stringToPhpValue accepts autoincrement !!");
        } catch(Exception $e) {
            $this->assertTrue(true);
        }

        // with no checknull
        $result = $tools->stringToPhpValue( 'integer','5',false);
        $this->assertEquals(5,$result);
        $result = $tools->stringToPhpValue( 'float','5',false);
        $this->assertEquals(5,$result);
        $result = $tools->stringToPhpValue( 'varchar','$foo',false);
        $this->assertEquals('$foo',$result);
        $result = $tools->stringToPhpValue('varchar','$f\'oo', false);
        $this->assertEquals('$f\'oo',$result);
        $result = $tools->stringToPhpValue('double','5.63', false);
        $this->assertEquals(5.63,$result);
        $result = $tools->stringToPhpValue('float','5.63', false);
        $this->assertEquals(5.63,$result);
        $result = $tools->stringToPhpValue('float','983298095.631212', false);
        $this->assertEquals(983298095.631212,$result);
        $result = $tools->stringToPhpValue('numeric','565465465463', false);
        $this->assertEquals('565465465463',$result);
        $result = $tools->stringToPhpValue('numeric','565469876543139798641315465463', false);
        $this->assertEquals('565469876543139798641315465463',$result);

        // with checknull 
        $result = $tools->stringToPhpValue('integer','NULL', true);
        $this->assertNull($result);
        $result = $tools->stringToPhpValue('varchar','NULL', true);
        $this->assertNull($result);
    }


    function testEscapeValue(){
    
        $tools= new mysqlDbTools(null);

        try {
            $tools->escapeValue('int','5', false);
            $this->fail("escapeValue accepts int !!");
        } catch(Exception $e) {
            $this->assertTrue(true);
        }
        try {
            $tools->escapeValue( 'string','$foo',false);
            $this->fail("escapeValue accepts string !!");
        } catch(Exception $e) {
            $this->assertTrue(true);
        }

        try {
            $tools->escapeValue( 'autoincrement','5',false);
            $this->fail("escapeValue accepts autoincrement !!");
        } catch(Exception $e) {
            $this->assertTrue(true);
        }


        // with no checknull
        $result = $tools->escapeValue( 'integer',5,false);
        $this->assertEquals("5",$result);
        $result = $tools->escapeValue( 'numeric',598787232098320,false);
        $this->assertEquals("598787232098320",$result);
        $result = $tools->escapeValue( 'numeric',59878723209832,false);
        $this->assertEquals("59878723209832",$result);
        $result = $tools->escapeValue( 'numeric',5987872320983,false);
        $this->assertEquals("5987872320983",$result);
        $result = $tools->escapeValue( 'numeric',598787232098,false);
        $this->assertEquals("598787232098",$result);
        $result = $tools->escapeValue( 'numeric',59878723209,false);
        $this->assertEquals("59878723209",$result);
        $result = $tools->escapeValue( 'numeric',5987872320,false);
        $this->assertEquals("5987872320",$result);
        $result = $tools->escapeValue( 'integer',598787232,false);
        $this->assertEquals("598787232",$result);
        $result = $tools->escapeValue( 'integer',59878723,false);
        $this->assertEquals("59878723",$result);
        $result = $tools->escapeValue( 'integer',5987872,false);
        $this->assertEquals("5987872",$result);
        $result = $tools->escapeValue( 'numeric',598787232098320909,false);
        $this->assertEquals("598787232098320909",$result);
        $result = $tools->escapeValue( 'numeric',5987872320983209098,false);
        $this->assertEquals("5987872320983209098",$result);
        $result = $tools->escapeValue( 'numeric',59878723209832090982,false);
        $this->assertEquals("5.987872320983209E+19",$result);
        //$result = $tools->escapeValue( 'numeric',5987872320983209098238723,false);
        //$this->assertEquals("5987872320983209098238723",$result);
        
        $result = $tools->escapeValue( 'float',5,false);
        $this->assertEquals("5",$result);
        $result = $tools->escapeValue( 'varchar','$foo',false);
        $this->assertEquals('\'$foo\'',$result);
        $result = $tools->escapeValue('varchar','$f\'oo', false);
        $this->assertEquals('\'$f\\\'oo\'',$result);
        $result = $tools->escapeValue('double',5.63, false);
        $this->assertEquals('5.63',$result);
        $result = $tools->escapeValue('float', 98084345.637655464, false);
        $this->assertEquals('98084345.63765547',$result);
        $result = $tools->escapeValue('decimal',98084345.637655464, false);
        $this->assertEquals('98084345.63765547',$result);
        $result = $tools->escapeValue('numeric','565465465463', false);
        $this->assertEquals('565465465463',$result);
        $result = $tools->escapeValue('numeric','565469876543139798641315465463', false);
        $this->assertEquals('565469876543139798641315465463',$result);

        // with checknull 
        $result = $tools->escapeValue('integer',5, true);
        $this->assertEquals('5',$result);
        $result = $tools->escapeValue('integer',null, true);
        $this->assertEquals('NULL',$result);
        $result = $tools->escapeValue('varchar',null, true);
        $this->assertEquals('NULL',$result);
    }

    function testParseCreateTable() {
        $tools = new mysqlDbTools(null);
        $sql = "CREATE TABLE city (
                city_id INTEGER  PRIMARY KEY AUTOINCREMENT,
                country_id integer NOT NULL,
                name  varchar(50) not null,
                postcode integer DEFAULT 0,
                latitude varchar(20), longitude varchar(20),
                CONSTRAINT coordinates
                 UNIQUE(latitude, longitude),
                FOREIGN KEY (country_id) REFERENCES country (country_id))
                ";
        $result = $tools->parseCREATETABLE($sql);
        $this->assertTrue($result !== false);
        $this->assertEquals('city', $result['name']);
        $this->assertEquals('', $result['options']);
        $this->assertFalse($result['temporary']);
        $this->assertFalse($result['ifnotexists']);
        $this->assertEquals(array(
            'city_id INTEGER PRIMARY KEY AUTOINCREMENT',
            'country_id integer NOT NULL',
            'name varchar(50) not null',
            'postcode integer DEFAULT 0',
            'latitude varchar(20)',
            'longitude varchar(20)',
        ), $result['columns']);
        $this->assertEquals(array(
            'CONSTRAINT coordinates UNIQUE(latitude, longitude)',
            'FOREIGN KEY (country_id) REFERENCES country (country_id)',
        ), $result['constraints']);

    }

    function testParseCreateTemporaryTable() {
        $tools = new mysqlDbTools(null);
        $sql = "CREATE TEMPORARY 
                TABLE IF NOT
                EXISTS city (
                city_id INTEGER  PRIMARY KEY AUTOINCREMENT,
                country_id integer NOT NULL,
                name  varchar(50) not null,
                postcode integer DEFAULT 0,
                latitude varchar(20), longitude varchar(20),
                CONSTRAINT coordinates
                 UNIQUE(latitude, longitude),
                FOREIGN KEY (country_id) REFERENCES country (country_id)
                ) BIDULE ";
        $result = $tools->parseCREATETABLE($sql);
        $this->assertTrue($result !== false);
        $this->assertEquals('city', $result['name']);
        $this->assertEquals('BIDULE', $result['options']);
        $this->assertTrue($result['temporary']);
        $this->assertTrue($result['ifnotexists']);
        $this->assertEquals(array(
            'city_id INTEGER PRIMARY KEY AUTOINCREMENT',
            'country_id integer NOT NULL',
            'name varchar(50) not null',
            'postcode integer DEFAULT 0',
            'latitude varchar(20)',
            'longitude varchar(20)',
        ), $result['columns']);
        $this->assertEquals(array(
            'CONSTRAINT coordinates UNIQUE(latitude, longitude)',
            'FOREIGN KEY (country_id) REFERENCES country (country_id)',
        ), $result['constraints']);
    }

    function testInsertDataEmptyTableBefore() {
        $this->dbProfile = 'mysqli_profile';
        $cnt = jDb::getConnection('mysqli_profile');
        $tools = $cnt->tools();

        $columns = array('key', 'keyalias', 'lang', 'label');

        // insert a record
        $records = array(
            array('key'=>5, 'keyalias'=>'dummy', 'lang'=>'en', 'label'=>'this is dummy'),
        );
        $this->insertRecordsIntoTable('labels_test',
            $columns,
            $records,
            true);

        // now insert new data, dummy record should not exist
        $count = $tools->insertBulkData('labels_test',
            $columns,
            array(
                array(8, 'foo.label1', 'fr', 'un label1'),
                array(8, 'foo.label2', 'en', 'a label1'),
                array(10, 'foo.label3', 'fr', 'super label')
            ),
            array('key', 'lang'),
            $tools::IBD_EMPTY_TABLE_BEFORE
        );
        $this->assertEquals(3, $count);

        $records = array(
            array('key' => 8, 'keyalias'=>'foo.label1', 'lang'=>'fr', 'label'=>'un label1'),
            array('key' => 8, 'keyalias'=>'foo.label2', 'lang'=>'en', 'label'=>'a label1'),
            array('key' => 10, 'keyalias'=>'foo.label3', 'lang'=>'fr', 'label'=>'super label')
        );
        $this->assertTableContainsRecords('labels_test', $records, true);
        $this->assertTableHasNRecords('labels_test', 3);


        $tools->insertBulkData('labels_test',
            $columns,
            array(
            ),
            array('key', 'lang'),
            $tools::IBD_EMPTY_TABLE_BEFORE
        );
        $this->assertTableIsEmpty('labels_test');
    }

    function testInsertDataOnlyEmptyTableWhereTableIsNotEmpty() {
        $this->dbProfile = 'mysqli_profile';
        $cnt = jDb::getConnection('mysqli_profile');
        $tools = $cnt->tools();

        $columns = array('key', 'keyalias', 'lang', 'label');

        // insert a record
        $records = array(
            array('key'=>5, 'keyalias'=>'dummy', 'lang'=>'en', 'label'=>'this is dummy'),
        );
        $this->insertRecordsIntoTable('labels_test',
            $columns,
            $records,
            true);

        // now insert new data, given records should not exist
        $count = $tools->insertBulkData('labels_test',
            $columns,
            array(
                array(8, 'foo.label1', 'fr', 'un label1'),
                array(8, 'foo.label2', 'en', 'a label1'),
                array(10, 'foo.label3', 'fr', 'super label')
            ),
            array('key', 'lang'),
            $tools::IBD_INSERT_ONLY_IF_TABLE_IS_EMPTY
        );
        $this->assertEquals(0, $count);
        $this->assertTableContainsRecords('labels_test', $records, true);
        $this->assertTableHasNRecords('labels_test', 1);
    }

    function testInsertDataOnlyEmptyTableWhereTableIsEmpty() {
        $this->dbProfile = 'mysqli_profile';
        $cnt = jDb::getConnection('mysqli_profile');
        $tools = $cnt->tools();

        $this->emptyTable('labels_test');

        $columns = array('key', 'keyalias', 'lang', 'label');

        $count = $tools->insertBulkData('labels_test',
            $columns,
            array(
                array(8, 'foo.label1', 'fr', 'un label1'),
                array(8, 'foo.label2', 'en', 'a label1'),
                array(10, 'foo.label3', 'fr', 'super label')
            ),
            array('key', 'lang'),
            $tools::IBD_INSERT_ONLY_IF_TABLE_IS_EMPTY
        );
        $this->assertEquals(3, $count);

        $records = array(
            array('key' => 8, 'keyalias'=>'foo.label1', 'lang'=>'fr', 'label'=>'un label1'),
            array('key' => 8, 'keyalias'=>'foo.label2', 'lang'=>'en', 'label'=>'a label1'),
            array('key' => 10, 'keyalias'=>'foo.label3', 'lang'=>'fr', 'label'=>'super label')
        );
        $this->assertTableContainsRecords('labels_test', $records, true);
        $this->assertTableHasNRecords('labels_test', 3);
    }

    function testInsertDataIgnoreIfExistNoExistingRecords() {
        $this->dbProfile = 'mysqli_profile';
        $cnt = jDb::getConnection('mysqli_profile');
        $tools = $cnt->tools();

        $columns = array('key', 'keyalias', 'lang', 'label');

        $records = array(
            array('key'=>5, 'keyalias'=>'dummy', 'lang'=>'en', 'label'=>'this is dummy'),
        );
        $this->insertRecordsIntoTable('labels_test',
            $columns,
            $records,
            true);

        $count = $tools->insertBulkData('labels_test',
            $columns,
            array(
                array(5, 'foo.label1', 'fr', 'un label1'),
                array(8, 'foo.label2', 'en', 'a label1'),
                array(10, 'foo.label3', 'fr', 'super label')
            ),
            array('key', 'lang'),
            $tools::IBD_IGNORE_IF_EXIST
        );
        $this->assertEquals(3, $count);

        $records = array(
            array('key'=>5, 'keyalias'=>'dummy', 'lang'=>'en', 'label'=>'this is dummy'),
            array('key' => 5, 'keyalias'=>'foo.label1', 'lang'=>'fr', 'label'=>'un label1'),
            array('key' => 8, 'keyalias'=>'foo.label2', 'lang'=>'en', 'label'=>'a label1'),
            array('key' => 10, 'keyalias'=>'foo.label3', 'lang'=>'fr', 'label'=>'super label')
        );
        $this->assertTableContainsRecords('labels_test', $records, true);
        $this->assertTableHasNRecords('labels_test', 4);
    }

    function testInsertDataIgnoreIfExistExistingRecords() {
        $this->dbProfile = 'mysqli_profile';
        $cnt = jDb::getConnection('mysqli_profile');
        $tools = $cnt->tools();

        $columns = array('key', 'keyalias', 'lang', 'label');

        $records = array(
            array('key'=>5, 'keyalias'=>'dummy', 'lang'=>'en', 'label'=>'this is dummy'),
        );
        $this->insertRecordsIntoTable('labels_test',
            $columns,
            $records,
            true);

        $count = $tools->insertBulkData('labels_test',
            $columns,
            array(
                array(5, 'foo.label1', 'en', 'un label1'),
                array(8, 'foo.label2', 'en', 'a label1'),
                array(10, 'foo.label3', 'fr', 'super label')
            ),
            array('key', 'lang'),
            $tools::IBD_IGNORE_IF_EXIST
        );
        $this->assertEquals(2, $count);

        $records = array(
            array('key'=>5, 'keyalias'=>'dummy', 'lang'=>'en', 'label'=>'this is dummy'),
            array('key' => 8, 'keyalias'=>'foo.label2', 'lang'=>'en', 'label'=>'a label1'),
            array('key' => 10, 'keyalias'=>'foo.label3', 'lang'=>'fr', 'label'=>'super label')
        );
        $this->assertTableContainsRecords('labels_test', $records, true);
        $this->assertTableHasNRecords('labels_test', 3);
    }


    function testInsertDataUpdateIfExistNoExistingRecords() {
        $this->dbProfile = 'mysqli_profile';
        $cnt = jDb::getConnection('mysqli_profile');
        $tools = $cnt->tools();

        $columns = array('key', 'keyalias', 'lang', 'label');

        $records = array(
            array('key'=>5, 'keyalias'=>'dummy', 'lang'=>'en', 'label'=>'this is dummy'),
        );
        $this->insertRecordsIntoTable('labels_test',
            $columns,
            $records,
            true);

        $count = $tools->insertBulkData('labels_test',
            $columns,
            array(
                array(5, 'foo.label1', 'fr', 'un label1'),
                array(8, 'foo.label2', 'en', 'a label1'),
                array(10, 'foo.label3', 'fr', 'super label')
            ),
            array('key', 'lang'),
            $tools::IBD_UPDATE_IF_EXIST
        );
        $this->assertEquals(3, $count);

        $records = array(
            array('key'=>5, 'keyalias'=>'dummy', 'lang'=>'en', 'label'=>'this is dummy'),
            array('key' => 5, 'keyalias'=>'foo.label1', 'lang'=>'fr', 'label'=>'un label1'),
            array('key' => 8, 'keyalias'=>'foo.label2', 'lang'=>'en', 'label'=>'a label1'),
            array('key' => 10, 'keyalias'=>'foo.label3', 'lang'=>'fr', 'label'=>'super label')
        );
        $this->assertTableContainsRecords('labels_test', $records, true);
        $this->assertTableHasNRecords('labels_test', 4);
    }

    function testInsertDataUpdateIfExistExistingRecords() {
        $this->dbProfile = 'mysqli_profile';
        $cnt = jDb::getConnection('mysqli_profile');
        $tools = $cnt->tools();

        $columns = array('key', 'keyalias', 'lang', 'label');

        $records = array(
            array('key'=>5, 'keyalias'=>'dummy', 'lang'=>'en', 'label'=>'this is dummy'),
        );
        $this->insertRecordsIntoTable('labels_test',
            $columns,
            $records,
            true);

        $count = $tools->insertBulkData('labels_test',
            $columns,
            array(
                array(5, 'foo.label1', 'en', 'un label1'),
                array(8, 'foo.label2', 'en', 'a label1'),
                array(10, 'foo.label3', 'fr', 'super label')
            ),
            array('key', 'lang'),
            $tools::IBD_UPDATE_IF_EXIST
        );
        $this->assertEquals(3, $count);

        $records = array(
            array('key'=>5, 'keyalias'=>'foo.label1', 'lang'=>'en', 'label'=>'un label1'),
            array('key' => 8, 'keyalias'=>'foo.label2', 'lang'=>'en', 'label'=>'a label1'),
            array('key' => 10, 'keyalias'=>'foo.label3', 'lang'=>'fr', 'label'=>'super label')
        );
        $this->assertTableContainsRecords('labels_test', $records, true);
        $this->assertTableHasNRecords('labels_test', 3);
    }
}


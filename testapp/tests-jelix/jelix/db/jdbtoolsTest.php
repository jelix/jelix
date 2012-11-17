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


class jDbToolsTest extends jUnitTestCase {

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
        $this->assertEquals('65.78E6', jDb::floatToStr(65.78E6));
        $this->assertEquals('65.78E42', jDb::floatToStr(65.78E42));

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
        $result = $tools->escapeValue( 'numeric',5987872320983209098238723,false);
        $this->assertEquals("5987872320983209098238723",$result);
        
        $result = $tools->escapeValue( 'float',5,false);
        $this->assertEquals("5",$result);
        $result = $tools->escapeValue( 'varchar','$foo',false);
        $this->assertEquals('\'$foo\'',$result);
        $result = $tools->escapeValue('varchar','$f\'oo', false);
        $this->assertEquals('\'$f\\\'oo\'',$result);
        $result = $tools->escapeValue('double',5.63, false);
        $this->assertEquals('5.63',$result);
        $result = $tools->escapeValue('float',98084345.637655464, false);
        $this->assertEquals('98084345.637655464',$result);
        $result = $tools->escapeValue('decimal',98084345.637655464, false);
        $this->assertEquals('98084345.637655464',$result);
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


}


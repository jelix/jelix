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


class UTjDbTools extends jUnitTestCase {


    function testEncloseName(){

        $tools= new mysqlDbTools(null);
        $result = $tools->encloseName('foo');
        $this->assertEqualOrDiff('`foo`',$result);

        $tools= new pgsqlDbTools(null);
        $result = $tools->encloseName('foo');
        $this->assertEqualOrDiff('"foo"',$result);

        $tools= new ociDbTools(null);
        $result = $tools->encloseName('foo');
        $this->assertEqualOrDiff('foo',$result);

        $tools= new sqliteDbTools(null);
        $result = $tools->encloseName('foo');
        $this->assertEqualOrDiff('foo',$result);
    }
    
   function testStringToPhpValue(){
    
        $tools= new mysqlDbTools(null);

        try {
            $tools->stringToPhpValue('int','5', false);
            $this->fail("stringToPhpValue accepts int !!");
        } catch(Exception $e) {
            $this->pass();
        }
        try {
            $tools->stringToPhpValue( 'string','$foo',false);
            $this->fail("stringToPhpValue accepts string !!");
        } catch(Exception $e) {
            $this->pass();
        }

        try {
            $tools->stringToPhpValue( 'autoincrement','5',false);
            $this->fail("stringToPhpValue accepts autoincrement !!");
        } catch(Exception $e) {
            $this->pass();
        }

        // with no checknull
        $result = $tools->stringToPhpValue( 'integer','5',false);
        $this->assertEqualOrDiff(5,$result);
        $result = $tools->stringToPhpValue( 'float','5',false);
        $this->assertEqualOrDiff(5,$result);
        $result = $tools->stringToPhpValue( 'varchar','$foo',false);
        $this->assertEqualOrDiff('$foo',$result);
        $result = $tools->stringToPhpValue('varchar','$f\'oo', false);
        $this->assertEqualOrDiff('$f\'oo',$result);
        $result = $tools->stringToPhpValue('double','5.63', false);
        $this->assertEqualOrDiff(5.63,$result);
        $result = $tools->stringToPhpValue('float','5.63', false);
        $this->assertEqualOrDiff(5.63,$result);
        $result = $tools->stringToPhpValue('float','983298095.631212', false);
        $this->assertEqualOrDiff(983298095.631212,$result);
        $result = $tools->stringToPhpValue('numeric','565465465463', false);
        $this->assertEqualOrDiff('565465465463',$result);
        $result = $tools->stringToPhpValue('numeric','565469876543139798641315465463', false);
        $this->assertEqualOrDiff('565469876543139798641315465463',$result);

        // with checknull 
        $result = $tools->stringToPhpValue('integer','NULL', true);
        $this->assertEqualOrDiff(null,$result);
        $result = $tools->stringToPhpValue('varchar','NULL', true);
        $this->assertEqualOrDiff(null,$result);
    }


    function testEscapeValue(){
    
        $tools= new mysqlDbTools(null);

        try {
            $tools->escapeValue('int','5', false);
            $this->fail("escapeValue accepts int !!");
        } catch(Exception $e) {
            $this->pass();
        }
        try {
            $tools->escapeValue( 'string','$foo',false);
            $this->fail("escapeValue accepts string !!");
        } catch(Exception $e) {
            $this->pass();
        }

        try {
            $tools->escapeValue( 'autoincrement','5',false);
            $this->fail("escapeValue accepts autoincrement !!");
        } catch(Exception $e) {
            $this->pass();
        }


        // with no checknull
        $result = $tools->escapeValue( 'integer',5,false);
        $this->assertEqualOrDiff("5",$result);
        $result = $tools->escapeValue( 'numeric',598787232098320,false);
        $this->assertEqualOrDiff("598787232098320",$result);
        $result = $tools->escapeValue( 'numeric',59878723209832,false);
        $this->assertEqualOrDiff("59878723209832",$result);
        $result = $tools->escapeValue( 'numeric',5987872320983,false);
        $this->assertEqualOrDiff("5987872320983",$result);
        $result = $tools->escapeValue( 'numeric',598787232098,false);
        $this->assertEqualOrDiff("598787232098",$result);
        $result = $tools->escapeValue( 'numeric',59878723209,false);
        $this->assertEqualOrDiff("59878723209",$result);
        $result = $tools->escapeValue( 'numeric',5987872320,false);
        $this->assertEqualOrDiff("5987872320",$result);
        $result = $tools->escapeValue( 'integer',598787232,false);
        $this->assertEqualOrDiff("598787232",$result);
        $result = $tools->escapeValue( 'integer',59878723,false);
        $this->assertEqualOrDiff("59878723",$result);
        $result = $tools->escapeValue( 'integer',5987872,false);
        $this->assertEqualOrDiff("5987872",$result);
        $result = $tools->escapeValue( 'numeric',5987872320983209098238723,false);
        $this->assertEqualOrDiff("5987872320983209098238723",$result);
        
        $result = $tools->escapeValue( 'float',5,false);
        $this->assertEqualOrDiff("5",$result);
        $result = $tools->escapeValue( 'varchar','$foo',false);
        $this->assertEqualOrDiff('\'$foo\'',$result);
        $result = $tools->escapeValue('varchar','$f\'oo', false);
        $this->assertEqualOrDiff('\'$f\\\'oo\'',$result);
        $result = $tools->escapeValue('double',5.63, false);
        $this->assertEqualOrDiff('5.63',$result);
        $result = $tools->escapeValue('float',98084345.637655464, false);
        $this->assertEqualOrDiff('98084345.6377',$result);
        $result = $tools->escapeValue('decimal',98084345.637655464, false);
        $this->assertEqualOrDiff('98084345.637655464',$result);
        $result = $tools->escapeValue('numeric','565465465463', false);
        $this->assertEqualOrDiff('565465465463',$result);
        $result = $tools->escapeValue('numeric','565469876543139798641315465463', false);
        $this->assertEqualOrDiff('565469876543139798641315465463',$result);

        // with checknull 
        $result = $tools->escapeValue('integer',5, true);
        $this->assertEqualOrDiff('5',$result);
        $result = $tools->escapeValue('integer',null, true);
        $this->assertEqualOrDiff('NULL',$result);
        $result = $tools->escapeValue('varchar',null, true);
        $this->assertEqualOrDiff('NULL',$result);
    }


}


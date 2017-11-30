<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor Julien Issler
* @copyright   2007 Laurent Jouanneau
* @copyright   2010 Julien Issler
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

include_once (JELIX_LIB_PATH.'plugins/db/pgsql/pgsql.dbschema.php');

class jDbSchema_pgsqlTest extends jUnitTestCase {

    public static function setUpBeforeClass() {
        self::initJelixConfig();
    }

    function testTableList() {
        $db = jDb::getConnection('testapp_pgsql');
        $db->exec('DROP TABLE IF EXISTS test_prod');
        $schema = $db->schema();

        $goodList = array(
            'jacl2_group',
            'jacl2_rights',
            'jacl2_subject',
            'jacl2_subject_group',
            'jacl2_user_group',
            'jsessions',
            'labels1_tests',
            'labels_tests',
            'product_tags_test',
            'product_test',
            'products',
            'testkvdb'
        );

        $list = $schema->getTables();
        $tables = array();
        foreach($list as $table) {
            $tables[] = $table->getName();
        }

        sort($goodList);
        sort($tables);
        $this->assertEquals($goodList, $tables);
    }

    function testTable() {
        $db = jDb::getConnection('testapp_pgsql');
        $schema = $db->schema();

        $table = $schema->getTable('product_test');

        $this->assertNotNull($table);

        $this->assertEquals('product_test', $table->getName());

        $pk = $table->getPrimaryKey();
        $this->assertEquals(array('id'), $pk->columns);

        $is64bits = ( PHP_INT_SIZE*8 == 64 );

        $verif='<array>
    <object class="jDbColumn" key="id">
        <string property="type" value="integer" />
        <string property="name" value="id" />
        <boolean property="notNull" value="true"/>
        <boolean property="autoIncrement" value="true"/>
        <string property="default" value="" />
        <boolean property="hasDefault" value="true"/>
        <integer property="length" value="0"/>
        <integer property="precision" value="0"/>
        <integer property="scale" value="0"/>
        <string property="sequence" value="product_test_id_seq" />
        <boolean property="unsigned" value="false" />
        <null property="minLength"/>
        <null property="maxLength"/>'.
            ($is64bits ?
                '<integer property="minValue" value="-2147483648"/>' :
                '<double property="minValue" value="-2147483648"/>').
            '<integer property="maxValue" value="2147483647"/>
    </object>
    <object class="jDbColumn" key="name">
        <string property="type" value="character" />
        <string property="name" value="name" />
        <boolean property="notNull" value="true"/>
        <boolean property="autoIncrement" value="false"/>
        <string property="default" value="" />
        <boolean property="hasDefault" value="false"/>
        <integer property="length" value="150"/>
        <integer property="precision" value="0"/>
        <integer property="scale" value="0"/>
        <boolean property="sequence" value="false" />
        <boolean property="unsigned" value="false" />
        <integer property="minLength" value="0"/>
        <integer property="maxLength" value="150"/>
        <null property="minValue"/>
        <null property="maxValue"/>
    </object>
    <object class="jDbColumn" key="price">
        <string property="type" value="real" />
        <string property="name" value="price" />
        <boolean property="notNull" value="true"/>
        <boolean property="autoIncrement" value="false"/>
        <string property="default" value="" />
        <boolean property="hasDefault" value="false"/>
        <integer property="length" value="0"/>
        <integer property="precision" value="0"/>
        <integer property="scale" value="0"/>
        <boolean property="sequence" value="false" />
        <boolean property="unsigned" value="false" />
        <null property="minLength"/>
        <null property="maxLength"/>
        <null property="minValue"/>
        <null property="maxValue"/>
    </object>
    <object class="jDbColumn" key="create_date">
        <string property="type" value="time" />
        <string property="name" value="create_date" />
        <boolean property="notNull" value="true"/>
        <boolean property="autoIncrement" value="false"/>
        <string property="default" value="" />
        <boolean property="hasDefault" value="false"/>
        <integer property="length" value="0"/>
        <integer property="precision" value="0"/>
        <integer property="scale" value="0"/>
        <boolean property="sequence" value="false" />
        <boolean property="unsigned" value="false" />
        <integer property="minLength" value="8"/>
        <integer property="maxLength" value="8"/>
        <null property="minValue"/>
        <null property="maxValue"/>
    </object>
    <object class="jDbColumn" key="promo">
        <string property="type" value="boolean" />
        <string property="name" value="promo" />
        <boolean property="notNull" value="true"/>
        <boolean property="autoIncrement" value="false"/>
        <string property="default" value=""/>
        <boolean property="hasDefault" value="false"/>
        <integer property="length" value="0"/>
        <integer property="precision" value="0"/>
        <integer property="scale" value="0"/>
        <boolean property="sequence" value="false" />
        <boolean property="unsigned" value="false" />
        <null property="minLength"/>
        <null property="maxLength"/>
        <integer property="minValue" value="0"/>
        <integer property="maxValue" value="1"/>
    </object>
</array>';

        $this->assertComplexIdenticalStr($table->getColumns(), $verif);
    }

    function testCreateTable()
    {
        $db = jDb::getConnection('testapp_pgsql');
        $db->exec('DROP TABLE IF EXISTS test_prod');
        $schema = $db->schema();


        $columns = array();
        $col = new jDbColumn('id', 'int', 0, false, null, true);
        $col->autoIncrement = true;
        $columns[] = $col;
        $columns[] = new jDbColumn('name', 'string', 50);
        $columns[] = new jDbColumn('price', 'double');
        $columns[] = new jDbColumn('promo', 'boolean');
        $columns[] = new jDbColumn('product_id', 'int');

        $schema->createTable('test_prod', $columns, 'id');

        $table = new pgsqlDbTable('test_prod', $schema);

        $this->assertEquals('test_prod', $table->getName());

        $pk = $table->getPrimaryKey();
        $this->assertEquals(array('id'), $pk->columns);

        $is64bits = ( PHP_INT_SIZE*8 == 64 );

        $verif='<array>
    <object class="jDbColumn" key="id">
        <string property="type" value="integer" />
        <string property="name" value="id" />
        <boolean property="notNull" value="true"/>
        <boolean property="autoIncrement" value="true"/>
        <string property="default" value="" />
        <boolean property="hasDefault" value="true"/>
        <integer property="length" value="0"/>
        <integer property="precision" value="0"/>
        <integer property="scale" value="0"/>
        <string property="sequence" value="test_prod_id_seq" />
        <boolean property="unsigned" value="false" />
        <null property="minLength"/>
        <null property="maxLength"/>'.
            ($is64bits ?
                '<integer property="minValue" value="-2147483648"/>' :
                '<double property="minValue" value="-2147483648"/>').
            '<integer property="maxValue" value="2147483647"/>
    </object>
    <object class="jDbColumn" key="name">
        <string property="type" value="character" />
        <string property="name" value="name" />
        <boolean property="notNull" value="true"/>
        <boolean property="autoIncrement" value="false"/>
        <string property="default" value="" />
        <boolean property="hasDefault" value="false"/>
        <integer property="length" value="50"/>
        <integer property="precision" value="0"/>
        <integer property="scale" value="0"/>
        <boolean property="sequence" value="false" />
        <boolean property="unsigned" value="false" />
        <integer property="minLength" value="0"/>
        <integer property="maxLength" value="50"/>
        <null property="minValue"/>
        <null property="maxValue"/>
    </object>
    <object class="jDbColumn" key="price">
        <string property="type" value="double" />
        <string property="name" value="price" />
        <boolean property="notNull" value="true"/>
        <boolean property="autoIncrement" value="false"/>
        <string property="default" value="" />
        <boolean property="hasDefault" value="false"/>
        <integer property="length" value="0"/>
        <integer property="precision" value="0"/>
        <integer property="scale" value="0"/>
        <boolean property="sequence" value="false" />
        <boolean property="unsigned" value="false" />
        <null property="minLength"/>
        <null property="maxLength"/>
        <null property="minValue"/>
        <null property="maxValue"/>
    </object>
    <object class="jDbColumn" key="product_id">
        <string property="type" value="integer" />
        <string property="name" value="product_id" />
        <boolean property="notNull" value="true"/>
        <boolean property="autoIncrement" value="false"/>
        <string property="default" value="" />
        <boolean property="hasDefault" value="false"/>
        <integer property="length" value="0"/>
        <integer property="precision" value="0"/>
        <integer property="scale" value="0"/>
        <boolean property="sequence" value="false" />
        <boolean property="unsigned" value="false" />
        <null property="minLength"/>
        <null property="maxLength"/>'.
            ($is64bits ?
                '<integer property="minValue" value="-2147483648"/>' :
                '<double property="minValue" value="-2147483648"/>').
            '<integer property="maxValue" value="2147483647"/>
    </object>
    <object class="jDbColumn" key="promo">
        <string property="type" value="boolean" />
        <string property="name" value="promo" />
        <boolean property="notNull" value="true"/>
        <boolean property="autoIncrement" value="false"/>
        <string property="default" value=""/>
        <boolean property="hasDefault" value="false"/>
        <integer property="length" value="0"/>
        <integer property="precision" value="0"/>
        <integer property="scale" value="0"/>
        <boolean property="sequence" value="false" />
        <boolean property="unsigned" value="false" />
        <null property="minLength"/>
        <null property="maxLength"/>
        <integer property="minValue" value="0"/>
        <integer property="maxValue" value="1"/>
    </object>
</array>';

        $this->assertComplexIdenticalStr($table->getColumns(), $verif);
    }


}


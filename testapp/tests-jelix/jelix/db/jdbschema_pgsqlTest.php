<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor Julien Issler
* @copyright   2007-2025 Laurent Jouanneau
* @copyright   2010 Julien Issler
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

include_once (JELIX_LIB_PATH.'plugins/db/pgsql/pgsql.dbschema.php');

class jDbSchema_pgsqlTest extends \Jelix\UnitTests\UnitTestCase {

    public static function setUpBeforeClass() : void {
        self::initJelixConfig();
    }
    protected $countryColumns = array();
    protected $cityColumns = array();

    public function setUp() : void {
        if (!count($this->countryColumns)) {
            $is64bits = ( PHP_INT_SIZE*8 == 64 );
            $this->countryColumns ['country_id'] = '<object class="jDbColumn" key="country_id">
        <string property="type" value="integer" />
        <string property="name" value="country_id" />
        <boolean property="notNull" value="true"/>
        <boolean property="autoIncrement" value="true"/>
        <string property="default" value="" />
        <boolean property="hasDefault" value="true"/>
        <integer property="length" value="0"/>
        <integer property="precision" value="0"/>
        <integer property="scale" value="0"/>
        <string property="sequence" value="country_country_id_seq" />
        <boolean property="unsigned" value="false" />
        <null property="minLength"/>
        <null property="maxLength"/>'.
                ($is64bits ?
                    '<integer property="minValue" value="-2147483648"/>' :
                    '<double property="minValue" value="-2147483648"/>').
                '<integer property="maxValue" value="2147483647"/>
    </object>';
            $this->countryColumns ['name'] = '<object class="jDbColumn" key="name">
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
    </object>';


            $this->cityColumns ['city_id'] = '<object class="jDbColumn" key="city_id">
        <string property="type" value="integer" />
        <string property="name" value="city_id" />
        <boolean property="notNull" value="true"/>
        <boolean property="autoIncrement" value="true"/>
        <string property="default" value="" />
        <boolean property="hasDefault" value="true"/>
        <integer property="length" value="0"/>
        <integer property="precision" value="0"/>
        <integer property="scale" value="0"/>
        <string property="sequence" value="city_city_id_seq" />
        <boolean property="unsigned" value="false" />
        <null property="minLength"/>
        <null property="maxLength"/>'.
                ($is64bits ?
                    '<integer property="minValue" value="-2147483648"/>' :
                    '<double property="minValue" value="-2147483648"/>').
                '<integer property="maxValue" value="2147483647"/>
    </object>';
            $this->cityColumns ['name'] = '<object class="jDbColumn" key="name">
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
    </object>';
            $this->cityColumns ['postcode'] = '<object class="jDbColumn" key="postcode">
        <string property="type" value="integer" />
        <string property="name" value="postcode" />
        <boolean property="notNull" value="false"/>
        <boolean property="autoIncrement" value="false"/>
        <string property="default" value="0" />
        <boolean property="hasDefault" value="true"/>
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
    </object>';
            $this->cityColumns ['latitude'] = '<object class="jDbColumn" key="latitude">
        <string property="type" value="character" />
        <string property="name" value="latitude" />
        <boolean property="notNull" value="false"/>
        <boolean property="autoIncrement" value="false"/>
        <null property="default"/>
        <boolean property="hasDefault" value="false"/>
        <integer property="length" value="20"/>
        <integer property="precision" value="0"/>
        <integer property="scale" value="0"/>
        <boolean property="sequence" value="false" />
        <boolean property="unsigned" value="false" />
        <integer property="minLength" value="0"/>
        <integer property="maxLength" value="20"/>
        <null property="minValue"/>
        <null property="maxValue"/>
    </object>';
            $this->cityColumns ['longitude'] = '<object class="jDbColumn" key="longitude">
        <string property="type" value="character" />
        <string property="name" value="longitude" />
        <boolean property="notNull" value="false"/>
        <boolean property="autoIncrement" value="false"/>
        <null property="default"/>
        <boolean property="hasDefault" value="false"/>
        <integer property="length" value="20"/>
        <integer property="precision" value="0"/>
        <integer property="scale" value="0"/>
        <boolean property="sequence" value="false" />
        <boolean property="unsigned" value="false" />
        <integer property="minLength" value="0"/>
        <integer property="maxLength" value="20"/>
        <null property="minValue"/>
        <null property="maxValue"/>
    </object>';

            $this->cityColumns ['description'] = '<object class="jDbColumn" key="description">
        <string property="type" value="text" />
        <string property="name" value="description" />
        <boolean property="notNull" value="false"/>
        <boolean property="autoIncrement" value="false"/>
        <null property="default"/>
        <boolean property="hasDefault" value="false"/>
        <integer property="length" value="0"/>
        <integer property="precision" value="0"/>
        <integer property="scale" value="0"/>
        <boolean property="sequence" value="false" />
        <boolean property="unsigned" value="false" />
        <integer property="minLength" value="0"/>
        <integer property="maxLength" value="0"/>
        <null property="minValue"/>
        <null property="maxValue"/>
    </object>';

            $this->cityColumns ['name2'] = '<object class="jDbColumn" key="name">
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
    </object>';

            $this->cityColumns ['superdesc'] = '<object class="jDbColumn" key="superdesc">
        <string property="type" value="text" />
        <string property="name" value="superdesc" />
        <boolean property="notNull" value="false"/>
        <boolean property="autoIncrement" value="false"/>
        <null property="default"/>
        <boolean property="hasDefault" value="false"/>
        <integer property="length" value="0"/>
        <integer property="precision" value="0"/>
        <integer property="scale" value="0"/>
        <boolean property="sequence" value="false" />
        <boolean property="unsigned" value="false" />
        <integer property="minLength" value="0"/>
        <integer property="maxLength" value="0"/>
        <null property="minValue"/>
        <null property="maxValue"/>
    </object>';
        }
    }

    protected $countryNameKey = '<object class="jDbUniqueKey" key="country_name_key">
                    <string property="name" value="country_name_key" />
                    <array property="columns">
                        <string value="name"/>
                    </array>
                </object>';
    protected $city_name_idx = '<object class="jDbIndex" key="city_name_idx">
                    <string property="name" value="city_name_idx" />
                    <array property="columns">
                        <string value="name"/>
                    </array>
                </object>';
    protected $city_name_postcode_idx = '<object class="jDbIndex" key="city_name_postcode_idx">
                    <string property="name" value="city_name_postcode_idx" />
                    <array property="columns">
                        <string value="name"/>
                        <string value="postcode"/>
                    </array>
                </object>';
    protected $city_country_id_fkey = '<object class="jDbReference" key="city_country_id_fkey">
                    <string property="name" value="city_country_id_fkey" />
                    <array property="columns">
                        <string value="country_id"/>
                    </array>
                    <string property="fTable" value="country" />
                    <array property="fColumns">
                        <string value="country_id"/>
                    </array>
                </object>';
    protected $city_country_id_fkey2 = '<object class="jDbReference" key="city_country_id_fkey">
                    <string property="name" value="city_country_id_fkey" />
                    <array property="columns">
                        <string value="name"/>
                        <string value="country_id"/>
                    </array>
                    <string property="fTable" value="country" />
                    <array property="fColumns">
                        <string value="name"/>
                        <string value="country_id"/>
                    </array>
                </object>';

    function testTableList() {
        $db = jDb::getConnection('testapp_pgsql');
        $db->exec('DROP TABLE IF EXISTS test_prod');
        $db->exec('DROP TABLE IF EXISTS city');
        $db->exec('DROP TABLE IF EXISTS bigcity');
        $db->exec('DROP TABLE IF EXISTS country');
        $schema = $db->schema();

        $goodList = array(
            'generated_column_test',
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
            'products_with_identity',
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
        <boolean property="notNull" value="false"/>
        <boolean property="autoIncrement" value="false"/>
        <null property="default" />
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
        <string property="default" value="" />
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

        $verif = '<object class="jDbPrimaryKey">
                <string property="name" value="product_test_pkey" />
                <array property="columns">
                    <string value="id"/>
                </array>
         </object>';
        $this->assertComplexIdenticalStr($table->getPrimaryKey(), $verif);
        $this->assertEquals(array(), $table->getIndexes());
        $this->assertEquals(array(), $table->getUniqueKeys());
        $this->assertEquals(array(), $table->getReferences());
        $this->assertTrue($table->getColumn('id')->isAutoincrementedColumn());
        $this->assertFalse($table->getColumn('name')->isAutoincrementedColumn());
        $this->assertFalse($table->getColumn('name')->generated);
    }

    function testTableHavingIdentity() {
        $db = jDb::getConnection('testapp_pgsql');
        $schema = $db->schema();

        $table = $schema->getTable('products_with_identity');

        $this->assertNotNull($table);

        $this->assertEquals('products_with_identity', $table->getName());

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
        <boolean property="notNull" value="false"/>
        <boolean property="autoIncrement" value="false"/>
        <string property="default" value="0" />
        <boolean property="hasDefault" value="true"/>
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
    <object class="jDbColumn" key="promo">
        <string property="type" value="boolean" />
        <string property="name" value="promo" />
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
        <integer property="minValue" value="0"/>
        <integer property="maxValue" value="1"/>
    </object>
</array>';

        $this->assertComplexIdenticalStr($table->getColumns(), $verif);

        $verif = '<object class="jDbPrimaryKey">
                <string property="name" value="products_with_identity_pkey" />
                <array property="columns">
                    <string value="id"/>
                </array>
         </object>';
        $this->assertComplexIdenticalStr($table->getPrimaryKey(), $verif);
        $this->assertEquals(array(), $table->getIndexes());
        $this->assertEquals(array(), $table->getUniqueKeys());
        $this->assertEquals(array(), $table->getReferences());
        $this->assertTrue($table->getColumn('id')->isAutoincrementedColumn());
        $this->assertFalse($table->getColumn('name')->isAutoincrementedColumn());
        $this->assertFalse($table->getColumn('name')->generated);
    }

    function testGeneratedColumn()
    {
        $db = jDb::getConnection('testapp_pgsql');
        $schema = $db->schema();

        $table = $schema->getTable('generated_column_test');

        $this->assertNotNull($table);

        $this->assertEquals('generated_column_test', $table->getName());

        $pk = $table->getPrimaryKey();
        $this->assertEquals(array('id'), $pk->columns);
        $this->assertTrue($table->getColumn('total')->generated);

        // insert test value
        $stmt = $db->prepare('INSERT INTO generated_column_test (description, amount, change) VALUES(:d, :a, :c)');

        $stmt->bindValue('d','candy');
        $stmt->bindValue('a', 2);
        $stmt->bindValue('c',1.02);

        $stmt->execute();

        $rs = $db->query('SELECT id, description, amount, change, total FROM generated_column_test');

        $record = $rs->fetch();

        $this->assertEquals($record->id, 1);
        $this->assertEquals($record->description, 'candy');
        $this->assertEquals($record->amount, 2);
        $this->assertEquals($record->change, 1.02);
        $this->assertEquals($record->total, 2.04);
    }

    function testCreateTable()
    {
        $db = jDb::getConnection('testapp_pgsql');
        $db->exec('DROP TABLE IF EXISTS test_prod');
        $db->exec('DROP TABLE IF EXISTS city');
        $db->exec('DROP TABLE IF EXISTS bigcity');
        $db->exec('DROP TABLE IF EXISTS country');

        $schema = $db->schema();


        $columns = array();
        $col = new jDbColumn('id', 'int', 0, false, null, true);
        $col->autoIncrement = true;
        $columns[] = $col;
        $columns[] = new jDbColumn('name', 'string', 50);
        $columns[] = new jDbColumn('price', 'double',0,  true, null, false);
        $columns[] = new jDbColumn('promo', 'boolean', 0, true, true);
        $columns[] = new jDbColumn('product_id', 'int', 0, false, null, true);

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
        <boolean property="notNull" value="false"/>
        <boolean property="autoIncrement" value="false"/>
        <null property="default" />
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
        <boolean property="notNull" value="false"/>
        <boolean property="autoIncrement" value="false"/>
        <null property="default" />
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
        <boolean property="notNull" value="false"/>
        <boolean property="autoIncrement" value="false"/>
        <boolean property="default" value="true"/>
        <boolean property="hasDefault" value="true"/>
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

    public function testGetTablesAndConstraintsIndexes() {
        $db = jDb::getConnection('testapp_pgsql');
        $db->exec('DROP TABLE IF EXISTS city');
        $db->exec('DROP TABLE IF EXISTS bigcity');
        $db->exec('DROP TABLE IF EXISTS country');

        $db->exec('CREATE TABLE country (
    country_id serial PRIMARY KEY,
    name varchar(50) not null,
    UNIQUE(name)
)');
        $db->exec('CREATE TABLE city (
    city_id serial PRIMARY KEY,
    country_id integer NOT NULL,
    name  varchar(50) not null,
    postcode integer DEFAULT 0,
    latitude varchar(20),
    longitude varchar(20),
    CONSTRAINT coordinates UNIQUE(latitude, longitude),
    FOREIGN KEY (country_id) REFERENCES country (country_id))');

        $db->exec('CREATE INDEX city_name_idx ON city (name)');
        $db->exec('CREATE UNIQUE INDEX city_name_postcode_idx ON city (name, postcode)');

        $schema = new pgsqlDbSchema($db);
        $country = $schema->getTable('country');
        $city = $schema->getTable('city');
        $this->assertEquals('country', $country->getName());
        $this->assertEquals('city', $city->getName());


        $pk = $country->getPrimaryKey();
        $this->assertEquals(array('country_id'), $pk->columns);
        $pk = $city->getPrimaryKey();
        $this->assertEquals(array('city_id'), $pk->columns);

        $this->assertTrue($country->getColumn('country_id')->isAutoincrementedColumn());
        $this->assertFalse($country->getColumn('name')->isAutoincrementedColumn());
        $this->assertTrue($city->getColumn('city_id')->isAutoincrementedColumn());
        $this->assertFalse($city->getColumn('country_id')->isAutoincrementedColumn());
        $this->assertFalse($city->getColumn('name')->isAutoincrementedColumn());

        $columns='<array>'.$this->countryColumns ['country_id'].
            $this->countryColumns ['name']. '</array>';
        $this->assertComplexIdenticalStr($country->getColumns(), $columns);

        $columns='<array>'.$this->cityColumns ['city_id'].
            $this->cityColumns ['name'].
            $this->cityColumns ['postcode'].
            $this->cityColumns ['latitude'].
            $this->cityColumns ['longitude'].'</array>';
        $this->assertComplexIdenticalStr($city->getColumns(), $columns);

        $this->assertEquals(array(), $country->getIndexes());

        $this->assertComplexIdenticalStr($country->getUniqueKeys(),
            '<array>'.$this->countryNameKey.'</array>'
        );
        $this->assertEquals(array(), $country->getReferences());

        $this->assertComplexIdenticalStr($city->getIndexes(),
            '<array>'.$this->city_name_idx.
            $this->city_name_postcode_idx.'</array>'
        );
        $this->assertComplexIdenticalStr($city->getUniqueKeys(),
            '<array>
            </array>'
        );
        $this->assertComplexIdenticalStr($city->getReferences(),
            '<array>'.$this->city_country_id_fkey.'</array>'
        );

    }

    /**
     * @depends testGetTablesAndConstraintsIndexes
     */
    public function testRenameTable() {
        $db = jDb::getConnection('testapp_pgsql');
        $schema = new pgsqlDbSchema($db);

        $schema->renameTable('city', 'bigcity');

        $goodList = array(
            'country',
            'bigcity',
            'generated_column_test',
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
            'products_with_identity',
            'test_prod',
            'testkvdb',
        );

        $list = $schema->getTables();
        $tables = array();
        foreach($list as $table) {
            $tables[] = $table->getName();
        }

        sort($goodList);
        sort($tables);
        $this->assertEquals($goodList, $tables);
        $city = $schema->getTable('bigcity');

        $pk = $city->getPrimaryKey();
        $this->assertEquals(array('city_id'), $pk->columns);

        $columns='<array>'.$this->cityColumns ['city_id'].
            $this->cityColumns ['name'].
            $this->cityColumns ['postcode'].
            $this->cityColumns ['latitude'].
            $this->cityColumns ['longitude'].'</array>';
        $this->assertComplexIdenticalStr($city->getColumns(), $columns);

        $this->assertComplexIdenticalStr($city->getIndexes(),
            '<array>'.$this->city_name_idx.
            $this->city_name_postcode_idx.'</array>'
        );
        $this->assertComplexIdenticalStr($city->getUniqueKeys(),
            '<array>
            </array>'
        );
        $this->assertComplexIdenticalStr($city->getReferences(),
            '<array>'.$this->city_country_id_fkey.'</array>'
        );
    }

    /**
     * @depends testRenameTable
     */
    public function testAddColumn() {
        $db = jDb::getConnection('testapp_pgsql');
        $schema = new pgsqlDbSchema($db);
        $city = $schema->getTable('bigcity');
        $col = new jDbColumn('description', 'text');
        $city->addColumn($col);

        $schema = new pgsqlDbSchema($db);
        $city = $schema->getTable('bigcity');

        $pk = $city->getPrimaryKey();
        $this->assertEquals(array('city_id'), $pk->columns);

        $columns='<array>'.$this->cityColumns ['city_id'].
            $this->cityColumns ['name'].
            $this->cityColumns ['postcode'].
            $this->cityColumns ['latitude'].
            $this->cityColumns ['longitude'].
            $this->cityColumns ['description'].
            '</array>';
        $this->assertComplexIdenticalStr($city->getColumns(), $columns);

        $this->assertComplexIdenticalStr($city->getIndexes(),
            '<array>'.$this->city_name_idx.
            $this->city_name_postcode_idx.'</array>'
        );
        $this->assertComplexIdenticalStr($city->getUniqueKeys(),
            '<array>
            </array>'
        );
        $this->assertComplexIdenticalStr($city->getReferences(),
            '<array>'.$this->city_country_id_fkey.'</array>'
        );
    }

    /**
     * @depends testAddColumn
     */
    public function testAlterColumn() {
        $db = jDb::getConnection('testapp_pgsql');
        $schema = new pgsqlDbSchema($db);
        $city = $schema->getTable('bigcity');

        $name = $city->getColumn('name', true);
        $name->length = 150;

        $desc = $city->getColumn('description', true);
        $desc->name = 'superdesc';

        $city->alterColumn($name);
        $city->alterColumn($desc, 'description');

        $schema = new pgsqlDbSchema($db);
        $city = $schema->getTable('bigcity');
        $pk = $city->getPrimaryKey();
        $this->assertEquals(array('city_id'), $pk->columns);

        $this->assertNull($city->getColumn('description'));

        $columns='<array>'.$this->cityColumns ['city_id'].
            $this->cityColumns ['name2'].
            $this->cityColumns ['postcode'].
            $this->cityColumns ['latitude'].
            $this->cityColumns ['longitude'].
            $this->cityColumns ['superdesc'].
            '</array>';
        $this->assertComplexIdenticalStr($city->getColumns(), $columns);

        $this->assertComplexIdenticalStr($city->getIndexes(),
            '<array>'.$this->city_name_idx.
            $this->city_name_postcode_idx.'</array>'
        );
        $this->assertComplexIdenticalStr($city->getUniqueKeys(),
            '<array>
            </array>'
        );
        $this->assertComplexIdenticalStr($city->getReferences(),
            '<array>'.$this->city_country_id_fkey.'</array>'
        );
    }

    /**
     * @depends testAlterColumn
     */
    public function testDropColumn() {
        $db = jDb::getConnection('testapp_pgsql');
        $schema = new pgsqlDbSchema($db);
        $city = $schema->getTable('bigcity');
        $city->dropColumn('superdesc');

        $schema = new pgsqlDbSchema($db);
        $city = $schema->getTable('bigcity');
        $pk = $city->getPrimaryKey();
        $this->assertEquals(array('city_id'), $pk->columns);

        $this->assertNull($city->getColumn('superdesc'));
        $columns='<array>'.$this->cityColumns ['city_id'].
            $this->cityColumns ['name2'].
            $this->cityColumns ['postcode'].
            $this->cityColumns ['latitude'].
            $this->cityColumns ['longitude'].
        '</array>';
        $this->assertComplexIdenticalStr($city->getColumns(), $columns);

        $this->assertComplexIdenticalStr($city->getIndexes(),
            '<array>'.$this->city_name_idx.
            $this->city_name_postcode_idx.'</array>'
        );
        $this->assertComplexIdenticalStr($city->getUniqueKeys(),
            '<array>
            </array>'
        );
        $this->assertComplexIdenticalStr($city->getReferences(),
            '<array>'.$this->city_country_id_fkey.'</array>'
        );
    }

    /**
     * @depends testDropColumn
     */
    public function testDropTable() {
        $db = jDb::getConnection('testapp_pgsql');
        $schema = new pgsqlDbSchema($db);

        $schema->dropTable('bigcity');
        $schema->dropTable($schema->getTable('country'));
        $goodList = array(
            'generated_column_test',
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
            'products_with_identity',
            'test_prod',
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
        $this->assertNull($schema->getTable('bigcity'));
        $this->assertNull($schema->getTable('country'));
    }


    /**
     * @depends testDropTable
     */
    public function testCreateTableAndAddDropPrimaryKey() {
        $db = jDb::getConnection('testapp_pgsql');
        $schema = new pgsqlDbSchema($db);

        $columns = array();
        $columns[] = new jDbColumn('country_id', 'serial');
        $columns[] = new jDbColumn('name', 'varchar', 50, false, null, true);

        $country = $schema->createTable('country', $columns, 'country_id');

        $pk = $country->getPrimaryKey();
        $this->assertEquals(array('country_id'), $pk->columns);

        $country->dropPrimaryKey();
        $this->assertFalse($country->getPrimaryKey());

        $schema = new pgsqlDbSchema($db);
        $country = $schema->getTable('country');
        $this->assertFalse($country->getPrimaryKey());

        $schema = new pgsqlDbSchema($db);
        $country = $schema->getTable('country');
        $country->setPrimaryKey($pk);
        $pk = $country->getPrimaryKey();
        $this->assertEquals(array('country_id'), $pk->columns);

        $schema = new pgsqlDbSchema($db);
        $country = $schema->getTable('country');
        $pk = $country->getPrimaryKey();
        $this->assertEquals(array('country_id'), $pk->columns);
    }

    /**
     * @depends testCreateTableAndAddDropPrimaryKey
     */
    public function testCreateTables() {
        $db = jDb::getConnection('testapp_pgsql');
        $db->exec('DROP TABLE IF EXISTS city');
        $db->exec('DROP TABLE IF EXISTS bigcity');
        $db->exec('DROP TABLE IF EXISTS country');
        $schema = new pgsqlDbSchema($db);

        $columns = array();
        $columns[] = new jDbColumn('country_id', 'serial');
        $columns[] = new jDbColumn('name', 'varchar', 50, false, null, true);
        $country = $schema->createTable('country', $columns, 'country_id');

        $columns = array();
        $columns[] = new jDbColumn('city_id', 'serial');
        $columns[] = new jDbColumn('country_id', 'integer', 0, false, null, true);
        $columns[] = new jDbColumn('name', 'varchar', 50, false, null, true);
        $columns[] = new jDbColumn('postcode', 'integer', 0, true, 0);
        $columns[] = new jDbColumn('latitude', 'varchar', 20);
        $columns[] = new jDbColumn('longitude', 'varchar', 20);
        $city = $schema->createTable('city', $columns, 'city_id');


        $pk = $country->getPrimaryKey();
        $this->assertEquals(array('country_id'), $pk->columns);
        $pk = $city->getPrimaryKey();
        $this->assertEquals(array('city_id'), $pk->columns);

        $columns='<array>'.$this->countryColumns ['country_id'].
            $this->countryColumns ['name']. '</array>';
        $this->assertComplexIdenticalStr($country->getColumns(), $columns);

        $columns='<array>'.$this->cityColumns ['city_id'].
            $this->cityColumns ['name'].
            $this->cityColumns ['postcode'].
            $this->cityColumns ['latitude'].
            $this->cityColumns ['longitude'].'</array>';
        $this->assertComplexIdenticalStr($city->getColumns(), $columns);

        $this->assertEquals(array(), $country->getIndexes());
        $this->assertEquals(array(), $country->getUniqueKeys());
        $this->assertEquals(array(), $country->getReferences());


        $this->assertEquals(array(), $city->getIndexes());
        $this->assertEquals(array(), $city->getUniqueKeys());
        $this->assertEquals(array(), $city->getReferences());
    }

    /**
     * @depends testCreateTables
     */
    public function testAddIndex() {
        $db = jDb::getConnection('testapp_pgsql');
        $schema = new pgsqlDbSchema($db);
        $city = $schema->getTable('city');
        $index = new jDbIndex('city_name_idx', '', array('name'));
        $city->addIndex($index);
        $index = new jDbIndex('city_name_postcode_idx', '', array('name', 'postcode'));
        $index->isUnique = true;
        $city->addIndex($index);

        $this->assertComplexIdenticalStr($city->getIndexes(),
            '<array>'.$this->city_name_idx.
            $this->city_name_postcode_idx.'</array>'
        );

        $schema = new pgsqlDbSchema($db); // reload all
        $city = $schema->getTable('city');
        $this->assertComplexIdenticalStr($city->getIndexes(),
            '<array>'.$this->city_name_idx.
            $this->city_name_postcode_idx.'</array>'
        );
    }

    /**
     * @depends testAddIndex
     */
    public function testDropIndex() {
        $db = jDb::getConnection('testapp_pgsql');
        $schema = new pgsqlDbSchema($db); // reload all
        $city = $schema->getTable('city');
        $this->assertComplexIdenticalStr($city->getIndexes(),
            '<array>'.$this->city_name_idx.
            $this->city_name_postcode_idx.'</array>'
        );

        $city->dropIndex('city_name_idx');
        $this->assertNull($city->getIndex('city_name_idx'));
        $this->assertComplexIdenticalStr($city->getIndexes(),
            '<array>'.$this->city_name_postcode_idx.'</array>'
        );

        $schema = new pgsqlDbSchema($db); // reload all
        $city = $schema->getTable('city');
        $this->assertNull($city->getIndex('city_name_idx'));
        $this->assertComplexIdenticalStr($city->getIndexes(),
            '<array>'.$this->city_name_postcode_idx.'</array>'
        );
    }

    /**
     * @depends testDropIndex
     */
    public function testAddUniqueKey() {
        $db = jDb::getConnection('testapp_pgsql');
        $schema = new pgsqlDbSchema($db); // reload all
        $country = $schema->getTable('country');

        $key = new jDbUniqueKey('country_name_key', array('name'));
        $country->addUniqueKey($key);
        $this->assertComplexIdenticalStr($country->getUniqueKeys(),
            '<array>'.$this->countryNameKey.'</array>'
        );

        $schema = new pgsqlDbSchema($db); // reload all
        $country = $schema->getTable('country');
        $this->assertComplexIdenticalStr($country->getUniqueKeys(),
            '<array>'.$this->countryNameKey.'</array>'
        );
    }

    /**
     * @depends testAddUniqueKey
     */
    public function testDropUniqueKey() {
        $db = jDb::getConnection('testapp_pgsql');
        $schema = new pgsqlDbSchema($db); // reload all
        $country = $schema->getTable('country');
        $country->dropUniqueKey('country_name_key');

        $this->assertEquals(array(), $country->getUniqueKeys());
        $schema = new pgsqlDbSchema($db); // reload all
        $country = $schema->getTable('country');
        $this->assertEquals(array(), $country->getUniqueKeys());
    }


    /**
     * @depends testDropUniqueKey
     */
    public function testAddReference() {
        $db = jDb::getConnection('testapp_pgsql');
        $schema = new pgsqlDbSchema($db); // reload all
        $city = $schema->getTable('city');

        $key = new jDbReference('city_country_id_fkey', array('country_id'),
            'country', array('country_id'));
        $city->addReference($key);
        $this->assertComplexIdenticalStr($city->getReferences(),
            '<array>'.$this->city_country_id_fkey.'</array>'
        );

        $schema = new pgsqlDbSchema($db); // reload all
        $city = $schema->getTable('city');
        $this->assertComplexIdenticalStr($city->getReferences(),
            '<array>'.$this->city_country_id_fkey.'</array>'
        );
    }

    /**
     * @depends testAddReference
     */
    public function testDropReference() {
        $db = jDb::getConnection('testapp_pgsql');
        $schema = new pgsqlDbSchema($db); // reload all
        $city = $schema->getTable('city');
        $city->dropReference('city_country_id_fkey');

        $this->assertEquals(array(), $city->getReferences());
        $schema = new pgsqlDbSchema($db); // reload all
        $city = $schema->getTable('city');
        $this->assertEquals(array(), $city->getReferences());
    }

    public function testEqualColumn()
    {
        $col1 = new jDbColumn(
            'id',
            'integer',
            0,
            true,
            '',
            true
        );
        $col1->nativeType = 'integer';
        $col1->autoIncrement = true;
        $col1->sequence = 'community_users_id_seq';

        $col2 = new jDbColumn(
            'id',
            'autoincrement',
            0,
            true,
            '',
            true
        );
        $col2->nativeType = 'serial';
        $col2->autoIncrement = true;
        $col2->sequence = false;

        $this->assertTrue($col1->isEqualTo($col2));
    }
}


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

include_once (JELIX_LIB_PATH.'plugins/db/sqlite3/sqlite3.dbschema.php');

class jDbSchema_sqlite3Test extends \Jelix\UnitTests\UnitTestCase {

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
        <boolean property="sequence" value="false" />
        <boolean property="unsigned" value="false" />
        <null property="minLength"/>
        <null property="maxLength"/>'.
                ($is64bits ?
                    '<integer property="minValue" value="-2147483648"/>' :
                    '<double property="minValue" value="-2147483648"/>').
                '<integer property="maxValue" value="2147483647"/>
    </object>';
            $this->countryColumns ['name'] = '<object class="jDbColumn" key="name">
        <string property="type" value="varchar" />
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
        <boolean property="sequence" value="false" />
        <boolean property="unsigned" value="false" />
        <null property="minLength"/>
        <null property="maxLength"/>'.
                ($is64bits ?
                    '<integer property="minValue" value="-2147483648"/>' :
                    '<double property="minValue" value="-2147483648"/>').
                '<integer property="maxValue" value="2147483647"/>
    </object>';
            $this->cityColumns ['name'] = '<object class="jDbColumn" key="name">
        <string property="type" value="varchar" />
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
        <string property="type" value="varchar" />
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
        <string property="type" value="varchar" />
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
        <null property="default" />
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
        <string property="type" value="varchar" />
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
        <null property="default" />
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

    protected $countryNameKey = '<object class="jDbUniqueKey" key="country_name_unique">
                    <string property="name" value="country_name_unique" />
                    <array property="columns">
                        <string value="name"/>
                    </array>
                </object>';
    protected $countryNameKey2 = '<object class="jDbUniqueKey" key="country_name_key">
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
    protected $city_coordinates_uniq = '<object class="jDbUniqueKey" key="coordinates">
                    <string property="name" value="coordinates" />
                    <array property="columns">
                        <string value="latitude"/>
                        <string value="longitude"/>
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
    protected $bigcity_country_id_fkey = '<object class="jDbReference" key="bigcity_country_id_fkey">
                    <string property="name" value="bigcity_country_id_fkey" />
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
        $db = jDb::getConnection('testapp_sqlite3');
        $db->exec('DROP TABLE IF EXISTS test_prod');
        $db->exec('DROP TABLE IF EXISTS city');
        $db->exec('DROP TABLE IF EXISTS bigcity');
        $db->exec('DROP TABLE IF EXISTS country');
        $schema = $db->schema();

        $goodList = array(
            'jsessions',
            'products',
            'product_test',
            'jacl2_group',
            'jacl2_rights',
            'jacl2_subject',
            'jacl2_subject_group',
            'jacl2_user_group'
        );

        $list = $schema->getTables();
        $tables = array();
        foreach($list as $table) {
            if ($table->getName() == 'sqlite_sequence') {
                continue;
            }
            $tables[] = $table->getName();
        }

        sort($goodList);
        sort($tables);
        $this->assertEquals($goodList, $tables);
    }

    function testTable() {
        $db = jDb::getConnection('testapp_sqlite3');
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
        <string property="type" value="varchar" />
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
        <string property="type" value="float" />
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
        <string property="type" value="datetime" />
        <string property="name" value="create_date" />
        <boolean property="notNull" value="false"/>
        <boolean property="autoIncrement" value="false"/>
        <null property="default" />
        <boolean property="hasDefault" value="true"/>
        <integer property="length" value="0"/>
        <integer property="precision" value="0"/>
        <integer property="scale" value="0"/>
        <boolean property="sequence" value="false" />
        <boolean property="unsigned" value="false" />
        <integer property="minLength" value="19"/>
        <integer property="maxLength" value="19"/>
        <null property="minValue"/>
        <null property="maxValue"/>
    </object>
    <object class="jDbColumn" key="promo">
        <string property="type" value="bool" />
        <string property="name" value="promo" />
        <boolean property="notNull" value="true"/>
        <boolean property="autoIncrement" value="false"/>
        <boolean property="default" value="false"/>
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
        $this->assertTrue($table->getColumn('id')->isAutoincrementedColumn());
        $this->assertFalse($table->getColumn('name')->isAutoincrementedColumn());
    }


    function testCreateTable()
    {
        $db = jDb::getConnection('testapp_sqlite3');
        $db->exec('DROP TABLE IF EXISTS test_prod');
        $db->exec('DROP TABLE IF EXISTS city');
        $db->exec('DROP TABLE IF EXISTS bigcity');
        $db->exec('DROP TABLE IF EXISTS country');
        $schema = $db->schema();


        $columns = array();
        $col = new jDbColumn('id', 'integer', 0, false, null, true);
        $col->autoIncrement = true;
        $columns[] = $col;
        $columns[] = new jDbColumn('name', 'string', 50);
        $columns[] = new jDbColumn('price', 'double', 0, true, null, false);
        $columns[] = new jDbColumn('promo', 'boolean', 0, true, true);
        $columns[] = new jDbColumn('product_id', 'int', 0, false, null, true);

        $schema->createTable('test_prod', $columns, 'id');

        $table = new sqlite3DbTable('test_prod', $schema);

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
        <string property="type" value="varchar" />
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
        <string property="type" value="bool" />
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
        $db = jDb::getConnection('testapp_sqlite3');
        $db->exec('DROP TABLE IF EXISTS city');
        $db->exec('DROP TABLE IF EXISTS bigcity');
        $db->exec('DROP TABLE IF EXISTS country');

        $db->exec('CREATE TABLE country (
    country_id INTEGER  PRIMARY KEY AUTOINCREMENT,
    name varchar(50) not null,
    UNIQUE(name)
)');
        $db->exec('CREATE TABLE city (
    city_id INTEGER  PRIMARY KEY AUTOINCREMENT,
    country_id integer NOT NULL,
    name  varchar(50) not null,
    postcode integer DEFAULT 0,
    latitude varchar(20),
    longitude varchar(20),
    CONSTRAINT coordinates UNIQUE(latitude, longitude),
    FOREIGN KEY (country_id) REFERENCES country (country_id))');

        $db->exec('CREATE INDEX city_name_idx ON city (name)');
        $db->exec('CREATE UNIQUE INDEX city_name_postcode_idx ON city (name, postcode)');

        $schema = new sqlite3DbSchema($db);
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
            '<array>'.$this->city_name_idx.$this->city_name_postcode_idx.'</array>'
        );

        $this->assertComplexIdenticalStr($city->getUniqueKeys(),
            '<array>'.$this->city_coordinates_uniq.'</array>'
        );
        $this->assertComplexIdenticalStr($city->getReferences(),
            '<array>'.$this->city_country_id_fkey.'</array>'
        );

    }

    /**
     * @depends testGetTablesAndConstraintsIndexes
     */
    public function testRenameTable() {
        $db = jDb::getConnection('testapp_sqlite3');
        $schema = new sqlite3DbSchema($db);

        $schema->renameTable('city', 'bigcity');

        $goodList = array(
            'country',
            'bigcity',
            'jsessions',
            'product_test',
            'products',
            'sqlite_sequence',
            'test_prod',
            'jacl2_group',
            'jacl2_rights',
            'jacl2_subject',
            'jacl2_subject_group',
            'jacl2_user_group'
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
            '<array>'.$this->city_name_idx.$this->city_name_postcode_idx.'</array>'
        );
        $this->assertComplexIdenticalStr($city->getUniqueKeys(),
            '<array>'.$this->city_coordinates_uniq.'</array>'
        );

        $this->assertComplexIdenticalStr($city->getReferences(),
            '<array>'.$this->bigcity_country_id_fkey.'</array>'
        );
    }

    /**
     * @depends testRenameTable
     */
    public function testAddColumn() {
        $db = jDb::getConnection('testapp_sqlite3');
        $schema = new sqlite3DbSchema($db);
        $city = $schema->getTable('bigcity');
        $col = new jDbColumn('description', 'text');
        $city->addColumn($col);

        $schema = new sqlite3DbSchema($db); // reload
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
            '<array>'.$this->city_name_idx.$this->city_name_postcode_idx.'</array>'
        );
        $this->assertComplexIdenticalStr($city->getUniqueKeys(),
            '<array>'.$this->city_coordinates_uniq.'</array>'
        );
        $this->assertComplexIdenticalStr($city->getReferences(),
            '<array>'.$this->bigcity_country_id_fkey.'</array>'
        );

    }

    /**
     * @depends testAddColumn
     */
    public function testAlterColumn() {
        $db = jDb::getConnection('testapp_sqlite3');
        $schema = new sqlite3DbSchema($db);
        $city = $schema->getTable('bigcity');

        $name = $city->getColumn('name', true);
        $name->length = 150;
        $city->alterColumn($name);

        $desc = $city->getColumn('description', true);
        $desc->name = 'superdesc';
        $city->alterColumn($desc, 'description');

        $schema = new sqlite3DbSchema($db);
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
            '<array>'.$this->city_name_idx.$this->city_name_postcode_idx.'</array>'
        );
        $this->assertComplexIdenticalStr($city->getUniqueKeys(),
            '<array>'.$this->city_coordinates_uniq.'</array>'
        );
        $this->assertComplexIdenticalStr($city->getReferences(),
            '<array>'.$this->bigcity_country_id_fkey.'</array>'
        );
    }

    /**
     * @depends testAlterColumn
     */
    public function testDropColumn() {
        $db = jDb::getConnection('testapp_sqlite3');
        $schema = new sqlite3DbSchema($db);
        $city = $schema->getTable('bigcity');
        $city->dropColumn('superdesc');

        $schema = new sqlite3DbSchema($db);
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
            '<array>'.$this->city_name_idx.$this->city_name_postcode_idx.'</array>'
        );
        $this->assertComplexIdenticalStr($city->getUniqueKeys(),
            '<array>'.$this->city_coordinates_uniq.'</array>'
        );
        $this->assertComplexIdenticalStr($city->getReferences(),
            '<array>'.$this->bigcity_country_id_fkey.'</array>'
        );

    }

    /**
     * @depends testDropColumn
     */
    public function testDropTable() {
        $db = jDb::getConnection('testapp_sqlite3');
        $schema = new sqlite3DbSchema($db);

        $schema->dropTable('bigcity');
        $schema->dropTable($schema->getTable('country'));
        $goodList = array(
            'product_test',
            'products',
            'sqlite_sequence',
            'test_prod',
            'jsessions',
            'jacl2_group',
            'jacl2_rights',
            'jacl2_subject',
            'jacl2_subject_group',
            'jacl2_user_group'
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
        $db = jDb::getConnection('testapp_sqlite3');
        $db->exec("DROP TABLE IF EXISTS country");
        $schema = new sqlite3DbSchema($db);

        $columns = array();
        // don't set autoincrement as it is not allowed on non primary/unique key
        // and then it will fail when we will remove the PK constraint
        $columns[] = new jDbColumn('country_id', 'integer');
        $columns[] = new jDbColumn('name', 'varchar', 50, false, null, true);

        $country = $schema->createTable('country', $columns, 'country_id');
        $pk = $country->getPrimaryKey();
        $this->assertEquals(array('country_id'), $pk->columns);

        $country->dropPrimaryKey();
        $this->assertFalse($country->getPrimaryKey());

        $schema = new sqlite3DbSchema($db);
        $country = $schema->getTable('country');
        $this->assertFalse($country->getPrimaryKey());

        $schema = new sqlite3DbSchema($db);
        $country = $schema->getTable('country');
        $country->setPrimaryKey($pk);
        $pk = $country->getPrimaryKey();
        $this->assertEquals(array('country_id'), $pk->columns);

        $schema = new sqlite3DbSchema($db);
        $country = $schema->getTable('country');
        $pk = $country->getPrimaryKey();
        $this->assertEquals(array('country_id'), $pk->columns);
    }

    /**
     * @depends testCreateTableAndAddDropPrimaryKey
     */
    public function testCreateTables() {

        $db = jDb::getConnection('testapp_sqlite3');
        $db->exec('DROP TABLE IF EXISTS city');
        $db->exec('DROP TABLE IF EXISTS bigcity');
        $db->exec('DROP TABLE IF EXISTS country');
        $schema = new sqlite3DbSchema($db);

        $columns = array();
        $columns[] = new jDbColumn('country_id', 'serial');
        $columns[] = new jDbColumn('name', 'varchar', 50, false, null, true);
        $country = $schema->createTable('country', $columns, 'country_id');

        $columns = array();
        $columns[] = new jDbColumn('city_id', 'serial');
        $columns[] = new jDbColumn('country_id', 'integer', false, null, true);
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
        $db = jDb::getConnection('testapp_sqlite3');
        $schema = new sqlite3DbSchema($db);
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

        $schema = new sqlite3DbSchema($db); // reload all
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
        $db = jDb::getConnection('testapp_sqlite3');
        $schema = new sqlite3DbSchema($db); // reload all
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

        $schema = new sqlite3DbSchema($db); // reload all
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
        $db = jDb::getConnection('testapp_sqlite3');
        $schema = new sqlite3DbSchema($db); // reload all
        $country = $schema->getTable('country');

        $key = new jDbUniqueKey('country_name_key', array('name'));
        $country->addUniqueKey($key);
        $this->assertComplexIdenticalStr($country->getUniqueKeys(),
            '<array>'.$this->countryNameKey2.'</array>'
        );

        $schema = new sqlite3DbSchema($db); // reload all
        $country = $schema->getTable('country');
        $this->assertComplexIdenticalStr($country->getUniqueKeys(),
            '<array>'.$this->countryNameKey2.'</array>'
        );
    }

    /**
     * @depends testAddUniqueKey
     */
    public function testDropUniqueKey() {
        $db = jDb::getConnection('testapp_sqlite3');
        $schema = new sqlite3DbSchema($db); // reload all
        $country = $schema->getTable('country');
        $this->assertComplexIdenticalStr($country->getUniqueKeys(),
            '<array>'.$this->countryNameKey2.'</array>'
        );

        $country->dropUniqueKey('country_name_key');

        $this->assertEquals(array(), $country->getUniqueKeys());

        $schema = new sqlite3DbSchema($db); // reload all
        $country = $schema->getTable('country');
        $this->assertEquals(array(), $country->getUniqueKeys());
    }


    /**
     * @depends testDropUniqueKey
     */
    public function testAddReference() {
        $db = jDb::getConnection('testapp_sqlite3');
        $schema = new sqlite3DbSchema($db); // reload all
        $city = $schema->getTable('city');

        $key = new jDbReference('city_country_id_fkey', array('country_id'),
            'country', array('country_id'));
        $city->addReference($key);
        $this->assertComplexIdenticalStr($city->getReferences(),
            '<array>'.$this->city_country_id_fkey.'</array>'
        );

        $schema = new sqlite3DbSchema($db); // reload all
        $city = $schema->getTable('city');
        $this->assertComplexIdenticalStr($city->getReferences(),
            '<array>'.$this->city_country_id_fkey.'</array>'
        );
    }

    /**
     * @depends testAddReference
     */
    public function testDropReference() {
        $db = jDb::getConnection('testapp_sqlite3');
        $schema = new sqlite3DbSchema($db); // reload all
        $city = $schema->getTable('city');
        $city->dropReference('city_country_id_fkey');

        $this->assertEquals(array(), $city->getReferences());
        $schema = new sqlite3DbSchema($db); // reload all
        $city = $schema->getTable('city');
        $this->assertEquals(array(), $city->getReferences());
    }

}



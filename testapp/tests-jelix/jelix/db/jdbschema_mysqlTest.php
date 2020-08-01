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

include_once (JELIX_LIB_PATH.'plugins/db/mysqli/mysqli.dbschema.php');

class jDbSchema_MysqlTest extends jUnitTestCase {

    public static function setUpBeforeClass() {
        self::initJelixConfig();
    }

    protected $countryColumns = array();
    protected $cityColumns = array();

    public function setUp(){
        if (!count($this->countryColumns)) {
            $is64bits = ( PHP_INT_SIZE*8 == 64 );
            $this->countryColumns ['country_id'] = '<object class="jDbColumn" key="country_id">
        <string property="type" value="int" />
        <string property="name" value="country_id" />
        <boolean property="notNull" value="true"/>
        <boolean property="autoIncrement" value="true"/>
        <string property="default" value="" />
        <boolean property="hasDefault" value="true"/>
        <integer property="length" value="0"/>
        <integer property="precision" value="11"/>
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
        <string property="type" value="int" />
        <string property="name" value="city_id" />
        <boolean property="notNull" value="true"/>
        <boolean property="autoIncrement" value="true"/>
        <string property="default" value="" />
        <boolean property="hasDefault" value="true"/>
        <integer property="length" value="0"/>
        <integer property="precision" value="11"/>
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
        <string property="type" value="int" />
        <string property="name" value="postcode" />
        <boolean property="notNull" value="false"/>
        <boolean property="autoIncrement" value="false"/>
        <string property="default" value="0" />
        <boolean property="hasDefault" value="true"/>
        <integer property="length" value="0"/>
        <integer property="precision" value="11"/>
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
        <boolean property="hasDefault" value="true"/>
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
        <boolean property="hasDefault" value="true"/>
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
        <boolean property="hasDefault" value="true"/>
        <integer property="length" value="0"/>
        <integer property="precision" value="0"/>
        <integer property="scale" value="0"/>
        <boolean property="sequence" value="false" />
        <boolean property="unsigned" value="false" />
        <integer property="minLength" value="0"/>
        <integer property="maxLength" value="65535"/>
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
        <null property="default"/>
        <boolean property="hasDefault" value="true"/>
        <integer property="length" value="0"/>
        <integer property="precision" value="0"/>
        <integer property="scale" value="0"/>
        <boolean property="sequence" value="false" />
        <boolean property="unsigned" value="false" />
        <integer property="minLength" value="0"/>
        <integer property="maxLength" value="65535"/>
        <null property="minValue"/>
        <null property="maxValue"/>
    </object>';
        }
    }

    protected $countryNameKey = '<object class="jDbUniqueKey" key="name">
                    <string property="name" value="name" />
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
    protected $city_name_postcode_idx = '<object class="jDbUniqueKey" key="city_name_postcode_idx">
                    <string property="name" value="city_name_postcode_idx" />
                    <array property="columns">
                        <string value="name"/>
                        <string value="postcode"/>
                    </array>
                </object>';
    protected $city_name_postcode_idx2 = '<object class="jDbIndex" key="city_name_postcode_idx2">
                    <string property="name" value="city_name_postcode_idx2" />
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
    protected $city_country_id_fkey = '<object class="jDbReference" key="city_ibfk_1">
                    <string property="name" value="city_ibfk_1" />
                    <array property="columns">
                        <string value="country_id"/>
                    </array>
                    <string property="fTable" value="country" />
                    <array property="fColumns">
                        <string value="country_id"/>
                    </array>
                </object>';
    protected $city_country_id_fkey2 = '<object class="jDbReference" key="bigcity_ibfk_1">
                    <string property="name" value="bigcity_ibfk_1" />
                    <array property="columns">
                        <string value="country_id"/>
                    </array>
                    <string property="fTable" value="country" />
                    <array property="fColumns">
                        <string value="country_id"/>
                    </array>
                </object>';
    protected $city_country_id_fkey3 = '<object class="jDbReference" key="city_country_id_fkey">
                    <string property="name" value="city_country_id_fkey" />
                    <array property="columns">
                        <string value="country_id"/>
                    </array>
                    <string property="fTable" value="country" />
                    <array property="fColumns">
                        <string value="country_id"/>
                    </array>
                </object>';
    function testTableList() {
        $db = jDb::getConnection();
        $db->exec('DROP TABLE IF EXISTS test_prod');
        $db->exec('DROP TABLE IF EXISTS city');
        $db->exec('DROP TABLE IF EXISTS bigcity');
        $db->exec('DROP TABLE IF EXISTS country');
        $schema = $db->schema();

        $goodList = array('jacl_group', 'jacl_right_values', 'jacl_right_values_group',
                          'jacl_rights', 'jacl_subject', 'jacl_user_group',
                          'jacl2_group','jacl2_user_group', 'jacl2_subject_group', 'jacl2_subject',
                          'jacl2_rights', 'jlx_user', 'myconfig', 'product_test',
                          'product_tags_test', 'labels_test', 'labels1_test', 'products', 'jlx_cache',
                          'jsessions', 'testkvdb', 'towns',
                          'admin_jacl2_group', 'admin_jacl2_rights', 'admin_jacl2_subject',
                          'admin_jacl2_subject_group', 'admin_jacl2_user_group', 'admin_jlx_user');

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
        $db = jDb::getConnection();
        $schema = $db->schema();

        $table = $schema->getTable('product_test');

        $this->assertNotNull($table);

        $this->assertEquals('product_test', $table->getName());

        $pk = $table->getPrimaryKey();
        $this->assertEquals(array('id'), $pk->columns);

        $is64bits = ( PHP_INT_SIZE*8 == 64 );

        $verif='<array>
    <object class="jDbColumn" key="id">
        <string property="type" value="int" />
        <string property="name" value="id" />
        <boolean property="notNull" value="true"/>
        <boolean property="autoIncrement" value="true"/>
        <string property="default" value="" />
        <boolean property="hasDefault" value="true"/>
        <integer property="length" value="0"/>
        <integer property="precision" value="11"/>
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
        <string property="type" value="boolean" />
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

        $verif = '<object class="jDbPrimaryKey">
                <string property="name" value="PRIMARY" />
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
    }

    function testCreateTable() {

        $db = jDb::getConnection();
        $schema = $db->schema();
        $db->exec('DROP TABLE IF EXISTS test_prod');
        $db->exec('DROP TABLE IF EXISTS city');
        $db->exec('DROP TABLE IF EXISTS bigcity');
        $db->exec('DROP TABLE IF EXISTS country');

        $columns = array();
        $col = new jDbColumn('id', 'integer', 0, false, null, true);
        $col->autoIncrement = true;
        $columns[] = $col;
        $columns[] = new jDbColumn('name','string',50);
        $columns[] = new jDbColumn('price','double', 0, true, null, false);
        $columns[] = new jDbColumn('promo','boolean',0, true, true);
        $columns[] = new jDbColumn('product_id','int', 0, false, null, true);

        $schema->createTable('test_prod', $columns, 'id', array('engine'=>'InnoDB'));

        $rs = $db->query('SHOW COLUMNS from test_prod');
        while($l = $rs->fetch()) {
            $list[$l->Field] = $l;
        }

        $obj = '<object>
        <string property="Type" value="int(11)" />
        <string property="Field" value="id" />
        <string property="Null" value="NO" />
        <string property="Extra"  value="auto_increment" />
        <null property="Default" />
        </object>';

        $this->assertComplexIdenticalStr($list['id'], $obj);

        $obj = '<object>
        <string property="Type" value="varchar(50)" />
        <string property="Field" value="name" />
        <string property="Null" value="YES" />
        <string property="Extra"  value="" />
        <null property="Default" />
        </object>';

        $this->assertComplexIdenticalStr($list['name'], $obj);

        $obj = '<object>
        <string property="Type" value="double" />
        <string property="Field" value="price" />
        <string property="Null" value="YES" />
        <string property="Extra"  value="" />
        <null property="Default" />
        </object>';

        $this->assertComplexIdenticalStr($list['price'], $obj);

        $obj = '<object>
        <string property="Type" value="tinyint(1)" />
        <string property="Field" value="promo" />
        <string property="Null" value="YES" />
        <string property="Extra"  value="" />
        <string property="Default" value="1"/>
        </object>';

        $this->assertComplexIdenticalStr($list['promo'], $obj);

        $obj = '<object>
        <string property="Type" value="int(11)" />
        <string property="Field" value="product_id" />
        <string property="Null" value="NO" />
        <string property="Extra"  value="" />
        <null property="Default" />
        </object>';

        $this->assertComplexIdenticalStr($list['product_id'], $obj);


        $table = new mysqliDbTable('test_prod', $schema);

        $this->assertEquals('test_prod', $table->getName());

        $pk = $table->getPrimaryKey();
        $this->assertEquals(array('id'), $pk->columns);

        $is64bits = ( PHP_INT_SIZE*8 == 64 );

        $verif='<array>
    <object class="jDbColumn" key="id">
        <string property="type" value="int" />
        <string property="name" value="id" />
        <boolean property="notNull" value="true"/>
        <boolean property="autoIncrement" value="true"/>
        <string property="default" value="" />
        <boolean property="hasDefault" value="true"/>
        <integer property="length" value="0"/>
        <integer property="precision" value="11"/>
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
        <boolean property="hasDefault" value="true"/>
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
        <string property="type" value="int" />
        <string property="name" value="product_id" />
        <boolean property="notNull" value="true"/>
        <boolean property="autoIncrement" value="false"/>
        <string property="default" value="" />
        <boolean property="hasDefault" value="false"/>
        <integer property="length" value="0"/>
        <integer property="precision" value="11"/>
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

    /**
     * @depends testCreateTable
     */
    function testReferences() {
        $db = jDb::getConnection();
        $schema = $db->schema();

        $table = $schema->getTable('test_prod');

        $reference = new jDbReference();
        $reference->name = "product_id_fkey";
        $reference->columns = array('product_id');
        $reference->fTable = 'product_test';
        $reference->fColumns = array('id');
        $table->addReference($reference);

        $table = new mysqliDbTable('test_prod', $schema);
        $references = $table->getReferences();
        $this->assertTrue(isset($references["product_id_fkey"]));
        $ref = $references["product_id_fkey"];
        $this->assertEquals("product_id_fkey", $ref->name);
        $this->assertEquals(array('product_id'), $ref->columns);
        $this->assertEquals('product_test', $ref->fTable);
        $this->assertEquals(array('id'), $ref->fColumns);
        $this->assertEquals('', $ref->onUpdate);
        $this->assertEquals('', $ref->onDelete);
    }

    /**
     * @depends testReferences
     */
    function testDropTableOld() {

        $db = jDb::getConnection();
        $schema = $db->schema();

        $table = $schema->getTable('test_prod');
        $schema->dropTable($table);

        $dbname = $db->profile['database'];
        $rs = $db->query ('SHOW TABLES FROM '.$db->encloseName($dbname));

        $col_name = 'Tables_in_'.$dbname;
        $found = false;
        while ($line = $rs->fetch ()){
            if ($line->$col_name == 'test_prod')
                $found=true;
        }
        $this->assertFalse($found);
    }


    public function testGetTablesAndConstraintsIndexes() {
        $db = jDb::getConnection('testapp');
        $db->exec('DROP TABLE IF EXISTS city');
        $db->exec('DROP TABLE IF EXISTS bigcity');
        $db->exec('DROP TABLE IF EXISTS country');

        $db->exec('CREATE TABLE country (
    country_id INTEGER AUTO_INCREMENT PRIMARY KEY,
    name varchar(50) not null,
    UNIQUE(name)
)');
        $db->exec('CREATE TABLE city (
    city_id INTEGER AUTO_INCREMENT PRIMARY KEY,
    country_id integer NOT NULL,
    name  varchar(50) not null,
    postcode integer DEFAULT 0,
    latitude varchar(20),
    longitude varchar(20),
    CONSTRAINT coordinates UNIQUE(latitude, longitude),
    FOREIGN KEY (country_id) REFERENCES country (country_id))');

        $db->exec('CREATE INDEX city_name_idx ON city (name)');
        $db->exec('CREATE UNIQUE INDEX city_name_postcode_idx ON city (name, postcode)');

        $schema = new mysqliDbSchema($db);
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

        $this->assertEquals(1, count($city->getIndexes()));
        $this->assertEquals(2, count($city->getUniqueKeys()));
        $this->assertEquals(1, count($city->getReferences()));
        $this->assertComplexIdenticalStr($city->getIndexes(),
            '<array>'.$this->city_name_idx.'</array>'
        );
        $this->assertComplexIdenticalStr($city->getUniqueKeys(),
            '<array>'.$this->city_coordinates_uniq.$this->city_name_postcode_idx.'</array>'
        );
        $this->assertComplexIdenticalStr($city->getReferences(),
            '<array>'.$this->city_country_id_fkey.'</array>'
        );

    }

    /**
     * @depends testGetTablesAndConstraintsIndexes
     */
    public function testRenameTable() {
        $db = jDb::getConnection('testapp');
        $schema = new mysqliDbSchema($db);

        $schema->renameTable('city', 'bigcity');

        $list = $schema->getTables();
        $tables = array();
        foreach($list as $table) {
            $tables[] = $table->getName();
        }

        $this->assertTrue(in_array('bigcity', $tables));
        $this->assertFalse(in_array('city', $tables));

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
            '<array>'.$this->city_name_idx.'</array>'
        );
        $this->assertComplexIdenticalStr($city->getUniqueKeys(),
            '<array>'.$this->city_coordinates_uniq.$this->city_name_postcode_idx.'</array>'
        );
        $this->assertComplexIdenticalStr($city->getReferences(),
            '<array>'.$this->city_country_id_fkey2.'</array>'
        );
    }

    /**
     * @depends testRenameTable
     */
    public function testAddColumn() {
        $db = jDb::getConnection('testapp');
        $schema = new mysqliDbSchema($db);
        $city = $schema->getTable('bigcity');
        $col = new jDbColumn('description', 'text');
        $city->addColumn($col);

        $schema = new mysqliDbSchema($db);
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
            '<array>'.$this->city_name_idx.'</array>'
        );
        $this->assertComplexIdenticalStr($city->getUniqueKeys(),
            '<array>'.$this->city_coordinates_uniq.$this->city_name_postcode_idx.'</array>'
        );
        $this->assertComplexIdenticalStr($city->getReferences(),
            '<array>'.$this->city_country_id_fkey2.'</array>'
        );
    }

    /**
     * @depends testAddColumn
     */
    public function testAlterColumn() {
        $db = jDb::getConnection('testapp');
        $schema = new mysqliDbSchema($db);
        $city = $schema->getTable('bigcity');

        $name = $city->getColumn('name', true);
        $name->length = 150;

        $desc = $city->getColumn('description', true);
        $desc->name = 'superdesc';

        $city->alterColumn($name);
        $city->alterColumn($desc, 'description');

        $schema = new mysqliDbSchema($db);
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
            '<array>'.$this->city_name_idx.'</array>'
        );
        $this->assertComplexIdenticalStr($city->getUniqueKeys(),
            '<array>'.$this->city_coordinates_uniq.$this->city_name_postcode_idx.'</array>'
        );
        $this->assertComplexIdenticalStr($city->getReferences(),
            '<array>'.$this->city_country_id_fkey2.'</array>'
        );
    }

    /**
     * @depends testAlterColumn
     */
    public function testDropColumn() {
        $db = jDb::getConnection('testapp');
        $schema = new mysqliDbSchema($db);
        $city = $schema->getTable('bigcity');
        $city->dropColumn('superdesc');

        $schema = new mysqliDbSchema($db);
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
            '<array>'.$this->city_name_idx.'</array>'
        );
        $this->assertComplexIdenticalStr($city->getUniqueKeys(),
            '<array>'.$this->city_coordinates_uniq.$this->city_name_postcode_idx.'</array>'
        );
        $this->assertComplexIdenticalStr($city->getReferences(),
            '<array>'.$this->city_country_id_fkey2.'</array>'
        );
    }

    /**
     * @depends testDropColumn
     */
    public function testDropTable() {
        $db = jDb::getConnection('testapp');
        $schema = new mysqliDbSchema($db);

        $schema->dropTable('bigcity');
        $schema->dropTable($schema->getTable('country'));

        $list = $schema->getTables();
        $tables = array();
        foreach($list as $table) {
            $tables[] = $table->getName();
        }

        $this->assertFalse(in_array('bigcity', $tables));
        $this->assertFalse(in_array('country', $tables));
        $this->assertNull($schema->getTable('bigcity'));
        $this->assertNull($schema->getTable('country'));
    }


    /**
     * @depends testDropTable
     */
    public function testCreateTableAndAddDropPrimaryKey() {
        $db = jDb::getConnection('testapp');
        $schema = new mysqliDbSchema($db);

        $columns = array();
        $id = new jDbColumn('country_id', 'INTEGER');
        // don't set autoincrement as it is not allowed on non primary/unique key
        // and then it will fail when we will remove the PK constraint
        //$id->autoIncrement = true;
        $columns[] = $id;
        $columns[] = new jDbColumn('name', 'varchar', 50, false, null, true);

        $country = $schema->createTable('country', $columns, 'country_id');

        $pk = $country->getPrimaryKey();
        $this->assertEquals(array('country_id'), $pk->columns);

        $country->dropPrimaryKey();
        $this->assertFalse($country->getPrimaryKey());

        $schema = new mysqliDbSchema($db);
        $country = $schema->getTable('country');
        $this->assertFalse($country->getPrimaryKey());

        $schema = new mysqliDbSchema($db);
        $country = $schema->getTable('country');
        $country->setPrimaryKey($pk);
        $pk = $country->getPrimaryKey();
        $this->assertEquals(array('country_id'), $pk->columns);

        $schema = new mysqliDbSchema($db);
        $country = $schema->getTable('country');
        $pk = $country->getPrimaryKey();
        $this->assertEquals(array('country_id'), $pk->columns);
    }

    /**
     * @depends testCreateTableAndAddDropPrimaryKey
     */
    public function testCreateTables() {
        $db = jDb::getConnection('testapp');
        $db->exec('DROP TABLE IF EXISTS city');
        $db->exec('DROP TABLE IF EXISTS bigcity');
        $db->exec('DROP TABLE IF EXISTS country');
        $schema = new mysqliDbSchema($db);

        $columns = array();
        $id = new jDbColumn('country_id', 'INTEGER');
        $id->autoIncrement = true;
        $columns[] = $id;
        $columns[] = new jDbColumn('name', 'varchar', 50, false, null, true);
        $country = $schema->createTable('country', $columns, 'country_id');

        $columns = array();
        $id = new jDbColumn('city_id', 'INTEGER');
        $id->autoIncrement = true;
        $columns[] = $id;
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
        $db = jDb::getConnection('testapp');
        $schema = new mysqliDbSchema($db);
        $city = $schema->getTable('city');
        $index = new jDbIndex('city_name_idx', '', array('name'));
        $city->addIndex($index);
        $index = new jDbIndex('city_name_postcode_idx2', '', array('name', 'postcode'));
        $index->isUnique = true;
        $city->addIndex($index);

        $this->assertComplexIdenticalStr($city->getIndexes(),
            '<array>'.$this->city_name_idx.
            $this->city_name_postcode_idx2.'</array>'
        );

        $schema = new mysqliDbSchema($db); // reload all
        $city = $schema->getTable('city');
        $this->assertComplexIdenticalStr($city->getIndexes(),
            '<array>'.$this->city_name_idx.
            $this->city_name_postcode_idx2.'</array>'
        );
    }

    /**
     * @depends testAddIndex
     */
    public function testDropIndex() {
        $db = jDb::getConnection('testapp');
        $schema = new mysqliDbSchema($db); // reload all
        $city = $schema->getTable('city');
        $this->assertComplexIdenticalStr($city->getIndexes(),
            '<array>'.$this->city_name_idx.
            $this->city_name_postcode_idx2.'</array>'
        );

        $city->dropIndex('city_name_idx');
        $this->assertNull($city->getIndex('city_name_idx'));
        $this->assertComplexIdenticalStr($city->getIndexes(),
            '<array>'.$this->city_name_postcode_idx2.'</array>'
        );

        $schema = new mysqliDbSchema($db); // reload all
        $city = $schema->getTable('city');
        $this->assertNull($city->getIndex('city_name_idx'));
        $this->assertComplexIdenticalStr($city->getIndexes(),
            '<array>'.$this->city_name_postcode_idx2.'</array>'
        );
    }

    /**
     * @depends testDropIndex
     */
    public function testAddUniqueKey() {
        $db = jDb::getConnection('testapp');
        $schema = new mysqliDbSchema($db); // reload all
        $country = $schema->getTable('country');

        $key = new jDbUniqueKey('country_name_key', array('name'));
        $country->addUniqueKey($key);
        $this->assertComplexIdenticalStr($country->getUniqueKeys(),
            '<array>'.$this->countryNameKey2.'</array>'
        );

        $schema = new mysqliDbSchema($db); // reload all
        $country = $schema->getTable('country');
        $this->assertComplexIdenticalStr($country->getUniqueKeys(),
            '<array>'.$this->countryNameKey2.'</array>'
        );
    }

    /**
     * @depends testAddUniqueKey
     */
    public function testDropUniqueKey() {
        $db = jDb::getConnection('testapp');
        $schema = new mysqliDbSchema($db); // reload all
        $country = $schema->getTable('country');
        $country->dropUniqueKey('country_name_key');

        $this->assertEquals(array(), $country->getUniqueKeys());
        $schema = new mysqliDbSchema($db); // reload all
        $country = $schema->getTable('country');
        $this->assertEquals(array(), $country->getUniqueKeys());
    }


    /**
     * @depends testDropUniqueKey
     */
    public function testAddReference() {
        $db = jDb::getConnection('testapp');
        $schema = new mysqliDbSchema($db); // reload all
        $city = $schema->getTable('city');

        $key = new jDbReference('city_country_id_fkey', array('country_id'),
            'country', array('country_id'));
        $city->addReference($key);
        $this->assertComplexIdenticalStr($city->getReferences(),
            '<array>'.$this->city_country_id_fkey3.'</array>'
        );

        $schema = new mysqliDbSchema($db); // reload all
        $city = $schema->getTable('city');
        $this->assertComplexIdenticalStr($city->getReferences(),
            '<array>'.$this->city_country_id_fkey3.'</array>'
        );
    }

    /**
     * @depends testAddReference
     */
    public function testDropReference() {
        $db = jDb::getConnection('testapp');
        $schema = new mysqliDbSchema($db); // reload all
        $city = $schema->getTable('city');
        $city->dropReference('city_country_id_fkey');

        $this->assertEquals(array(), $city->getReferences());
        $schema = new mysqliDbSchema($db); // reload all
        $city = $schema->getTable('city');
        $this->assertEquals(array(), $city->getReferences());
    }



}


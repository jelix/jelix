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

include_once (JELIX_LIB_PATH.'plugins/db/mysql/mysql.dbschema.php');

class UTjDbSchemaMysql extends jUnitTestCase {


    function testTableList() {
        $db = jDb::getConnection();
        $schema = $db->schema();

        $goodList = array('jacl_group', 'jacl_right_values', 'jacl_right_values_group',
                          'jacl_rights', 'jacl_subject', 'jacl_user_group',
                          'jacl2_group','jacl2_user_group', 'jacl2_subject_group', 'jacl2_subject',
                          'jacl2_rights', 'jlx_user', 'myconfig', 'product_test',
                          'product_tags_test', 'labels_test', 'labels1_test', 'products', 'jlx_cache',
                          'jsessions', 'testkvdb');

        $list = $schema->getTables();
        $tables = array();
        foreach($list as $table) {
            $tables[] = $table->getName();
        }

        sort($goodList);
        sort($tables);
        $this->assertEqual($tables, $goodList);
    }

    function testTable() {
        $db = jDb::getConnection();
        $schema = $db->schema();

        $table = $schema->getTable('product_test');

        if (!$this->assertNotNull($table))
            return;

        $this->assertEqual($table->getName(), 'product_test');

        $pk = $table->getPrimaryKey();
        $this->assertEqual($pk->columns, array('id'));

        $verif='<array>
    <object class="jDbColumn" key="id">
        <string property="type" value="int" />
        <string property="name" value="id" />
        <boolean property="notNull" value="true"/>
        <boolean property="autoIncrement" value="true"/>
        <string property="default" value="" />
        <boolean property="hasDefault" value="false"/>
        <integer property="length" value="0"/>
        <boolean property="sequence" value="false" />
        <boolean property="unsigned" value="false" />
        <null property="minLength"/>
        <null property="maxLength"/>
        <double property="minValue" value="-2147483648"/>
        <integer property="maxValue" value="2147483647"/>
    </object>
    <object class="jDbColumn" key="name">
        <string property="type" value="varchar" />
        <string property="name" value="name" />
        <boolean property="notNull" value="true"/>
        <boolean property="autoIncrement" value="false"/>
        <string property="default" value="" />
        <boolean property="hasDefault" value="false"/>
        <integer property="length" value="150"/>
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
        <boolean property="sequence" value="false" />
        <boolean property="unsigned" value="false" />
        <integer property="minLength" value="19"/>
        <integer property="maxLength" value="19"/>
        <null property="minValue"/>
        <null property="maxValue"/>
    </object>
    <object class="jDbColumn" key="promo">
        <string property="type" value="tinyint" />
        <string property="name" value="promo" />
        <boolean property="notNull" value="true"/>
        <boolean property="autoIncrement" value="false"/>
        <string property="default" value=""/>
        <boolean property="hasDefault" value="false"/>
        <integer property="length" value="0"/>
        <boolean property="sequence" value="false" />
        <boolean property="unsigned" value="false" />
        <null property="minLength"/>
        <null property="maxLength"/>
        <integer property="minValue" value="-128"/>
        <integer property="maxValue" value="127"/>
    </object>
</array>';

        $this->assertComplexIdenticalStr($table->getColumns(), $verif);
    }



    function testCreateTable() {
        $db = jDb::getConnection();
        $schema = $db->schema();


        $columns = array();
        $col = new jDbColumn('id', 'int', 0, false, null, true);
        $col->autoIncrement = true;
        $columns[] = $col;
        $columns[] = new jDbColumn('name','string',50);
        $columns[] = new jDbColumn('price','double');
        $columns[] = new jDbColumn('promo','boolean');

        $table = $schema->createTable('test_prod', $columns, 'id');

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
        <null property="Default" />
        </object>';

        $this->assertComplexIdenticalStr($list['promo'], $obj);
    }

    function testDropTable() {

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
}


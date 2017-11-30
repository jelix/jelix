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

}


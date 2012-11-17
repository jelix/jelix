<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2007-2012 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jDb_sqlite3Test extends jUnitTestCase {

    public static function setUpBeforeClass() {
        self::initJelixConfig();
    }

    function setUp (){
        try {
            jProfiles::get('jdb', 'testapp_sqlite3', true);
        }
        catch(Exception $e) {
            $this->markTestSkipped(get_class($this).' cannot be run: undefined testapp_sqlite3 profile');
            return;
        }

        if (!class_exists('SQLite3')) {
            $this->markTestSkipped(get_class($this).' cannot be run: sqlite3 extension is not installed');
        }
        parent::setUp();
    }

    function testTools(){
        $tools = jDb::getConnection('testapp_sqlite3')->tools();
        $fields = $tools->getFieldList('products');
        $structure = '<array>
    <object key="id" class="jDbFieldProperties">
        <string property="type" value="integer" />
        <string property="name" value="id" />
        <boolean property="notNull" value="true" />
        <boolean property="primary" value="true" />
        <boolean property="autoIncrement" value="true" />
        <boolean property="hasDefault" value="false" />
        <string property="default" value="" />
        <integer property="length" value="0" />
    </object>
    <object key="name" class="jDbFieldProperties">
        <string property="type" value="varchar" />
        <string property="name" value="name" />
        <boolean property="notNull" value="true" />
        <boolean property="primary" value="false" />
        <boolean property="autoIncrement" value="false" />
        <boolean property="hasDefault" value="false" />
        <string property="default" value="" />
        <integer property="length" value="150" />
    </object>
    <object key="price" class="jDbFieldProperties">
        <string property="type" value="float" />
        <string property="name" value="price" />
        <boolean property="notNull" value="false" />
        <boolean property="primary" value="false" />
        <boolean property="autoIncrement" value="false" />
        <boolean property="hasDefault" value="true" />
        <string property="default" value="0" />
        <integer property="length" value="0" />
    </object>
</array>';
        $this->assertComplexIdenticalStr($fields, $structure, 'bad results');
    }


}


?>
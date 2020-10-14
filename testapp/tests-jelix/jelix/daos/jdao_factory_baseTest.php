<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2011 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(__DIR__.'/daotests.lib.php');

require_once(JELIX_LIB_PATH.'db/jDb.class.php');
require_once(JELIX_LIB_PATH.'plugins/db/mysqli/mysqli.dbconnection.php');

class jdao_factory_baseTest extends jUnitTestCaseDb {

    protected $conn, $rs;

    public static function setUpBeforeClass() {
        self::initJelixConfig();
        jApp::pushCurrentModule('jelix_tests');
        jDao::get('testapp~products'); // just to load the generated class of the dao
        jDao::get('testapp~productsalias'); // just to load the generated class of the dao
    }

    function setUp() {
        $this->conn = $this->getMockBuilder('mysqliDbConnection')
                        ->disableOriginalConstructor()
                        ->setMethods(array('query', 'exec', 'limitQuery', 'disconnect'))
                        ->getMock();

        $this->rs =  $this->getMockBuilder('mysqliDbResultSet')
                        ->disableOriginalConstructor()
                        ->getMock();
        $this->conn->expects($this->any())
                    ->method('query')
                    ->will($this->returnValue($this->rs));
        $this->conn->expects($this->any())
                    ->method('limitQuery')
                    ->will($this->returnValue($this->rs));
        $this->conn->expects($this->any())
                    ->method('query')
                    ->will($this->returnValue(0));
        /*$this->conn->expects($this->any())
                    ->method('prefixTable')
                    ->will($this->returnCallback(function($table) {
                        return $table;
                    }));*/
    }

    function testFindAll() {
        $this->conn->profile = array('table_prefix'=>'', '_name'=>'default');
        $dao = new cDao_testapp_Jx_products_Jx_mysql($this->conn);
        $this->conn->expects($this->once())
                    ->method('query')
                    ->with($this->equalTo('SELECT `products`.`id`, `products`.`name`, `products`.`price`, `products`.`promo`, `products`.`publish_date` FROM `products` AS `products`'));
        $dao->findAll();
    }

    function testFindAllPrefix() {
        $this->conn->profile = array('table_prefix'=>'foo_', '_name'=>'default');
        $dao = new cDao_testapp_Jx_products_Jx_mysql($this->conn);
        $this->conn->expects($this->once())
                    ->method('query')
                    ->with($this->equalTo('SELECT `products`.`id`, `products`.`name`, `products`.`price`, `products`.`promo`, `products`.`publish_date` FROM `foo_products` AS `products`'));
        $dao->findAll();
    }

    function testFindAllAlias() {
        $this->conn->profile = array('table_prefix'=>'', '_name'=>'default');
        $dao = new cDao_testapp_Jx_productsalias_Jx_mysql($this->conn);
        $this->conn->expects($this->once())
                    ->method('query')
                    ->with($this->equalTo('SELECT `p`.`id`, `p`.`name`, `p`.`price`, `p`.`promo` FROM `products` AS `p`'));
        $dao->findAll();
    }

    function testFindAllAliasPrefix() {
        $this->conn->profile = array('table_prefix'=>'foo_', '_name'=>'default');
        $dao = new cDao_testapp_Jx_productsalias_Jx_mysql($this->conn);
        $this->conn->expects($this->once())
                    ->method('query')
                    ->with($this->equalTo('SELECT `p`.`id`, `p`.`name`, `p`.`price`, `p`.`promo` FROM `foo_products` AS `p`'));
        $dao->findAll();
    }

    function testCountAll() {
        $this->conn->profile = array('table_prefix'=>'', '_name'=>'default');
        $o = new stdClass();
        $o->c = '54';
        $this->rs->expects($this->any())
                    ->method('fetch')
                    ->will($this->returnValue($o));

        $dao = new cDao_testapp_Jx_products_Jx_mysql($this->conn);
        $this->conn->expects($this->once())
                    ->method('query')
                    ->with($this->equalTo('SELECT COUNT(*) as c  FROM `products` AS `products`'));
        $dao->countAll();
    }

    function testCountAllPrefix() {
        $this->conn->profile = array('table_prefix'=>'', '_name'=>'default');
        $o = new stdClass();
        $o->c = '54';
        $this->rs->expects($this->any())
                    ->method('fetch')
                    ->will($this->returnValue($o));
        $this->conn->profile = array('table_prefix'=>'foo_');
        $dao = new cDao_testapp_Jx_products_Jx_mysql($this->conn);
        $this->conn->expects($this->once())
                    ->method('query')
                    ->with($this->equalTo('SELECT COUNT(*) as c  FROM `foo_products` AS `products`'));
        $dao->countAll();
    }

    function testCountAllAlias() {
        $this->conn->profile = array('table_prefix'=>'', '_name'=>'default');
        $o = new stdClass();
        $o->c = '54';
        $this->rs->expects($this->any())
                    ->method('fetch')
                    ->will($this->returnValue($o));
        $dao = new cDao_testapp_Jx_productsalias_Jx_mysql($this->conn);
        $this->conn->expects($this->once())
                    ->method('query')
                    ->with($this->equalTo('SELECT COUNT(*) as c  FROM `products` AS `p`'));
        $dao->countAll();
    }

    function testCountAllAliasPrefix() {
        $o = new stdClass();
        $o->c = '54';
        $this->rs->expects($this->any())
                    ->method('fetch')
                    ->will($this->returnValue($o));
        $this->conn->profile = array('table_prefix'=>'foo_');
        $dao = new cDao_testapp_Jx_productsalias_Jx_mysql($this->conn);
        $this->conn->expects($this->once())
                    ->method('query')
                    ->with($this->equalTo('SELECT COUNT(*) as c  FROM `foo_products` AS `p`'));
        $dao->countAll();
    }

    function testGet() {
        $o = new stdClass();
        $this->rs->expects($this->any())
                    ->method('fetch')
                    ->will($this->returnValue($o));
        $this->conn->profile = array('table_prefix'=>'', '_name'=>'default');
        $dao = new cDao_testapp_Jx_products_Jx_mysql($this->conn);
        $this->conn->expects($this->once())
                    ->method('query')
                    ->with($this->equalTo('SELECT `products`.`id`, `products`.`name`, `products`.`price`, `products`.`promo`, `products`.`publish_date` FROM `products` AS `products` WHERE  `products`.`id` = 32'));
        $dao->get(32);
    }

    function testGetPrefix() {
        $o = new stdClass();
        $this->rs->expects($this->any())
                    ->method('fetch')
                    ->will($this->returnValue($o));
        $this->conn->profile = array('table_prefix'=>'foo_', '_name'=>'default');
        $dao = new cDao_testapp_Jx_products_Jx_mysql($this->conn);
        $this->conn->expects($this->once())
                    ->method('query')
                    ->with($this->equalTo('SELECT `products`.`id`, `products`.`name`, `products`.`price`, `products`.`promo`, `products`.`publish_date` FROM `foo_products` AS `products` WHERE  `products`.`id` = 32'));
        $dao->get(32);
    }

    function testGetAlias() {
        $o = new stdClass();
        $this->rs->expects($this->any())
                    ->method('fetch')
                    ->will($this->returnValue($o));
        $this->conn->profile = array('table_prefix'=>'', '_name'=>'default');
        $dao = new cDao_testapp_Jx_productsalias_Jx_mysql($this->conn);
        $this->conn->expects($this->once())
                    ->method('query')
                    ->with($this->equalTo('SELECT `p`.`id`, `p`.`name`, `p`.`price`, `p`.`promo` FROM `products` AS `p` WHERE  `p`.`id` = 32'));
        $dao->get(32);
    }

    function testGetAliasPrefix() {
        $o = new stdClass();
        $this->rs->expects($this->any())
                    ->method('fetch')
                    ->will($this->returnValue($o));
        $this->conn->profile = array('table_prefix'=>'foo_', '_name'=>'default');
        $dao = new cDao_testapp_Jx_productsalias_Jx_mysql($this->conn);
        $this->conn->expects($this->once())
                    ->method('query')
                    ->with($this->equalTo('SELECT `p`.`id`, `p`.`name`, `p`.`price`, `p`.`promo` FROM `foo_products` AS `p` WHERE  `p`.`id` = 32'));
        $dao->get(32);
    }

    function testFindBy() {
        $dao = new cDao_testapp_Jx_products_Jx_mysql($this->conn);
        $cond = new jDaoConditions ('AND');
        $cond->addItemOrder('price', 'asc');
        // note: in the order clause, names are note enclosed between quotes because of the mock
        $this->conn->expects($this->once())
                    ->method('query')
                    ->with($this->equalTo('SELECT `products`.`id`, `products`.`name`, `products`.`price`, `products`.`promo`, `products`.`publish_date` FROM `products` AS `products` ORDER BY `price` asc'));
        $dao->findBy($cond);
    }

    function testFindByPrefix() {
        $this->conn->profile = array('table_prefix'=>'foo_', '_name'=>'default');
        $dao = new cDao_testapp_Jx_products_Jx_mysql($this->conn);
        $cond = new jDaoConditions ('AND');
        $cond->addItemOrder('price', 'asc');
        // note: in the order clause, names are note enclosed between quotes because of the mock
        $this->conn->expects($this->once())
                    ->method('query')
                    ->with($this->equalTo('SELECT `products`.`id`, `products`.`name`, `products`.`price`, `products`.`promo`, `products`.`publish_date` FROM `foo_products` AS `products` ORDER BY `price` asc'));
        $dao->findBy($cond);
    }

    function testFindByAlias() {
        $this->conn->profile = array('table_prefix'=>'', '_name'=>'default');
        $dao = new cDao_testapp_Jx_productsalias_Jx_mysql($this->conn);
        $cond = new jDaoConditions ('AND');
        $cond->addItemOrder('price', 'asc');
        // note: in the order clause, names are note enclosed between quotes because of the mock
        $this->conn->expects($this->once())
                    ->method('query')
                    ->with($this->equalTo('SELECT `p`.`id`, `p`.`name`, `p`.`price`, `p`.`promo` FROM `products` AS `p` ORDER BY `price` asc'));
        $dao->findBy($cond);
    }

    function testFindByAliasPrefix() {
        $this->conn->profile = array('table_prefix'=>'foo_', '_name'=>'default');
        $dao = new cDao_testapp_Jx_productsalias_Jx_mysql($this->conn);
        $cond = new jDaoConditions ('AND');
        $cond->addItemOrder('price', 'asc');
        // note: in the order clause, names are note enclosed between quotes because of the mock
        $this->conn->expects($this->once())
                    ->method('query')
                    ->with($this->equalTo('SELECT `p`.`id`, `p`.`name`, `p`.`price`, `p`.`promo` FROM `foo_products` AS `p` ORDER BY `price` asc'));
        $dao->findBy($cond);
    }
}

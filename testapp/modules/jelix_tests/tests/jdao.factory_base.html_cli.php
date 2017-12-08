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

require_once(JELIX_LIB_PATH.'db/jDb.class.php');
require_once(LIB_PATH.'/simpletest/mock_objects.php');

class UTDaoFactoryBase extends jUnitTestCaseDb {

    function setUpRun() {
        jDao::get('testapp~products'); // just to load the generated class of the dao
        jDao::get('testapp~productsalias'); // just to load the generated class of the dao
        Mock::generatePartial('jDbConnection', 'MockjDbConnection', array('query', 'limitQuery', 'exec', 'lastIdInTable', 'tools', 'schema',
                                                                          'beginTransaction', 'commit', 'rollback', 'prepare', 'errorInfo',
                                                                          'errorCode', 'lastInsertId', '_autoCommitNotify','_connect','_disconnect',
                                                                          '_doQuery','_doExec','_doLimitQuery', 'getAttribute', 'setAttribute'));
        Mock::generatePartial('jDbResultSet', 'MockjDbResultSet', array('fetch', 'bindColumn', 'bindParam', 'bindValue', 'columnCount', 'execute', 'rowCount', '_free', '_fetch', '_rewind'));
    }

    protected $conn, $rs;
    function setUp() {
        $this->conn = new MockjDbConnection();
        $this->rs = new MockjDbResultSet();
        $this->conn->returns('query', $this->rs);
        $this->conn->returns('limitQuery', $this->rs);
        $this->conn->returns('exec', 0);
    }

    function testFindAll() {
        $dao = new cDao_testapp_Jx_products_Jx_mysql($this->conn);
        $this->conn->expectOnce('query', array('SELECT `products`.`id`, `products`.`name`, `products`.`price`, `products`.`promo`, `products`.`publish_date` FROM `products` AS `products`'));
        $dao->findAll();
    }

    function testFindAllPrefix() {
        $this->conn->profile = array('table_prefix'=>'foo_');
        $dao = new cDao_testapp_Jx_products_Jx_mysql($this->conn);
        $this->conn->expectOnce('query', array('SELECT `products`.`id`, `products`.`name`, `products`.`price`, `products`.`promo`, `products`.`publish_date` FROM `foo_products` AS `products`'));
        $dao->findAll();
    }

    function testFindAllAlias() {
        $dao = new cDao_testapp_Jx_productsalias_Jx_mysql($this->conn);
        $this->conn->expectOnce('query', array('SELECT `p`.`id`, `p`.`name`, `p`.`price`, `p`.`promo` FROM `products` AS `p`'));
        $dao->findAll();
    }

    function testFindAllAliasPrefix() {
        $this->conn->profile = array('table_prefix'=>'foo_');
        $dao = new cDao_testapp_Jx_productsalias_Jx_mysql($this->conn);
        $this->conn->expectOnce('query', array('SELECT `p`.`id`, `p`.`name`, `p`.`price`, `p`.`promo` FROM `foo_products` AS `p`'));
        $dao->findAll();
    }

    function testCountAll() {
        $o = new stdClass();
        $o->c = '54';
        $this->rs->setReturnValue('fetch', $o);
        $dao = new cDao_testapp_Jx_products_Jx_mysql($this->conn);
        $this->conn->expectOnce('query', array('SELECT COUNT(*) as c  FROM `products` AS `products`'));
        $dao->countAll();
    }

    function testCountAllPrefix() {
        $o = new stdClass();
        $o->c = '54';
        $this->rs->setReturnValue('fetch', $o);
        $this->conn->profile = array('table_prefix'=>'foo_');
        $dao = new cDao_testapp_Jx_products_Jx_mysql($this->conn);
        $this->conn->expectOnce('query', array('SELECT COUNT(*) as c  FROM `foo_products` AS `products`'));
        $dao->countAll();
    }

    function testCountAllAlias() {
        $o = new stdClass();
        $o->c = '54';
        $this->rs->setReturnValue('fetch', $o);
        $dao = new cDao_testapp_Jx_productsalias_Jx_mysql($this->conn);
        $this->conn->expectOnce('query', array('SELECT COUNT(*) as c  FROM `products` AS `p`'));
        $dao->countAll();
    }

    function testCountAllAliasPrefix() {
        $o = new stdClass();
        $o->c = '54';
        $this->rs->setReturnValue('fetch', $o);
        $this->conn->profile = array('table_prefix'=>'foo_');
        $dao = new cDao_testapp_Jx_productsalias_Jx_mysql($this->conn);
        $this->conn->expectOnce('query', array('SELECT COUNT(*) as c  FROM `foo_products` AS `p`'));
        $dao->countAll();
    }

    function testGet() {
        $o = new stdClass();
        $this->rs->setReturnValue('fetch', $o);
        $dao = new cDao_testapp_Jx_products_Jx_mysql($this->conn);
        $this->conn->expectOnce('query', array('SELECT `products`.`id`, `products`.`name`, `products`.`price`, `products`.`promo`, `products`.`publish_date` FROM `products` AS `products` WHERE  `products`.`id` = 32'));
        $dao->get(32);
    }

    function testGetPrefix() {
        $o = new stdClass();
        $this->rs->setReturnValue('fetch', $o);
        $this->conn->profile = array('table_prefix'=>'foo_');
        $dao = new cDao_testapp_Jx_products_Jx_mysql($this->conn);
        $this->conn->expectOnce('query', array('SELECT `products`.`id`, `products`.`name`, `products`.`price`, `products`.`promo`, `products`.`publish_date` FROM `foo_products` AS `products` WHERE  `products`.`id` = 32'));
        $dao->get(32);
    }

    function testGetAlias() {
        $o = new stdClass();
        $this->rs->setReturnValue('fetch', $o);
        $dao = new cDao_testapp_Jx_productsalias_Jx_mysql($this->conn);
        $this->conn->expectOnce('query', array('SELECT `p`.`id`, `p`.`name`, `p`.`price`, `p`.`promo` FROM `products` AS `p` WHERE  `p`.`id` = 32'));
        $dao->get(32);
    }

    function testGetAliasPrefix() {
        $o = new stdClass();
        $this->rs->setReturnValue('fetch', $o);
        $this->conn->profile = array('table_prefix'=>'foo_');
        $dao = new cDao_testapp_Jx_productsalias_Jx_mysql($this->conn);
        $this->conn->expectOnce('query', array('SELECT `p`.`id`, `p`.`name`, `p`.`price`, `p`.`promo` FROM `foo_products` AS `p` WHERE  `p`.`id` = 32'));
        $dao->get(32);
    }

    function testFindBy() {
        $dao = new cDao_testapp_Jx_products_Jx_mysql($this->conn);
        $cond = new jDaoConditions ('AND');
        $cond->addItemOrder('price', 'asc');
        // note: in the order clause, names are note enclosed between quotes because of the mock
        $this->conn->expectOnce('query', array('SELECT `products`.`id`, `products`.`name`, `products`.`price`, `products`.`promo`, `products`.`publish_date` FROM `products` AS `products` ORDER BY price asc'));
        $dao->findBy($cond);
    }

    function testFindByPrefix() {
        $this->conn->profile = array('table_prefix'=>'foo_');
        $dao = new cDao_testapp_Jx_products_Jx_mysql($this->conn);
        $cond = new jDaoConditions ('AND');
        $cond->addItemOrder('price', 'asc');
        // note: in the order clause, names are note enclosed between quotes because of the mock
        $this->conn->expectOnce('query', array('SELECT `products`.`id`, `products`.`name`, `products`.`price`, `products`.`promo`, `products`.`publish_date` FROM `foo_products` AS `products` ORDER BY price asc'));
        $dao->findBy($cond);
    }

    function testFindByAlias() {
        $dao = new cDao_testapp_Jx_productsalias_Jx_mysql($this->conn);
        $cond = new jDaoConditions ('AND');
        $cond->addItemOrder('price', 'asc');
        // note: in the order clause, names are note enclosed between quotes because of the mock
        $this->conn->expectOnce('query', array('SELECT `p`.`id`, `p`.`name`, `p`.`price`, `p`.`promo` FROM `products` AS `p` ORDER BY price asc'));
        $dao->findBy($cond);
    }

    function testFindByAliasPrefix() {
        $this->conn->profile = array('table_prefix'=>'foo_');
        $dao = new cDao_testapp_Jx_productsalias_Jx_mysql($this->conn);
        $cond = new jDaoConditions ('AND');
        $cond->addItemOrder('price', 'asc');
        // note: in the order clause, names are note enclosed between quotes because of the mock
        $this->conn->expectOnce('query', array('SELECT `p`.`id`, `p`.`name`, `p`.`price`, `p`.`promo` FROM `foo_products` AS `p` ORDER BY price asc'));
        $dao->findBy($cond);
    }
}

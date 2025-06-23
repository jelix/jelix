<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2011-2025 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


require_once(JELIX_LIB_PATH.'db/jDb.class.php');

class jdao_factory_baseTest extends \Jelix\UnitTests\UnitTestCaseDb {

    protected $conn, $rs;

    public static function setUpBeforeClass() : void  {
        self::initJelixConfig();
        jApp::pushCurrentModule('jelix_tests');
        jDao::get('testapp~products'); // just to load the generated class of the dao
        jDao::get('testapp~productsalias'); // just to load the generated class of the dao
    }

    function setUp() : void
    {
    }

    static $unprefixProfile = array(
        'table_prefix'=>'',
        '_name'=>'default',
        'dbtype' => 'mysql',
        'driver' => 'mysqli',
    );
    static $prefixProfile = array(
        'table_prefix'=>'foo_',
        '_name'=>'default',
        'dbtype' => 'mysql',
        'driver' => 'mysqli',
    );


    public function setProtectedProperty($object, $property, $value)
    {
        $reflection = new ReflectionClass($object);
        $reflection_property = $reflection->getProperty($property);
        $reflection_property->setValue($object, $value);
    }

    protected function getConn($profile)
    {
        $conn = $this->getMockBuilder('\Jelix\Database\Connector\Mysqli\Connection')
            ->disableOriginalConstructor()
            ->onlyMethods(array('query', 'exec', 'limitQuery', '_disconnect'))
            ->getMock();
        $this->setProtectedProperty($conn, '_profile', $profile);

        $rs =  $this->getMockBuilder('\Jelix\Database\Connector\Mysqli\ResultSet')
            ->disableOriginalConstructor()
            ->getMock();
        $conn->expects($this->any())
            ->method('query')
            ->willReturn($rs);
        $conn->expects($this->any())
            ->method('limitQuery')
            ->willReturn($rs);
        $conn->expects($this->any())
            ->method('query')
            ->willReturn(0);
        return [$conn, $rs];
    }

    function testFindAll() {
        list($conn, $rs) = $this->getConn(self::$unprefixProfile);
        $dao = new cDao_testapp_Jx_products_Jx_mysql($conn);
        $conn->expects($this->once())
                    ->method('query')
                    ->with($this->equalTo('SELECT `products`.`id`, `products`.`name`, `products`.`price`, `products`.`promo`, `products`.`publish_date` FROM `products` AS `products`'));
        $dao->findAll();
    }

    function testFindAllPrefix() {
        list($conn, $rs) = $this->getConn(self::$prefixProfile);
        $dao = new cDao_testapp_Jx_products_Jx_mysql($conn);
        $conn->expects($this->once())
                    ->method('query')
                    ->with($this->equalTo('SELECT `products`.`id`, `products`.`name`, `products`.`price`, `products`.`promo`, `products`.`publish_date` FROM `foo_products` AS `products`'));
        $dao->findAll();
    }

    function testFindAllAlias() {
        list($conn, $rs) = $this->getConn(self::$unprefixProfile);
        $dao = new cDao_testapp_Jx_productsalias_Jx_mysql($conn);
        $conn->expects($this->once())
                    ->method('query')
                    ->with($this->equalTo('SELECT `p`.`id`, `p`.`name`, `p`.`price`, `p`.`promo` FROM `products` AS `p`'));
        $dao->findAll();
    }

    function testFindAllAliasPrefix() {
        list($conn, $rs) = $this->getConn(self::$prefixProfile);
        $dao = new cDao_testapp_Jx_productsalias_Jx_mysql($conn);
        $conn->expects($this->once())
                    ->method('query')
                    ->with($this->equalTo('SELECT `p`.`id`, `p`.`name`, `p`.`price`, `p`.`promo` FROM `foo_products` AS `p`'));
        $dao->findAll();
    }

    function testCountAll() {
        list($conn, $rs) = $this->getConn(self::$unprefixProfile);
        $o = new stdClass();
        $o->c = '54';
        $rs->expects($this->any())
                    ->method('fetch')
                    ->will($this->returnValue($o));

        $dao = new cDao_testapp_Jx_products_Jx_mysql($conn);
        $conn->expects($this->once())
                    ->method('query')
                    ->with($this->equalTo('SELECT COUNT(*) as c  FROM `products` AS `products`'));
        $dao->countAll();
    }

    function testCountAllPrefix() {
        list($conn, $rs) = $this->getConn(self::$prefixProfile);
        $o = new stdClass();
        $o->c = '54';
        $rs->expects($this->any())
                    ->method('fetch')
                    ->will($this->returnValue($o));
        $dao = new cDao_testapp_Jx_products_Jx_mysql($conn);
        $conn->expects($this->once())
                    ->method('query')
                    ->with($this->equalTo('SELECT COUNT(*) as c  FROM `foo_products` AS `products`'));
        $dao->countAll();
    }

    function testCountAllAlias() {
        list($conn, $rs) = $this->getConn(self::$unprefixProfile);
        $o = new stdClass();
        $o->c = '54';
        $rs->expects($this->any())
                    ->method('fetch')
                    ->will($this->returnValue($o));
        $dao = new cDao_testapp_Jx_productsalias_Jx_mysql($conn);
        $conn->expects($this->once())
                    ->method('query')
                    ->with($this->equalTo('SELECT COUNT(*) as c  FROM `products` AS `p`'));
        $dao->countAll();
    }

    function testCountAllAliasPrefix() {

        list($conn, $rs) = $this->getConn(self::$prefixProfile);
        $o = new stdClass();
        $o->c = '54';
        $rs->expects($this->any())
                    ->method('fetch')
                    ->will($this->returnValue($o));
        $dao = new cDao_testapp_Jx_productsalias_Jx_mysql($conn);
        $conn->expects($this->once())
                    ->method('query')
                    ->with($this->equalTo('SELECT COUNT(*) as c  FROM `foo_products` AS `p`'));
        $dao->countAll();
    }

    function testGet() {
        list($conn, $rs) = $this->getConn(self::$unprefixProfile);
        $o = new stdClass();
        $rs->expects($this->any())
                    ->method('fetch')
                    ->will($this->returnValue($o));
        $dao = new cDao_testapp_Jx_products_Jx_mysql($conn);
        $conn->expects($this->once())
                    ->method('query')
                    ->with($this->equalTo('SELECT `products`.`id`, `products`.`name`, `products`.`price`, `products`.`promo`, `products`.`publish_date` FROM `products` AS `products` WHERE  `products`.`id` = 32'));
        $dao->get(32);
    }

    function testGetPrefix() {
        list($conn, $rs) = $this->getConn(self::$prefixProfile);
        $o = new stdClass();
        $rs->expects($this->any())
                    ->method('fetch')
                    ->will($this->returnValue($o));
        $dao = new cDao_testapp_Jx_products_Jx_mysql($conn);
        $conn->expects($this->once())
                    ->method('query')
                    ->with($this->equalTo('SELECT `products`.`id`, `products`.`name`, `products`.`price`, `products`.`promo`, `products`.`publish_date` FROM `foo_products` AS `products` WHERE  `products`.`id` = 32'));
        $dao->get(32);
    }

    function testGetAlias() {
        list($conn, $rs) = $this->getConn(self::$unprefixProfile);
        $o = new stdClass();
        $rs->expects($this->any())
                    ->method('fetch')
                    ->will($this->returnValue($o));
        $dao = new cDao_testapp_Jx_productsalias_Jx_mysql($conn);
        $conn->expects($this->once())
                    ->method('query')
                    ->with($this->equalTo('SELECT `p`.`id`, `p`.`name`, `p`.`price`, `p`.`promo` FROM `products` AS `p` WHERE  `p`.`id` = 32'));
        $dao->get(32);
    }

    function testGetAliasPrefix() {
        list($conn, $rs) = $this->getConn(self::$prefixProfile);
        $o = new stdClass();
        $rs->expects($this->any())
                    ->method('fetch')
                    ->will($this->returnValue($o));
        $dao = new cDao_testapp_Jx_productsalias_Jx_mysql($conn);
        $conn->expects($this->once())
                    ->method('query')
                    ->with($this->equalTo('SELECT `p`.`id`, `p`.`name`, `p`.`price`, `p`.`promo` FROM `foo_products` AS `p` WHERE  `p`.`id` = 32'));
        $dao->get(32);
    }

    function testFindBy() {
        list($conn, $rs) = $this->getConn(self::$unprefixProfile);
        $dao = new cDao_testapp_Jx_products_Jx_mysql($conn);
        $cond = new jDaoConditions ('AND');
        $cond->addItemOrder('price', 'asc');
        // note: in the order clause, names are note enclosed between quotes because of the mock
        $conn->expects($this->once())
                    ->method('query')
                    ->with($this->equalTo('SELECT `products`.`id`, `products`.`name`, `products`.`price`, `products`.`promo`, `products`.`publish_date` FROM `products` AS `products` ORDER BY `price` asc'));
        $dao->findBy($cond);
    }

    function testFindByPrefix() {
        list($conn, $rs) = $this->getConn(self::$prefixProfile);
        $dao = new cDao_testapp_Jx_products_Jx_mysql($conn);
        $cond = new jDaoConditions ('AND');
        $cond->addItemOrder('price', 'asc');
        // note: in the order clause, names are note enclosed between quotes because of the mock
        $conn->expects($this->once())
                    ->method('query')
                    ->with($this->equalTo('SELECT `products`.`id`, `products`.`name`, `products`.`price`, `products`.`promo`, `products`.`publish_date` FROM `foo_products` AS `products` ORDER BY `price` asc'));
        $dao->findBy($cond);
    }

    function testFindByAlias() {
        list($conn, $rs) = $this->getConn(self::$unprefixProfile);
        $dao = new cDao_testapp_Jx_productsalias_Jx_mysql($conn);
        $cond = new jDaoConditions ('AND');
        $cond->addItemOrder('price', 'asc');
        // note: in the order clause, names are note enclosed between quotes because of the mock
        $conn->expects($this->once())
                    ->method('query')
                    ->with($this->equalTo('SELECT `p`.`id`, `p`.`name`, `p`.`price`, `p`.`promo` FROM `products` AS `p` ORDER BY `price` asc'));
        $dao->findBy($cond);
    }

    function testFindByAliasPrefix() {
        list($conn, $rs) = $this->getConn(self::$prefixProfile);
        $dao = new cDao_testapp_Jx_productsalias_Jx_mysql($conn);
        $cond = new jDaoConditions ('AND');
        $cond->addItemOrder('price', 'asc');
        // note: in the order clause, names are note enclosed between quotes because of the mock
        $conn->expects($this->once())
                    ->method('query')
                    ->with($this->equalTo('SELECT `p`.`id`, `p`.`name`, `p`.`price`, `p`.`promo` FROM `foo_products` AS `p` ORDER BY `price` asc'));
        $dao->findBy($cond);
    }
}

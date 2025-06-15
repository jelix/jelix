<?php
use Jelix\Dao\Parser\DaoTable;

class jDaoImportTest extends \Jelix\UnitTests\UnitTestCase {

    public function setUp () : void  {
        self::initJelixConfig();
        jApp::pushCurrentModule('jelix_tests');
    }

    function tearDown() : void  {
        jApp::popCurrentModule();
    }

    public function testExtendedRecords() {
        $post = jDao::createRecord('jelix_tests~posts');
        $blogPost = jDao::createRecord('jelix_tests~post_blog');
        $trackerPost = jDao::createRecord('jelix_tests~post_tracker');
        
        $this->assertInstanceOf('postDaoRecord', $post);
        $this->assertInstanceOf('postDaoRecord', $blogPost);
        $this->assertInstanceOf('postDaoRecord', $trackerPost);
        $this->assertInstanceOf('\JelixTests\Tests\Dao\PostBlog', $blogPost);
        $this->assertInstanceOf('postTrackerDaoRecord', $trackerPost);

        $postDao = jDao::create('jelix_tests~posts');
        $blogPostDao = jDao::create('jelix_tests~post_blog');
        $trackerPostDao = jDao::create('jelix_tests~post_tracker');

        $post = $postDao->createRecord();
        $blogPost = $blogPostDao->createRecord();
        $trackerPost = $trackerPostDao->createRecord();

        $this->assertInstanceOf('postDaoRecord', $post);
        $this->assertInstanceOf('postDaoRecord', $blogPost);
        $this->assertInstanceOf('postDaoRecord', $trackerPost);
        $this->assertInstanceOf('\JelixTests\Tests\Dao\PostBlog', $blogPost);
        $this->assertInstanceOf('postTrackerDaoRecord', $trackerPost);
    }
    
    public function testExtendedFactory() {

        $postDao = jDao::create('jelix_tests~posts');
        $blogPostDao = jDao::create('jelix_tests~post_blog');
        $this->assertInstanceOf('\Jelix\Dao\AbstractDaoFactory', $postDao);
        $this->assertInstanceOf('\Jelix\Dao\AbstractDaoFactory', $blogPostDao);
        $this->assertInstanceOf('\JelixTests\Tests\Dao\PostBlogFactory', $blogPostDao);

        $this->assertTrue(method_exists($blogPostDao, 'getByEmail'));
    }

    public function testImportedEvents() {
        $postSel = new jSelectorDao('jelix_tests~posts', '');
        $blogSel = new jSelectorDao('jelix_tests~post_blog', '');
        $trackerSel = new jSelectorDao('jelix_tests~post_tracker', '');
        $context = new jDaoContext('', jDb::getConnection(''));
        $compiler = new \Jelix\Dao\Generator\Compiler();

        $postParser =  $compiler->parse($postSel, $context);
        $this->assertEquals(array('deletebefore'), $postParser->getEvents());

       	$blogParser = $compiler->parse($blogSel, $context);
        $this->assertEquals(array('deletebefore'), $blogParser->getEvents());
       	
       	$trackerParser = $compiler->parse($trackerSel, $context);
        $this->assertEquals(array('deletebefore', 'insertbefore', 'updatebefore'), $trackerParser->getEvents());
    }
    
    public function testImportWithRedefinedMethods() {
        $postSel = new jSelectorDao('jelix_tests~posts', '');
        $trackerSel = new jSelectorDao('jelix_tests~post_tracker', '');
        $context = new jDaoContext('', jDb::getConnection(''));
        $compiler = new \Jelix\Dao\Generator\Compiler();

        $postTrackerParser = $compiler->parse($trackerSel, $context);;

        $postsTable = new DaoTable('posts', 'posts', array('id'), DaoTable::TYPE_PRIMARY);
        $postsTable->fields = array('id', 'title', 'author', 'content', 'type', 'status', 'date');
        $this->assertEquals(
            array(
                'posts'=> $postsTable
            ),
                            $postTrackerParser->getTables());
        $properties = '<?xml version="1.0"?>
        <array>
            <object key="id" class="\Jelix\Dao\Parser\DaoProperty">
                <string p="name" value="id"/>
                <string p="fieldName" value="id"/>
                <string p="table" value="posts"/>
                <string p="datatype" value="int"/>
                <string p="unifiedType" value="integer"/>
                <boolean p="autoIncrement" value="false" />
                <null p="regExp"/>
                <boolean p="required" value="true"/>
                <boolean p="requiredInConditions" value="true"/>
                <boolean p="isPK" value="true" />
                <boolean p="isFK" value="false" />
                <string p="updatePattern" value="" />
                <string p="insertPattern" value="%s" />
                <string p="selectPattern" value="%s" />
                <string p="sequenceName" value="" />
                <null p="maxlength"/>
                <null p="minlength"/>
                <null p="defaultValue" />
                <boolean p="ofPrimaryTable" value="true" />
            </object>
            <object key="title" class="\Jelix\Dao\Parser\DaoProperty">
                <string p="name" value="title"/>
                <string p="fieldName" value="title"/>
                <string p="table" value="posts"/>
                <string p="datatype" value="varchar"/>
                <string p="unifiedType" value="varchar"/>
                <boolean p="autoIncrement" value="false" />
                <null p="regExp"/>
                <boolean p="required" value="true"/>
                <boolean p="requiredInConditions" value="true"/>
                <boolean p="isPK" value="false" />
                <boolean p="isFK" value="false" />
                <string p="updatePattern" value="%s" />
                <string p="insertPattern" value="%s" />
                <string p="selectPattern" value="%s" />
                <string p="sequenceName" value="" />
                <integer p="maxlength" value="245"/>
                <null p="minlength"/>
                <null p="defaultValue" />
                <boolean p="ofPrimaryTable" value="true" />
            </object>
            <object key="author" class="\Jelix\Dao\Parser\DaoProperty">
                <string p="name" value="author"/>
                <string p="fieldName" value="author"/>
                <string p="table" value="posts"/>
                <string p="datatype" value="varchar"/>
                <string p="unifiedType" value="varchar"/>
                <boolean p="autoIncrement" value="false" />
                <null p="regExp"/>
                <boolean p="required" value="false"/>
                <boolean p="requiredInConditions" value="false"/>
                <boolean p="isPK" value="false" />
                <boolean p="isFK" value="false" />
                <string p="updatePattern" value="%s" />
                <string p="insertPattern" value="%s" />
                <string p="selectPattern" value="%s" />
                <string p="sequenceName" value="" />
                <integer p="maxlength" value="50"/>
                <null p="minlength"/>
                <null p="defaultValue"/>
                <boolean p="ofPrimaryTable" value="true" />
            </object>
            <object key="content" class="\Jelix\Dao\Parser\DaoProperty">
                <string p="name" value="content"/>
                <string p="fieldName" value="content"/>
                <string p="table" value="posts"/>
                <string p="datatype" value="longtext"/>
                <string p="unifiedType" value="text"/>
                <boolean p="autoIncrement" value="false" />
                <null p="regExp"/>
                <boolean p="required" value="false"/>
                <boolean p="requiredInConditions" value="false"/>
                <boolean p="isPK" value="false" />
                <boolean p="isFK" value="false" />
                <string p="updatePattern" value="%s" />
                <string p="insertPattern" value="%s" />
                <string p="selectPattern" value="%s" />
                <string p="sequenceName" value="" />
                <null p="maxlength"/>
                <null p="minlength"/>
                <null p="defaultValue"/>
                <boolean p="ofPrimaryTable" value="true" />
            </object>
            <object key="type" class="\Jelix\Dao\Parser\DaoProperty">
                <string p="name" value="type"/>
                <string p="fieldName" value="type"/>
                <string p="table" value="posts"/>
                <string p="datatype" value="varchar"/>
                <string p="unifiedType" value="varchar"/>
                <boolean p="autoIncrement" value="false" />
                <null p="regExp"/>
                <boolean p="required" value="true"/>
                <boolean p="requiredInConditions" value="true"/>
                <boolean p="isPK" value="false" />
                <boolean p="isFK" value="false" />
                <string p="updatePattern" value="%s" />
                <string p="insertPattern" value="%s" />
                <string p="selectPattern" value="%s" />
                <string p="sequenceName" value="" />
                <integer p="maxlength" value="32"/>
                <null p="minlength"/>
                <null p="defaultValue" />
                <boolean p="ofPrimaryTable" value="true" />
            </object>
            <object key="status" class="\Jelix\Dao\Parser\DaoProperty">
                <string p="name" value="status"/>
                <string p="fieldName" value="status"/>
                <string p="table" value="posts"/>
                <string p="datatype" value="varchar"/>
                <string p="unifiedType" value="varchar"/>
                <boolean p="autoIncrement" value="false" />
                <null p="regExp"/>
                <boolean p="required" value="false"/>
                <boolean p="requiredInConditions" value="false"/>
                <boolean p="isPK" value="false" />
                <boolean p="isFK" value="false" />
                <string p="updatePattern" value="%s" />
                <string p="insertPattern" value="%s" />
                <string p="selectPattern" value="%s" />
                <string p="sequenceName" value="" />
                <integer p="maxlength" value="15"/>
                <null p="minlength"/>
                <null p="defaultValue"/>
                <boolean p="ofPrimaryTable" value="true" />
            </object>
            <object key="date" class="\Jelix\Dao\Parser\DaoProperty">
                <string p="name" value="date"/>
                <string p="fieldName" value="date"/>
                <string p="table" value="posts"/>
                <string p="datatype" value="datetime"/>
                <string p="unifiedType" value="datetime"/>
                <boolean p="autoIncrement" value="false" />
                <null p="regExp"/>
                <boolean p="required" value="true"/>
                <boolean p="requiredInConditions" value="true"/>
                <boolean p="isPK" value="false" />
                <boolean p="isFK" value="false" />
                <string p="updatePattern" value="%s" />
                <string p="insertPattern" value="%s" />
                <string p="selectPattern" value="%s" />
                <string p="sequenceName" value="" />
                <null p="maxlength"/>
                <null p="minlength"/>
                <null p="defaultValue" />
                <boolean p="ofPrimaryTable" value="true" />
            </object>
        </array>
        ';

        $this->assertComplexIdenticalStr($postTrackerParser->getProperties(), $properties);
        $this->assertEquals('posts',
                            $postTrackerParser->getPrimaryTable());
        /*
             <object key="countOpenPattern" class="\Jelix\Dao\Parser\DaoMethod">
                <string p="name" value="countOpenPattern"/>
                <string p="type" value="count"/>
                <boolean p="distinct" value="false"/>
                <boolean p="eventBeforeEnabled" value="false"/>
                <boolean p="eventAfterEnabled" value="false"/>
                <object m="getConditions()" class="\Jelix\Dao\DaoConditions">
                    <object p="condition" class="\Jelix\Dao\DaoCondition">
                        <null p="parent" />
                        <array p="conditions">
                           <array>{"field_id" : "type",
                            "value" : "tracker",
                            "operator" : "=",
                            "isExpr" : false,
                            "field_pattern" : ""}
                            </array>
                           <array>
                            {"field_id" : "status",
                            "value" : "open",
                            "operator" : "=",
                            "isExpr" : false,
                            "field_pattern" : "LOWER(%s)"}
                            </array>
                        </array>
                        <array p="group">[]</array>
                    </object>
                    <array p="order">[]</array>
                </object>
                <array m="getParameters ()">[]</array>
                <array m="getParametersDefaultValues ()">[]</array>
                <null m="getLimit ()"/>
                <array m="getValues ()">[]</array>
                <null m="getProcStock ()"/>
                <null m="getBody ()"/>
            </object>
        
            <object key="findOpenPattern" class="\Jelix\Dao\Parser\DaoMethod">
                <string p="name" value="findOpenPattern"/>
                <string p="type" value="select"/>
                <boolean p="distinct" value="false"/>
                <boolean p="eventBeforeEnabled" value="false"/>
                <boolean p="eventAfterEnabled" value="false"/>
                <object m="getConditions()" class="\Jelix\Dao\DaoConditions">
                    <object p="condition" class="\Jelix\Dao\DaoCondition">
                        <null p="parent" />
                        <array p="conditions">
                           <array>
                            {"field_id" : "type",
                            "value" : "tracker",
                            "operator" : "=",
                            "isExpr" : false,
                            "field_pattern" : ""}
                            </array>
                           <array>
                            {"field_id" : "status",
                            "value" : "open",
                            "operator" : "=",
                            "isExpr" : false,
                            "field_pattern" : "LOWER(%s)"}
                            </array>
                        </array>
                        <array p="group">[]</array>
                    </object>
                    <array p="order">[]</array>
                </object>
                <array m="getParameters ()">[]</array>
                <array m="getParametersDefaultValues ()">[]</array>
                <null m="getLimit ()"/>
                <array m="getValues ()">[]</array>
                <null m="getProcStock ()"/>
                <null m="getBody ()"/>
            </object>
        */
        $methods = '<?xml version="1.0"?>
        <array>
            <object key="findAll" class="\Jelix\Dao\Parser\DaoMethod">
                <string p="name" value="findAll"/>
                <string p="type" value="select"/>
                <boolean p="distinct" value="false"/>
                <boolean p="eventBeforeEnabled" value="false"/>
                <boolean p="eventAfterEnabled" value="false"/>
                <object m="getConditions()" class="\Jelix\Dao\DaoConditions">
                    <object p="condition" class="\Jelix\Dao\DaoCondition">
                        <null p="parent" />
                        <array p="conditions">
                           <array>
                            {"field_id" : "type",
                            "value" : "tracker",
                            "operator" : "=",
                            "isExpr" : false,
                            "field_pattern" : "",
                            "dbType" : ""}
                            </array>
                        </array>
                        <array p="group">[]</array>
                    </object>
                    <array p="order">[]</array>
                </object>
                <array m="getParameters ()">[]</array>
                <array m="getParametersDefaultValues ()">[]</array>
                <null m="getLimit ()"/>
                <array m="getValues ()">[]</array>
                <null m="getProcStock ()"/>
                <null m="getBody ()"/>
            </object>
            <object key="countOpen" class="\Jelix\Dao\Parser\DaoMethod">
                <string p="name" value="countOpen"/>
                <string p="type" value="count"/>
                <boolean p="distinct" value="false"/>
                <boolean p="eventBeforeEnabled" value="false"/>
                <boolean p="eventAfterEnabled" value="false"/>
                <object m="getConditions()" class="\Jelix\Dao\DaoConditions">
                    <object p="condition" class="\Jelix\Dao\DaoCondition">
                        <null p="parent" />
                        <array p="conditions">
                           <array>
                            {"field_id" : "type",
                            "value" : "tracker",
                            "operator" : "=",
                            "isExpr" : false,
                            "field_pattern" : "",
                            "dbType" : ""}
                            </array>
                           <array>
                            {"field_id" : "status",
                            "value" : "open",
                            "operator" : "=",
                            "isExpr" : false,
                            "field_pattern" : "",
                            "dbType" : ""}
                            </array>
                        </array>
                        <array p="group">[]</array>
                    </object>
                    <array p="order">[]</array>
                </object>
                <array m="getParameters ()">[]</array>
                <array m="getParametersDefaultValues ()">[]</array>
                <null m="getLimit ()"/>
                <array m="getValues ()">[]</array>
                <null m="getProcStock ()"/>
                <null m="getBody ()"/>
            </object>
            
            <object key="findOpen" class="\Jelix\Dao\Parser\DaoMethod">
                <string p="name" value="findOpen"/>
                <string p="type" value="select"/>
                <boolean p="distinct" value="false"/>
                <boolean p="eventBeforeEnabled" value="false"/>
                <boolean p="eventAfterEnabled" value="false"/>
                <object m="getConditions()" class="\Jelix\Dao\DaoConditions">
                    <object p="condition" class="\Jelix\Dao\DaoCondition">
                        <null p="parent" />
                        <array p="conditions">
                           <array>
                            {"field_id" : "type",
                            "value" : "tracker",
                            "operator" : "=",
                            "isExpr" : false,
                            "field_pattern" : "",
                            "dbType" : ""}
                            </array>
                           <array>{
                            "field_id" : "status",
                            "value" : "open",
                            "operator" : "=",
                            "isExpr" : false,
                            "field_pattern" : "",
                            "dbType" : ""}
                            </array>
                        </array>
                        <array p="group">[]</array>
                    </object>
                    <array p="order">[]</array>
                </object>
                <array m="getParameters ()">[]</array>
                <array m="getParametersDefaultValues ()">[]</array>
                <null m="getLimit ()"/>
                <array m="getValues ()">[]</array>
                <null m="getProcStock ()"/>
                <null m="getBody ()"/>
            </object>
            
        </array>';
        $this->assertComplexIdenticalStr($postTrackerParser->getMethods(), $methods);
        $this->assertEquals(array(),
                            $postTrackerParser->getOuterJoins());
        $this->assertEquals(array(),
                            $postTrackerParser->getInnerJoins());
        $this->assertEquals('jelix_tests~postTracker',
                            $postTrackerParser->getCustomRecord()->toString());
        $daos = $postTrackerParser->getImportedDao();
        $this->assertEquals('jelix_tests~posts',
                            $daos[0]->toString());
    }

    public function testImportWithRedefinedProperties() {
        $this->launchTestImportWithRedefinedProperties('jelix_tests~post_blog');
    }

    public function testImportWithRedefinedPropertiesAndTable() {
        // with a dao that redeclare the table
        $this->launchTestImportWithRedefinedProperties('jelix_tests~post_blog2');
    }
    
    protected function launchTestImportWithRedefinedProperties($daoName) {
        $postSel = new jSelectorDao('jelix_tests~posts', '');
        $blogSel = new jSelectorDao($daoName, '');


        $context = new jDaoContext('', jDb::getConnection(''));
        $compiler = new \Jelix\Dao\Generator\Compiler();

        $postBlogParser = $compiler->parse($blogSel, $context);

        $postsTable = new DaoTable('posts', 'posts', array('id'), DaoTable::TYPE_PRIMARY);
        $postsTable->fields = array('id', 'title', 'author', 'content', 'type', 'status', 'date', 'email');
        $this->assertEquals(
            array(
                'posts'=> $postsTable
            ),
            $postBlogParser->getTables());
        $properties = '<?xml version="1.0"?>
        <array>
            <object key="id" class="\Jelix\Dao\Parser\DaoProperty">
                <string p="name" value="id"/>
                <string p="fieldName" value="id"/>
                <string p="table" value="posts"/>
                <string p="datatype" value="int"/>
                <string p="unifiedType" value="integer"/>
                <boolean p="autoIncrement" value="false" />
                <null p="regExp"/>
                <boolean p="required" value="true"/>
                <boolean p="requiredInConditions" value="true"/>
                <boolean p="isPK" value="true" />
                <boolean p="isFK" value="false" />
                <string p="updatePattern" value="" />
                <string p="insertPattern" value="%s" />
                <string p="selectPattern" value="%s" />
                <string p="sequenceName" value="" />
                <null p="maxlength"/>
                <null p="minlength"/>
                <null p="defaultValue" />
                <boolean p="ofPrimaryTable" value="true" />
            </object>
            <object key="title" class="\Jelix\Dao\Parser\DaoProperty">
                <string p="name" value="title"/>
                <string p="fieldName" value="title"/>
                <string p="table" value="posts"/>
                <string p="datatype" value="varchar"/>
                <string p="unifiedType" value="varchar"/>
                <boolean p="autoIncrement" value="false" />
                <null p="regExp"/>
                <boolean p="required" value="true"/>
                <boolean p="requiredInConditions" value="true"/>
                <boolean p="isPK" value="false" />
                <boolean p="isFK" value="false" />
                <string p="updatePattern" value="%s" />
                <string p="insertPattern" value="%s" />
                <string p="selectPattern" value="%s" />
                <string p="sequenceName" value="" />
                <integer p="maxlength" value="245"/>
                <null p="minlength"/>
                <null p="defaultValue" />
                <boolean p="ofPrimaryTable" value="true" />
            </object>
            <object key="author" class="\Jelix\Dao\Parser\DaoProperty">
                <string p="name" value="author"/>
                <string p="fieldName" value="author"/>
                <string p="table" value="posts"/>
                <string p="datatype" value="varchar"/>
                <string p="unifiedType" value="varchar"/>
                <boolean p="autoIncrement" value="false" />
                <null p="regExp"/>
                <boolean p="required" value="false"/>
                <boolean p="requiredInConditions" value="false"/>
                <boolean p="isPK" value="false" />
                <boolean p="isFK" value="false" />
                <string p="updatePattern" value="%s" />
                <string p="insertPattern" value="%s" />
                <string p="selectPattern" value="%s" />
                <string p="sequenceName" value="" />
                <integer p="maxlength" value="100"/>
                <null p="minlength"/>
                <null p="defaultValue"/>
                <boolean p="ofPrimaryTable" value="true" />
            </object>
            <object key="email" class="\Jelix\Dao\Parser\DaoProperty">
                <string p="name" value="email"/>
                <string p="fieldName" value="email"/>
                <string p="table" value="posts"/>
                <string p="datatype" value="varchar"/>
                <string p="unifiedType" value="varchar"/>
                <boolean p="autoIncrement" value="false" />
                <null p="regExp"/>
                <boolean p="required" value="false"/>
                <boolean p="requiredInConditions" value="false"/>
                <boolean p="isPK" value="false" />
                <boolean p="isFK" value="false" />
                <string p="updatePattern" value="%s" />
                <string p="insertPattern" value="%s" />
                <string p="selectPattern" value="%s" />
                <string p="sequenceName" value="" />
                <integer p="maxlength" value="120"/>
                <null p="minlength"/>
                <null p="defaultValue"/>
                <boolean p="ofPrimaryTable" value="true" />
            </object>
            <object key="content" class="\Jelix\Dao\Parser\DaoProperty">
                <string p="name" value="content"/>
                <string p="fieldName" value="content"/>
                <string p="table" value="posts"/>
                <string p="datatype" value="longtext"/>
                <string p="unifiedType" value="text"/>
                <boolean p="autoIncrement" value="false" />
                <null p="regExp"/>
                <boolean p="required" value="false"/>
                <boolean p="requiredInConditions" value="false"/>
                <boolean p="isPK" value="false" />
                <boolean p="isFK" value="false" />
                <string p="updatePattern" value="%s" />
                <string p="insertPattern" value="%s" />
                <string p="selectPattern" value="%s" />
                <string p="sequenceName" value="" />
                <null p="maxlength"/>
                <null p="minlength"/>
                <null p="defaultValue"/>
                <boolean p="ofPrimaryTable" value="true" />
            </object>
            <object key="type" class="\Jelix\Dao\Parser\DaoProperty">
                <string p="name" value="type"/>
                <string p="fieldName" value="type"/>
                <string p="table" value="posts"/>
                <string p="datatype" value="varchar"/>
                <string p="unifiedType" value="varchar"/>
                <boolean p="autoIncrement" value="false" />
                <null p="regExp"/>
                <boolean p="required" value="true"/>
                <boolean p="requiredInConditions" value="true"/>
                <boolean p="isPK" value="false" />
                <boolean p="isFK" value="false" />
                <string p="updatePattern" value="%s" />
                <string p="insertPattern" value="%s" />
                <string p="selectPattern" value="%s" />
                <string p="sequenceName" value="" />
                <integer p="maxlength" value="32"/>
                <null p="minlength"/>
                <null p="defaultValue" />
                <boolean p="ofPrimaryTable" value="true" />
            </object>
            <object key="status" class="\Jelix\Dao\Parser\DaoProperty">
                <string p="name" value="status"/>
                <string p="fieldName" value="status"/>
                <string p="table" value="posts"/>
                <string p="datatype" value="varchar"/>
                <string p="unifiedType" value="varchar"/>
                <boolean p="autoIncrement" value="false" />
                <null p="regExp"/>
                <boolean p="required" value="false"/>
                <boolean p="requiredInConditions" value="false"/>
                <boolean p="isPK" value="false" />
                <boolean p="isFK" value="false" />
                <string p="updatePattern" value="%s" />
                <string p="insertPattern" value="%s" />
                <string p="selectPattern" value="%s" />
                <string p="sequenceName" value="" />
                <integer p="maxlength" value="15"/>
                <null p="minlength"/>
                <null p="defaultValue"/>
                <boolean p="ofPrimaryTable" value="true" />
            </object>
            <object key="date" class="\Jelix\Dao\Parser\DaoProperty">
                <string p="name" value="date"/>
                <string p="fieldName" value="date"/>
                <string p="table" value="posts"/>
                <string p="datatype" value="datetime"/>
                <string p="unifiedType" value="datetime"/>
                <boolean p="autoIncrement" value="false" />
                <null p="regExp"/>
                <boolean p="required" value="true"/>
                <boolean p="requiredInConditions" value="true"/>
                <boolean p="isPK" value="false" />
                <boolean p="isFK" value="false" />
                <string p="updatePattern" value="%s" />
                <string p="insertPattern" value="%s" />
                <string p="selectPattern" value="%s" />
                <string p="sequenceName" value="" />
                <null p="maxlength"/>
                <null p="minlength"/>
                <null p="defaultValue" />
                <boolean p="ofPrimaryTable" value="true" />
            </object>
        </array>
        ';

        $this->assertComplexIdenticalStr($postBlogParser->getProperties(), $properties);
        $this->assertEquals('posts',
                            $postBlogParser->getPrimaryTable());
        
        $this->assertEquals(array(), $postBlogParser->getMethods());
        $this->assertEquals(array(),
                            $postBlogParser->getOuterJoins());
        $this->assertEquals(array(),
                            $postBlogParser->getInnerJoins());
        $this->assertEquals('\JelixTests\Tests\Dao\PostBlog',
                            $postBlogParser->getCustomRecord()->toString());
        $daos = $postBlogParser->getImportedDao();
        $this->assertEquals('jelix_tests~posts',
                            $daos[0]->toString());
    }
}

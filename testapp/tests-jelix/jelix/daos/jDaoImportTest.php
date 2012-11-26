<?php

class jDaoImportTest extends jUnitTestCase {

    public function setUp () {
        self::initJelixConfig();
    }

    public function testExtendedRecords() {
        $post = jDao::createRecord('testapp~posts');
        $blogPost = jDao::createRecord('testapp~post_blog');
        $trackerPost = jDao::createRecord('testapp~post_tracker');
        
        $this->assertInstanceOf('postDaoRecord', $post);
        $this->assertInstanceOf('postDaoRecord', $blogPost);
        $this->assertInstanceOf('postDaoRecord', $trackerPost);
        $this->assertInstanceOf('postBlogDaoRecord', $blogPost);
        $this->assertInstanceOf('postTrackerDaoRecord', $trackerPost);
    }
    
    public function testImportedEvents() {
        $postSel = new jSelectorDao('testapp~posts', '');
        $blogSel = new jSelectorDao('testapp~post_blog', '');
        $trackerSel = new jSelectorDao('testapp~post_tracker', '');
        $dbtools = jApp::loadPlugin($postSel->driver, 'db', '.dbtools.php', $postSel->driver.'DbTools');

        $postParser = new jDaoParser($postSel);
        $postXml = new SimpleXMLElement(file_get_contents($postSel->getPath()));
        $postParser->parse($postXml, $dbtools);
        $this->assertEquals(array('deletebefore'), $postParser->getEvents());

       	$blogParser = new jDaoParser($blogSel);
       	$blogXml = new SimpleXMLElement(file_get_contents($blogSel->getPath()));
       	$blogParser->parse($blogXml, $dbtools);
        $this->assertEquals(array('deletebefore'), $blogParser->getEvents());
       	
       	$trackerParser = new jDaoParser($trackerSel);
       	$trackerXml = new SimpleXMLElement(file_get_contents($trackerSel->getPath()));
        $trackerParser->parse($trackerXml, $dbtools);
        $this->assertEquals(array('deletebefore', 'insertbefore', 'updatebefore'), $trackerParser->getEvents());
    }
}

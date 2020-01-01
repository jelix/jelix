<?php

class feedReaderTest extends \Jelix\UnitTests\UnitTestCase
{
    public static function setUpBeforeClass() : void {
        self::initJelixConfig();
    }

    function testRSSCall() {

        $reader = new jRSS20Reader(TESTAPP_URL.'index.php/testapp/syndication/rss');
        $infos = $reader->getInfos();

        $this->assertEquals('Test syndication testapp jelix', $infos->title);
        $this->assertEquals('test de syndication en rss 2.0 dans testapp', $infos->description);
        $this->assertEquals('', $infos->ttl);

        $items = $reader->getItems();
        $this->assertEquals(3, count($items));

        $this->assertEquals('foo1', $items[0]->title);
        $this->assertEquals('http://testapp.jelix.org/1', $items[0]->link);
        $this->assertEquals('2006-11-11 12:32:41', $items[0]->published);

        $this->assertEquals('foo2', $items[1]->title);
        $this->assertEquals('http://testapp.jelix.org/2', $items[1]->link);
        $this->assertEquals('2006-11-11 12:32:42', $items[1]->published);

        $this->assertEquals('foo3', $items[2]->title);
        $this->assertEquals('http://testapp.jelix.org/3', $items[2]->link);
        $this->assertEquals('2006-11-11 12:32:43', $items[2]->published);
    }


    function testAtomCall() {

        $reader = new jAtom10Reader(TESTAPP_URL.'index.php/testapp/syndication/atom');
        $infos = $reader->getInfos();

        $this->assertEquals('Test syndication testapp jelix', $infos->title);
        $this->assertEquals('test de syndication en atom 1.0 dans testapp', $infos->description);

        $items = $reader->getItems();
        $this->assertEquals(3, count($items));

        $this->assertEquals('foo1', $items[0]->title);
        $this->assertEquals('http://testapp.jelix.org/1', $items[0]->link);
        $this->assertEquals('2006-11-11 12:32:41', $items[0]->published);

        $this->assertEquals('foo2', $items[1]->title);
        $this->assertEquals('http://testapp.jelix.org/2', $items[1]->link);
        $this->assertEquals('2006-11-11 12:32:42', $items[1]->published);

        $this->assertEquals('foo3', $items[2]->title);
        $this->assertEquals('http://testapp.jelix.org/3', $items[2]->link);
        $this->assertEquals('2006-11-11 12:32:43', $items[2]->published);
    }

}
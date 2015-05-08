<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @copyright   2015 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jFileTest extends jUnitTestCase {



    public function testShortestPath() {
        $this->assertEquals('.', jFile::shortestPath('/', '/'));
        $this->assertEquals('.', jFile::shortestPath('/aaaa', '/aaaa'));
        $this->assertEquals('.', jFile::shortestPath('/aaaa', '/aaaa/.'));
        $this->assertEquals('.', jFile::shortestPath('/aaaa/.', '/aaaa/.'));
        $this->assertEquals('.', jFile::shortestPath('/aaaa/.', '/aaaa'));
        $this->assertEquals('.', jFile::shortestPath('/aaaa/bbbb', '/aaaa/bbbb'));
        $this->assertEquals('aaaa', jFile::shortestPath('/', '/aaaa/'));
        $this->assertEquals('..', jFile::shortestPath('/aaaa', '/'));
        $this->assertEquals('../../dddd', jFile::shortestPath('/aaaa/bbbb/cccc', '/aaaa/dddd/'));
        $this->assertEquals('../../dddd', jFile::shortestPath('/aaaa/bbbb/cccc', '/aaaa/dddd/'));
        $this->assertEquals('../../dddd/eeeee', jFile::shortestPath('/aaaa/bbbb/cccc', '/aaaa/dddd/eeeee'));
        $this->assertEquals('cccc', jFile::shortestPath('/aaaa/bbbb', '/aaaa/bbbb/cccc'));
        $this->assertEquals('cccc/eeeee', jFile::shortestPath('/aaaa/bbbb', '/aaaa/bbbb/cccc/eeeee'));
        $this->assertEquals('..', jFile::shortestPath('/aaaa/bbbb/cccc', '/aaaa/bbbb/'));

        $this->assertEquals('.', jFile::shortestPath('C:/', 'C:/'));
        $this->assertEquals('.', jFile::shortestPath('C:/aaaa', 'C:/aaaa'));
        $this->assertEquals('.', jFile::shortestPath('C:/aaaa/bbbb', 'C:/aaaa/bbbb'));
        $this->assertEquals('aaaa', jFile::shortestPath('C:/', 'C:/aaaa/'));
        $this->assertEquals('..', jFile::shortestPath('C:/aaaa', 'C:/'));
        $this->assertEquals('../../dddd', jFile::shortestPath('C:/aaaa/bbbb/cccc', 'C:/aaaa/dddd/'));
        $this->assertEquals('../../dddd/eeeee', jFile::shortestPath('C:/aaaa/bbbb/cccc', 'C:/aaaa/dddd/eeeee'));
        $this->assertEquals('cccc', jFile::shortestPath('C:/aaaa/bbbb', 'C:/aaaa/bbbb/cccc'));
        $this->assertEquals('cccc/eeeee', jFile::shortestPath('C:/aaaa/bbbb', 'C:/aaaa/bbbb/cccc/eeeee'));
        $this->assertEquals('..', jFile::shortestPath('C:/aaaa/bbbb/cccc', 'C:/aaaa/bbbb/'));
        $this->assertEquals('D:/aaaa/dddd', jFile::shortestPath('C:/aaaa/bbbb/cccc', 'D:/aaaa/dddd/'));
        $this->assertEquals('D:/', jFile::shortestPath('C:/aaaa/bbbb/cccc', 'D:/'));
    }

    public function testNormalizePath() {
        $this->assertEquals('/', jFile::normalizePath('/'));
        $this->assertEquals('/aaa/bbb/ccc', jFile::normalizePath('/aaa/bbb/ccc/'));
        $this->assertEquals('/aaa/bbb/ccc', jFile::normalizePath('/aaa////bbb/ccc/'));
        $this->assertEquals('/aaa/bbb/ccc', jFile::normalizePath('/aaa/./bbb/ccc/'));
        $this->assertEquals('/aaa/bbb/ccc', jFile::normalizePath('/aaa/./bbb/ccc/.'));
        $this->assertEquals('/aaa/bbb/ccc', jFile::normalizePath('/aaa/bbb/ccc/.'));
        $this->assertEquals('/aaa/bbb/ccc', jFile::normalizePath('/aaa/./bbb/./././ccc/'));
        $this->assertEquals('/aaa/ccc', jFile::normalizePath('/aaa/bbb/../ccc/'));
        $this->assertEquals('/ccc', jFile::normalizePath('/aaa/bbb/../../ccc/'));
        $this->assertEquals('/aaa/ccc', jFile::normalizePath('/aaa/./bbb/../ccc/'));
        $this->assertEquals('/ccc', jFile::normalizePath('/aaa/bbb/../../../../../ccc/'));

        $this->assertEquals('C:/', jFile::normalizePath('C:\\'));
        $this->assertEquals('C:/aaa/bbb/ccc', jFile::normalizePath('C:\\aaa\\bbb\\ccc\\'));
        $this->assertEquals('C:/aaa/bbb/ccc', jFile::normalizePath('C:/aaa////bbb/ccc/'));
        $this->assertEquals('C:/aaa/bbb/ccc', jFile::normalizePath('C:/aaa/./bbb/ccc/'));
        $this->assertEquals('C:/aaa/bbb/ccc', jFile::normalizePath('C:/aaa/./bbb/./././ccc/'));
        $this->assertEquals('C:/aaa/ccc', jFile::normalizePath('C:/aaa/bbb/../ccc/'));
        $this->assertEquals('C:/ccc', jFile::normalizePath('C:/aaa/bbb/../../ccc/'));
        $this->assertEquals('C:/aaa/ccc', jFile::normalizePath('C:/aaa/./bbb/../ccc/'));
        $this->assertEquals('C:/ccc', jFile::normalizePath('C:/aaa/bbb/../../../../../ccc/'));
    }
}


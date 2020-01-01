<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @copyright   2015 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jFileTest extends \Jelix\UnitTests\UnitTestCase {

    function testParseJelixPath() {
        $this->assertEquals(LIB_PATH.'foo', jFile::parseJelixPath('lib:foo'));
        $this->assertEquals(jApp::appPath('foo'), jFile::parseJelixPath('app:foo'));
        $this->assertEquals(jApp::varPath('foo'), jFile::parseJelixPath('var:foo'));
        $this->assertEquals(jApp::tempPath('foo'), jFile::parseJelixPath('temp:foo'));
        $this->assertEquals(jApp::wwwPath('foo'), jFile::parseJelixPath('www:foo'));
    }

}


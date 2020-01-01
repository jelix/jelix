<?php
/**
* @package     testapp
* @subpackage  testsjelix
* @author      Laurent Jouanneau
* @copyright   2014 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class configTest extends \Jelix\UnitTests\UnitTestCase {

    public function testConfig(){
        self::initJelixConfig();

        $this->assertEquals(0664, jApp::config()->chmodFile);
        $this->assertEquals(0775, jApp::config()->chmodDir);
    }
}

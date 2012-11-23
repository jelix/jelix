<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Julien Issler
* @contributor Laurent Jouanneau
* @copyright   2009 Julien Issler, 2012 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jDb_MysqlTest extends jUnitTestCaseDb {

    public function setUp() {
        self::initJelixConfig();
    }

    function testFieldNameEnclosure(){
        $this->assertEquals('`toto`', jDb::getConnection()->encloseName('toto'));
    }

}
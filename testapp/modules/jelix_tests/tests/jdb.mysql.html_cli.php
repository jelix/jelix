<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Julien Issler
* @contributor
* @copyright   2009 Julien Issler
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class UTjDbMysql extends jUnitTestCaseDb {

    function testFieldNameEnclosure(){
        $this->assertEqualOrDiff(jDb::getConnection()->encloseName('toto'),'`toto`');
    }

}
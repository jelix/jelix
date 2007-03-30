<?php
/**
* @package     testapp
* @subpackage  unittest module
* @author      Jouanneau Laurent
* @contributor
* @copyright   2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


require_once(dirname(__FILE__).'/junittestcasedb.class.php');

class UTjacl extends jUnitTestCaseDb {


    public function test(){
        $this->sendMessage('no test for the moment');
    }


}

?>
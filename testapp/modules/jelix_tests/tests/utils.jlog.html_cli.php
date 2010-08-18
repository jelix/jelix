<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Jouanneau Laurent
* @contributor Julien Issler
* @copyright   2007 Jouanneau laurent
* @copyright   2010 Julien Issler
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class UTjlog extends UnitTestCase {

    public function testLogFile(){
        $file = JELIX_APP_LOG_PATH.'test.log';
        if(file_exists($file))
            file_put_contents($file,'');

        global $gJConfig;
        $gJConfig->logfiles['test'] = 'test.log';

        jLog::log('aaa','test');
        $this->assertTrue(file_exists($file));
        $this->assertTrue(strpos(file_get_contents($file), 'aaa') !==false);

        jLog::log('bbb','test');
        $this->assertTrue(strpos(file_get_contents($file), 'aaa') !==false);
        $this->assertTrue(strpos(file_get_contents($file), 'bbb') !==false);
    }

    public function testLogResponse(){

        global $gJConfig;
        global $gJCoord;

        $gJConfig->logfiles['test'] = '!response';
        $gJCoord->logMessages = array();

        jLog::log('aaa','test');
        $this->assertTrue(isset($gJCoord->logMessages['response']));
        $this->assertTrue(isset($gJCoord->logMessages['response'][0]));
        $this->assertTrue($gJCoord->logMessages['response'][0] == 'aaa');

        jLog::log('bbb','test');
        $this->assertTrue(isset($gJCoord->logMessages['response'][0]));
        $this->assertTrue(isset($gJCoord->logMessages['response'][1]));
        $this->assertTrue($gJCoord->logMessages['response'][0] == 'aaa');
        $this->assertTrue($gJCoord->logMessages['response'][1] == 'bbb');

        $gJCoord->logMessages = array();
    }

    public function testLogFirebug(){

        global $gJConfig;
        global $gJCoord;

        $gJConfig->logfiles['test'] = '!firebug';
        $gJCoord->logMessages = array();

        jLog::log('aaa','test');
        $this->assertTrue(isset($gJCoord->logMessages['firebug']));
        $this->assertTrue(isset($gJCoord->logMessages['firebug'][0]));
        $this->assertTrue($gJCoord->logMessages['firebug'][0] == 'aaa');

        jLog::log('bbb','test');
        $this->assertTrue(isset($gJCoord->logMessages['firebug'][0]));
        $this->assertTrue(isset($gJCoord->logMessages['firebug'][1]));
        $this->assertTrue($gJCoord->logMessages['firebug'][0] == 'aaa');
        $this->assertTrue($gJCoord->logMessages['firebug'][1] == 'bbb');

        $gJCoord->logMessages = array();
    }

}

?>
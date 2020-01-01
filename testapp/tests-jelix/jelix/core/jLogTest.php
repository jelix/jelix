<?php
/**
* @package     testapp
* @subpackage  testsjelix
* @author      Laurent Jouanneau
* @contributor Julien Issler
* @copyright   2007-2012 Laurent Jouanneau
* @copyright   2010 Julien Issler
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jLogTest extends \Jelix\UnitTests\UnitTestCase {

    public function testLogFile(){
        self::initJelixConfig();
        $file = jApp::logPath('test.log');
        if(file_exists($file))
            file_put_contents($file,'');

        jApp::config()->logger['test'] = 'file';
        jApp::config()->fileLogger['test'] = 'test.log';

        jLog::log('aaa','test');
        $this->assertTrue(file_exists($file));
        $this->assertTrue(strpos(file_get_contents($file), 'aaa') !==false);

        jLog::log('bbb','test');
        $this->assertTrue(strpos(file_get_contents($file), 'aaa') !==false);
        $this->assertTrue(strpos(file_get_contents($file), 'bbb') !==false);
    }
}

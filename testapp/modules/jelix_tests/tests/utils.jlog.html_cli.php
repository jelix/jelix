<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor Julien Issler
* @copyright   2007-2010 Laurent Jouanneau
* @copyright   2010 Julien Issler
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class UTjlog extends UnitTestCase {

    public function testLogFile(){
        $file = jApp::logPath('test.log');
        if(file_exists($file))
            file_put_contents($file,'');

        global $gJConfig;
        $gJConfig->logger['test'] = 'file';
        $gJConfig->fileLogger['test'] = 'test.log';

        jLog::log('aaa','test');
        $this->assertTrue(file_exists($file));
        $this->assertTrue(strpos(file_get_contents($file), 'aaa') !==false);

        jLog::log('bbb','test');
        $this->assertTrue(strpos(file_get_contents($file), 'aaa') !==false);
        $this->assertTrue(strpos(file_get_contents($file), 'bbb') !==false);
    }
}

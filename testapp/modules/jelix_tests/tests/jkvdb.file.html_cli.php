<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @copyright   2010 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(dirname(__FILE__).'/jkvdb.lib.php');

/**
* Tests API jKVDb
* @package     testapp
* @subpackage  jelix_tests module
*/

class UTjKVDbFile extends UTjKVDb {

    protected $profile = 'usingfile';

    public function setUp (){
        if (file_exists(JELIX_APP_TEMP_PATH.'kvfiles/tests/'))
            jFile::removeDir(JELIX_APP_TEMP_PATH.'kvfiles/tests/',false);
    }

    public function tearDown() {
    }
}

?>
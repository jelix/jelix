<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @copyright   201 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(__DIR__.'/jkvdb.lib.php');

/**
* Tests API jKVDb
* @package     testapp
* @subpackage  jelix_tests module
*/

class jkvdb_dbaTest extends jKVDbTest {

    static function setUpBeforeClass() {
        $p = jApp::tempPath('kvdbdba.db4');
        if (file_exists($p)) {
            unlink($p);
        }
    }

    function setUp (){
        $this->profile = 'usingdba';
        $this->supportTTL = false;
        self::initJelixConfig();
        if (!$this->_kvdbSetUp())
            return;

        //$this->mmc = dba_open(jApp::tempPath('kvdbdba.db4'), 'rl', 'db4');

        parent::setUp();
    }

    public function tearDown() {
        if ($this->mmc) {
            dba_close($this->mmc);
            $this->mmc = null;
        }
    }
}

?>

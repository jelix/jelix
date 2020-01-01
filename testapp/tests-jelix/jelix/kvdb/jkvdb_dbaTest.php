<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @copyright   2010-2015 Laurent Jouanneau
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

    static function setUpBeforeClass() : void {
        $p = jApp::tempPath('kvdbdba.db4');
        if (file_exists($p)) {
            unlink($p);
        }
    }

    function setUp() : void {
        $this->profile = 'usingdba';
        $this->supportTTL = false;
        self::initJelixConfig();
        if (!$this->_kvdbSetUp())
            return;
        if (!in_array('db4', dba_handlers())) {
            $this->markTestSkipped(get_class($this).' cannot be run: no db4 handler');
            return;
        }

        parent::setUp();
    }

    public function tearDown() : void {
        if ($this->mmc) {
            dba_close($this->mmc);
            $this->mmc = null;
        }
    }
}


<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2009 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
* @since 1.2
*/

require_once(__DIR__.'/installer.lib.php');


class UTjInstaller extends UnitTestCase {

    protected $installer;
    protected $instReport;

    public function setUp() {
        $this->instReport = new testInstallReporter();
        //$this->installer = new testInstallerMain($this->instReport);
    }

    public function tearDown() {
        $this->instReport = null;
        $this->installer = null;
    }


    public function testOneModule() {
        //$this->installer->initForTest();
    }

}


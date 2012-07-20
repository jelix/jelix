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

class testInstallerMisc extends jInstaller {
    
    
}

class UTjinstallermisc extends UnitTestCase {

    public function setUp() {
        jApp::saveContext();
    }

    public function tearDown() {
        jApp::restoreContext();
    }


    public function testDummy() {

    }

}


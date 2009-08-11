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

class testInstallerBase extends jInstallerComponentBase {
    
    function compver($v1, $v2){
        return $this->compareVersion($v1, $v2);
    }

    function getInstaller($config)   { return null;}
    function getUpgraders($config) { return array();}

}


class UTjinstallermisc extends UnitTestCase {

    public function setUp() {
    }

    public function testCompareVersion() {
        
        $inst = new testInstallerBase('','','','','',null);
        
        // 0 = equals
        // -1 : v1 < v2
        // 1 : v1 > v2
        $this->assertEqual(0, $inst->compver('1.0','1.0'));
        $this->assertEqual(1, $inst->compver('1.1','1.0'));
        $this->assertEqual(-1, $inst->compver('1.0','1.1'));
        $this->assertEqual(-1, $inst->compver('1.1','1.1.1'));
        $this->assertEqual(1, $inst->compver('1.1.2','1.1'));
        $this->assertEqual(1, $inst->compver('1.2','1.2b'));
        $this->assertEqual(1, $inst->compver('1.2','1.2a'));
        $this->assertEqual(1, $inst->compver('1.2','1.2RC'));
        $this->assertEqual(1, $inst->compver('1.2','1.2bETA'));
        $this->assertEqual(1, $inst->compver('1.2','1.2alpha'));

        $this->assertEqual(-1, $inst->compver('1.2b','1.2'));
        $this->assertEqual(-1, $inst->compver('1.2a','1.2'));
        $this->assertEqual(-1, $inst->compver('1.2RC','1.2'));
        $this->assertEqual(-1, $inst->compver('1.2bEta','1.2'));
        $this->assertEqual(-1, $inst->compver('1.2alpha','1.2'));

        
        $this->assertEqual(-1, $inst->compver('1.2b1','1.2b2'));
        $this->assertEqual(-1, $inst->compver('1.2B1','1.2b2'));
        $this->assertEqual(1, $inst->compver('1.2b2','1.2b1'));
        $this->assertEqual(1, $inst->compver('1.2b2','1.2b2-dev'));
        $this->assertEqual(-1, $inst->compver('1.2b2-dev','1.2b2'));
        $this->assertEqual(-1, $inst->compver('1.2b2-dev.2324','1.2b2'));
        $this->assertEqual(0, $inst->compver('1.2b2pre','1.2b2-dev'));
        $this->assertEqual(1, $inst->compver('1.2b2pre.4','1.2b2-dev'));
        $this->assertEqual(-1, $inst->compver('1.2b2pre.4','1.2b2-dev.9'));
        $this->assertEqual(-1, $inst->compver('1.2b2pre','1.2b2-dev.9'));
        $this->assertEqual(-1, $inst->compver('1.2RC1','1.2RC2'));
        
        $this->assertEqual(-1, $inst->compver('1.2RC-dev','1.2RC'));
        $this->assertEqual(1, $inst->compver('1.2RC','1.2RC-dev'));

        $this->assertEqual(0, $inst->compver('1.*','1'));
        $this->assertEqual(0, $inst->compver('1.1.*','1.1.1'));
        $this->assertEqual(0, $inst->compver('1.1.2','1.1.*'));
        $this->assertEqual(0, $inst->compver('1.1.*','1.1'));
        $this->assertEqual(0, $inst->compver('1.1','1.1.*'));
        $this->assertEqual(-1, $inst->compver('1.1.*','1.2'));
        $this->assertEqual(-1, $inst->compver('1.1','1.2.*'));
        
        $this->assertEqual(0, $inst->compver('1.1','*'));
        $this->assertEqual(0, $inst->compver('*','1.1'));
        
    }

}


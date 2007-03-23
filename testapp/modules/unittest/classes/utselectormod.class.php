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

class UTSelectorMod extends UnitTestCase {

    function testGoodSel() {
        $sels=array(
            "testapp~sommaire"=>array('testapp','sommaire'),
        );
        $this->runtest($sels , 'jSelectorZone');
        $sels=array(
            "unittestservice"=>array('unittest','unittestservice'),
        );
        $this->runtest($sels, 'jSelectorClass');
    }

    /*
    selector.invalid.syntax=(10)Syntax du sélecteur invalide
    selector.invalid.target=(11)Le sélecteur ne désigne pas une ressource du bon type
    selector.module.unknow=(12)
    */
    function testBadSel() {
        $sels=array(
            "testapp~"=>10,
            ""=>10,
            "~ctrl"=>10,
            "testapp~sqd'"=>10,
            "foo~default"=>12,
            "~"=>10,
            "~#"=>10,
            "a-b~toto"=>10,
            "testapp~ro-ro"=>10,
            "testapp~#"=>10, 
            "#~#"=>10, 
            "#"=>10, 
            "foo"=>11
        );
        $this->runbadtest($sels);
    }

    protected function runtest($list, $class){
        foreach($list as $sel=>$res){
            $valid=true;
            try{
                $s = new $class($sel);
                $valid = $s->module == $res[0] && $s->resource == $res[1];
                $this->assertTrue($valid,  ' test de '.$sel. ' : contient ces données inattendues ('.$s->module.', '.$s->resource.')');
            }catch(jExceptionSelector $e){
                $this->fail( 'jExceptionSelector inattendue sur test de '.$sel. ' : '.$e->getMessage());
            }catch(Exception $e){
                $this->fail( 'exception inattendue sur test de '.$sel. ' : '.$e->getMessage());
            }
        }
    }


    protected function runbadtest($list){
        foreach($list as $sel=>$res){
            $valid=true;
            try{
                $s = new jSelectorClass($sel);
                $this->fail(' test de '.$sel.' : le selecteur devrait être invalide');
            }catch(jExceptionSelector $e){
                 $this->assertTrue($e->getCode() == $res, ' test de '.$sel. ' : mauvais code exception ( '.$e->getCode().' au lieu de '.$res.' )');
            }catch(Exception $e){
                $this->fail( 'exception inattendue sur test de '.$sel. ' : '.$e->getMessage());
            }
        }

    }

}

?>
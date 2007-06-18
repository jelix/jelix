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

    function testZoneSelector() {
        $sels=array(
            "testapp~sommaire"=>array('testapp','sommaire'),
        );

        foreach($sels as $sel=>$res){
            try{
                $s = new jSelectorZone($sel);
                $valid = $s->module == $res[0] && $s->resource == $res[1];
                $this->assertTrue($valid,  ' test de jSelectorZone('.$sel. ') : contient ces données inattendues ('.$s->module.', '.$s->resource.')');
            }catch(jExceptionSelector $e){
                $this->fail( 'jExceptionSelector inattendue sur test de '.$sel. ' : '.$e->getMessage().' ('.$e->getLocalKey().')');
            }catch(Exception $e){
                $this->fail( 'exception inattendue sur test de '.$sel. ' : '.$e->getMessage());
            }
        }
    }


    function testClassSelector() {
        $sels=array(
            "unittestservice"=>array('unittest','unittestservice', '', 'unittestservice'),
            "unittest~unittestservice"=>array('unittest','unittestservice', '', 'unittestservice'),
            "unittest~tests/foo"=>array('unittest','tests/foo', 'tests/', 'foo'),
        );

        foreach($sels as $sel=>$res){
            $s=null;
            try{
                $s = new jSelectorClass($sel);
                $valid = $s->module == $res[0] && $s->resource == $res[1] && $s->subpath == $res[2] && $s->className == $res[3];
                $this->assertTrue($valid,  ' test de jSelectorClass('.$sel. ') : contient ces données inattendues ('.$s->module.', '.$s->resource.','.$s->subpath.','.$s->className.')');
            }catch(jExceptionSelector $e){
                $this->fail( 'jExceptionSelector inattendue sur test de '.$sel. ' : '.$e->getMessage().' ('.$e->getLocalKey().')');
            }catch(Exception $e){
                $this->fail( 'exception inattendue sur test de '.$sel. ' : '.$e->getMessage());
            }
        }
    }

    /*
    selector.invalid.syntax=(10)Syntax du sélecteur invalide
    selector.invalid.target=(11)Le sélecteur ne désigne pas une ressource du bon type
    selector.module.unknow=(12)
    */
    function testBadClassSelector() {
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
            "foo"=>11,
            "../../../foo"=>11,
        );
        foreach($sels as $sel=>$res){
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


    function testInterfaceSelector() {
        $sels=array(
            "test"=>array('unittest','test', '', 'test'),
            "unittest~test"=>array('unittest','test', '', 'test'),
            "unittest~tests/foo"=>array('unittest','tests/foo', 'tests/', 'foo'),
        );

        foreach($sels as $sel=>$res){
            $s=null;
            try{
                $s = new jSelectorInterface($sel);
                $valid = $s->module == $res[0] && $s->resource == $res[1] && $s->subpath == $res[2] && $s->className == $res[3];
                $this->assertTrue($valid,  ' test de jSelectorInterface('.$sel. ') : contient ces données inattendues ('.$s->module.', '.$s->resource.','.$s->subpath.','.$s->className.')');
            }catch(jExceptionSelector $e){
                $this->fail( 'jExceptionSelector inattendue sur test de '.$sel. ' : '.$e->getMessage().' ('.$e->getLocalKey().')');
            }catch(Exception $e){
                $this->fail( 'exception inattendue sur test de '.$sel. ' : '.$e->getMessage());
            }
        }
    }


}

?>
<?php
/**
* @package     jelix
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2007-2012 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class selectors_moduleTest extends \Jelix\UnitTests\UnitTestCase {

    public function setUp() : void  {
        self::initJelixConfig();
        jApp::pushCurrentModule('jelix_tests');
        parent::setUp();
    }
    function tearDown() : void  {
        jApp::popCurrentModule();
    }

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
                $this->fail( 'jExceptionSelector inattendue sur test de '.$sel. ' : '.$e->getMessage().' ('.$e->getLocaleKey().')');
            }catch(Exception $e){
                $this->fail( 'exception inattendue sur test de '.$sel. ' : '.$e->getMessage());
            }
        }
    }

    function testClassSelector() {
        $sels=array(
            "myclass"=>array('jelix_tests','myclass', '', 'myclass'),
            "jelix_tests~myclass"=>array('jelix_tests','myclass', '', 'myclass'),
            "jelix_tests~tests/foo"=>array('jelix_tests','tests/foo', 'tests/', 'foo'),
        );

        foreach($sels as $sel=>$res){
            $s=null;
            try{
                $s = new jSelectorClass($sel);
                $valid = $s->module == $res[0] && $s->resource == $res[1] && $s->subpath == $res[2] && $s->className == $res[3];
                $this->assertTrue($valid,  ' test de jSelectorClass('.$sel. ') : contient ces données inattendues ('.$s->module.', '.$s->resource.','.$s->subpath.','.$s->className.')');
            }catch(jExceptionSelector $e){
                $this->fail( 'jExceptionSelector inattendue sur test de '.$sel. ' : '.$e->getMessage().' ('.$e->getLocaleKey().')');
            }catch(Exception $e){
                $this->fail( 'exception inattendue sur test de '.$sel. ' : '.$e->getMessage());
            }
        }
    }

    /*
    selector.invalid.syntax=(10)Syntax du sélecteur invalide
    selector.invalid.target=(11)Le sélecteur ne désigne pas une ressource du bon type
    selector.module.unknown=(12)
    */
    function testBadClassSelector() {
        $sels=array(
            "testapp~"=>16,
            ""=>16,
            "~ctrl"=>16,
            "testapp~sqd'"=>16,
            "foo~default"=>18,
            "~"=>16,
            "~#"=>16,
            "a-b~toto"=>16,
            "testapp~ro-ro"=>16,
            "testapp~#"=>16,
            "#~#"=>16,
            "#"=>16,
            "foo"=>17,
            "../../../foo"=>17,
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
            "test"=>array('jelix_tests','test', '', 'test'),
            "jelix_tests~test"=>array('jelix_tests','test', '', 'test'),
            "jelix_tests~tests/foo"=>array('jelix_tests','tests/foo', 'tests/', 'foo'),
        );

        foreach($sels as $sel=>$res){
            $s=null;
            try{
                $s = new jSelectorIface($sel);
                $valid = $s->module == $res[0] && $s->resource == $res[1] && $s->subpath == $res[2] && $s->className == $res[3];
                $this->assertTrue($valid,  ' test de jSelectorIface('.$sel. ') : contient ces données inattendues ('.$s->module.', '.$s->resource.','.$s->subpath.','.$s->className.')');
            }catch(jExceptionSelector $e){
                $this->fail( 'jExceptionSelector inattendue sur test de '.$sel. ' : '.$e->getMessage().' ('.$e->getLocaleKey().')');
            }catch(Exception $e){
                $this->fail( 'exception inattendue sur test de '.$sel. ' : '.$e->getMessage());
            }
        }
    }


}

?>
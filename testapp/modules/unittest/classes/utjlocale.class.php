<?php
/**
* @package     testapp
* @subpackage  unittest module
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/



class bundleTest extends jBundle {

    public function readProperties($fichier){
        $this->_loadResources($fichier,'foo');
        return $this->_strings['foo'];
    }

}

require_once(dirname(__FILE__).'/junittestcase.class.php');

class UTjlocale extends jUnitTestCase {

    protected $filePath =  JELIX_APP_PATH.'modules/unittest/locales/';

    public function testBundleUnknowFile(){

        $b = new bundleTest('','');
        try {
            $b->readProperties($this->filePath.'unknowfile');
            $this->fail('should throw an exception when trying reading unknowfile');
        }catch(Exception $e){
            $this->assertTrue($e->getMessage() == 'Cannot load the resource '.$this->filePath.'unknowfile',
            'should throw the right exception when trying reading unknowfile (wrong message: '.$e->getMessage().')');
        }

    }

    protected $firstlist = array(
        'test_A.properties' => '<array>array()</array>',
        'test_B.properties' => '<array>array("aaa"=>"bbb")</array>',
        'test_C.properties' => '<array>array("aaa"=>"bbb","ccc"=>"ddd")</array>',
        'test_D.properties' => '<array>array("module.description"=>"Tests unitaires jelix")</array>',
        'test_E.properties' => '<array>array("module.description"=>"Tests unitaires jelix")</array>',
        'test_F.properties' => '<array>array("module.description"=>"Tests unitaires jelix")</array>',
        );

    public function testBundle(){
        $b = new bundleTest('','');
        foreach($firstlist as $file=>$content){
            $strings = $b->readProperties($this->filePath.$file);
            $this->assertComplexIdenticalStr($strings,"<?xml version=\"1.0\"?>\n$content");
        }
    }
/*
'<?xml version="1.0"?>

    <array>array("aaa"=>"bbb")</array>
            <string key="name" value="news" />
</array>'
*/
}

?>
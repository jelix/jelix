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
        if(isset($this->_strings['foo']))
            return $this->_strings['foo'];
        else return null;
    }

}

require_once(dirname(__FILE__).'/junittestcase.class.php');

class UTjlocale extends jUnitTestCase {

    protected $filePath;

    public function testBundleUnknowFile(){
        $this->filePath =  JELIX_APP_PATH.'modules/unittest/locales/';
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
        'test_A.properties' => '<null> </null>',
        'test_B.properties' => '<array>array("aaa"=>"bbb")</array>',
        'test_C.properties' => '<array>array("aaa"=>"bbb","ccc"=>"ddd")</array>',
        'test_D.properties' => '<array>array("module.description"=&gt;"Tests unitaires jelix")</array>',
        'test_E.properties' => '<array>array("module.description"=&gt;"Tests unitaires jelix")</array>',
        'test_F.properties' => '<array><string key="module.description" value="Tests unitaires jelix" /></array>',
        'test_G.properties' => '<array><string key="module.description" value="Tests unitaires jelix" /><string key="ooo" value="bbbb" /></array>',
        'test_H.properties' => '<array><string key="module.description" value="Tests unitaires # jelix" /><string key="ooo" value="bbbb" /></array>',
        'test_I.properties' => '<array><string key="module.description" value="Tests unitaires # jelix" /><string key="ooo" value="bbbb" /></array>',
        );

    public function testBundle(){
        
        foreach($this->firstlist as $file=>$content){
            $b = new bundleTest('','');
            try{
                $strings = $b->readProperties($this->filePath.$file);
                $this->assertComplexIdenticalStr($strings,"<?xml version=\"1.0\"?>\n$content",$file );
            }catch(Exception $e){
                $this->fail('test failed because of exception : ['.$e->getCode().'] '.$e->getMessage());
            }
        }
    }
}

?>
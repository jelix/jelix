<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Jouanneau Laurent
* @contributor
* @copyright   2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(JELIX_LIB_PATH.'forms/jForms.class.php');

class UTjformsWithDao extends jUnitTestCaseDb {

    function testStart(){
        global $gJCoord;
        $_SESSION['JFORMS'] = array();
        $form = jForms::create('product');
        $form = jForms::create('label', array(1,'fr'));
        $form = jForms::create('label', array(1,'en'));
        $this->emptyTable('product_test');
        $this->emptyTable('product_tags_test');
        $this->emptyTable('labels_test');
        $this->savedParams = $gJCoord->request->params;
    }

    function testInsertDao(){
        global $gJCoord;

        $gJCoord->request->params['name'] = 'phone';
        $gJCoord->request->params['price'] = '45';
        $gJCoord->request->params['tag'] = array('professionnal','book');
        $form = jForms::fill('product');

        // save main data
        $this->id = $form->saveToDao('products');
        $this->assertTrue(preg_match("/^[0-9]+$/",$this->id));
        $records = array(
            array('id'=>$this->id, 'name'=>'phone', 'price'=>45),
        );
        $this->assertTableContainsRecords('product_test', $records);

        // save data of the tags control which is a container
        $form->saveControlToDao('tag','product_tags',$this->id);
        $records = array(
            array('product_id'=>$this->id, 'tag'=>'professionnal'),
            array('product_id'=>$this->id, 'tag'=>'book'),
        );
        $this->assertTableContainsRecords('product_tags_test', $records);

        //insert a second product
        $gJCoord->request->params['name'] = 'computer';
        $gJCoord->request->params['price'] = '590';
        $gJCoord->request->params['tag'] = array('professionnal','promotion');
        $form = jForms::fill('product');

        $this->id2 = $form->saveToDao('products');
        $this->assertTrue(preg_match("/^[0-9]+$/",$this->id2));
        $this->assertNotEqual($this->id, $this->id2);
        $records = array(
            array('id'=>$this->id, 'name'=>'phone', 'price'=>45),
            array('id'=>$this->id2, 'name'=>'computer', 'price'=>590),
        );
        $this->assertTableContainsRecords('product_test', $records);

        // save data of the tags control which is a container
        $form->saveControlToDao('tag','product_tags',$this->id2);
        $records = array(
            array('product_id'=>$this->id, 'tag'=>'professionnal'),
            array('product_id'=>$this->id, 'tag'=>'book'),
            array('product_id'=>$this->id2,'tag'=>'professionnal'),
            array('product_id'=>$this->id2,'tag'=>'promotion'),
        );
        $this->assertTableContainsRecords('product_tags_test', $records);
    }

    function testInsertDao2(){
        global $gJCoord;

        $gJCoord->request->params['label'] = 'bonjour';
        $form = jForms::fill('label', array(1,'fr'));

        // save main data
        $id = $form->saveToDao('labels');
        $this->assertEqual($id, array(1,'fr'));
        $records = array(
            array('key'=>1, 'lang'=>'fr', 'label'=>'bonjour'),
        );
        $this->assertTableContainsRecords('labels_test', $records);

        //insert a second label
        $gJCoord->request->params['label'] = 'Hello';
        $form = jForms::fill('label', array(1,'en'));

        $id2 = $form->saveToDao('labels');
        $this->assertEqual($id2, array(1,'en'));
        $records = array(
            array('key'=>1, 'lang'=>'fr', 'label'=>'bonjour'),
            array('key'=>1, 'lang'=>'en', 'label'=>'Hello'),
        );
        $this->assertTableContainsRecords('labels_test', $records);
    }

    function testUpdateDao(){

        global $gJCoord;

        $form = jForms::create('product',$this->id); // "fill" need an existing form

        $gJCoord->request->params['name'] = 'other phone';
        $gJCoord->request->params['price'] = '68';
        $gJCoord->request->params['tag'] = array('high tech','best seller');

        $form = jForms::fill('product',$this->id);
        $id = $form->saveToDao('products');

        $this->assertEqual($id, $this->id);

        $form->saveToDao('products'); // try to update an unchanged record 

        $records = array(
            array('id'=>$this->id, 'name'=>'other phone', 'price'=>68),
            array('id'=>$this->id2,'name'=>'computer',    'price'=>590),
        );
        $this->assertTableContainsRecords('product_test', $records);

        // save data of the tags control which is a container
        $form->saveControlToDao('tag','product_tags',$this->id);
        $records = array(
            array('product_id'=>$this->id2, 'tag'=>'professionnal'),
            array('product_id'=>$this->id2, 'tag'=>'promotion'),
            array('product_id'=>$this->id,  'tag'=>'high tech'),
            array('product_id'=>$this->id,  'tag'=>'best seller'),
        );
        $this->assertTableContainsRecords('product_tags_test', $records);

    }

    function testLoadDao(){
        jForms::destroy('product');
        jForms::destroy('product', $this->id);
        $verif='
<array>
     <array key="jelix_tests~product">array()</array>
</array>';
        $this->assertComplexIdenticalStr($_SESSION['JFORMS'], $verif);

        $form = jForms::create('product', $this->id);

$verif='
<array>
     <array key="jelix_tests~product">
        <object key="'.$this->id.'" class="jFormsDataContainer">
            <integer property="formId" value="'.$this->id.'" />
            <string property="formSelector" value="jelix_tests~product" />
            <array property="data">
                <string key="name" value="" />
                <string key="price" value="" />
                <array key="tag">array()</array>
            </array>
            <array property="errors">array()</array>
        </object>
     </array>
</array>';
        $this->assertComplexIdenticalStr($_SESSION['JFORMS'], $verif);

        $form->initFromDao('products');

$verif='
<array>
     <array key="jelix_tests~product">
        <object key="'.$this->id.'" class="jFormsDataContainer">
            <integer property="formId" value="'.$this->id.'" />
            <string property="formSelector" value="jelix_tests~product" />
            <array property="data">
                <string key="name" value="other phone" />
                <string key="price" value="68" />
                <array key="tag">array()</array>
            </array>
            <array property="errors">array()</array>
        </object>
     </array>
</array>';


        $this->assertComplexIdenticalStr($_SESSION['JFORMS'], $verif);


        $form->initControlFromDao('tag', 'product_tags');
$verif='
<array>
     <array key="jelix_tests~product">
        <object key="'.$this->id.'" class="jFormsDataContainer">
            <integer property="formId" value="'.$this->id.'" />
            <string property="formSelector" value="jelix_tests~product" />
            <array property="data">
                <string key="name" value="other phone" />
                <string key="price" value="68" />
                <array key="tag">array(\'best seller\', \'high tech\')</array>
            </array>
            <array property="errors">array()</array>
        </object>
     </array>
</array>';
        $this->assertComplexIdenticalStr($_SESSION['JFORMS'], $verif);
    }

    function testEnd(){
        global $gJCoord;
        $gJCoord->request->params = $this->savedParams;
    }
}
?>
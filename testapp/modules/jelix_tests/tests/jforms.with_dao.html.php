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

require_once(JELIX_LIB_FORMS_PATH.'jForms.class.php');

class UTjformsWithDao extends jUnitTestCaseDb {

    function testStart(){
        global $gJCoord;
        $_SESSION['JFORMS'] = array();
        $form = jForms::create('product');
        $this->emptyTable('product_test');
        $this->savedParams = $gJCoord->request->params;
    }

    function testInsertDao(){
        global $gJCoord;

        $gJCoord->request->params['name'] = 'phone';
        $gJCoord->request->params['price'] = '45';
        $form = jForms::fill('product');
        $this->id = $form->saveToDao('products');

        $records = array(
            array('id'=>$this->id,
            'name'=>'phone',
            'price'=>45),
        );
        $this->assertTableContainsRecords('product_test', $records);

        //insert a second product
        $gJCoord->request->params['name'] = 'computer';
        $gJCoord->request->params['price'] = '590';
        $form = jForms::fill('product');
        $this->id2 = $form->saveToDao('products');

        $records = array(
            array('id'=>$this->id,
            'name'=>'phone',
            'price'=>45),
            array('id'=>$this->id2,
            'name'=>'computer',
            'price'=>590),
        );
        $this->assertTableContainsRecords('product_test', $records);

    }

    function testUpdateDao(){

        global $gJCoord;

        $form = jForms::create('product',$this->id); // "fill" need an existing form

        $gJCoord->request->params['name'] = 'other phone';
        $gJCoord->request->params['price'] = '68';
        $form = jForms::fill('product',$this->id);
        $id = $form->saveToDao('products');

        $this->assertEqual($id, $this->id);

        $records = array(
            array('id'=>$this->id,
            'name'=>'other phone',
            'price'=>68),
            array('id'=>$this->id2,
            'name'=>'computer',
            'price'=>590),
        );
        $this->assertTableContainsRecords('product_test', $records);
    }

    function testLoadDao(){
        jForms::destroy('product');
        jForms::destroy('product', $this->id);
        $verif='
<array>
     <array key="product">array()</array>
</array>';
        $this->assertComplexIdenticalStr($_SESSION['JFORMS'], $verif);

        $form = jForms::create('product', $this->id);

$verif='
<array>
     <array key="product">
        <object key="'.$this->id.'" class="jFormsDataContainer">
            <integer property="formId" value="'.$this->id.'" />
            <string property="formSelector" value="product" />
            <array property="datas">
                <string key="name" value="" />
                <string key="price" value="" />
            </array>
            <array property="errors">array()</array>
        </object>
     </array>
</array>';
        $this->assertComplexIdenticalStr($_SESSION['JFORMS'], $verif);

        $form->initFromDao('products');

$verif='
<array>
     <array key="product">
        <object key="'.$this->id.'" class="jFormsDataContainer">
            <integer property="formId" value="'.$this->id.'" />
            <string property="formSelector" value="product" />
            <array property="datas">
                <string key="name" value="other phone" />
                <string key="price" value="68" />
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
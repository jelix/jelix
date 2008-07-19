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

class UTjforms extends jUnitTestCase {
    protected $form1;
    protected $form2;

    protected $form1Descriptor, $form2Descriptor, $formLabelDescriptor;

    function testStart(){
        $_SESSION['JFORMS'] = array();
        $this->form1Descriptor = '
<object class="cForm_jelix_tests_Jx_product">
    <object method="getContainer()" class="jFormsDataContainer">
        <integer property="formId" value="'.jForms::DEFAULT_ID.'" />
        <string property="formSelector" value="product" />
        <array property="data">
            <string key="name" value="" />
            <string key="price" value="" />
        </array>
        <array property="errors">array()</array>
    </object>
    <array method="getAllData()">
        <string key="name" value="" />
        <string key="price" value="" />
    </array>
    <integer method="id()" value="'.jForms::DEFAULT_ID.'" />
    <array method="getControls()">
        <object key="name" class="jFormsControlInput">
            <string property="ref" value="name"/>
            <boolean property="required" value="true"/>
            <boolean method="isReadOnly()" value="false"/>
            <string property="label" value="product name"/>
            <string property="defaultValue" value=""/>
        </object>
        <object key="price" class="jFormsControlInput">
            <string property="ref" value="price"/>
            <boolean property="required" value="false"/>
            <boolean method="isReadOnly()" value="false"/>
            <string property="label" value="The price"/>
            <string property="defaultValue" value=""/>
        </object>
    </array>
</object>';


        $this->form2Descriptor ='
<object class="cForm_jelix_tests_Jx_product">
    <object method="getContainer()" class="jFormsDataContainer">
        <string property="formId" value="akey" />
        <string property="formSelector" value="product" />
        <array property="data">
            <string key="name" value="" />
            <string key="price" value="" />
        </array>
        <array property="errors">array()</array>
    </object>
    <array method="getAllData()">
        <string key="name" value="" />
        <string key="price" value="" />
    </array>
    <string method="id()" value="akey" />
    <array method="getControls()">
        <object key="name" class="jFormsControlInput">
            <string property="ref" value="name"/>
            <boolean property="required" value="true"/>
            <boolean method="isReadOnly()" value="false"/>
            <string property="label" value="product name"/>
            <string property="defaultValue" value=""/>
        </object>
        <object key="price" class="jFormsControlInput">
            <string property="ref" value="price"/>
            <boolean property="required" value="false"/>
            <boolean method="isReadOnly()" value="false"/>
            <string property="label" value="The price"/>
            <string property="defaultValue" value=""/>
        </object>
    </array>
</object>';

        $this->formLabelDescriptor = '
<object class="cForm_jelix_tests_Jx_label">
    <object method="getContainer()" class="jFormsDataContainer">
        <array property="formId">array(1,\'fr\')</array>
        <string property="formSelector" value="label" />
        <array property="data">
            <string key="label" value="" />
        </array>
        <array property="errors">array()</array>
    </object>
    <array method="getAllData()">
        <string key="label" value="" />
    </array>
    <array method="id()">array(1,\'fr\')</array>
    <array method="getControls()">
        <object key="label" class="jFormsControlInput">
            <string property="ref" value="label"/>
            <boolean property="required" value="true"/>
            <boolean method="isReadOnly()" value="false"/>
            <string property="label" value="The label"/>
            <string property="defaultValue" value=""/>
        </object>
    </array>
</object>';
    }

    function testCreate(){
        $this->form1 = jForms::create('product');
        $this->assertComplexIdenticalStr($this->form1, $this->form1Descriptor);
        return;
        $verif='
<array>
     <array key="product">
        <object key="'.jForms::DEFAULT_ID.'" class="jFormsDataContainer">
            <integer property="formId" value="'.jForms::DEFAULT_ID.'" />
            <string property="formSelector" value="product" />
            <array property="data">
                <string key="name" value="" />
                <string key="price" value="" />
            </array>
            <array property="errors">array()</array>
        </object>
     </array>
</array>';
        $this->assertComplexIdenticalStr($_SESSION['JFORMS'], $verif);

        $this->form2 = jForms::create('product', 'akey');
        $this->assertComplexIdenticalStr($this->form2, $this->form2Descriptor);
        $verif='
<array>
     <array key="product">
        <object key="'.jForms::DEFAULT_ID.'" class="jFormsDataContainer">
            <integer property="formId" value="'.jForms::DEFAULT_ID.'" />
            <string property="formSelector" value="product" />
            <array property="data">
                <string key="name" value="" />
                <string key="price" value="" />
            </array>
            <array property="errors">array()</array>
        </object>
        <object key="akey" class="jFormsDataContainer">
            <string property="formId" value="akey" />
            <string property="formSelector" value="product" />
            <array property="data">
                <string key="name" value="" />
                <string key="price" value="" />
            </array>
            <array property="errors">array()</array>
        </object>
     </array>
</array>';
        $this->assertComplexIdenticalStr($_SESSION['JFORMS'], $verif);


        $this->formLabel = jForms::create('label', array(1,'fr'));
        $this->assertComplexIdenticalStr($this->formLabel, $this->formLabelDescriptor);
        $verif='
<array>
     <array key="product">
        <object key="'.jForms::DEFAULT_ID.'" class="jFormsDataContainer">
            <integer property="formId" value="'.jForms::DEFAULT_ID.'" />
            <string property="formSelector" value="product" />
            <array property="data">
                <string key="name" value="" />
                <string key="price" value="" />
            </array>
            <array property="errors">array()</array>
        </object>
        <object key="akey" class="jFormsDataContainer">
            <string property="formId" value="akey" />
            <string property="formSelector" value="product" />
            <array property="data">
                <string key="name" value="" />
                <string key="price" value="" />
            </array>
            <array property="errors">array()</array>
        </object>
     </array>
     <array key="label">
        <object key="a:2:{i:0;i:1;i:1;s:2:&quot;fr&quot;;}" class="jFormsDataContainer">
            <array property="formId">array(1,\'fr\')</array>
            <string property="formSelector" value="label" />
            <array property="data">
                <string key="label" value="" />
            </array>
            <array property="errors">array()</array>
        </object>
     </array>
</array>';
        $this->assertComplexIdenticalStr($_SESSION['JFORMS'], $verif);
    }

    function testGet(){
        return;
        $f1 = jForms::get('product');
        $this->assertComplexIdenticalStr($f1, $this->form1Descriptor);
        $this->assertIdentical($f1, $this->form1);

        $f2 = jForms::get('product', 'akey');
        $this->assertComplexIdenticalStr($f2, $this->form2Descriptor);
        $this->assertIdentical($f2, $this->form2);

        $f3 = jForms::get('product', 'anUnknowKey');
        $this->assertNull($f3);

        $f4 = jForms::get('label', array(1,'fr'));
        $this->assertComplexIdenticalStr($f4, $this->formLabelDescriptor);
        $this->assertIdentical($f4, $this->formLabel);
    }

    function testFill(){
        return;
        global $gJCoord;
        $savedParams = $gJCoord->request->params;

        $form = jForms::fill('product');
        $this->assertComplexIdenticalStr($form, $this->form1Descriptor);

        $gJCoord->request->params['name'] = 'phone';
        $gJCoord->request->params['price'] = '45';

        $form = jForms::fill('product');
        $verif = '
<object class="cForm_jelix_tests_Jx_product">
    <object method="getContainer()" class="jFormsDataContainer">
        <integer property="formId" value="'.jForms::DEFAULT_ID.'" />
        <string property="formSelector" value="product" />
        <array property="data">
            <string key="name" value="phone" />
            <string key="price" value="45" />
        </array>
        <array property="errors">array()</array>
    </object>
    <array method="getAllData()">
        <string key="name" value="phone" />
        <string key="price" value="45" />
    </array>
    <integer method="id()" value="'.jForms::DEFAULT_ID.'" />
    <array method="getControls()">
        <object key="name" class="jFormsControlInput">
            <string property="ref" value="name"/>
            <boolean property="required" value="true"/>
            <boolean method="isReadOnly()" value="false"/>
            <string property="label" value="product name"/>
            <string property="defaultValue" value=""/>
        </object>
        <object key="price" class="jFormsControlInput">
            <string property="ref" value="price"/>
            <boolean property="required" value="false"/>
            <boolean method="isReadOnly()" value="false"/>
            <string property="label" value="The price"/>
            <string property="defaultValue" value=""/>
        </object>
    </array>
</object>';
        $this->assertComplexIdenticalStr($form, $verif);

        // verify that the other form hasn't changed
        $form = jForms::get('product', 'akey');
        $this->assertComplexIdenticalStr($form, $this->form2Descriptor);

        $form = jForms::fill('product', 'akey');
        $verif = '
<object class="cForm_jelix_tests_Jx_product">
    <object method="getContainer()" class="jFormsDataContainer">
        <integer property="formId" value="akey" />
        <string property="formSelector" value="product" />
        <array property="data">
            <string key="name" value="phone" />
            <string key="price" value="45" />
        </array>
        <array property="errors">array()</array>
    </object>
    <array method="getAllData()">
        <string key="name" value="phone" />
        <string key="price" value="45" />
    </array>
    <integer method="id()" value="akey" />
    <array method="getControls()">
        <object key="name" class="jFormsControlInput">
            <string property="ref" value="name"/>
            <boolean property="required" value="true"/>
            <boolean method="isReadOnly()" value="false"/>
            <string property="label" value="product name"/>
            <string property="defaultValue" value=""/>
        </object>
        <object key="price" class="jFormsControlInput">
            <string property="ref" value="price"/>
            <boolean property="required" value="false"/>
            <boolean method="isReadOnly()" value="false"/>
            <string property="label" value="The price"/>
            <string property="defaultValue" value=""/>
        </object>
    </array>
</object>';

        $gJCoord->request->params= $savedParams;
    }


    function testDestroy(){
        return;
        jForms::destroy('product');

        $verif='
<array>
     <array key="product">
        <object key="akey" class="jFormsDataContainer">
            <string property="formId" value="akey" />
            <string property="formSelector" value="product" />
            <array property="data">
                <string key="name" value="phone" />
                <string key="price" value="45" />
            </array>
            <array property="errors">array()</array>
        </object>
     </array>
     <array key="label">
        <object key="a:2:{i:0;i:1;i:1;s:2:&quot;fr&quot;;}" class="jFormsDataContainer">
            <array property="formId">array(1,\'fr\')</array>
            <string property="formSelector" value="label" />
            <array property="data">
                <string key="label" value="" />
            </array>
            <array property="errors">array()</array>
        </object>
     </array>
</array>';
        $this->assertComplexIdenticalStr($_SESSION['JFORMS'], $verif);

        jForms::destroy('product','akey');
        $verif='
<array>
     <array key="product">array()</array>
     <array key="label">
        <object key="a:2:{i:0;i:1;i:1;s:2:&quot;fr&quot;;}" class="jFormsDataContainer">
            <array property="formId">array(1,\'fr\')</array>
            <string property="formSelector" value="label" />
            <array property="data">
                <string key="label" value="" />
            </array>
            <array property="errors">array()</array>
        </object>
     </array>
</array>';
        $this->assertComplexIdenticalStr($_SESSION['JFORMS'], $verif);
    }

}
?>
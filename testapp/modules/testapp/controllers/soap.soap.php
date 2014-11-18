<?php
/**
* @package     testapp
* @subpackage  testapp module
* @version     1
* @author      Sylvain de Vathaire
* @contributor
* @copyright   2008 Sylvain de Vathaire
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
require(__DIR__.'/../classes/soapstructs.php');

/**
* Web Services usefull to test SOAP request handling, WSDL generation, web servives documentation generation
*/
class soapCtrl extends jController {

    /**  
     * Test without any parameter
     * @return string Server date
     */
    function getServerDate() {
        $rep = $this->getResponse('soap');
        $rep->data = date('Y-m-d\TH:i:s O');
        return $rep;
    }

    /** 
     * Test with a simple parameter
     * @externalparam string $name
     * @return string
     */
    function hello() {
        $rep = $this->getResponse('soap');
        $rep->data = "Hello ".$this->param('name');
        return $rep;
    }

    /** 
     * Test with a simple parameter
     * @externalparam string $name
     * @return void
     */
    function redirecttohello(){
        $rep = $this->getResponse('redirectUrl');
        $url = new jUrl($this->request->urlScript, array('service'=>'testapp~soap', 'method'=>'hello'));
        $rep->url = $this->request->getServerURI().$url->toString();
        return $rep;
    }

    /** 
     * Test with multiple string param
     * @externalparam string $string1 
     * @externalparam string $string2 
     * @externalparam string $string3
     * @return string
     */
    function concatString() {
        $rep = $this->getResponse('soap');
        $rep->data = $this->param('string1').$this->param('string2').$this->param('string3');
        return $rep;
    }

    /** 
     * Test with an array as param
     * @externalparam string[] $myArray
     * @return string
     */
    function concatArray() {
        $rep = $this->getResponse('soap');
        $rep->data = implode(' ', $this->param('myArray'));
        return $rep;
    }

   /** 
     * Test with an associative array as param
     * @externalparam string[=>] $myArray
     * @return string
     */
    function concatAssociativeArray() {
        $rep = $this->getResponse('soap');
        $myArray = $this->param('myArray');
        $rep->data = $myArray['arg1'].' '.$myArray['arg2'].' '.$myArray['arg3'];
        return $rep;
    }

   /** 
     * Test with an associative array as return value
     * @return string[=>]
     */
    function returnAssociativeArray() {
        $rep = $this->getResponse('soap');
        $rep->data = array('arg1'=>'Hi ! ', 'arg2'=>'Sylvain', 'arg3'=>'How are you ?');
        return $rep;
    }

   /** 
     * Test with an associative array ob object as return value
     * @return MyTestStructQuatre[=>]
     */
    function returnAssociativeArrayOfObjects() {
        $rep = $this->getResponse('soap');
        $rep->data = array('arg1'=>new MyTestStructQuatre(), 'arg2'=>new MyTestStructQuatre(), 'arg3'=>new MyTestStructQuatre());
        return $rep;
    }


    /** 
     * Test that return an object
     * @return MyTestStruct
     */
    function returnObject() {
        $rep = $this->getResponse('soap');
        $rep->data = new MyTestStruct();
        return $rep;
    }

   /** 
     * Test that return an array of objects
     * @return MyTestStruct[] Tableau d'objet de test
     */
    function returnObjects() {
        $rep = $this->getResponse('soap');
        $rep->data = array(new MyTestStruct(), new MyTestStruct(), new MyTestStruct(), new MyTestStruct());
        return $rep;
    }


    /** 
     * Test that receive an object and return an object
     * @externalparam MyTestStruct $input
     * @return MyTestStruct
     */
    function receiveObject() {
        $rep = $this->getResponse('soap');
        $input = $this->param('input');
        $input->name = 'Name updated';
        $rep->data = $input;
        return $rep;
    }

    /**
     * Test that return an object which have an other object as member propertie
     * @return MyTestStructBis
     */
    function returnObjectBis() {
        $rep = $this->getResponse('soap');
        $rep->data = new MyTestStructBis();
        return $rep;
    }

    /**
     * Test of circular references
     * @return MyTestStructTer[]
     */
    function returnCircularReference() {
        $rep = $this->getResponse('soap');
        $object1 = new MyTestStructTer('object1');
        $object2 = new MyTestStructTer('object2');
        $object1->test = $object2;
        $object2->test = $object1;
        $rep->data = array($object1, $object2);
        return $rep;
    }

}

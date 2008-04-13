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
     * @param string $name
     * @return string
     */
    function hello() {
        $rep = $this->getResponse('soap');
        $rep->data = "Hello ".$this->param('name');
        return $rep;
    }


    /** 
     * Test with multiple string param
     * @param string $string1 
     * @param string $string2 
     * @param string $string3
     * @return string
     */
    function concatString() {
        $rep = $this->getResponse('soap');
        $rep->data = $this->param('string1').$this->param('string2').$this->param('string3');
        return $rep;
    }

    /** 
     * Test with an array as param
     * @param string[] $myArray
     * @return string
     */
    function concatArray() {
        $rep = $this->getResponse('soap');
        $rep->data = implode(' ', $this->param('myArray'));
        return $rep;
    }

   /** 
     * Test with an associative array as param
     * @param string[=>] $myArray
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
     * @param MyTestStruct $input
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
/**
 * Struct used for tests
 */
class MyTestStruct{
    /**
     * @var string
     */
    public $name = 'De Vathaire';

    /**
     * @var string
     */
    public $firstName = 'Sylvain';

    /**
     * @var string
     */
    public $city = 'Paris';
}

/**
 * An other struct used for test, this one have an other object as member propertie
 */
class MyTestStructBis{

    /**
     * @var MyTestStruct
     */
    public $test;

    /**
     * @var string
     */
    public $msg = 'hello';

    function __construct(){
        $this->test = new MyTestStruct();
    }

}

/**
 * An other struct used for test, this one have is used to test circular references
 */
class MyTestStructTer{

    /**
     * @var MyTestStructTer
     */
    public $test;

    /**
     * @var string
     */
    public $msg;

    function __construct($msg){
        $this->msg = $msg;
    }

}

/**
 * An other struct used for test, this one is used to test associative array of objects
 */
class MyTestStructQuatre{

    /**
     * @var string
     */
    public $name = 'De Vathaire';

    /**
     * @var string
     */
    public $firstName = 'Sylvain';

    /**
     * @var string
     */
    public $city = 'Paris';
}

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


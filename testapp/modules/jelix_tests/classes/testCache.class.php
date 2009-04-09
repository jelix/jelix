<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Tahina Ramaroson
* @contributor Sylvain de Vathaire
* @copyright   NEOV 2009
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
* Tests API jCache
* @package     testapp
* @subpackage  jelix_tests module
*/

function testFunction($arg1,$arg2){

    return $arg1 + $arg2;

}

class testCache {

    public static function staticMethod($arg1,$arg2){

        return $arg1 + $arg2;

    }

    public function method($arg1,$arg2){

        return $arg1 + $arg2;

    }

}
?>

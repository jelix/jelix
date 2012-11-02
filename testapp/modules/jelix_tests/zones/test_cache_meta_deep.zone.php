<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Brice Tencé
* @contributor
* @copyright   2012 Brice Tencé
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class test_cache_meta_deepZone extends jZone {

    protected $_useCache = true;

    protected function _createContent(){

        jZone::get( 'test_cache_meta_deep2' );
    }

}


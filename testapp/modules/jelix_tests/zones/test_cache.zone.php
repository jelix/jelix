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

class test_cacheZone extends jZone {

    protected $_tplname = 'test_zone_cache';
    protected $_useCache = true;

    protected function _prepareTpl(){

        if( $this->param('cancelCache') ) {
            $this->_cancelCache = true;
        }

        $GLOBALS['zoneTestCache'] ++;
    }

}


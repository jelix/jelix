<?php
/**
* @package  Jelix\Legacy
* @author   Laurent Jouanneau
* @contributor
* @copyright 2014 Laurent Jouanneau
* @link     http://www.jelix.org
* @licence  MIT
*/

/**
 * dummy class for compatibility
 * @see \Jelix\Core\Includer
 * @deprecated
 */
class jIncluder {

    private function __construct() {}

    public static function inc($aSelector) {
        \Jelix\Core\Includer::inc($aSelector);
    }

    public static function incAll($aType){
        return \Jelix\Core\Includer::incAll($aType);
    }

    public static function clear() {
        \Jelix\Core\Includer::clear();
    }


}

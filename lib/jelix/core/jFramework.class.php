<?php
/**
* @author     Laurent Jouanneau
* @copyright  2015 Laurent Jouanneau
* @link       http://jelix.org
* @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
* 
*/
class jFramework {
    
    static protected $_version = null;
    
    static public function version() {
        if (self::$_version === null) {
            self::$_version = trim(str_replace(array('SERIAL', "\n"),
                                          array('0', ''),
                                          file_get_contents(__DIR__.'/../VERSION')));
        }
        return self::$_version;
    }

    static public function versionMax() {
        $v =  self::version();
        return preg_replace('/\.([a-z0-9\-]+)$/i', '.*', $v);
    }
}

<?php
/**
* @package    jelix
* @subpackage core
* @author     Laurent Jouanneau
* @copyright  2011 Laurent Jouanneau
* @link       http://jelix.org
* @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
* 
* @package    jelix
* @subpackage utils
*/
class jApp {

    /**
     * load a plugin from a plugin directory (any type of plugins)
     * @param string $name the name of the plugin
     * @param string $type the type of the plugin
     * @param string $suffix the suffix of the filename
     * @param string $classname the name of the class to instancy
     * @param mixed $args  the argument for the constructor of the class. null = no argument.
     * @return null|object  null if the plugin doesn't exists
     */
    public static function loadPlugin($name, $type, $suffix, $classname, $args = null) {

        if (!class_exists($classname,false)) {
            global $gJConfig;
            $optname = '_pluginsPathList_'.$type;
            if (!isset($gJConfig->$optname))
                return null;
            $opt = & $gJConfig->$optname;
#ifnot ENABLE_OPTIMIZED_SOURCE
            if (!isset($opt[$name])
                || !file_exists($opt[$name]) ){
                return null;
            }
#endif
            require_once($opt[$name].$name.$suffix);
        }
        if (!is_null($args))
            return new $classname($args);
        else
            return new $classname();
    }
}

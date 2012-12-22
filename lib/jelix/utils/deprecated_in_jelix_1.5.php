<?php
/**
* @package      jelix
* @subpackage   utils
* @author       Olivier Demah
* @copyright    2012 Olivier Demah
* @link         http://www.jelix.org
* @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/** This Closure is used in jelix 1.5 to check :
 * if the new config file mainconfig.ini.php exists and
 * ** if yes, use it 
 * ** if no, test if the old (and deprecated) defaultconfig.ini.php if exists then
 * ***** trigger an error
 * and finally return the config file name + full path
 *
 * The goal of this closure is to keep the code DRY and avoid to use the same peace of code
 * everywhere the defaultconfig.ini.php was used before 1.6 except for the main config o
 * Jelix itself
 */

$myMainConfigFileName = function($name,$path)
{
    // default config file name   
    $configFileName['fullpath'] = $path.'mainconfig.ini.php';
    $configFileName['name'] = $name;
       
    if ( file_exists ($path.$name) ) {
        $configFileName['name'] = $name;
        $configFileName['fullpath'] = $path.$name;
    }
    elseif(file_exists($path.'defaultconfig.ini.php')) {
        $configFileName['name'] = 'defaultconfig.ini.php';
        $configFileName['fullpath'] = $path.'defaultconfig.ini.php';
        trigger_error("the config file var/config/defaultconfig.ini.php is deprecated and will be removed in the next major release", E_USER_DEPRECATED);
    }
    
    return $configFileName;
};


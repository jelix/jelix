<?php
/**
* @package      jelix
* @subpackage   utils
* @author       Olivier Demah
* @copyright    2012 Olivier Demah
* @link         http://www.jelix.org
* @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/** This function is used in jelix 1.5 to check :
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

function myMainConfigFileName ($path)
{
       
    if ( file_exists ($path.'mainconfig.ini.php') ) {
        $configFileName = $path.'mainconfig.ini.php';
    }
    elseif(file_exists($path.'defaultconfig.ini.php')) {
        $configFileName = $path.'defaultconfig.ini.php';
        trigger_error("the config file var/config/defaultconfig.ini.php is deprecated and will be removed in the next major release", E_USER_DEPRECATED);
    }
    // if none of them exists return false and throw an exception
    else
        $configFileName = false;
    
    return $configFileName;
};



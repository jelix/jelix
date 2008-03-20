<?php
/**
* @package     jelix
* @subpackage  installer
* @author      Laurent Jouanneau
* @contributor 
* @copyright   2008 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
* EXPERIMENTAL
* interface for classes used as reporter for installation
* This classes are responsible to show information to the user
* @package     jelix
* @subpackage  installer
* @experimental
* @since 1.1
*/
interface jIInstallReporter {

    function error($string);

    function warning($string);

    function notice($string);

    function message($string);

}

?>
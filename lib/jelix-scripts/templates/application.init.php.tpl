<?php
/**
* @package  {$appname}
* @subpackage
* @version  $Id$
* @author
* @contributor
* @copyright
* @link
* @licence  http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
*/

define ('JELIX_APP_PATH', dirname (__FILE__).'/'); // don't change

define ('JELIX_APP_TEMP_PATH',    realpath(JELIX_APP_PATH.'{$rp_temp}').'/');
define ('JELIX_APP_VAR_PATH',     realpath(JELIX_APP_PATH.'{$rp_var}').'/');
define ('JELIX_APP_LOG_PATH',     realpath(JELIX_APP_PATH.'{$rp_log}').'/');
define ('JELIX_APP_CONFIG_PATH',  realpath(JELIX_APP_PATH.'{$rp_conf}').'/');
define ('JELIX_APP_WWW_PATH',     realpath(JELIX_APP_PATH.'{$rp_www}').'/');


?>
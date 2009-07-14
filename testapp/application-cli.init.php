<?php
/**
* @package  jelix
* @subpackage testapp
* @author   Laurent Jouanneau
* @contributor
* @copyright 2005-2009 Laurent Jouanneau
* @link     http://jelix.org
* @licence  http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
*/

require (dirname(__FILE__).'/../lib/jelix/init.php');
define ('JELIX_APP_PATH', dirname (__FILE__).'/'); // don't change

define ('JELIX_APP_TEMP_PATH',    realpath(JELIX_APP_PATH.'../temp/testapp-cli/').'/');
define ('JELIX_APP_VAR_PATH',     realpath(JELIX_APP_PATH.'var/').'/');
define ('JELIX_APP_LOG_PATH',     realpath(JELIX_APP_PATH.'var/log-cli/').'/');
define ('JELIX_APP_CONFIG_PATH',  realpath(JELIX_APP_PATH.'var/config/').'/');
define ('JELIX_APP_WWW_PATH',     realpath(JELIX_APP_PATH.'www/').'/');
define ('JELIX_APP_CMD_PATH',     realpath(JELIX_APP_PATH.'scripts/').'/');


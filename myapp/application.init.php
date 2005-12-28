<?php
/**
* @package  jelix
* @subpackage myapp
* @version  $Id$
* @author   Jouanneau Laurent
* @contributor
* @copyright 2005-2006 Jouanneau laurent
* @link     http://www.jelix.org
* @licence  http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
*/


define ('JELIX_APP_PATH', dirname (__FILE__).'/'); // don't change


define ('JELIX_APP_TEMP_PATH',    realpath(dirname(__FILE__).'/../temp/myapp/').'/');
define ('JELIX_APP_VAR_PATH',     realpath(dirname(__FILE__).'/var/').'/');
define ('JELIX_APP_LOG_PATH',     JELIX_APP_VAR_PATH.'log/');
define ('JELIX_APP_CONFIG_PATH',  JELIX_APP_VAR_PATH.'config/');


?>
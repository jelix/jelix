<?php
/**
* @package  jelix
* @subpackage testapp
* @author    Laurent Jouanneau
* @copyright 2005-2024 Laurent Jouanneau
* @link      https://www.jelix.org
* @licence   http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
*/

use Jelix\Core\App;
use Jelix\Routing\Router;

require ('../application.init.php');
require (JELIX_LIB_CORE_PATH.'request/jClassicRequest.class.php');

\Jelix\Core\AppManager::errorIfAppClosed();

App::loadConfig('index/config.ini.php');

App::setRouter(new Router());
App::router()->process(new jClassicRequest());


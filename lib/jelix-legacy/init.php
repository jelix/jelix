<?php
/**
* Initialize all defines and includes necessary files
*
#if ENABLE_OPTIMIZED_SOURCE
* Some lines of code were get from Copix project (Copix 2.3dev20050901)
* and are copyrighted 2001-2005 CopixTeam (LGPL Licence)
#endif
* @package  jelix
* @subpackage core
* @author   Laurent Jouanneau
#if ENABLE_OPTIMIZED_SOURCE
* @author Croes Gerald
* @contributor Loic Mathaud, Julien Issler
* @copyright 2005-2014 Laurent Jouanneau
* @copyright 2001-2005 CopixTeam
* @copyright 2006 Loic Mathaud
* @copyright 2007-2009 Julien Issler
* @link http://www.copix.org
#else
* @contributor Loic Mathaud, Julien Issler
* @copyright 2005-2012 Laurent Jouanneau
* @copyright 2007 Julien Issler
#endif
* @link     http://www.jelix.org
* @licence  GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * Version number of Jelix
 * @name  JELIX_VERSION
 */
#ifdef LIB_VERSION
#expand define ('JELIX_VERSION', '__LIB_VERSION__');
#else
define ('JELIX_VERSION', str_replace('SERIAL', '0', file_get_contents(__DIR__.'/VERSION')));
#endif

/**
 * base of namespace path used in xml files of jelix
 * @name  JELIX_NAMESPACE_BASE
 */
define ('JELIX_NAMESPACE_BASE' , 'http://jelix.org/ns/');

define ('JELIX_LIB_PATH',         __DIR__.'/');
define ('JELIX_LIB_CORE_PATH',    JELIX_LIB_PATH.'core/');
define ('JELIX_LIB_UTILS_PATH',   JELIX_LIB_PATH.'utils/');
define ('LIB_PATH',               dirname(JELIX_LIB_PATH).'/');


define ('BYTECODE_CACHE_EXISTS', function_exists('apc_cache_info') || function_exists('eaccelerator_info') || function_exists('xcache_info'));

error_reporting (E_ALL | E_STRICT);

/**
 * Jelix Autoloader
 */
class LegacyJelixAutoloader {

    static public $libPath;

    static public function loadClass($class) {
        if (strpos($class, 'jelix\\') === 0) {
            $f = LIB_PATH.'jelix-legacy/'.str_replace('\\', DIRECTORY_SEPARATOR, substr($class,6)).'.php';
        }
        elseif (preg_match('/^j(Dao|Tpl|Event|Db|Controller|Forms|Auth|Installer|KV).*/i', $class, $m)) {
            $f = self::$libPath[$m[1]].$class.'.class.php';
        }
        elseif (preg_match('/^cDao(?:Record)?_(.+)_Jx_(.+)_Jx_(.+)$/', $class, $m)) {
            // for DAO which are stored in sessions for example
            if (!isset(jApp::config()->_modulesPathList[$m[1]])) {
                //this may happen if we have several entry points, but the current one does not have this module accessible
                return;
            }
            $s = new jSelectorDao($m[1].'~'.$m[2], $m[3], false);
            if (jApp::config()->compilation['checkCacheFiletime']) {
                // if it is needed to check the filetime, then we use jIncluder
                // because perhaps we will have to recompile the dao before the include
                jIncluder::inc($s);
            }
            else {
                $f = $s->getCompiledFilePath();
                // we should verify that the file is here and if not, we recompile
                // (case where the temp has been cleaned, see bug #6062 on berlios.de)
                if (!file_exists($f)) {
                    jIncluder::inc($s);
                }
                else {
                    require($f);
                }
            }
            return;
        }
        else {
            $f = JELIX_LIB_UTILS_PATH.$class.'.class.php';
        }

        if(file_exists($f)){
            require($f);
        }
    }
}

LegacyJelixAutoloader::$libPath =array('Db'=>JELIX_LIB_PATH.'db/', 'Dao'=>JELIX_LIB_PATH.'dao/',
 'Forms'=>JELIX_LIB_PATH.'forms/',
 'Tpl'=>JELIX_LIB_PATH.'tpl/', 'Controller'=>JELIX_LIB_PATH.'controllers/',
 'Auth'=>JELIX_LIB_PATH.'auth/', 'Installer'=>JELIX_LIB_PATH.'installer/',
 'KV'=>JELIX_LIB_PATH.'kvdb/');

spl_autoload_register("LegacyJelixAutoloader::loadClass");


#if ENABLE_OPTIMIZED_SOURCE
#includephp core/jICoordPlugin.iface.php
#includephp core/jIUrlEngine.iface.php
#includephp core/jBasicErrorHandler.class.php
#includephp core/jException.class.php
#includephp core/jServer.class.php
#includephp core/selector/jSelectorActFast.class.php
#includephp core/selector/jSelectorAct.class.php
#includephp core/selector/jSelectorDao.class.php
#includephp core/selector/jSelectorDaoRecord.class.php
#includephp core/selector/jSelectorForm.class.php
#includephp core/selector/jSelectorTpl.class.php
#includephp core/selector/jSelectorZone.class.php
#includephp core/jUrlBase.class.php
#includephp core/jUrlAction.class.php
#includephp core/jUrl.class.php
#includephp core/jController.class.php
#includephp core/jSession.class.php

#else
require (JELIX_LIB_CORE_PATH . 'jICoordPlugin.iface.php');
require (JELIX_LIB_CORE_PATH . 'jIUrlEngine.iface.php');
require (JELIX_LIB_CORE_PATH . 'jBasicErrorHandler.class.php');
require (JELIX_LIB_CORE_PATH . 'jException.class.php');
require (JELIX_LIB_CORE_PATH . 'jServer.class.php');
require (JELIX_LIB_CORE_PATH . 'selector/jSelectorActFast.class.php');
require (JELIX_LIB_CORE_PATH . 'selector/jSelectorAct.class.php');
require (JELIX_LIB_CORE_PATH . 'selector/jSelectorDao.class.php');
require (JELIX_LIB_CORE_PATH . 'selector/jSelectorDaoRecord.class.php');
require (JELIX_LIB_CORE_PATH . 'selector/jSelectorForm.class.php');
require (JELIX_LIB_CORE_PATH . 'selector/jSelectorTpl.class.php');
require (JELIX_LIB_CORE_PATH . 'selector/jSelectorZone.class.php');
require (JELIX_LIB_CORE_PATH . 'jUrlBase.class.php');
require (JELIX_LIB_CORE_PATH . 'jUrlAction.class.php');
require (JELIX_LIB_CORE_PATH . 'jUrl.class.php');
require (JELIX_LIB_CORE_PATH . 'jController.class.php');
require (JELIX_LIB_CORE_PATH . 'jSession.class.php');
#endif

/**
 * @deprecated use \Jelix\Core\AppManager::errorIfAppClosed()
 */
function checkAppOpened() {
    \Jelix\Core\AppManager::errorIfAppClosed();
}

/**
 * @deprecated use \Jelix\Core\AppManager::errorIfAppInstalled();
 */
function checkAppNotInstalled() {
    \Jelix\Core\AppManager::errorIfAppInstalled();
}

/**
 * @deprecated use \Jelix\Core\AppManager::isAppInstalled();
 */
function isAppInstalled() {
    return \Jelix\Core\AppManager::isAppInstalled();
}

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
* @author   Jouanneau Laurent
#if ENABLE_OPTIMIZED_SOURCE
* @author Croes Gerald
* @contributor Loic Mathaud, Julien Issler
* @copyright 2005-2007 Jouanneau laurent
* @copyright 2001-2005 CopixTeam
* @copyright 2006 Mathaud Loic
* @copyright 2007 Julien Issler
* @link http://www.copix.org
#else
* @contributor Loic Mathaud, Julien Issler
* @copyright 2005-2007 Jouanneau laurent
* @copyright 2007 Julien Issler
#endif
* @link     http://www.jelix.org
* @licence  GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

#if ENABLE_PHP_JELIX
if(!function_exists('jelix_version')){
    die('this edition of Jelix needs jelix php extension.');
}
#endif

/**
 * Version number of Jelix
 * @name  JELIX_VERSION
 */
#expand define ('JELIX_VERSION', '__LIB_VERSION__');

#ifnot ENABLE_PHP_JELIX
/**
 * base of namespace path used in xml files of jelix
 * @name  JELIX_NAMESPACE_BASE
 */
define ('JELIX_NAMESPACE_BASE' , 'http://jelix.org/ns/');
#endif

define ('JELIX_LIB_PATH',         dirname (__FILE__).'/');
define ('JELIX_LIB_CORE_PATH',    JELIX_LIB_PATH.'core/');
define ('JELIX_LIB_UTILS_PATH',   JELIX_LIB_PATH.'utils/');
define ('LIB_PATH',               dirname(JELIX_LIB_PATH).'/');

#if WITH_BYTECODE_CACHE == 'auto'
define ('BYTECODE_CACHE_EXISTS', function_exists('apc_cache_info') || function_exists('eaccelerator_info') || function_exists('xcache_info'));
#elseif WITH_BYTECODE_CACHE
define ('BYTECODE_CACHE_EXISTS', true);
#else
define ('BYTECODE_CACHE_EXISTS', false);
#endif

#if PHP50 || PHP51
if(!defined('E_RECOVERABLE_ERROR'))
    define ('E_RECOVERABLE_ERROR',4096);
error_reporting (E_ALL | E_STRICT | E_RECOVERABLE_ERROR);
#else
error_reporting (E_ALL | E_STRICT);
#endif

#if ENABLE_OPTIMIZED_SOURCE

#ifnot ENABLE_PHP_JELIX
#includephp core/jICoordPlugin.iface.php
#includephp core/jISelector.iface.php
#endif
#includephp core/jErrorHandler.lib.php
#includephp core/jException.lib.php
#includephp core/jContext.class.php
#includephp core/jConfig.class.php
#includephp core/jSelector.class.php
#includephp core/selector/jSelectorModule.class.php
#includephp core/selector/jSelectorActFast.class.php
#includephp core/selector/jSelectorAct.class.php
#includephp core/selector/jSelectorClass.class.php
#includephp core/selector/jSelectorDao.class.php
#includephp core/selector/jSelectorForm.class.php
#includephp core/selector/jSelectorIface.class.php
#includephp core/selector/jSelectorLoc.class.php
#includephp core/selector/jSelectorTpl.class.php
#includephp core/selector/jSelectorZone.class.php
#includephp core/selector/jSelectorSimpleFile.class.php
#includephp core/selector/jSelectorFile.lib.php
#includephp core/jUrl.class.php
#includephp core/jCoordinator.class.php
#includephp core/jController.class.php
#includephp core/jRequest.class.php
#includephp core/jResponse.class.php
#includephp core/jBundle.class.php
#includephp core/jLocale.class.php
#includephp core/jIncluder.class.php
#includephp core/jSession.class.php

#else

#ifnot ENABLE_PHP_JELIX
require (JELIX_LIB_CORE_PATH . 'jICoordPlugin.iface.php');
require (JELIX_LIB_CORE_PATH . 'jISelector.iface.php');
#endif
require (JELIX_LIB_CORE_PATH . 'jErrorHandler.lib.php');
require (JELIX_LIB_CORE_PATH . 'jException.lib.php');
require (JELIX_LIB_CORE_PATH . 'jContext.class.php');
require (JELIX_LIB_CORE_PATH . 'jConfig.class.php');
require (JELIX_LIB_CORE_PATH . 'jSelector.class.php');
require (JELIX_LIB_CORE_PATH . 'selector/jSelectorModule.class.php');
require (JELIX_LIB_CORE_PATH . 'selector/jSelectorActFast.class.php');
require (JELIX_LIB_CORE_PATH . 'selector/jSelectorAct.class.php');
require (JELIX_LIB_CORE_PATH . 'selector/jSelectorClass.class.php');
require (JELIX_LIB_CORE_PATH . 'selector/jSelectorDao.class.php');
require (JELIX_LIB_CORE_PATH . 'selector/jSelectorForm.class.php');
require (JELIX_LIB_CORE_PATH . 'selector/jSelectorIface.class.php');
require (JELIX_LIB_CORE_PATH . 'selector/jSelectorLoc.class.php');
require (JELIX_LIB_CORE_PATH . 'selector/jSelectorTpl.class.php');
require (JELIX_LIB_CORE_PATH . 'selector/jSelectorZone.class.php');
require (JELIX_LIB_CORE_PATH . 'selector/jSelectorSimpleFile.class.php');
require (JELIX_LIB_CORE_PATH . 'selector/jSelectorFile.lib.php');
require (JELIX_LIB_CORE_PATH . 'jUrl.class.php');
require (JELIX_LIB_CORE_PATH . 'jCoordinator.class.php');
require (JELIX_LIB_CORE_PATH . 'jController.class.php');
require (JELIX_LIB_CORE_PATH . 'jRequest.class.php');
require (JELIX_LIB_CORE_PATH . 'jResponse.class.php');
require (JELIX_LIB_CORE_PATH . 'jBundle.class.php');
require (JELIX_LIB_CORE_PATH . 'jLocale.class.php');
require (JELIX_LIB_CORE_PATH . 'jIncluder.class.php');
require (JELIX_LIB_CORE_PATH . 'jSession.class.php');
#endif

/**
 * The main object of Jelix which process all things
 * @global jCoordinator $gJCoord
 * @name $gJCoord
 */
$gJCoord = null;

/**
 * Object that contains all configuration values
 * @global stdobject $gJConfig
 * @name $gJConfig
 */
$gJConfig = null;

/**
 * contains path for __autoload function
 * @global array $gLibPath
 * @name $gLibPath
 * @see __autoload()
 */
$gLibPath=array('Db'=>JELIX_LIB_PATH.'db/', 'Dao'=>JELIX_LIB_PATH.'dao/',
 'Forms'=>JELIX_LIB_PATH.'forms/', 'Event'=>JELIX_LIB_PATH.'events/',
 'Tpl'=>JELIX_LIB_PATH.'tpl/', 'Acl'=>JELIX_LIB_PATH.'acl/', 'Controller'=>JELIX_LIB_PATH.'controllers/',
 'Auth'=>JELIX_LIB_PATH.'auth/', 'Installer'=>JELIX_LIB_PATH.'installer/');

/**
 * __autoload function used by php to try to load an unknown class
 */
function __autoload($class){
    if(preg_match('/^j(Dao|Tpl|Acl|Event|Db|Controller|Forms|Auth|Installer).*/i', $class, $m)){
        $f=$GLOBALS['gLibPath'][$m[1]].$class.'.class.php';
    }elseif(preg_match('/^cDao(?:Record)?_(.+)_Jx_(.+)_Jx_(.+)$/', $class, $m)){
        // pour les dao stockés en sessions notament
        $s = new jSelectorDao($m[1].'~'.$m[2], $m[3], false);
        if($GLOBALS['gJConfig']->compilation['checkCacheFiletime']){
            // si il faut verifier le filetime, alors on inclus via le jIncluder
            // au cas où il faudrait recompiler le dao avant l'inclusion de la classe
            jIncluder::inc($s);
            return;
        }else{
            $f = $s->getCompiledFilePath ();
            // on verifie que le fichier est là (dans le cas d'un temp purgé, cf bug #6062)
            if(!file_exists($f)){ // si absent, on recompile
                jIncluder::inc($s);
                return;
            }
        }
    }else{
        $f = JELIX_LIB_UTILS_PATH.$class.'.class.php';
    }

#if ENABLE_OPTIMIZED_SOURCE
    require_once($f);
#else
    if(file_exists($f)){
        require_once($f);
    }else{
        throw new Exception("Jelix fatal error : Unknow class $class");
    }
#endif
}


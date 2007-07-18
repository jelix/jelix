<?php
/**
* Initialize all defines and includes necessary files
*
#if ENABLE_OPTIMIZED_SOURCE
* Some line of code come from php framework Copix 2.3dev20050901
* They are copyrighted 2001-2005 CopixTeam (LGPL Licence)
#endif
* @package  jelix
* @subpackage core
* @author   Jouanneau Laurent
#if ENABLE_OPTIMIZED_SOURCE
* @author Croes Gerald
* @contributor Loic Mathaud
* @copyright 2005-2007 Jouanneau laurent
* @copyright 2001-2005 CopixTeam
* @copyright 2006 Mathaud Loic
* @link http://www.copix.org
#else
* @contributor Loic Mathaud
* @copyright 2005-2007 Jouanneau laurent
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
define ('JELIX_LIB_AUTH_PATH',    JELIX_LIB_PATH.'auth/');
define ('JELIX_LIB_DB_PATH',      JELIX_LIB_PATH.'db/');
define ('JELIX_LIB_ACL_PATH',     JELIX_LIB_PATH.'acl/');
define ('JELIX_LIB_DAO_PATH',     JELIX_LIB_PATH.'dao/');
define ('JELIX_LIB_EVENTS_PATH',  JELIX_LIB_PATH.'events/');
define ('JELIX_LIB_FORMS_PATH',   JELIX_LIB_PATH.'forms/');
define ('JELIX_LIB_REQUEST_PATH', JELIX_LIB_PATH.'core/request/');
define ('JELIX_LIB_RESPONSE_PATH',JELIX_LIB_PATH.'core/response/');
define ('JELIX_LIB_TPL_PATH',     JELIX_LIB_PATH.'tpl/');
define ('JELIX_LIB_CTRL_PATH',    JELIX_LIB_PATH.'controllers/');

// all path of libs
define ('LIB_PATH',           realpath(dirname (__FILE__).'/../').'/');
define ('JELIX_PLUGINS_PATH', LIB_PATH.'jelix-plugins/');
define ('JELIX_MODULE_PATH',  LIB_PATH.'jelix-modules/');

#if WITH_BYTECODE_CACHE == 'auto'
define ('BYTECODE_CACHE_EXISTS', function_exists('apc_cache_info')|| function_exists('eaccelerator_info'));
#endif

#if PHP50 || PHP51
if(!defined('E_RECOVERABLE_ERROR'))
    define ('E_RECOVERABLE_ERROR',4096);
error_reporting (E_ALL | E_STRICT | E_RECOVERABLE_ERROR);
#else
error_reporting (E_ALL | E_STRICT);
#endif

#if ENABLE_OPTIMIZED_SOURCE

#includephp core/jErrorHandler.lib.php
#includephp core/jException.lib.php
#includephp core/jContext.class.php
#includephp core/jConfig.class.php
#includephp core/jSelector.class.php
#includephp core/jUrl.class.php
#includephp core/jCoordinator.class.php
#includephp core/jController.class.php
#includephp core/jRequest.class.php
#includephp core/jResponse.class.php
#includephp core/jLocale.class.php
#includephp core/jIncluder.class.php
#ifnot ENABLE_PHP_JELIX
#includephp core/jICoordPlugin.iface.php
#endif
#else

// chargement du coeur
require (JELIX_LIB_CORE_PATH . 'jErrorHandler.lib.php');
require (JELIX_LIB_CORE_PATH . 'jException.lib.php');
require (JELIX_LIB_CORE_PATH . 'jContext.class.php');
require (JELIX_LIB_CORE_PATH . 'jConfig.class.php');
require (JELIX_LIB_CORE_PATH . 'jSelector.class.php');
require (JELIX_LIB_CORE_PATH . 'jUrl.class.php');
require (JELIX_LIB_CORE_PATH . 'jCoordinator.class.php');
require (JELIX_LIB_CORE_PATH . 'jController.class.php');
require (JELIX_LIB_CORE_PATH . 'jRequest.class.php');
require (JELIX_LIB_CORE_PATH . 'jResponse.class.php');
require (JELIX_LIB_CORE_PATH . 'jLocale.class.php');
require (JELIX_LIB_CORE_PATH . 'jIncluder.class.php');
#ifnot ENABLE_PHP_JELIX
require (JELIX_LIB_CORE_PATH . 'jICoordPlugin.iface.php');
#endif
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
$gLibPath=array('Db'=>JELIX_LIB_DB_PATH, 'Dao'=>JELIX_LIB_DAO_PATH,
 'Forms'=>JELIX_LIB_FORMS_PATH, 'Event'=>JELIX_LIB_EVENTS_PATH,
 'Tpl'=>JELIX_LIB_TPL_PATH, 'Acl'=>JELIX_LIB_ACL_PATH, 'Controller'=>JELIX_LIB_CTRL_PATH);

/**
 * __autoload function used by php to try to load an unknown class
 */
function __autoload($class){
   if(preg_match('/^j(Dao|Tpl|Acl|Event|Db|Controller|Forms).*$/', $class, $m)){
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

?>

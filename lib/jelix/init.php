<?php
/**
* @package  jelix
* @subpackage core
* @version  $Id$
* @author   Jouanneau Laurent
* @contributor Loic Mathaud
* @copyright 2005-2006 Jouanneau laurent
* @link     http://www.jelix.org
* @licence  GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

#expand define ('JELIX_VERSION', '__LIB_VERSION__');
define ('JELIX_NAMESPACE_BASE' , 'http://jelix.org/ns/');

// all path needed by jelix
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
define ('JELIX_LIB_TPL_PATH',      JELIX_LIB_PATH.'tpl/');
define ('JELIX_LIB_CTRL_PATH',      JELIX_LIB_PATH.'controllers/');

// all path of libs
define ('LIB_PATH',           realpath(dirname (__FILE__).'/../').'/');
define ('JELIX_PLUGINS_PATH', LIB_PATH.'jelix-plugins/');
define ('JELIX_MODULE_PATH',  LIB_PATH.'jelix-modules/');

// chargement du coeur
require_once (JELIX_LIB_CORE_PATH . 'jErrorHandler.lib.php');
require_once (JELIX_LIB_CORE_PATH . 'jException.lib.php');
require_once (JELIX_LIB_CORE_PATH . 'jContext.class.php');
require_once (JELIX_LIB_CORE_PATH . 'jConfig.class.php');
require_once (JELIX_LIB_CORE_PATH . 'jSelector.class.php');
require_once (JELIX_LIB_CORE_PATH . 'url/jUrl.class.php');
require_once (JELIX_LIB_CORE_PATH . 'jCoordinator.class.php');
require_once (JELIX_LIB_CORE_PATH . 'jController.class.php');
require_once (JELIX_LIB_CORE_PATH . 'jRequest.class.php');
require_once (JELIX_LIB_CORE_PATH . 'jResponse.class.php');
require_once (JELIX_LIB_CORE_PATH . 'jLocale.class.php');
require_once (JELIX_LIB_CORE_PATH . 'jIncluder.class.php');
require_once (JELIX_LIB_CORE_PATH . 'jIPlugin.iface.php');

// global variables
$gJCoord = null;
$gJConfig = array();
$gJContext = array();

$gLibPath=array('Db'=>JELIX_LIB_DB_PATH, 'Dao'=>JELIX_LIB_DAO_PATH,
 'Forms'=>JELIX_LIB_FORMS_PATH, 'Event'=>JELIX_LIB_EVENTS_PATH, 'Auth'=>JELIX_LIB_AUTH_PATH,
 'Tpl'=>JELIX_LIB_TPL_PATH, 'Acl'=>JELIX_LIB_ACL_PATH, 'Controller'=>JELIX_LIB_CTRL_PATH);


function __autoload($class){
   if(preg_match('/^j(Acl|Controller|Dao|Db|Forms|Event|Auth|Tpl).*$/', $class, $m)){
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

   //if(file_exists($f)){
      require_once($f);
   //}

}

?>

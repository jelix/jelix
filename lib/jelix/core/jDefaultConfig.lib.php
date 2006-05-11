<?php
/**
* @package    jelix
* @subpackage core
* @version    $Id$
* @author     Jouanneau Laurent
* @contributor
* @copyright  2005-2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


$gDefaultConfig = array (
  'defaultModule' => 'myapp',
  'defaultAction' => '_',
  'checkTrustedModules' => '',
  'trustedModules' => '', // liste des modules, séparés par des virgules, sans espace
  'pluginsPath' => 'app:plugins/,lib:jelix-plugins/',
  'modulesPath' => 'app:modules/,lib:jelix-modules/',
  'tplPluginsPath' => 'lib:jelix/tpl/plugins/',

  'defaultLocale' => 'fr_FR',
  'defaultCharset'=> 'ISO-8859-1',

  'dbProfils' => 'dbProfils.ini.php',

  'defaultTheme' => 'default',
  'use_error_handler' => '1',
  'plugins' => array(),
  'responses' =>
  array (
     'html'=>'jResponseHtml',
     'redirect'=>'jResponseRedirect',
     'redirectUrl'=>'jResponseRedirectUrl',
     'binary'=>'jResponseBinary',
     'text'=>'jResponseText',
     'jsonrpc'=>'jResponseJsonrpc',
     'xmlrpc'=>'jResponseXmlrpc',
     'xul'=>'jResponseXul',
     'xuloverlay'=>'jResponseXulOverlay',
     'xuldialog'=>'jResponseXulDialog',
     'xulpage'=>'jResponseXulPage',
     'rdf'=>'jResponseRdf'
  ),
  'error_handling'=> array (
        'messageLogFormat' => '%date%\\t[%code%]\\t%msg%\\t%file%\\t%line%\\n',
        'logFile' => 'error.log',
        'email' => 'root@localhost',
        'emailHeaders' => 'From: webmaster@yoursite.com\\nX-Mailer: Jelix\\nX-Priority: 1 (Highest)\\n',
        'error' => 'ECHO EXIT',
        'warning' =>  'ECHO ',
        'notice' =>  '',
        'strict' =>  '',
        'default' =>  'ECHO EXIT',
        'exception' => 'ECHO'
    ),

  'compilation' =>
  array (
    'checkCacheFiletime' => '1',
    'force' => ''
  ),
  'urlengine' =>
  array (
    'useIIS' => '',
    'IISPathKey' => '\'__JELIX_URL__\'',
    'IISStripslashesPathKey' => '1',
    'defaultEntrypoint' => 'index',
    'entrypointExtension' => '.php',
    'engine' => 'simple',
    'basePath'=>'/',
    'enableParser' => '1',
    'multiview' => '',
    'notfoundAct' => 'jelix~notfound'
  ),
  'simple_urlengine_entrypoints' =>
  array (
     'index' => '@classic',
     'xmlrpc'=> '@xmlrpc',
     'jsonrpc'=>'@jsonrpc'
  ),
  'logfiles'=>array(
   'default'=>'messages.log'
  )
);
?>

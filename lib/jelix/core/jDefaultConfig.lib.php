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
  'defaultAction' => 'default',
  'checkTrustedModules' => '',
  'trustedModules' => '', // liste des modules, séparés par des virgules, sans espace
  'pluginsPath' => 'app:plugins/,lib:jelix-plugins/',
  'modulesPath' => 'app:modules/,lib:jelix-modules/',
  'tplPluginsPath' => 'lib:jelix/tpl_plugins/',

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
     'jsonrpc'=>'jResponseJsonRpc',
     'xmlrpc'=>'jResponseXmlRpc',
     'xul'=>'jResponseXul',
     'xuloverlay'=>'jResponseXulOverlay',
     'xuldialog'=>'jResponseXulDialog',
     'xulpage'=>'jResponseXulPage'
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
    'check_cache_filetime' => '1',
    'force' => ''
  ),
  'urlengine' =>
  array (
    'use_IIS' => '',
    'IIS_path_key' => '\'__JELIX_URL__\'',
    'IIS_stripslashes_path_key' => '1',
    'default_entrypoint' => 'index',
    'entrypoint_extension' => '.php',
    'engine' => 'simple',
    'basepath'=>'',
    'enable_parser' => '1',
    'multiview_on' => '',
    'notfound_act' => 'jelix~notfound'
  ),
  'urlengine_specific_entrypoints' =>
  array (
     'index' => '@classic',
     'xmlrpc'=> '@xmlrpc',
     'jsonrpc'=>'@jsonrpc'
  )
);
?>

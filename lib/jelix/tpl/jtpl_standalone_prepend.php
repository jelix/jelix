<?php
/**
* @package     jTpl Standalone
* @version     $Id$
* @author      Mathaud Loic
* @contributor Laurent Jouanneau
* @copyright   2006 Mathaud Loic
* @copyright   2006 Jouanneau Laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
if(!defined('JTPL_PATH'))
    define('JTPL_PATH', dirname(__FILE__) . '/');
if(!defined('JTPL_CACHE_PATH'))
    define('JTPL_CACHE_PATH', realpath(JTPL_PATH.'temp/') . '/');
if(!defined('JTPL_PLUGIN_PATH'))
    define('JTPL_PLUGIN_PATH', realpath(JTPL_PATH.'plugins/') . '/');
if(!defined('JTPL_LOCALES_PATH'))
    define('JTPL_LOCALES_PATH', realpath(JTPL_PATH.'locales/') . '/');
if(!defined('JTPL_TEMPLATES_PATH'))
    define('JTPL_TEMPLATES_PATH', realpath(JTPL_PATH.'templates/') . '/'); 

$GLOBALS['jTplConfig'] = array(
    'tplpluginsPathList'=> array(
        'common' => array(JTPL_PLUGIN_PATH . 'common/'),
        'html' => array(JTPL_PLUGIN_PATH . 'html/')
    ),
    'compilation_force' => false,
    'lang'=>'fr',
    'localesGetter' => 'getLocales'
);

include(JTPL_PATH . 'jTpl.class.php');


function getLocales($locale) {
    return $locale;
}

?>

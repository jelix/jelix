<?php
/**
* @package     jTpl Standalone
* @author      Mathaud Loic
* @contributor Laurent Jouanneau
* @copyright   2006 Mathaud Loic
* @copyright   2006-2008 Jouanneau Laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

define('JTPL_PATH', dirname(__FILE__) . '/');

function getDummyLocales($locale) {
    return $locale;
}

class jTplConfig {

    /**
     * the path of the directory which contains the
     * templates
     */
    static $templatePath = '';

    /**
     * boolean which indicates if the templates
     * should be compiled at each call or not
     */
    static $compilationForce = false;

    /**
     * the lang activated in the templates
     */
    static $lang = 'fr';

    /**
     * the charset used in the templates
     */
    static $charset = 'UTF-8';

    /**
     * the function which allow to retrieve the locales used in your templates
     */
    static $localesGetter = 'getDummyLocales';

    /**
     * the path of the cache directory
     */
    static $cachePath = '';

    /**
     * the path of the directory which contains the
     * localization files of jtpl
     */
    static $localizedMessagesPath = '';

    /**
     * @internal
     */
    static $localizedMessages = array();

    /**
     * @internal
     */
    static $pluginPathList = array();
    
    
    static function addPluginsRepository($path){
        if(trim($path) == '') return;
        if(!file_exists($path)){
            throw new Exception('The given path, '.$path.' doesn\'t exists');
        }
        if(substr($path,-1) !='/')
            $path.='/';

        if ($handle = opendir($path)) {
            while (false !== ($f = readdir($handle))) {
                if ($f{0} != '.' && is_dir($path.$f)) {
                    self::$pluginPathList[$f][]= $path.$f.'/';
                }
            }
            closedir($handle);
        }        
    }
}

jTplConfig::$cachePath = realpath(JTPL_PATH.'temp/') . '/';
jTplConfig::$localizedMessagesPath = realpath(JTPL_PATH.'locales/') . '/';
jTplConfig::$templatePath = realpath(JTPL_PATH.'templates/') . '/';

jTplConfig::addPluginsRepository(realpath(JTPL_PATH.'plugins/'));

include(JTPL_PATH . 'jTpl.class.php');





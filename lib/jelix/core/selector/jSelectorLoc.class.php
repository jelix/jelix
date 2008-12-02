<?php
/**
* see jISelector.iface.php for documentation about selectors.
* @package     jelix
* @subpackage  core_selector
* @author      Laurent Jouanneau
* @contributor Rahal
* @contributor Julien Issler
* @contributor Baptiste Toinot
* @copyright   2005-2007 Laurent Jouanneau
* @copyright   2007 Rahal
* @copyright   2008 Julien Issler
* @copyright   2008 Baptiste Toinot
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * selector for localisation string
 *
 * localisation string are stored in file properties.
 * syntax : "module~prefixFile.keyString".
 * Corresponding file : locales/xx_XX/prefixFile.CCC.properties.
 * xx_XX and CCC are lang and charset set in the configuration
 *
 * @package    jelix
 * @subpackage core_selector
 */
class jSelectorLoc extends jSelectorModule {
    protected $type = 'loc';
    public $fileKey = '';
    public $messageKey = '';
    public $locale ='';
    public $charset='';
    public $_compiler = 'jLocalesCompiler';
    protected $_where;

    function __construct($sel, $locale=null, $charset=null){
        global $gJConfig;
        if ($locale === null){
            $locale = $gJConfig->locale;
        }
        if ($charset === null){
            $charset = $gJConfig->charset;
        }
        if(strpos($locale,'_') === false){
            $locale.='_'.strtoupper($locale);
        }
        $this->locale = $locale;
        $this->charset = $charset;
        $this->_suffix = '.'.$charset.'.properties';
        $this->_compilerPath=JELIX_LIB_CORE_PATH.'jLocalesCompiler.class.php';

#if ENABLE_PHP_JELIX
        if(jelix_scan_locale_sel($sel, $this)){
            if($this->module ==''){
                $this->module = jContext::get ();
            }
#else
        if(preg_match("/^(([a-zA-Z0-9_\.]+)~)?([a-zA-Z0-9_]+)\.([a-zA-Z0-9_\.]+)$/", $sel, $m)){
            if($m[1]!='' && $m[2]!=''){
                $this->module = $m[2];
            }else{
                $this->module = jContext::get ();
            }
            $this->resource = $m[3];
            $this->fileKey = $m[3];
            $this->messageKey = $m[4];
#endif
            $this->_createPath();
            $this->_createCachePath();
        }else{
            throw new jExceptionSelector('jelix~errors.selector.invalid.syntax', array($sel,$this->type));
        }
    }

    protected function _createPath(){
        global $gJConfig;
        if(!isset($gJConfig->_modulesPathList[$this->module])){
            if ($this->module == 'jelix')
                throw new Exception('jelix module is not enabled !!');
            throw new jExceptionSelector('jelix~errors.selector.module.unknow', $this->toString());
        }

        $locales = array($this->locale);
        $lang = substr($this->locale,0,2);
        $generic_locale = $lang.'_'.strtoupper($lang);
        if($this->locale !== $generic_locale)
            $locales[] = $generic_locale;

        foreach($locales as $locale){
            // check if the locale has been overloaded
            $overloadedPath = JELIX_APP_VAR_PATH.'overloads/'.$this->module.'/locales/'.$locale.'/'.$this->resource.$this->_suffix;
            if (is_readable ($overloadedPath)){
                $this->_path = $overloadedPath;
                $this->_where = 'overloaded/';
                $this->_cacheSuffix = '.'.$locale.'.'.$this->charset.'.php';
                return;
            }
            // else check for the original locale file
            $path = $gJConfig->_modulesPathList[$this->module].'locales/'.$locale.'/'.$this->resource.$this->_suffix;
            if (is_readable ($path)){
                $this->_where = 'modules/';
                $this->_path = $path;
                $this->_cacheSuffix = '.'.$locale.'.'.$this->charset.'.php';
                return;
            }
        }

        // to avoid infinite loop in a specific lang or charset, we should check if we don't
        // try to retrieve the same message as the one we use for the exception below,
        // and if it is this message, it means that the error message doesn't exist
        // in the specific lang or charset, so we retrieve it in en_EN language and UTF-8 charset
        if($this->toString() == 'jelix~errors.selector.invalid.target'){
            $l = 'en_EN';
            $c = 'UTF-8';
        }
        else{
            $l = null;
            $c = null;
        }
        throw new jExceptionSelector('jelix~errors.selector.invalid.target', array($this->toString(), "locale"), 1, $l, $c);
    }

    protected function _createCachePath(){
        // on ne partage pas le même cache pour tous les emplacements possibles
        // au cas où un overload était supprimé
        $this->_cachePath = JELIX_APP_TEMP_PATH.'compiled/locales/'.$this->_where.$this->module.'~'.$this->resource.$this->_cacheSuffix;
    }

    public function toString($full=false){
        if($full)
            return $this->type.':'.$this->module.'~'.$this->fileKey.'.'.$this->messageKey;
        else
            return $this->module.'~'.$this->fileKey.'.'.$this->messageKey;
    }
}

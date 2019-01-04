<?php
/**
* see Jelix/Core/Selector/SelectorInterface.php for documentation about selectors.
*
* @package     jelix
* @subpackage  core_selector
* @author      Laurent Jouanneau
* @copyright   2005-2018 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


/**
 * Template selector
 *
 * syntax : "module~tplName".
 * file : templates/tplName.tpl .
 * @package    jelix
 * @subpackage core_selector
 */
class jSelectorTpl extends jSelectorModule {
    protected $type = 'tpl';
    protected $_dirname = 'templates/';
    protected $_suffix = '.tpl';
    protected $_cachePrefix;
    public $outputType='';
    public $trusted=true;
    public $userModifiers = array();
    public $userFunctions = array();

    /**
     * @param string $sel the template selector
     * @param string $outputtype  the type of output (html, text..) By default, it takes the response type
     * @param boolean $trusted  says if the template file is trusted or not
     */
    function __construct($sel, $outputtype='', $trusted=true){
        if($outputtype == '') {
            if(jApp::coord()->response)
                $this->outputType = jApp::coord()->response->getFormatType();
            else
                $this->outputType = jApp::coord()->request->defaultResponseType;
        } else
            $this->outputType = $outputtype;
        $this->trusted = $trusted;
        $this->_compiler='jTplCompiler';
        $this->_compilerPath=JELIX_LIB_PATH.'tpl/jTplCompiler.class.php';
        parent::__construct($sel);
    }

    /**
     * @throws jExceptionSelector
     */
    protected function _createPath(){

        if(!jApp::isModuleEnabled($this->module)){
            throw new jExceptionSelector('jelix~errors.selector.module.unknown', $this->toString());
        }

        $locale = jApp::config()->locale;
        $fallbackLocale = jApp::config()->fallbackLocale;
        $path = $this->resource;
        $lpath = $locale.'/'.$this->resource;
        $flpath = '';

        if ($locale != $fallbackLocale && $fallbackLocale) {
            $flpath = $fallbackLocale.'/'.$this->resource;
        }

        if (($theme = jApp::config()->theme) != 'default') {
            if ($this->checkThemePath($theme.'/'.$this->module, $lpath, $flpath, $path)) {
                return;
            }
        }

        if ($this->checkThemePath('default/'.$this->module, $lpath, $flpath, $path)) {
            return;
        }

        // check if the template exists in the current module
        $mpath = jApp::getModulePath($this->module).$this->_dirname;
        $this->_path = $mpath.$locale.'/'.$this->resource.'.tpl';
        if (is_readable ($this->_path)){
            $this->_cachePrefix = 'modules/'.$this->module.'/'.$lpath;
            return;
        }

        if ($flpath) {
            $this->_path = $mpath.$fallbackLocale.'/'.$this->resource.'.tpl';
            if (is_readable ($this->_path)){
                $this->_cachePrefix = 'modules/'.$this->module.'/'.$flpath;
                return;
            }
        }

        $this->_path = $mpath.$this->resource.'.tpl';
        if (is_readable ($this->_path)){
            $this->_cachePrefix = 'modules/'.$this->module.'/'.$path;
            return;
        }

        throw new jExceptionSelector('jelix~errors.selector.invalid.target', array($this->toString(), "template"));
    }

    protected function checkThemePath($subDir, $lpath, $flpath, $path) {
        if (file_exists(jApp::varPath('themes/'.$subDir))) {
            // check if there is a redefined template for the current theme & locale in var/theme
            $this->_path = jApp::varPath('themes/'.$subDir.'/'.$lpath.'.tpl');
            if (is_readable ($this->_path)){
                $this->_cachePrefix = 'var/themes/'.$subDir.'/'.$lpath;
                return true;
            }

            if ($flpath) {
                // check if there is a redefined template for the current theme & fallback locale in var/theme
                $this->_path = jApp::varPath('themes/'.$subDir.'/'.$flpath.'.tpl');
                if (is_readable ($this->_path)){
                    $this->_cachePrefix = 'var/themes/'.$subDir.'/'.$flpath;
                    return true;
                }
            }

            // check if there is a redefined template for the current theme in var/theme
            $this->_path = jApp::varPath('themes/'.$subDir.'/'.$path.'.tpl');
            if (is_readable ($this->_path)){
                $this->_cachePrefix = 'var/themes/'.$subDir.'/'.$path;
                return true;
            }
        }

        if (file_exists(jApp::appPath('app/themes/'.$subDir))) {
            // check if there is a redefined template for the current theme & locale in app/theme
            $this->_path = jApp::appPath('app/themes/' . $subDir . '/' . $lpath . '.tpl');
            if (is_readable($this->_path)) {
                $this->_cachePrefix = 'app/themes/' . $subDir . '/' . $lpath;
                return true;
            }

            if ($flpath) {
                // check if there is a redefined template for the current theme & fallback locale in app/theme
                $this->_path = jApp::appPath('app/themes/' . $subDir . '/' . $flpath . '.tpl');
                if (is_readable($this->_path)) {
                    $this->_cachePrefix = 'app/themes/' . $subDir . '/' . $flpath;
                    return true;
                }
            }

            // check if there is a redefined template for the current theme in app/theme
            $this->_path = jApp::appPath('app/themes/' . $subDir . '/' . $path . '.tpl');
            if (is_readable($this->_path)) {
                $this->_cachePrefix = 'app/themes/' . $subDir . '/' . $path;
                return true;
            }
        }
        return false;
    }



    protected function _createCachePath(){
        // don't share the same cache for all the possible dirs
        // in case of overload removal
        $this->_cachePath = jApp::tempPath('compiled/templates/'.$this->_cachePrefix.'_'.$this->outputType.($this->trusted?'_t':'').'_15'.$this->_cacheSuffix);
    }
}
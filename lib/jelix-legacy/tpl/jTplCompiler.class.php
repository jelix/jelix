<?php
/**
* @package     jelix
* @subpackage  jtpl
* @author      Laurent Jouanneau
* @contributor Loic Mathaud (standalone version), Dominique Papin, DSDenes, Christophe Thiriot, Julien Issler, Brice Tence
* @copyright   2005-2014 Laurent Jouanneau
* @copyright   2006 Loic Mathaud, 2007 Dominique Papin, 2009 DSDenes, 2010 Christophe Thiriot
* @copyright   2010 Julien Issler, 2010 Brice Tence
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(__DIR__.'/jTplCompilerAbstract.php');

/**
 * This is the compiler of templates: it converts a template into a php file.
 * @package     jelix
 * @subpackage  jtpl
 */
class jTplCompiler extends jTplCompilerAbstract implements jISimpleCompiler {


    /**
     * Launch the compilation of a template
     *
     * Store the result (a php content) into a cache file given by the selector.
     * @param jSelectorTpl $selector the template selector
     * @return boolean true if ok
     */
    public function compile ($selector) {
        $this->_sourceFile = $selector->getPath();
        $this->outputType = $selector->outputType;
        $this->trusted = $selector->trusted;
        $md5 = md5($selector->module.'_'.$selector->resource.'_'.$this->outputType.($this->trusted?'_t':''));

        jApp::pushCurrentModule($selector->module);

        if (!file_exists($this->_sourceFile)) {
            $this->doError0('errors.tpl.not.found');
        }
        
        $header = "if (jApp::config()->compilation['checkCacheFiletime'] &&\n";
        $header .= "filemtime('".$this->_sourceFile.'\') > '.filemtime($this->_sourceFile)."){ return false;\n} else {\n";
        $footer = "return true;}\n";

        $this->compileString(file_get_contents($this->_sourceFile), $selector->getCompiledFilePath(),
            $selector->userModifiers, $selector->userFunctions, $md5, $header, $footer);

        jApp::popCurrentModule();
        return true;
    }

    protected function _saveCompiledString($cachefile, $result) {
        jFile::write($cachefile, $result);
    }

    protected function getCompiledLocaleRetriever($locale) {
        return 'jLocale::get(\''.$locale.'\')';
    }

    public function addMetaContent ($content) {
        $this->_metaBody .= $content."\n";
    }

    /**
     * Try to find a plugin
     * @param string $type type of plugin (function, modifier...)
     * @param string $name the plugin name
     * @return array|boolean an array containing the path of the plugin
     *                      and the name of the plugin function, or false if not found
     */
    protected function _getPlugin ($type, $name) {
        $foundPath = '';

        $config = jApp::config();
        if (isset($config->{'_tplpluginsPathList_'.$this->outputType})) {
            foreach ($config->{'_tplpluginsPathList_'.$this->outputType} as $path) {
                $foundPath = $path.$type.'.'.$name.'.php';
                if (file_exists($foundPath)) {
                    return array($foundPath, 'jtpl_'.$type.'_'.$this->outputType.'_'.$name);
                }
            }
        }

        if (isset($config->_tplpluginsPathList_common)) {
            foreach ($config->_tplpluginsPathList_common as $path) {
                $foundPath = $path.$type.'.'.$name.'.php';
                if (file_exists($foundPath)) {
                    return array($foundPath, 'jtpl_'.$type.'_common_'.$name);
                }
            }
        }
        return false;
    }

    public function doError0 ($err) {
        throw new jException('jelix~'.$err,array($this->_sourceFile));
    }

    public function doError1 ($err, $arg) {
        throw new jException('jelix~'.$err,array($arg, $this->_sourceFile));
    }

    public function doError2 ($err, $arg1, $arg2) {
        throw new jException('jelix~'.$err, array($arg1, $arg2, $this->_sourceFile));
    }
}

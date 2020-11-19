<?php
/**
 * @package     jelix
 * @subpackage  jtpl
 *
 * @author      Laurent Jouanneau
 * @contributor Loic Mathaud (standalone version), Dominique Papin, DSDenes, Christophe Thiriot, Julien Issler, Brice Tence
 *
 * @copyright   2005-2015 Laurent Jouanneau
 * @copyright   2006 Loic Mathaud, 2007 Dominique Papin, 2009 DSDenes, 2010 Christophe Thiriot
 * @copyright   2010 Julien Issler, 2010 Brice Tence
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * This is the compiler of templates: it converts a template into a php file.
 *
 * @package     jelix
 * @subpackage  jtpl
 */
class jTplCompiler extends \Jelix\Castor\CompilerCore implements jISimpleCompiler
{
    protected static $castorPluginsPath = null;

    public function __construct()
    {
        parent::__construct();
        if (self::$castorPluginsPath === null) {
            $config = new \Jelix\Castor\Config('');
            self::$castorPluginsPath = $config->pluginPathList;
        }
    }

    /**
     * Launch the compilation of a template.
     *
     * Store the result (a php content) into a cache file given by the selector.
     *
     * @param jSelectorTpl $selector the template selector
     *
     * @return bool true if ok
     */
    public function compile($selector)
    {
        $this->_sourceFile = $selector->getPath();
        $this->outputType = $selector->outputType;
        $this->trusted = $selector->trusted;
        $md5 = md5($selector->module.'_'.$selector->resource.'_'.$this->outputType.($this->trusted ? '_t' : ''));

        jApp::pushCurrentModule($selector->module);

        if (!file_exists($this->_sourceFile)) {
            $this->doError0('errors.tpl.not.found');
        }

        $header = "if (jApp::config()->compilation['checkCacheFiletime'] &&\n";
        $header .= "filemtime('".$this->_sourceFile.'\') > '.filemtime($this->_sourceFile)."){ return false;\n} else {\n";
        $footer = "return true;}\n";

        $this->compileString(
            file_get_contents($this->_sourceFile),
            $selector->getCompiledFilePath(),
            $selector->userModifiers,
            $selector->userFunctions,
            $md5,
            $header,
            $footer
        );

        jApp::popCurrentModule();

        return true;
    }

    protected function _saveCompiledString($cachefile, $result)
    {
        jFile::write($cachefile, $result);
    }

    protected function getCompiledLocaleRetriever($locale)
    {
        return 'jLocale::get(\''.$locale.'\')';
    }

    public function addMetaContent($content)
    {
        $this->_metaBody .= $content."\n";
    }

    /**
     * Try to find a plugin.
     *
     * @param string $type type of plugin (function, modifier...)
     * @param string $name the plugin name
     *
     * @return array|bool an array containing the path of the plugin
     *                    and the name of the plugin function, or false if not found
     */
    protected function _getPlugin($type, $name)
    {
        $config = jApp::config();

        $checker = function ($list, $outputType) use ($type, $name) {
            foreach ($list as $path) {
                $foundPath = $path.$type.'.'.$name.'.php';
                if (file_exists($foundPath)) {
                    return array($foundPath, 'jtpl_'.$type.'_'.$outputType.'_'.$name);
                }
            }

            return null;
        };

        if (isset($config->{'_tplpluginsPathList_'.$this->outputType})) {
            $plugin = $checker($config->{'_tplpluginsPathList_'.$this->outputType}, $this->outputType);
            if ($plugin !== null) {
                return $plugin;
            }
        }

        if (isset($config->_tplpluginsPathList_common)) {
            $plugin = $checker($config->_tplpluginsPathList_common, 'common');
            if ($plugin !== null) {
                return $plugin;
            }
        }
        if (isset(self::$castorPluginsPath[$this->outputType])) {
            $plugin = $checker(self::$castorPluginsPath[$this->outputType], $this->outputType);
            if ($plugin !== null) {
                return $plugin;
            }
        }

        if (isset(self::$castorPluginsPath['common'])) {
            $plugin = $checker(self::$castorPluginsPath['common'], 'common');
            if ($plugin !== null) {
                return $plugin;
            }
        }

        return false;
    }

    public function doError0($err)
    {
        throw new jException('jelix~'.$err, array($this->_sourceFile));
    }

    public function doError1($err, $arg)
    {
        throw new jException('jelix~'.$err, array($arg, $this->_sourceFile));
    }

    public function doError2($err, $arg1, $arg2)
    {
        throw new jException('jelix~'.$err, array($arg1, $arg2, $this->_sourceFile));
    }
}

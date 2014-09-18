<?php
/**
* @package     jTpl Standalone
* @author      Loic Mathaud
* @contributor Laurent Jouanneau
* @copyright   2006 Loic Mathaud
* @copyright   2006-2008 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

define('JTPL_PATH', __DIR__ . '/');

function getDummyLocales($locale) {
    return $locale;
}

class jTplConfig {

    /**
     * the path of the directory which contains the
     * templates. The path should have a / at the end.
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
    static $lang = 'en';

    /**
     * the charset used in the templates
     */
    static $charset = 'UTF-8';

    /**
     * the function which allow to retrieve the locales used in your templates
     */
    static $localesGetter = 'getDummyLocales';

    /**
     * the path of the cache directory.  The path should have a / at the end.
     */
    static $cachePath = '';

    /**
     * the path of the directory which contains the
     * localization files of jtpl.  The path should have a / at the end.
     */
    static $localizedMessagesPath = '';

    /**
     * umask for directories created in the cache directory
     */
    static $umask = 0000;

    /**
     * permissions for directories created in the cache directory
     */
    static $chmodDir = 0755;

    /**
     * permissions for cache files
     */
    static $chmodFile = 0644;

    /**
     * @internal
     */
    static $localizedMessages = array();

    /**
     * @internal
     */
    static $pluginPathList = array();

    static function addPluginsRepository ($path) {
        if (trim($path) == '') return;

        if (!file_exists($path)) {
            throw new Exception('The given path, '.$path.' doesn\'t exists');
        }

        if (substr($path,-1) != '/')
            $path .= '/';

        if ($handle = opendir($path)) {
            while (false !== ($f = readdir($handle))) {
                if ($f[0] != '.' && is_dir($path.$f)) {
                    self::$pluginPathList[$f][] = $path.$f.'/';
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

include(JTPL_PATH . 'jTplAbstract.php');
require_once(JTPL_PATH.'jTplCompilerAbstract.php');

class jTpl extends jTplAbstract {
    #expand     const VERSION = '__JTPL_VERSION__';

    /**
     * include the compiled template file and call one of the generated function
     * @param string|jSelectorTpl $tpl template selector
     * @param string $outputtype the type of output (html, text etc..)
     * @param boolean $trusted  says if the template file is trusted or not
     * @return string the suffix name of the function to call
     */
    protected function getTemplate ($tpl, $outputtype = '', $trusted = true) {
        $tpl = jTplConfig::$templatePath . $tpl;
        if ($outputtype == '')
            $outputtype = 'html';

        $cachefile = dirname($this->_templateName).'/';
        if ($cachefile == './')
            $cachefile = '';

        if (jTplConfig::$cachePath == '/' || jTplConfig::$cachePath == '')
            throw new Exception('cache path is invalid ! its value is: "'.jTplConfig::$cachePath.'".');

        $cachefile = jTplConfig::$cachePath.$cachefile.$outputtype.($trusted?'_t':'').'_'.basename($tpl);

        $mustCompile = jTplConfig::$compilationForce || !file_exists($cachefile);
        if (!$mustCompile) {
            if (filemtime($tpl) > filemtime($cachefile)) {
                $mustCompile = true;
            }
        }

        if ($mustCompile) {
            include_once(JTPL_PATH . 'jTplCompiler.class.php');

            $compiler = new jTplCompiler();
            $compiler->compile($this->_templateName, $tpl, $outputtype, $trusted,
                               $this->userModifiers, $this->userFunctions);
        }
        require_once($cachefile);
        return md5($tpl.'_'.$outputtype.($trusted?'_t':''));
    }

    public function fetch ($tpl, $outputtype='', $trusted = true, $callMeta=true) {
        return $this->_fetch($tpl, $tpl, $outputtype, $trusted, $callMeta);
    }
    
    protected function loadCompiler() {
        require_once(JTPL_PATH . 'jTplCompiler.class.php');
        return  $cachePath = jTplConfig::$cachePath . '/virtuals/';
    }

    protected function compilationNeeded($cacheFile) {
        return jTplConfig::$compilationForce || !file_exists($cacheFile);
    }

    /**
     * return the current encoding
     * @return string the charset string
     * @since 1.0b2
     */
    public static function getEncoding () {
        return jTplConfig::$charset;
    }


    public function getLocaleString($locale) {
        $getter = jTplConfig::$localesGetter;
        if ($getter)
            $res = call_user_func($getter, $locale);
        else
            $res = $locale;
        return $res;
    }
}


class jTplCompiler extends jTplCompilerAbstract {
    private $_locales;
    /**
     * Initialize some properties
     */
    function __construct () {
        parent::__construct();
        require_once(jTplConfig::$localizedMessagesPath.jTplConfig::$lang.'.php');
        $this->_locales = jTplConfig::$localizedMessages;
    }

    /**
     * Launch the compilation of a template
     *
     * Store the result (a php content) into a cache file inside the cache directory
     * @param string $tplfile the file name that contains the template
     * @return boolean true if ok
     */
    public function compile ($tplName, $tplFile, $outputtype, $trusted,
                             $userModifiers = array(), $userFunctions = array()) {
        $this->_sourceFile = $tplFile;
        $this->outputType = $outputtype;
        $cachefile = jTplConfig::$cachePath .dirname($tplName).'/'.$this->outputType.($trusted?'_t':'').'_'. basename($tplName);
        $this->trusted = $trusted;
        $md5 = md5($tplFile.'_'.$this->outputType.($this->trusted?'_t':''));

        if (!file_exists($this->_sourceFile)) {
            $this->doError0('errors.tpl.not.found');
        }

        $this->compileString(file_get_contents($this->_sourceFile), $cachefile,
            $userModifiers, $userFunctions, $md5);
        return true;
    }

    protected function _saveCompiledString($cachefile, $result) {
        $_dirname = dirname($cachefile).'/';

        if (!is_dir($_dirname)) {
            umask(jTplConfig::$umask);
            mkdir($_dirname, jTplConfig::$chmodDir, true);
        }
        else if (!@is_writable($_dirname)) {
            throw new Exception (sprintf($this->_locales['file.directory.notwritable'], $cachefile, $_dirname));
        }

        // write to tmp file, then rename it to avoid
        // file locking race condition
        $_tmp_file = tempnam($_dirname, 'wrt');

        if (!($fd = @fopen($_tmp_file, 'wb'))) {
            $_tmp_file = $_dirname . '/' . uniqid('wrt');
            if (!($fd = @fopen($_tmp_file, 'wb'))) {
                throw new Exception(sprintf($this->_locales['file.write.error'], $cachefile, $_tmp_file));
            }
        }

        fwrite($fd, $result);
        fclose($fd);

        // Delete the file if it already exists (this is needed on Win,
        // because it cannot overwrite files with rename()
        if (substr(PHP_OS,0,3) == 'WIN' && file_exists($cachefile)) {
            @unlink($cachefile);
        }

        @rename($_tmp_file, $cachefile);
        @chmod($cachefile, jTplConfig::$chmodFile);
    }
    
    protected function getCompiledLocaleRetriever($locale) {
        return '$t->getLocaleString(\''.$locale.'\')';
    }
    
    protected function _getPlugin ($type, $name) {
        $foundPath = '';

        if (isset(jTplConfig::$pluginPathList[$this->outputType])) {
            foreach (jTplConfig::$pluginPathList[$this->outputType] as $path) {
                $foundPath = $path.$type.'.'.$name.'.php';

                if (file_exists($foundPath)) {
                    return array($foundPath, 'jtpl_'.$type.'_'.$this->outputType.'_'.$name);
                }
            }
        }
        if (isset(jTplConfig::$pluginPathList['common'])) {
            foreach (jTplConfig::$pluginPathList['common'] as $path) {
                $foundPath = $path.$type.'.'.$name.'.php';
                if (file_exists($foundPath)) {
                    return array($foundPath, 'jtpl_'.$type.'_common_'.$name);
                }
            }
        }
        return false;
    }

    public function doError0 ($err) {
        throw new Exception(sprintf($this->_locales[$err], $this->_sourceFile));
    }

    public function doError1 ($err, $arg) {
        throw new Exception(sprintf($this->_locales[$err], $arg, $this->_sourceFile));
    }

    public function doError2 ($err, $arg1, $arg2) {
        throw new Exception(sprintf($this->_locales[$err], $arg1, $arg2, $this->_sourceFile));
    }
}

<?php
/**
 * @package    jelix
 * @subpackage core
 *
 * @author     Laurent Jouanneau
 * @author     Gerald Croes
 * @copyright  2001-2005 CopixTeam, 2005-2018 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
use Jelix\PropertiesFile\Parser;
use Jelix\PropertiesFile\Properties;

/**
 * a bundle contains all readed properties in a given language, and for all charsets.
 *
 * @package  jelix
 * @subpackage core
 */
class jBundle
{
    /**
     * @var jISelector
     */
    public $fic;
    /**
     * @var string
     */
    public $locale;

    protected $_loadedCharset = array();
    protected $_strings = array();

    /**
     * constructor.
     *
     * @param jISelector $file   selector of a properties file
     * @param string     $locale the code lang
     */
    public function __construct($file, $locale)
    {
        $this->fic = $file;
        $this->locale = $locale;
    }

    /**
     * get the translation.
     *
     * @param string $key     the locale key
     * @param string $charset
     *
     * @return string the localized string
     */
    public function get($key, $charset = null)
    {
        if ($charset == null) {
            $charset = jApp::config()->charset;
        }
        if (!in_array($charset, $this->_loadedCharset)) {
            $this->_loadLocales($charset);
        }

        if (isset($this->_strings[$charset][$key])) {
            return $this->_strings[$charset][$key];
        }

        return null;
    }

    /**
     * Loads the resources for a given locale/charset.
     *
     * @param string $charset the charset
     */
    protected function _loadLocales($charset)
    {
        $this->_loadedCharset[] = $charset;

        $source = $this->fic->getPath();
        $cache = $this->fic->getCompiledFilePath();

        // check if we have a compiled version of the ressources

        if (is_readable($cache)) {
            $okcompile = true;

            if (jApp::config()->compilation['force']) {
                $okcompile = false;
            } else {
                if (jApp::config()->compilation['checkCacheFiletime']) {
                    if (is_readable($source) && filemtime($source) > filemtime($cache)) {
                        $okcompile = false;
                    }
                }
            }

            if ($okcompile) {
                $this->_strings[$charset] = include $cache;

                return;
            }
        }
        $properties = new Properties();
        $reader = new Parser();
        $reader->parseFromFile($source, $properties, $charset);
        $this->_strings[$charset] = $properties->getAllProperties();
        $content = '<?php return '.var_export($this->_strings[$charset], true).";\n";
        jFile::write($cache, $content);
    }
}

<?php
/**
 * @author     Laurent Jouanneau
 * @author     Gerald Croes
 * @copyright  2001-2005 CopixTeam, 2005-2018 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Locale;

use Jelix\Core\App;
use Jelix\PropertiesFile\Parser;
use Jelix\PropertiesFile\Properties;

/**
 * a bundle contains all readed properties in a given language.
 */
class Bundle
{
    /**
     * @var LocaleSelector
     */
    protected $_file;

    /**
     * @var string
     */
    protected $_locale;

    protected $_strings = null;

    /**
     * constructor.
     *
     * @param LocaleSelector $file   selector of a properties file
     * @param string         $locale the code lang
     */
    public function __construct(LocaleSelector $file, $locale)
    {
        $this->_file = $file;
        $this->_locale = $locale;
    }

    /**
     * get the translation.
     *
     * @param string $key     the locale key
     *
     * @return string the localized string
     */
    public function get($key)
    {
        if ($this->_strings === null) {
            $this->_loadLocales();
        }

        if (isset($this->_strings[$key])) {
            return $this->_strings[$key];
        }

        return null;
    }

    /**
     * Loads the resources for a given locale
     */
    protected function _loadLocales()
    {
        $source = $this->_file->getPath();
        $cache = $this->_file->getCompiledFilePath();

        // check if we have a compiled version of the ressources

        if (is_readable($cache)) {
            $okcompile = true;

            if (App::config()->compilation['force']) {
                $okcompile = false;
            } else {
                if (App::config()->compilation['checkCacheFiletime']) {
                    if (is_readable($source) && filemtime($source) > filemtime($cache)) {
                        $okcompile = false;
                    }
                }
            }

            if ($okcompile) {
                $this->_strings = include $cache;

                return;
            }
        }

        $properties = new Properties();
        $reader = new Parser();
        $reader->parseFromFile($source, $properties);
        $this->_strings = $properties->getAllProperties();
        $content = '<?php return '.var_export($this->_strings, true).";\n";
        \jFile::write($cache, $content);
    }
}

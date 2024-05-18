<?php
/**
 * @author     Laurent Jouanneau
 * @author     Gerald Croes
 * @copyright  2001-2005 CopixTeam, 2005-2023 Laurent Jouanneau
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
            $cache = $this->_file->getCompiledFilePath();
            $this->_strings = include $cache;
        }

        if (isset($this->_strings[$key])) {
            return $this->_strings[$key];
        }

        return null;
    }
}

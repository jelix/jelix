<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2009-2018 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer;

/**
 * Installer Exception.
 *
 * It handles installer messages.
 *
 * @since 1.7
 */
class Exception extends \Exception
{
    /**
     * the locale key.
     *
     * @var string
     */
    protected $localeKey = '';

    /**
     * parameters for the locale key.
     */
    protected $localeParams;

    /**
     * @param string $localekey    a locale key
     * @param array  $localeParams parameters for the message (for sprintf)
     */
    public function __construct($localekey, $localeParams = null)
    {
        $this->localeKey = $localekey;
        $this->localeParams = $localeParams;
        parent::__construct($localekey, 0);
    }

    /**
     * getter for the locale parameters.
     *
     * @return string[]
     */
    public function getLocaleParameters()
    {
        return $this->localeParams;
    }

    /**
     * getter for the locale key.
     *
     * @return string
     */
    public function getLocaleKey()
    {
        return $this->localeKey;
    }
}

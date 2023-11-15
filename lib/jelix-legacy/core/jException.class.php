<?php
/**
 * @package     jelix
 * @subpackage  core
 *
 * @author      Laurent Jouanneau
 * @contributor Sylvain de Vathaire, Julien Issler
 *
 * @copyright   2005-2014 laurent Jouanneau, 2007 Sylvain de Vathaire
 * @copyright   2008 Julien Issler
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * Jelix Exception.
 *
 * It handles locale messages. Message property contains the locale key,
 * and a new property contains the localized message.
 *
 * @package  jelix
 * @subpackage core
 */
class jException extends Exception
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
    protected $localeParams = array();

    /**
     * @param string $localekey    a locale key
     * @param array  $localeParams parameters for the message (for sprintf)
     * @param int    $code         error code (can be provided by the localized message)
     * @param string $lang
     * @param string $charset  deprecated. Not used anymore
     */
    public function __construct($localekey, $localeParams = array(), $code = 1, $lang = null, $charset = null)
    {
        $this->localeKey = $localekey;
        $this->localeParams = $localeParams;
        if ($charset !== null) {
            trigger_error("jException::__construct(): charset parameter is deprecated and not used any more.", E_USER_DEPRECATED);
        }
        try {
            $message = \Jelix\Locale\Locale::get($localekey, $localeParams, $lang);
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        if (preg_match('/^\s*\((\d+)\)(.+)$/ms', $message, $m)) {
            $code = $m[1];
            $message = $m[2];
        }
        parent::__construct($message, $code);
    }

    /**
     * getter for the locale parameters.
     *
     * @return string
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

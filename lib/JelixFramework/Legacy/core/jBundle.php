<?php
/**
 * @package  Jelix\Legacy
 *
 * @author   Laurent Jouanneau
 * @contributor
 *
 * @copyright 2014 Laurent Jouanneau
 *
 * @see     http://www.jelix.org
 * @licence  MIT
 */

use Jelix\Locale\LocaleSelector;

/**
 * dummy class for compatibility.
 *
 * @see \Jelix\Locale\Bundle
 * @deprecated
 */
class jBundle extends \Jelix\Locale\Bundle
{
    /**
     * @var LocaleSelector
     * @deprecated
     */
    public $fic;

    /**
     * @var string
     * @deprecated
     */
    public $locale;

    public function __construct($file, $locale)
    {
        $this->fic = $file;
        $this->locale = $locale;

        parent::__construct($file, $locale);
    }

    public function get($key, $charset = null)
    {
        if ($charset !== null) {
            trigger_error("jBundle::get(): charset parameter is deprecated and not used any more.", E_USER_DEPRECATED);
        }
        return parent::get($key);
    }
}

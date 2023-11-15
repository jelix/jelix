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

/**
 * dummy class for compatibility.
 *
 * @see \Jelix\Locale\LocaleSelector
 * @deprecated
 */
class jSelectorLoc extends \Jelix\Locale\LocaleSelector
{
    /**
     * @var string
     */
    public $charset = 'UTF-8';

    public function __construct($sel, $locale = null, $charset = null)
    {
        if ($charset !== null) {
            trigger_error("jSelectorLoc::__construct(): charset parameter is deprecated and not used any more.", E_USER_DEPRECATED);
        }
        parent::__construct($sel, $locale);
    }
}

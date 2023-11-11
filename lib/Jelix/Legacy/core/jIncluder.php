<?php
/**
 * @package  Jelix\Legacy
 *
 * @author   Laurent Jouanneau
 * @contributor
 *
 * @copyright 2014-2023 Laurent Jouanneau
 *
 * @see     http://www.jelix.org
 * @licence  MIT
 */

/**
 * dummy class for compatibility.
 *
 * @see \Jelix\Core\Includer\Includer
 * @deprecated
 */
class jIncluder
{
    private function __construct()
    {
    }

    public static function inc($aSelector)
    {
        \Jelix\Core\Includer\Includer::inc($aSelector);
    }

    public static function incAll($aType)
    {
        return \Jelix\Core\Includer\Includer::incAll($aType);
    }

    public static function clear()
    {
        \Jelix\Core\Includer\Includer::clear();
    }
}

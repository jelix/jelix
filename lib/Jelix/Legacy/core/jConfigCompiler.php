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
 * @see \Jelix\Core\Config\Compiler
 * @deprecated
 */
class jConfigCompiler
{
    private function __construct()
    {
    }

    public static function read($configFile, $allModuleInfo = false, $isCli = false, $pseudoScriptName = '')
    {
        $compiler = new \Jelix\Core\Config\Compiler($configFile, $pseudoScriptName);

        return $compiler->read($allModuleInfo);
    }

    public static function readAndCache($configFile, $isCli = null, $pseudoScriptName = '')
    {
        $compiler = new \Jelix\Core\Config\Compiler($configFile, $pseudoScriptName);

        return $compiler->readAndCache();
    }

    public static function findServerName($ext = '.php', $isCli = false)
    {
        return \Jelix\Core\Config\Compiler::findServerName($ext);
    }
}

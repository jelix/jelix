<?php
/**
* @package  Jelix\Legacy
* @author   Laurent Jouanneau
* @contributor
* @copyright 2014 Laurent Jouanneau
* @link     http://www.jelix.org
* @licence  MIT
*/

$GLOBALS['JELIX_LEGACY_CLASSES'] = json_decode(file_get_contents(__DIR__.'/mapping.json'), true);

function jelix_legacy_autoload($class) {
    if (isset($GLOBALS['JELIX_LEGACY_CLASSES'][$class])) {
        $f = __DIR__.'/'.$GLOBALS['JELIX_LEGACY_CLASSES'][$class];
        if (file_exists($f)) {
            require($f);
        }
    }
}

spl_autoload_register("jelix_legacy_autoload");

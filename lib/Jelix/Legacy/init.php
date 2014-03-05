<?php
/**
* @package  Jelix\Legacy
* @author   Laurent Jouanneau
* @contributor
* @copyright 2014 Laurent Jouanneau
* @link     http://www.jelix.org
* @licence  MIT
*/

$GLOBALS['JELIX_LEGACY_CLASSES'] = array(
    "jApp" => __DIR__.'/core/jApp.php',
    "jAppManager" => __DIR__.'/core/jAppManager.php',
    "jAutoloader" => __DIR__.'/core/jAutoloader.php',
    "jClasses" => __DIR__.'/utils/jClasses.php',
    "jClassBinding" => __DIR__.'/utils/jClassBinding.php',
    "jConfig" => __DIR__.'/core/jConfig.php',
    "jConfigAutoloader" => __DIR__.'/core/jConfigAutoloader.php',
    "jConfigCompiler" => __DIR__.'/core/jConfigCompiler.php',
    "jIMultiFileCompiler" => __DIR__.'/core/jIMultiFileCompiler.php',
    "jIncluder" => __DIR__.'/core/jIncluder.php',
    "jISimpleCompiler" => __DIR__.'/core/jISimpleCompiler.php',
    "jProfiles" => __DIR__.'/utils/jProfiles.php',
);


function jelix_legacy_autoload($class) {
    if (isset($GLOBALS['JELIX_LEGACY_CLASSES'][$class])) {
        $f = $GLOBALS['JELIX_LEGACY_CLASSES'][$class];
        if (file_exists($f)) {
            require($f);
        }
    }
}

spl_autoload_register("jelix_legacy_autoload");

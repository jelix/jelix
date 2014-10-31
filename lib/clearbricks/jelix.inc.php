<?php

function __($str)
{
    return $str;
}

class jelixClearBricksAutoloader {
    static protected $files;
    static function init() {
        self:: $files = array(
            'files'	=> __DIR__.'/common/lib.files.php',
            'path'	=> __DIR__.'/common/lib.files.php',
        );
    }
    static function load($name) {
        if (isset(self::$files[$name])) {
            require_once(self::$files[$name]);
        }
    }
}

spl_autoload_register("jelixClearBricksAutoloader::load");

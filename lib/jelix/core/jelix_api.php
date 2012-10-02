<?php

/**
 * pure PHP version of API provided by the extension of Jelix for PHP
 * @package      jelix
 * @subpackage   core
 * @author       Laurent Jouanneau
 * @copyright    2012 Laurent Jouanneau
 * @link         http://www.jelix.org
 * @licence      GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

if (function_exists('jelix_version')) {
    return;
}


function jelix_read_ini($fileName, $config = null) {
    $conf = jIniFile::read($fileName);
    if ($config !== null) {
        foreach ($conf as $k=>$v) {
            if (!isset($config->$k)) {
                $config->$k = $v;
                continue;
            }
    
            if ($k[1] == '_')
                continue;
            if (is_array($v)) {
                $config->$k = array_merge($config->$k, $v);
            }
            else {
                $config->$k = $v;
            }
        }
        return $config;
    }
    $conf = (object) $conf;
    return $conf;
}

/*
function jelix_scan_module_sel() {
    
}
function jelix_scan_action_sel() {
    
}
function jelix_scan_old_action_sel() {
    
}
function jelix_scan_class_sel() {
    
}
function jelix_scan_locale_sel() {
    
}
*/
<?php

require_once('../jtpl_standalone_prepend.php');
jTplConfig::$lang = 'fr';
$pluginPath = __DIR__.'/../../plugins/tpl/';
if (file_exists($pluginPath)) {
    jTplConfig::addPluginsRepository(realpath($pluginPath));   
}

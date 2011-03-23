<?php

require_once(dirname(__FILE__).'/../application.init.php');

jApp::setEnv('jelixtests');



function jelix_init_test_env() {
    require_once(JELIX_LIB_CORE_PATH.'jConfigCompiler.class.php');
    global $gJConfig;
    $gJConfig = jConfigCompiler::read('index/config.ini.php', false, true);
}

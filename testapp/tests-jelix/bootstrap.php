<?php

require_once(dirname(__FILE__).'/../application.init.php');

jApp::setEnv('jelixtests');



function jelix_init_test_env() {
    $config = jConfigCompiler::read('index/config.ini.php', true, true, 'index.php');
    jApp::setConfig($config);
    jApp::initLegacy();
}

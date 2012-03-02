<?php

require_once(dirname(__FILE__).'/../application.init.php');
require_once(LIB_PATH.'jelix-tests/classes/junittestcase.class.php');
require_once(LIB_PATH.'jelix-tests/classes/junittestcasedb.class.php');

jApp::setEnv('jelixtests');



function jelix_init_test_env() {
    $config = jConfigCompiler::read('index/config.ini.php', true, true, 'index.php');
    jApp::setConfig($config);
    jApp::initLegacy();
}

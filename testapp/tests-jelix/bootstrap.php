<?php

require_once(__DIR__.'/../application.init.php');
require_once(LIB_PATH.'jelix-tests/classes/junittestcase.class.php');
require_once(LIB_PATH.'jelix-tests/classes/junittestcasedb.class.php');

jApp::setEnv('jelixtests');



function jelix_init_test_env() {
    require_once(JELIX_LIB_CORE_PATH.'jConfigCompiler.class.php');
    $config = jConfigCompiler::read('index/config.ini.php', true, true, 'index.php');
    jApp::setConfig($config);
}

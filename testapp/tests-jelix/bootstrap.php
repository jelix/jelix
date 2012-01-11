<?php

require_once(dirname(__FILE__).'/../application.init.php');

jApp::setEnv('jelixtests');



function jelix_init_test_env() {
    jApp::loadConfig('index/config.ini.php', false);
    jApp::initLegacy();
}

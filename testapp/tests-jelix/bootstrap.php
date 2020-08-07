<?php

require_once(__DIR__.'/../application.init.php');
require_once(LIB_PATH.'jelix-tests/classes/junittestcase.class.php');
require_once(LIB_PATH.'jelix-tests/classes/junittestcasedb.class.php');

ini_set('date.timezone', 'Europe/Paris');
jApp::setEnv('jelixtests');
if (file_exists(jApp::tempPath()))
    jAppManager::clearTemp(jApp::tempPath());

define('TESTAPP_URL', 'http://testapp.local/');
define('TESTAPP_URL_HOST', 'testapp.local');
define('TESTAPP_HOST', 'testapp16.local');

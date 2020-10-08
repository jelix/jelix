<?php

require_once(__DIR__.'/../application.init.php');

ini_set('date.timezone', 'Europe/Paris');
date_default_timezone_set('Europe/Paris');

jApp::setEnv('jelixtests');
if (file_exists(jApp::tempPath())) {
    jAppManager::clearTemp(jApp::tempPath());
} else {
    jFile::createDir(jApp::tempPath(), intval("775",8));
}

define('TESTAPP_URL', 'http://testapp18.local/');
define('TESTAPP_URL_HOST', 'testapp18.local');
define('TESTAPP_HOST', 'testapp18.local');

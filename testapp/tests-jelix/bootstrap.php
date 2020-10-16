<?php

require_once(__DIR__.'/../application.init.php');

// defines some values. File installed by the environment (docker, vagrant, travis..)
require_once('/srv/phpunit_bootstrap.php');

ini_set('date.timezone', 'Europe/Paris');
date_default_timezone_set('Europe/Paris');

jApp::setEnv('jelixtests');
if (file_exists(jApp::tempPath())) {
    jAppManager::clearTemp(jApp::tempPath());
} else {
    jFile::createDir(jApp::tempPath(), intval("775",8));
}


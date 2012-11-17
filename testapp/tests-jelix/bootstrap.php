<?php

require_once(__DIR__.'/../application.init.php');
require_once(LIB_PATH.'jelix-tests/classes/junittestcase.class.php');
require_once(LIB_PATH.'jelix-tests/classes/junittestcasedb.class.php');

jApp::setEnv('jelixtests');

define('TESTAPP_URL', 'http://testapp.local/');
define('TESTAPP_HOST', 'testapp.local');

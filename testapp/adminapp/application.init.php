<?php
/**
* @package   adminapp
* @subpackage
* @author    your name
* @copyright 2015 a name
* @link      http://www.yourwebsite.undefined
* @license    All rights reserved
*/

require(__DIR__.'/../vendor/autoload.php');

jApp::initPaths(
    __DIR__.'/',
    __DIR__.'/../www/',
    __DIR__.'/var/',
    __DIR__.'/var/log/',
    __DIR__.'/var/config/'
);
jApp::setTempBasePath(__DIR__.'/../temp/');
jApp::setEnv('admin');

require (__DIR__.'/../vendor/jelix_app_path.php');
/*
jApp::declareModulesDir(array(
                        __DIR__.'/../../lib/jelix-modules/',
                        __DIR__.'/../../lib/jelix-admin-modules/'
                    ));
jApp::declarePluginsDir(array(
                        __DIR__.'/../../lib/jelix-plugins/'
                    ));
*/
#!/usr/bin/env php
<?php
/**
* @author      Laurent Jouanneau
* @copyright   2016 Laurent Jouanneau
* @link        http://jelix.org
* @licence     MIT
*/
require(__DIR__.'/../vendor/autoload.php');
require(__DIR__.'/../lib/jelix-scripts/includes/scripts.inc.php');

use Jelix\DevHelper\CreateAppApplication;

$application = new CreateAppApplication();
$application->run();

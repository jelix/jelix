<?php
/**
* @package  jelix
* @subpackage 
* @author   Laurent Jouanneau
* @contributor
* @copyright 2010  Laurent Jouanneau
* @link      http://jelix.org
* @licence   http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
*/

require ('../application.init.php');

jApp::loadConfig('index/config.ini.php');

$min_customConfigPaths = \Jelix\Minify\MinifySetup::getConfigPaths();

require(__DIR__.'/../vendor/mrclay/minify/min/index.php');

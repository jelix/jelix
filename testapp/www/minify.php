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

// See Minify documentation/configuration to know this options
// values here are default values. You can configure only these options.
//$min_allowDebugFlag = false;
//$min_errorLogger = false;
//$min_cacheFileLocking = true;
//$min_serveOptions['bubbleCssImports'] = false;
//$min_serveOptions['maxAge'] = 1800;
//$min_serveOptions['minApp']['allowDirs'] = array('//js', '//css');
//$min_serveOptions['minApp']['maxFiles'] = 10;
//$min_symlinks = array();
//$min_uploaderHoursBehind = 0;

//$min_customConfigPaths = array(
//    'groups' => 'path/to/mygroupconfig.php'
//);

require(jApp::getAllModulesPath()['jminify'].'lib/MinifySetup.php');
\Jelix\Minify\MinifySetup::init();

require(__DIR__.'/../vendor/mrclay/minify/min/index.php');
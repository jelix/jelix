<?php
/**
* @package  jelix
* @subpackage testapp
* @author   Laurent Jouanneau
* @contributor
* @copyright 2005-2011 Laurent Jouanneau
* @link     http://www.jelix.org
* @licence  http://www.gnu.org/licenses/gpl.html GNU General Public Licence, see LICENCE file
*/

require (__DIR__.'/vendor/autoload.php');

jApp::initPaths(
    __DIR__.'/'
    //__DIR__.'/www/',
    //__DIR__.'/var/',
    //__DIR__.'/var/log/',
    //__DIR__.'/var/config/'
);
jApp::setTempBasePath(realpath(__DIR__.'/temp').'/');


require (__DIR__.'/vendor/jelix_app_path.php');

/*jApp::declareModulesDir(array(
                        __DIR__.'/../lib/jelix-modules/',
                        __DIR__.'/../lib/jelix-admin-modules/',
                        __DIR__.'/modules/'
                    ));
jApp::declarePluginsDir(array(
                        __DIR__.'/../lib/jelix-plugins/',
                        __DIR__.'/plugins'
                    ));
*/


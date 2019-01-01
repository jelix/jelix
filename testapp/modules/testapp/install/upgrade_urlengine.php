<?php

/**
* @package     testapp
* @subpackage  testapp module
* @author      Laurent Jouanneau
* @copyright   2012 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class testappModuleUpgrader_urlengine extends \Jelix\Installer\Module\Installer {

    protected $targetVersions = array('1.4b2.2406');
    protected $date = '2012-07-20';

    function install(\Jelix\Installer\Module\API\InstallHelpers $helpers) {
        $helpers->getConfigIni()->setValue('engine', 'basic_significant', 'urlengine');
    }
}
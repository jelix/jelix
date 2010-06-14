<?php
/**
* @package     jelix
* @subpackage  jacl2db module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2009-2010 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jacl2dbModuleInstallerBase extends jInstallerModule {

    protected $defaultDbProfile = 'jacl2_profile';

    protected $forEachEntryPointsConfig = true;

    protected $useDatabase = true;

}

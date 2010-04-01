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

    public function setEntryPoint($ep, $config, $dbProfile) {

        // let's retrieve the profile used for jacl2
        $dbProfilesFile = $config->getValue('dbProfils');
        if ($dbProfilesFile == '')
            $dbProfilesFile = 'dbprofils.ini.php';
        $dbprofiles = parse_ini_file(JELIX_APP_CONFIG_PATH.$dbProfilesFile);
        if (isset($dbprofiles['jacl2_profile'])) {
            if (is_string($dbprofiles['jacl2_profile']))
                $dbProfile = $dbprofiles['jacl2_profile'];
            else
                $dbProfile = 'jacl2_profile';
        }
        parent::setEntryPoint($ep, $config, $dbProfile);
        return md5($ep->configFile.'-'.$dbProfile);
    }

}

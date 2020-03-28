<?php

/**
 * @package    jelix-modules
 * @subpackage jelix-module
 *
 * @author     Laurent Jouanneau
 * @copyright  2020 Laurent Jouanneau
 *
 * @see       https://jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

class jelixModuleUpgrader_01_notfoundact extends \Jelix\Installer\Module\Installer
{
    protected $targetVersions = array('1.7.3');
    protected $date = '2020-03-28';

    public function install(Jelix\Installer\Module\API\InstallHelpers $helpers)
    {
        $this->migrateConfig($helpers->getLocalConfigIni());
        $this->migrateConfig($helpers->getLiveConfigIni());
        foreach ($helpers->getEntryPointsList() as $ep) {
            $this->migrateConfig($ep->getSingleConfigIni());
        }
    }

    /**
     * @param \Jelix\IniFile\IniReaderInterface $ini
     */
    protected function migrateConfig($ini) {

        if (! $ini instanceof \Jelix\IniFile\IniModifierInterface) {
            echo "ERROR ".$ini->getFileName()." not allowed to be writable by the Jelix installer\n";
        }

        $val = $ini->getValue('notfoundAct', 'urlengine');
        if ($val !== null) {
            $ini->setValue('notFoundAct', $val, 'urlengine');
            $ini->save();
        }

    }
}

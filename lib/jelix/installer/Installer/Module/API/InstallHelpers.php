<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer\Module\API;

/**
 * @since 1.7
 */
class InstallHelpers extends PreInstallHelpers
{
    use FileHelpersTrait;
    use DbProfileHelpersTrait;

    /**
     * @var DatabaseHelpers
     */
    protected $databaseHelpers;

    public function __construct(\Jelix\Installer\GlobalSetup $setup, DatabaseHelpers $database)
    {
        parent::__construct($setup);
        $this->databaseHelpers = $database;
    }

    /**
     * @return DatabaseHelpers
     */
    public function database()
    {
        return $this->databaseHelpers;
    }

    /**
     * default config, main config combined with local config (RW) and live config (RW).
     *
     * @return \Jelix\IniFile\IniModifierArray
     */
    public function getConfigIni()
    {
        $ini = $this->globalSetup->getSystemConfigIni(true);
        $ini['local'] = $this->globalSetup->getLocalConfigIni();
        $ini['live'] = $this->globalSetup->getLiveConfigIni();

        return $ini;
    }

    /**
     * the liveconfig.ini.php file.
     *
     * @return \Jelix\IniFile\IniModifierInterface
     *
     * @since 1.7
     */
    public function getLiveConfigIni()
    {
        return $this->globalSetup->getLiveConfigIni();
    }

    /**
     * declare web assets into the main configuration.
     *
     * @param string $name       the name of webassets
     * @param array  $values     should be an array with one or more of these keys 'css' (array), 'js'  (array), 'require' (string)
     * @param string $collection the name of the webassets collection
     * @param bool   $force
     */
    public function declareGlobalWebAssets($name, array $values, $collection, $force)
    {
        $config = $this->getLocalConfigIni();
        $this->globalSetup->declareWebAssetsInConfig($config, $name, $values, $collection, $force);
    }

    /**
     * remove web assets from the main configuration.
     *
     * @param string $name       the name of webassets
     * @param string $collection the name of the webassets collection
     */
    public function removeGlobalWebAssets($name, $collection)
    {
        $config = $this->getLocalConfigIni();
        $this->globalSetup->removeWebAssetsFromConfig($config, $name, $collection);
    }
}

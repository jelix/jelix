<?php
/**
 * @package     jelix
 * @subpackage  installer
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 * @link        http://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * container for entry points properties for configurators
 *
 * @method getType()
 * @method getScriptName()
 * @method getFileName()
 * @method isCliScript()
 * @method getUrlMap()
 * @method getEpId()
 * @method getModulesList()
 * @method getAppConfigIni()
 * @method getLocalConfigIni()
 * @method getLiveConfigIni()
 * @method getConfigFileName()
 * @method getConfigObj()
 * @method setConfigObj($config)
 */
class jInstallerEntryPointConfigurator
{
    /**
     * @var jInstallerEntryPoint2
     */
    protected $entryPoint;

    protected $configureOnLocalConfig = false;

    /**
     * @var jInstallerGlobalSetup
     */
    protected $globalSetup;

    function __construct(jInstallerEntryPoint2 $entryPoint,
                         jInstallerGlobalSetup $globalSetup,
                         $configureOnLocalConfig
    )
    {
        $this->entryPoint = $entryPoint;
        $this->globalSetup = $globalSetup;
        $this->configureOnLocalConfig = $configureOnLocalConfig;
    }

    public function __call ( $function_name , $arguments) {
        return call_user_func_array([$this->entryPoint, $function_name], $arguments);
    }

    function getConfigurationMode() {
        return $this->configureOnLocalConfig;
    }

    /**
     * access to the configuration, app config or local config
     * depending on the configuration mode
     *
     * @return \Jelix\IniFile\IniModifierArray
     * @see self::getLocalConfigIni(), self:getAppConfigIni()
     */
    function getConfigIni()
    {
        if ($this->configureOnLocalConfig) {
            return $this->entryPoint->getLocalConfigIni();
        }
        return $this->entryPoint->getAppConfigIni();
    }

    /**
     * Declare web assets into the entry point config
     * @param string $name the name of webassets
     * @param array $values should be an array with one or more of these keys 'css' (array), 'js'  (array), 'require' (string)
     * @param string $collection the name of the webassets collection
     * @param bool $force
     */
    public function declareWebAssets($name, array $values, $collection, $force)
    {
        $this->globalSetup->declareWebAssetsInConfig($this->entryPoint->getAppConfigIni()['entrypoint'], $name, $values, $collection, $force);
    }
}

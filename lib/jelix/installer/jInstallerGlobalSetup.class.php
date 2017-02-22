<?php
/**
 * @package     jelix
 * @subpackage  installer
 * @author      Laurent Jouanneau
 * @copyright   2017 Laurent Jouanneau
 * @link        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * Class jInstallerGlobalSetup
 * @since 1.7.0
 */
class jInstallerGlobalSetup {

    /**
     * the mainconfig.ini.php combined with the defaultconfig.ini.php
     * @var \Jelix\IniFile\MultiIniModifier
     */
    protected $mainConfig;

    /**
     * the localconfig.ini.php content combined with $mainConfig
     * @var \Jelix\IniFile\MultiIniModifier
     */
    protected $localConfig;

    /**
     * @var \Jelix\Routing\UrlMapping\XmlMapModifier
     */
    protected $xmlMapModifier;

    /**
     *  @var \Jelix\IniFile\IniModifier it represents the installer.ini.php file.
     */
    public $installerIni = null;

    /**
     * jInstallerGlobalSetup constructor.
     * @param string|null $mainConfigFileName
     * @param string|null $localConfigFileName
     * @param string|null $urlXmlFileName
     */
    function __construct($mainConfigFileName = null,
                         $localConfigFileName = null,
                         $urlXmlFileName = null)
    {

        if (!$mainConfigFileName) {
            $mainConfigFileName = jApp::mainConfigFile();
        }
        $this->mainConfig = new \Jelix\IniFile\MultiIniModifier(jConfig::getDefaultConfigFile(),
                                                                $mainConfigFileName);

        if (!$localConfigFileName) {
            $localConfigFileName = jApp::varConfigPath('localconfig.ini.php');
            if (!file_exists($localConfigFileName)) {
                $localConfigDist = jApp::varConfigPath('localconfig.ini.php.dist');
                if (file_exists($localConfigDist)) {
                    copy($localConfigDist, $localConfigFileName);
                }
                else {
                    file_put_contents($localConfigFileName, ';<'.'?php die(\'\');?'.'>');
                }
            }
        }
        $this->localConfig = new \Jelix\IniFile\MultiIniModifier($this->mainConfig,
                                                                 $localConfigFileName);

        $this->installerIni = $this->loadInstallerIni();

        if (!$urlXmlFileName) {
            $urlXmlFileName = jApp::appConfigPath($this->localConfig->getValue('significantFile', 'urlengine'));
        }
        $this->xmlMapModifier = new \Jelix\Routing\UrlMapping\XmlMapModifier($urlXmlFileName, true);

        // be sure temp path is ready
        $chmod = $this->mainConfig->getValue('chmodDir');
        jFile::createDir(jApp::tempPath(), intval($chmod, 8));
    }


    /**
     * the mainconfig.ini.php file combined with defaultconfig.ini.php
     * @return \Jelix\IniFile\MultiIniModifier
     */
    public function getMainConfigIni() {
        return $this->mainConfig;
    }

    /**
     * the localconfig.ini.php file combined with getMainConfigIni()
     * @return \Jelix\IniFile\MultiIniModifier
     */
    public function getLocalConfigIni() {
        return $this->localConfig;
    }


    /**
     * the installer.ini.php
     * @return \Jelix\IniFile\IniModifier
     */
    public function getInstallerIni() {
        return $this->installerIni;
    }

    /**
     * @return \Jelix\IniFile\IniModifier the modifier for the installer.ini.php file
     * @throws Exception
     */
    protected function loadInstallerIni() {
        if (!file_exists(jApp::varConfigPath('installer.ini.php'))) {
            if (false === @file_put_contents(jApp::varConfigPath('installer.ini.php'), ";<?php die(''); ?>
; for security reasons , don't remove or modify the first line
; don't modify this file if you don't know what you do. it is generated automatically by jInstaller

")) {
                throw new Exception('impossible to create var/config/installer.ini.php');
            }
        }
        else {
            copy(jApp::varConfigPath('installer.ini.php'), jApp::varConfigPath('installer.bak.ini.php'));
        }
        return new \Jelix\IniFile\IniModifier(jApp::varConfigPath('installer.ini.php'));
    }

    /**
     * @return \Jelix\Routing\UrlMapping\XmlMapModifier
     */
    public function getUrlModifier() {
        return $this->xmlMapModifier;
    }

    /**
     *
     */
    protected $installerContexts = array();

    public function getInstallerContexts($moduleName) {
        $contexts = $this->installerIni->getValue($moduleName.'.contexts','__modules_data');
        if ($contexts !== null && $contexts !== "") {
            $contexts = explode(',', $contexts);
        }
        else {
            $contexts = array();
        }
        return $contexts;
    }

    public function updateInstallerContexts($moduleName, $contexts) {
        $this->installerIni->setValue($moduleName.'.contexts', implode(',',$contexts), '__modules_data');
    }

    public function removeInstallerContexts($moduleName) {
        $this->installerIni->removeValue($moduleName.'.contexts', '__modules_data');
    }


}
<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2017 Laurent Jouanneau
 * @link        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
namespace Jelix\Installer;

use \Jelix\IniFile\IniReader;
use \Jelix\Core\App;
use \Jelix\Core\Config;

/**
 * @since 1.7.0
 */
class GlobalSetup {

    /**
     * @var \Jelix\IniFile\IniModifierArray
     */
    protected $configIni;

    /**
     * @var \Jelix\IniFile\IniModifierArray
     */
    protected $localConfigIni;

    /**
     * @var \Jelix\IniFile\IniModifierArray
     */
    protected $liveConfigIni;

    /**
     * @var \Jelix\Routing\UrlMapping\XmlMapModifier
     */
    protected $urlMapModifier;

    /**
     *  @var \Jelix\IniFile\IniModifier it represents the installer.ini.php file.
     */
    protected $installerIni = null;

    /**
     * GlobalSetup constructor.
     * @param string|null $mainConfigFileName
     * @param string|null $localConfigFileName
     * @param string|null $liveConfigFileName
     * @param string|null $urlXmlFileName
     */
    function __construct($mainConfigFileName = null,
                         $localConfigFileName = null,
                         $liveConfigFileName = null,
                         $urlXmlFileName = null)
    {


        if (!$mainConfigFileName) {
            $mainConfigFileName = App::mainConfigFile();
        }

        if (!$localConfigFileName) {
            $localConfigFileName = App::varConfigPath('localconfig.ini.php');
            if (!file_exists($localConfigFileName)) {
                $localConfigDist = App::varConfigPath('localconfig.ini.php.dist');
                if (file_exists($localConfigDist)) {
                    copy($localConfigDist, $localConfigFileName);
                }
                else {
                    file_put_contents($localConfigFileName, ';<'.'?php die(\'\');?'.'> static local configuration');
                }
            }
        }
        if (!$liveConfigFileName) {
            $liveConfigFileName = App::varConfigPath('liveconfig.ini.php');
            if (!file_exists($liveConfigFileName)) {
                file_put_contents($liveConfigFileName, ';<' . '?php die(\'\');?' . '> live local configuration');
            }
        }

        $defaultConfig = new IniReader(Config::getDefaultConfigFile());

        $this->configIni = new \Jelix\IniFile\IniModifierArray(array(
            'default'=> $defaultConfig,
            'main' => $mainConfigFileName,
        ));
        $this->localConfigIni = clone $this->configIni;
        $this->localConfigIni['local'] = $localConfigFileName;

        $this->liveConfigIni = clone $this->localConfigIni;
        $this->liveConfigIni['live'] = $liveConfigFileName;

        $this->installerIni = $this->loadInstallerIni();

        if (!$urlXmlFileName) {
            $urlXmlFileName = App::appConfigPath($this->localConfigIni->getValue('significantFile', 'urlengine'));
        }
        $this->urlMapModifier = new \Jelix\Routing\UrlMapping\XmlMapModifier($urlXmlFileName, true);

        // be sure temp path is ready
        $chmod = $this->configIni->getValue('chmodDir');
        \jFile::createDir(App::tempPath(), intval($chmod, 8));
    }

    /**
     * the combined global config files, defaultconfig.ini.php and mainconfig.ini.php
     * @return \Jelix\IniFile\IniModifierArray
     */
    public function getConfigIni() {
        return $this->configIni;
    }

    /**
     * the combined global config files, defaultconfig.ini.php and mainconfig.ini.php,
     * with localconfig.ini.php
     * @return \Jelix\IniFile\IniModifierArray
     */
    public function getLocalConfigIni() {
        return $this->localConfigIni;
    }

    /**
     * the combined config files defaultconfig.ini.php and mainconfig.ini.php
     * with localconfig.ini.php and liveconfig.ini.php
     * @return \Jelix\IniFile\IniModifierArray
     */
    public function getLiveConfigIni() {
        return $this->liveConfigIni;
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
        if (!file_exists(App::varConfigPath('installer.ini.php'))) {
            if (false === @file_put_contents(App::varConfigPath('installer.ini.php'), ";<?php die(''); ?>
; for security reasons , don't remove or modify the first line
; don't modify this file if you don't know what you do. it is generated automatically by jInstaller

")) {
                throw new Exception('impossible to create var/config/installer.ini.php');
            }
        }
        else {
            copy(App::varConfigPath('installer.ini.php'), App::varConfigPath('installer.bak.ini.php'));
        }
        return new \Jelix\IniFile\IniModifier(App::varConfigPath('installer.ini.php'));
    }

    /**
     * @return \Jelix\Routing\UrlMapping\XmlMapModifier
     */
    public function getUrlModifier() {
        return $this->urlMapModifier;
    }

    /**
     * Declare a new entry point
     *
     * @param string $epId
     * @param string $epType
     * @param string $configFileName
     * @throws Exception
     */
    public function declareNewEntryPoint($epId, $epType, $configFileName) {

        $this->urlMapModifier->addEntryPoint($epId, $epType);

        $doc = $this->loadProjectXml();
        $eplist = $doc->documentElement->getElementsByTagName("entrypoints");
        if (!$eplist->length) {
            $ep = $doc->createElementNS(JELIX_NAMESPACE_BASE.'project/1.0', 'entrypoints');
            $doc->documentElement->appendChild($ep);
        }
        else {
            $ep = $eplist->item(0);
            foreach($ep->getElementsByTagName("entry") as $entry){
                if ($entry->getAttribute("file") == $epId.'.php'){
                    $entryType = $entry->getAttribute("type") ?: 'classic';
                    if ($entryType != $epType) {
                        throw new Exception("There is already an entrypoint with the same name but with another type ($epId, $epType)");
                    }
                    return;
                }
            }
        }

        $elem = $doc->createElementNS(JELIX_NAMESPACE_BASE.'project/1.0', 'entry');
        $elem->setAttribute("file", $epId.'.php');
        $elem->setAttribute("config", $configFileName);
        $elem->setAttribute("type", $epType);
        $ep->appendChild($elem);
        $ep->appendChild(new \DOMText("\n    "));
        $doc->save(App::appPath('project.xml'));
    }

    protected function loadProjectXml() {
        $doc = new \DOMDocument();
        if (!$doc->load(App::appPath('project.xml'))) {
            throw new Exception("declareNewEntryPoint: cannot load project.xml");
        }
        return $doc;
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

    /**
     * @param \Jelix\IniFile\IniModifier $config
     * @param string $name the name of webassets
     * @param array $values
     * @param string $collection the name of the webassets collection
     * @param boolean $force
     */
    public function declareWebAssetsInConfig(\Jelix\IniFile\IniModifier $config, $name, array $values, $collection, $force) {

        $section = 'webassets_'.$collection;
        if (!$force && (
                $config->getValue($name.'.css', $section) ||
                $config->getValue($name.'.js', $section) ||
                $config->getValue($name.'.require', $section)
            )) {
            return;
        }

        if (isset($values['css'])) {
            $config->setValue($name.'.css', $values['css'], $section);
        }
        else {
            $config->removeValue($name.'.css', $section);
        }
        if (isset($values['js'])) {
            $config->setValue($name.'.js', $values['js'], $section);
        }
        else {
            $config->removeValue($name.'.js', $section);
        }
        if (isset($values['require'])) {
            $config->setValue($name.'.require', $values['require'], $section);
        }
        else {
            $config->removeValue($name.'.require', $section);
        }
    }

}
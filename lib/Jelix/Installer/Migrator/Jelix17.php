<?php
/**
 * @package     jelix
 *
 * @author      Laurent Jouanneau
 * @copyright   2019-2023 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer\Migrator;

use Jelix\Core\App;
use Jelix\IniFile\IniModifier;
use Jelix\Installer\ModuleStatus;

/**
 * Migration from Jelix 1.6 to 1.7/1.8
 *
 * The process should be idempotent
 */
class Jelix17
{
    /**
     * the object responsible of the results output.
     *
     * @var \Jelix\Installer\Reporter\ReporterInterface
     */
    protected $reporter;

    /**
     * @var \Jelix\IniFile\IniReader
     */
    protected $defaultConfigIni;

    public function __construct(\Jelix\Installer\Reporter\ReporterInterface $reporter)
    {
        $this->reporter = $reporter;
        $this->defaultConfigIni = new \Jelix\IniFile\IniReader(\Jelix\Core\Config\AppConfig::getDefaultConfigFile());
    }

    public function migrate()
    {
        $this->reporter->message('Start migration to Jelix 1.7.0', 'notice');

        $this->moveIntoAppSystem();

        $mainConfigIni = new IniModifier(App::appSystemPath('mainconfig.ini.php'));

        $entrypoints = $this->migrateProjectXml($mainConfigIni);

        if (file_exists(App::varConfigPath('profiles.ini.php'))) {
            $profilesini = new IniModifier(App::varConfigPath('profiles.ini.php'));
            $this->migrateProfilesIni($profilesini);
        }

        if (file_exists(App::varConfigPath('profiles.ini.php.dist'))) {
            $profilesini = new IniModifier(App::varConfigPath('profiles.ini.php.dist'));
            $this->migrateProfilesIni($profilesini);
        }

        // move plugin configuration file to global config
        $this->migrateCoordPluginsConf($mainConfigIni);
        foreach ($entrypoints as $epInfo) {
            $this->migrateCoordPluginsConf($epInfo['config']);
        }

        $this->migrateAccessValue($mainConfigIni);

        $this->migrateJelixInstallParameters($mainConfigIni);

        $this->upgradeUrlEngine($mainConfigIni, $entrypoints);

        $this->upgradeWebAssets($mainConfigIni, $entrypoints);

        $this->updateScripts();

        $mainConfigIni->save();

        $this->reporter->message('Migration to Jelix 1.7.0 is done', 'notice');

        $this->reporter->message('Start migration to Jelix 1.7.3', 'notice');
        $this->migrate173($mainConfigIni);
        $this->reporter->message('Migration to Jelix 1.7.3 is done', 'notice');
    }

    public function localMigrate()
    {
        $installerIni = new IniModifier(App::varConfigPath('installer.ini.php'));
        if ($installerIni->isSection('modules')) {
            return;
        }

        if (file_exists(App::varConfigPath('profiles.ini.php'))) {
            $profilesini = new IniModifier(App::varConfigPath('profiles.ini.php'));
            $this->migrateProfilesIni($profilesini);
        }

        $localConfigPath = App::varConfigPath('localconfig.ini.php');
        $localConfigIni = null;

        if (file_exists($localConfigPath)) {
            $localConfigIni = new IniModifier($localConfigPath);

            $frameworkIni = new IniModifier(App::appSystemPath('framework.ini.php'));
            $mainConfigIni = new IniModifier(App::appSystemPath('mainconfig.ini.php'));
            $mainConfig = new \Jelix\IniFile\IniModifierArray(array(
                'default' => $this->defaultConfigIni,
                'main' => $mainConfigIni,
            ));

            $webassets = new WebAssetsUpgrader($mainConfig);
            $this->migrateCoordPluginsConf($localConfigIni, true);
            foreach ($frameworkIni->getSectionList() as $section) {
                if (!preg_match('/^entrypoint\\:(.*)$/', $section, $m)) {
                    continue;
                }
                $configValue = $frameworkIni->getValue('config', $section);
                $configFile = App::varConfigPath($configValue);
                if (file_exists($configFile)) {
                    $epConfigIni = new IniModifier(App::appSystemPath($configValue));
                    $localEpConfigIni = new IniModifier($configFile);
                    $this->migrateCoordPluginsConf($localEpConfigIni, true);
                    if ($localEpConfigIni->getValues('modules')) {
                        $this->reporter->message('Migrate modules section from var/config/'.$configValue.' content to localconfig.ini.php', 'notice');
                        $this->migrateModulesSection($localConfigIni, $localEpConfigIni);
                    }
                    $this->upgradeWebAssetsEp(
                        $webassets,
                        $mainConfigIni,
                        $epConfigIni,
                        $localConfigIni,
                        $localEpConfigIni
                    );
                    $localEpConfigIni->save();
                }
            }

            $this->migrateAccessValue($localConfigIni, $mainConfigIni);
            $this->migrateJelixInstallParameters($localConfigIni);
            $this->migrateInstallerIni();

            $localConfigIni->save();
        }
        $this->reporter->message('Migration of local configuration to Jelix 1.7.0 is done', 'notice');
    }

    private function moveIntoAppSystem()
    {
        $newConfigPath = App::appSystemPath();
        if (file_exists(App::appPath('app/config'))) {
            // for jelix 1.7.0-pre < version < 1.7.0-beta.5
            rename(App::appPath('app/config'), $newConfigPath);
        } elseif (!file_exists($newConfigPath)) {
            $this->reporter->message('Create app/system/', 'notice');
            \jFile::createDir($newConfigPath);
        }

        // move mainconfig.php to app/system/
        if (!file_exists($newConfigPath.'mainconfig.ini.php')) {
            if (!file_exists(App::varConfigPath('mainconfig.ini.php'))) {
                if (!file_exists(App::varConfigPath('defaultconfig.ini.php'))) {
                    throw new \Exception('Migration to Jelix 1.7.0 canceled: where is your mainconfig.ini.php?');
                }
                $this->reporter->message('Move var/config/defaultconfig.ini.php to app/system/mainconfig.ini.php', 'notice');
                rename(App::varConfigPath('defaultconfig.ini.php'), $newConfigPath.'mainconfig.ini.php');
            } else {
                $this->reporter->message('Move var/config/mainconfig.ini.php to app/system/', 'notice');
                rename(App::varConfigPath('mainconfig.ini.php'), $newConfigPath.'mainconfig.ini.php');
            }
        }

        if (!file_exists(App::appPath('app/responses')) && file_exists(App::appPath('responses'))) {
            $this->reporter->message('Move responses/ to app/responses/', 'notice');
            rename(App::appPath('responses'), App::appPath('app/responses'));
        }
    }

    private function migrateProjectXml($mainConfigIni)
    {
        $entrypoints = array();
        $frameworkIni = new IniModifier(
            App::appSystemPath('framework.ini.php'),
            ';<'.'?php die(\'\');?'.'>'
        );
        $projectDOM = new \DOMDocument();
        $projectDOM->load(App::appPath('project.xml'));
        $projectxml = simplexml_import_dom($projectDOM);
        if (!isset($projectxml->entrypoints) || !isset($projectxml->entrypoints->entry)) {
            return $entrypoints;
        }
        // read all entry points data
        foreach ($projectxml->entrypoints->entry as $entrypoint) {
            $name = (string) $entrypoint['file'];
            $configFile = (string) $entrypoint['config'];
            $type = isset($entrypoint['type']) ? (string) $entrypoint['type'] : 'classic';

            $frameworkIni->setValues(
                array('config' => $configFile, 'type' => $type),
                'entrypoint:'.$name
            );

            $dest = App::appSystemPath($configFile);
            if (!file_exists($dest)) {
                if (!file_exists(App::varConfigPath($configFile))) {
                    $this->reporter->message("Config file var/config/{$configFile} indicated in project.xml, does not exist", 'warning');

                    continue;
                }

                $this->reporter->message("Move var/config/{$configFile} to app/system/", 'notice');
                \jFile::createDir(dirname($dest));
                rename(App::varConfigPath($configFile), $dest);
            }

            $epConfigIni = new IniModifier(App::appSystemPath($configFile));
            $entrypoints[str_replace('.php', '', $name)] = array(
                'name' => $name,
                'type' => $type,
                'config' => $epConfigIni,
            );

            $urlFile = $epConfigIni->getValue('significantFile', 'urlengine');
            if ($urlFile != '') {
                if (!file_exists(App::appSystemPath($urlFile)) && file_exists(App::varConfigPath($urlFile))) {
                    $this->reporter->message("Move var/config/{$urlFile} to app/system/", 'notice');
                    rename(App::varConfigPath($urlFile), App::appSystemPath($urlFile));
                }
            }
            if ($epConfigIni->getValues('modules')) {
                $this->reporter->message('Migrate modules section from app/system/'.$configFile.' content to mainconfig.ini.php', 'notice');
                $this->migrateModulesSection($mainConfigIni, $epConfigIni);
            }
        }

        $entrypointsDOM = $projectDOM->documentElement->getElementsByTagName('entrypoints')[0];
        $projectDOM->documentElement->removeChild($entrypointsDOM);
        $dependenciesDOM = $projectDOM->documentElement->getElementsByTagName('dependencies');
        if ($dependenciesDOM->length) {
            $projectDOM->documentElement->removeChild($dependenciesDOM[0]);
        }
        $directoriesDOM = $projectDOM->documentElement->getElementsByTagName('directories');
        if ($directoriesDOM->length) {
            $projectDOM->documentElement->removeChild($directoriesDOM[0]);
        }
        $projectDOM->save(App::appPath('project.xml'));
        $frameworkIni->save();

        return $entrypoints;
    }

    private function migrateProfilesIni(IniModifier $profilesini)
    {
        foreach ($profilesini->getSectionList() as $name) {
            // move jSoapClient classmap files
            if (strpos($name, 'jsoapclient:') === 0) {
                $classmapFile = $profilesini->getValue('classmap_file', $name);
                if ($classmapFile != ''
                    && file_exists(App::varConfigPath($classmapFile))
                ) {
                    if (!file_exists(App::appSystemPath($classmapFile))) {
                        $this->reporter->message('Move '.$classmapFile.' to app/system/', 'notice');
                        rename(App::varConfigPath($classmapFile), App::appSystemPath($classmapFile));
                    } else {
                        unlink(App::varConfigPath($classmapFile));
                    }
                }
            }
            // profiles.ini.php change mysql driver from "mysql" to "mysqli"
            elseif (strpos($name, 'jdb:') === 0) {
                $driver = $profilesini->getValue('driver', $name);
                if ($driver == 'mysql') {
                    $this->reporter->message('Profiles.ini: change db driver from mysql to mysqli for '.$name.' profile', 'notice');
                    $profilesini->setValue('driver', 'mysqli', $name);
                } elseif ($driver == 'sqlite') {
                    $this->reporter->message('Profiles.ini: you still use the sqlite driver in the profile '.$name, 'warning');
                    $this->reporter->message('You must convert your databases to sqlite3 and use the sqlite3 driver for jdb', 'warning');
                }
            }
        }
        $profilesini->save();
    }

    protected $allPluginConfigs = array();

    private function migrateCoordPluginsConf(IniModifier $config, $localConf = false)
    {
        $config->removeValue('pluginsPath');
        $config->removeValue('modulesPath');
        $pluginsConf = $config->getValues('coordplugins');
        foreach ($pluginsConf as $name => $conf) {
            if (strpos($name, '.') !== false) {
                continue;
            }
            if ($conf == '1' || $conf == '') {
                continue;
            }
            // the configuration value is a filename
            $confPath = App::varConfigPath($conf);
            if (!file_exists($confPath)) {
                if (!isset($this->allPluginConfigs[$conf])) {
                    $this->reporter->message('plugin conf file '.$conf.' does not exist', 'Warning');

                    continue;
                }
            }
            if (!isset($this->allPluginConfigs[$conf])) {
                $ini = new IniModifier($confPath);
                $this->allPluginConfigs[$conf] = $ini;
            } else {
                $ini = $this->allPluginConfigs[$conf];
            }
            $sections = $ini->getSectionList();
            if (count($sections)) {
                // the file has some section, we cannot merge it into $config as
                // is, so just move it to app/system
                if (file_exists($ini->getFileName()) && !$localConf) {
                    $rpath = \Jelix\FileUtilities\Path::shortestPath(App::varConfigPath(), $ini->getFileName());
                    if (!file_exists(App::appSystemPath($rpath))) {
                        $this->reporter->message('Move plugin conf file '.$rpath.' to app/system/', 'notice');
                        rename($ini->getFileName(), App::appSystemPath($rpath));
                    }
                }

                continue;
            }
            $this->reporter->message('Import plugin conf file '.$conf.' into global configuration', 'notice');
            $config->import($ini, $name);
            $config->setValue($name, '1', 'coordplugins');
            if (file_exists($ini->getFileName())) {
                unlink($ini->getFileName());
            }
        }
        $config->save();
    }

    /**
     * @param IniModifier $masterConfigIni
     * @param IniModifier $epConfigIni
     * @return void
     * @throws \Jelix\IniFile\IniException
     */
    protected function migrateModulesSection(IniModifier $masterConfigIni, IniModifier $epConfigIni)
    {
        $modulesParameters = $epConfigIni->getValues('modules');
        if ($modulesParameters) {
            $modules = array();
            foreach ($modulesParameters as $name => $value) {
                list($module, $param) = explode('.', $name, 2);
                if (!isset($modules[$module])) {
                    $modules[$module] = array();
                }
                $modules[$module][$param] = $value;
            }

            foreach ($modules as $name => $parameters) {
                if (!isset($parameters['access']) || $parameters['access'] == 0) {
                    continue;
                }
                $mainAccess = $masterConfigIni->getValue($name.'.access', 'modules');
                if ($mainAccess === null || $mainAccess === 0) {
                    foreach ($parameters as $paramName => $paramValue) {
                        $masterConfigIni->setValue($name.'.'.$paramName, $paramValue, 'modules');
                    }
                } elseif ($mainAccess < $parameters['access']) {
                    $masterConfigIni->setValue($name.'.access', $parameters['access'], 'modules');
                }
            }

            $epConfigIni->removeValue('', 'modules');
            $epConfigIni->save();
            $masterConfigIni->save();
        }
    }

    /**
     * @param IniModifier $masterConfigIni
     * @param IniModifier|null $mainConfigIni mainconfig if masterConfig is localconfig
     * @return void
     * @throws \Jelix\IniFile\IniException
     */
    protected function migrateAccessValue(IniModifier $masterConfigIni, $mainConfigIni = null)
    {
        $modulesParameters = $masterConfigIni->getValues('modules');
        if ($modulesParameters) {
            foreach ($modulesParameters as $name => $value) {
                list($module, $param) = explode('.', $name, 2);
                if ($param == 'access') {
                    $mainEnabled = null;
                    if ($mainConfigIni) {
                        $mainEnabled = $mainConfigIni->getValue($module . '.enabled', 'modules');
                    }

                    if ($mainEnabled === null || $mainEnabled != ($value > 0)) {
                        $masterConfigIni->setValue($module.'.enabled', ($value > 0), 'modules');
                    }
                    $masterConfigIni->removeValue($module.'.access', 'modules');
                }
            }
            $masterConfigIni->save();
        }
    }

    protected function migrateInstallerIni()
    {
        $installerIni = new IniModifier(App::varConfigPath('installer.ini.php'));
        if (!$installerIni->isSection('modules')) {
            $this->reporter->message('Migrate var/config/installer.ini.php content', 'notice');
            $allModules = array();
            foreach ($installerIni->getSectionList() as $section) {
                if ($section == '__modules_data') {
                    continue;
                }
                $modules = array();
                foreach ($installerIni->getValues($section) as $name => $value) {
                    list($module, $param) = explode('.', $name, 2);
                    if (!isset($modules[$module])) {
                        $modules[$module] = array();
                    }
                    if ($param == 'version') {
                        $modules[$module][$param] = (string) $value;
                    } else {
                        $modules[$module][$param] = $value;
                    }
                }

                foreach ($modules as $module => $params) {
                    if (
                        isset($allModules[$module])
                        || !isset($params['installed'])
                        || $params['installed'] == 0
                    ) {
                        continue;
                    }
                    $allModules[$module] = $params;
                }
            }

            foreach ($allModules as $module => $params) {
                foreach ($params as $name => $value) {
                    $installerIni->setValue($module.'.'.$name, $value, 'modules');
                }
            }
            foreach ($installerIni->getSectionList() as $section) {
                if ($section == '__modules_data' || $section == 'modules') {
                    continue;
                }
                $installerIni->removeSection($section);
            }
            $installerIni->save();
        }
    }

    protected function migrateJelixInstallParameters(IniModifier $masterConfigIni)
    {
        // set installparameters for the jelix module
        $jelixWWWPath = $masterConfigIni->getValue('jelixWWWPath', 'urlengine');
        if (!$jelixWWWPath) {
            return;
        }
        $targetPath = App::wwwPath($jelixWWWPath);
        if (file_exists($targetPath)) {
            if (is_dir($targetPath)) {
                $wwwfiles = 'copy';
            } elseif (is_link($targetPath)) {
                $wwwfiles = 'link';
            } else {
                $wwwfiles = 'vhost';
            }
        } else {
            // no file, so the path to jelix-www should probably be set into the
            // web server configuration
            $wwwfiles = 'vhost';
        }

        $jelixInstallParams = $masterConfigIni->getValue('jelix.installparam', 'modules');
        if ($jelixInstallParams) {
            $jelixInstallParams = $originalJelixInstallParams = ModuleStatus::unserializeParameters($jelixInstallParams);
            if (!isset($jelixInstallParams['wwwfiles'])) {
                $jelixInstallParams['wwwfiles'] = $wwwfiles;
            }
        } else {
            $originalJelixInstallParams = array();
            $jelixInstallParams = array('wwwfiles' => $wwwfiles);
        }
        $jelixInstallParams = ModuleStatus::serializeParametersAsArray($jelixInstallParams);
        $originalJelixInstallParams = ModuleStatus::serializeParametersAsArray($originalJelixInstallParams);
        if ($jelixInstallParams != $originalJelixInstallParams) {
            $this->reporter->message('Update installer parameters for the jelix : '.json_encode($jelixInstallParams), 'notice');
            $masterConfigIni->setValue('jelix.installparam', $jelixInstallParams, 'modules');
        }
    }

    /**
     * @param array $entrypoints
     *
     * @throws \Exception
     */
    private function upgradeUrlEngine(IniModifier $mainConfigIni, $entrypoints)
    {
        // move urls.xml to app/system
        $urlFile = $mainConfigIni->getValue('significantFile', 'urlengine');
        if ($urlFile == null) {
            $urlFile = 'urls.xml';
        }
        if (!file_exists(App::appSystemPath($urlFile)) && file_exists(App::varConfigPath($urlFile))) {
            $this->reporter->message("Move var/config/{$urlFile} to app/system/", 'notice');
            rename(App::varConfigPath($urlFile), App::appSystemPath($urlFile));
        }

        $urlXmlFileName = App::appSystemPath($urlFile);
        $urlMapModifier = new \Jelix\Routing\UrlMapping\XmlMapModifier($urlXmlFileName, true);

        foreach ($entrypoints as $epId => $ep) {
            $fullConfig = new \Jelix\IniFile\IniModifierArray(
                array(
                    'default' => $this->defaultConfigIni,
                    'main' => $mainConfigIni,
                    'entrypoint' => $ep['config'],
                )
            );
            $urlMap = $urlMapModifier->getEntryPoint($ep['name']);
            if (!$urlMap) {
                $urlMap = $urlMapModifier->addEntryPoint($ep['name'], $ep['type']);
            }
            $upgraderUrl = new UrlEngineUpgrader($fullConfig, $epId, $urlMap);
            if ($ep['type'] == 'cmdline') {
                $upgraderUrl::cleanConfig($ep['config']);
            } else {
                $upgraderUrl->upgrade();
            }

            $ep['config']->save();
        }

        UrlEngineUpgrader::cleanConfig($mainConfigIni);

        $mainConfigIni->save();
    }

    /**
     * @param IniModifier $mainConfigIni
     * @param array       $entrypoints
     *
     * @throws \Exception
     */
    private function upgradeWebAssets($mainConfigIni, $entrypoints)
    {
        $mainConfig = new \Jelix\IniFile\IniModifierArray(array(
            'default' => $this->defaultConfigIni,
            'main' => $mainConfigIni,
        ));

        $webassets = new WebAssetsUpgrader($mainConfig);
        foreach ($entrypoints as $epId => $ep) {
            $this->upgradeWebAssetsEp($webassets, $mainConfigIni, $ep['config']);
        }
        $webassets = new WebAssetsUpgrader($this->defaultConfigIni);
        $webassets->changeConfig($mainConfig, $mainConfig['main']);
    }

    /**
     * @param WebAssetsUpgrader $webassets
     * @param IniModifier $mainConfigIni
     * @param IniModifier $epConfigIni
     * @param IniModifier $localConfigIni
     * @param IniModifier $localEpConfigIni
     * @return void
     */
    private function upgradeWebAssetsEp(
        $webassets,
        $mainConfigIni,
        $epConfigIni,
        $localConfigIni = null,
        $localEpConfigIni = null
    ) {
        if (!$localConfigIni) {
            $epConfig = new \Jelix\IniFile\IniModifierArray(array(
                'default' => $this->defaultConfigIni,
                'main' => $mainConfigIni,
                'entrypoint' => $epConfigIni,
            ));
        } else {
            $epConfig = new \Jelix\IniFile\IniModifierArray(array(
                'default' => $this->defaultConfigIni,
                'main' => $mainConfigIni,
                'entrypoint' => $epConfigIni,
                'local' => $localConfigIni,
                'localentrypoint' => $localEpConfigIni,
            ));
        }
        $webassets->changeConfig($epConfig, $epConfigIni);
    }

    private function updateScripts()
    {
        $consolePath = App::appPath('console.php');
        if (!file_exists($consolePath)) {
            file_put_contents($consolePath, '<'.'?php require (__DIR__.\'/application.init.php\');
\\Jelix\\Scripts\\ModulesCommands::run();');
            $this->reporter->message('create console.php to launch module commands', 'notice');
        }

        $devPath = App::appPath('dev.php');
        if (!file_exists($devPath)) {
            copy(LIB_PATH.'jelix-scripts/templates/dev.php.tpl', $devPath);
            $this->reporter->message('create dev.php to launch module commands', 'notice');
        } else {
            $content = file_get_contents($devPath);
            if (strpos($content, 'JelixCommands::launch') === false) {
                copy(LIB_PATH.'jelix-scripts/templates/dev.php.tpl', $devPath);
                $this->reporter->message('Update dev.php to launch developer commands', 'notice');
            }
        }

        $cmdPath = App::appPath('cmd.php');
        if (file_exists($cmdPath)) {
            unlink($cmdPath);
            $this->reporter->message('remove cmd.php, which is replaced by dev.php', 'notice');
        }

        $configurePath = App::appPath('install/configurator.php');
        if (!file_exists($configurePath)) {
            file_put_contents($configurePath, '<'.'?php require (__DIR__.\'/../application.init.php\');
\\Jelix\\Scripts\\Configure::launch();');
            $this->reporter->message('create install/configurator.php to launch instance configuration', 'notice');
        }

        $installerPath = App::appPath('install/installer.php');
        $rewriteInstaller = false;
        if (file_exists($installerPath)) {
            $content = file_get_contents($installerPath);
            $rewriteInstaller = (strpos($content, 'Installer::launch') === false);
        }
        if (!file_exists($installerPath) || $rewriteInstaller) {
            file_put_contents($installerPath, '<'.'?php require (__DIR__.\'/../application.init.php\');
\\Jelix\\Scripts\\Installer::launch();');
            $this->reporter->message('create install/installer.php to launch instance installation', 'notice');
        }
    }

    protected function migrate173(IniModifier $mainConfigIni)
    {
        $this->migrateConfig173($mainConfigIni);
        $frameworkIni = new IniModifier(\jApp::appSystemPath('framework.ini.php'));
        foreach ($frameworkIni->getSectionList() as $section) {
            if (!preg_match('/^entrypoint\\:(.*)$/', $section, $m)) {
                continue;
            }
            $configValue = $frameworkIni->getValue('config', $section);
            $configFile = \jApp::appSystemPath($configValue);
            if (file_exists($configFile)) {
                $ini = new IniModifier($configFile);
                $this->migrateConfig173($ini);
            }
        }
    }

    /**
     * @param \Jelix\IniFile\IniReaderInterface $ini
     */
    protected function migrateConfig173($ini)
    {
        $val = $ini->getValue('notfoundAct', 'urlengine');
        if ($val !== null) {
            $ini->removeValue('notfoundAct', 'urlengine');
            $ini->setValue('notFoundAct', $val, 'urlengine');
            $ini->save();
        }
    }

    protected function error($msg)
    {
        $this->reporter->message($msg, 'error');
    }

    protected function ok($msg)
    {
        $this->reporter->message($msg, '');
    }

    protected function warning($msg)
    {
        $this->reporter->message($msg, 'warning');
    }

    protected function notice($msg)
    {
        $this->reporter->message($msg, 'notice');
    }
}

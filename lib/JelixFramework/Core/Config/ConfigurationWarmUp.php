<?php

/**
 * @author    Laurent Jouanneau
 * @copyright 2024-2026 Laurent Jouanneau
 *
 * @see       https://www.jelix.org
 *
 * @licence   GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Core\Config;

use Jelix\Core\AppInstance;
use Jelix\Core\Infos\FrameworkInfos;
use Jelix\IniFile\Util as IniFileMgr;
use Jelix\Installer\WarmUp\FilePlace;
use Jelix\Installer\WarmUp\FilePlaceEnum;
use Jelix\Installer\WarmUp\WarmUpLauncherInterface;

/**
 * Launch the compilation of configuration file to generate
 * a single configuration file that will be merged with the
 * live configuration files at runtime.
 */
class ConfigurationWarmUp implements WarmUpLauncherInterface
{
    public function __construct(
        protected AppInstance $app,
        protected FrameworkInfos $frameworkInfos
    ) {}

    public function getLaunchSteps()
    {
        return WarmUpLauncherInterface::STEP_POSTINSTALL;
    }

    /**
     * Generates the static configuration file.
     */
    public function launch(array $modulesList, int $step): void
    {
        \jFile::createDir($this->app->varLibPath.'config/');

        foreach ($this->frameworkInfos->getEntryPoints() as $ep) {
            $staticConfigFile = $this->app->varLibPath.AppConfig::getStaticBuildFilename($ep->getConfigFile());

            $compiler = new Compiler($this->app, $ep->getConfigFile());
            $config = $compiler->readStaticConfiguration($this->app->getFrameworkConfig());

            // if bytecode cache is enabled, it's better to store configuration
            // as PHP code, reading performance are much better than reading
            // an ini file (266 times less). However, if bytecode cache is disabled,
            // reading performance are better with ini : 32% better. Json is only 22% better.
            if (BYTECODE_CACHE_EXISTS) {
                if ($f = @fopen($staticConfigFile, 'wb')) {
                    fwrite($f, '<?php return '.var_export(get_object_vars($config), true).";\n?>");
                    fclose($f);
                    chmod($staticConfigFile, $config->chmodFile);
                } else {
                    throw new Exception('Error while writing static configuration cache file -- '.$staticConfigFile);
                }
            } else {
                IniFileMgr::write(get_object_vars($config), $staticConfigFile, ";<?php die('');?>\n", $config->chmodFile);
            }
        }
    }

    public function doesItSupportFile(FilePlace $file): bool
    {
        if ($file->place != FilePlaceEnum::App && $file->place != FilePlaceEnum::Var) {
            return false;
        }
        if (($file->place == FilePlaceEnum::App && str_starts_with($file->subPath, 'system/'))
            || ($file->place == FilePlaceEnum::Var && str_starts_with($file->subPath, 'config/'))) {
            return str_ends_with($file->filePath, '.ini.php');
        }

        return false;
    }

    public function launchOnFile(FilePlace $file): void
    {
        // XXX we could improve performance, by compiling only the static
        // file corresponding to an entrypoint, when the file is the config file
        // of an entrypoint
        $this->launch(array(), 0);
    }
}

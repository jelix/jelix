<?php
/**
 * check a jelix installation.
 *
 * @author   Laurent Jouanneau
 * @contributor Bastien Jaillot
 * @contributor Olivier Demah, Brice Tence, Julien Issler
 *
 * @copyright 2007-2018 Laurent Jouanneau, 2008 Bastien Jaillot, 2009 Olivier Demah, 2010 Brice Tence, 2011 Julien Issler
 *
 * @see     http://www.jelix.org
 * @licence  GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 *
 * @since 1.7
 */

namespace Jelix\Installer\Checker;
use Jelix\Core\App;

/**
 * check an installation of a jelix application.
 *
 * @since 1.7
 */
class Checker extends CheckerBase
{
    protected function _otherCheck()
    {
        $this->checkAppPaths();
        $this->loadBuildFile();
    }

    public function checkAppPaths()
    {
        $ok = true;
        if (!defined('JELIX_LIB_PATH') || !App::isInit()) {
            throw new \Exception($this->messages->get('path.core'));
        }

        if (!file_exists(App::tempBasePath()) || !is_writable(App::tempBasePath())) {
            $this->error('path.temp');
            $ok = false;
        }
        if (!file_exists(App::logPath()) || !is_writable(App::logPath())) {
            $this->error('path.log');
            $ok = false;
        }
        if (!file_exists(App::varPath())) {
            $this->error('path.var');
            $ok = false;
        }
        if (!file_exists(App::appSystemPath())) {
            $this->error('path.config');
            $ok = false;
        }
        if (!file_exists(App::varConfigPath())) {
            $this->error('path.config');
            $ok = false;
        } elseif ($this->checkForInstallation) {
            if (!is_writable(App::varConfigPath())) {
                $this->error('path.config.writable');
                $ok = false;
            }
            if (file_exists(App::varConfigPath('profiles.ini.php'))
                && !is_writable(App::varConfigPath('profiles.ini.php'))) {
                $this->error('path.profiles.writable');
                $ok = false;
            }
            if (file_exists(App::varConfigPath('installer.ini.php'))
                && !is_writable(App::varConfigPath('installer.ini.php'))) {
                $this->error('path.installer.writable');
                $ok = false;
            }
        }

        if (!file_exists(App::wwwPath())) {
            $this->error('path.www');
            $ok = false;
        }

        foreach ($this->otherPaths as $path) {
            $realPath = \jFile::parseJelixPath($path);
            if (!file_exists($realPath)) {
                $this->error('path.custom.not.exists', array($path));
                $ok = false;
            } elseif (!is_writable($realPath)) {
                $this->error('path.custom.writable', array($path));
                $ok = false;
            } else {
                $this->ok('path.custom.ok', array($path));
            }
        }

        if ($ok) {
            $this->ok('paths.ok');
        } else {
            throw new \Exception($this->messages->get('too.critical.error'));
        }

        return $ok;
    }

    protected function loadBuildFile()
    {
        $composerFile = __DIR__.'/../../../../../composer.json';
        if (!file_exists($composerFile)) {
            $this->buildProperties['PHP_VERSION_TARGET'] = '7.4';
        } else {
            $content = json_decode(file_get_contents($composerFile));
            preg_match('/([0-9\.]+)/', $content->require->php, $m);
            $this->buildProperties['PHP_VERSION_TARGET'] = $m[1];
        }
    }

    protected function checkPhpSettings()
    {
        /*
        if (file_exists(App::mainConfigFile())) {
            $defaultconfig = parse_ini_file(App::mainConfigFile(), true, INI_SCANNER_TYPED);
        }
        else {
            $defaultconfig = array();
        }
        if (file_exists(App::appSystemPath("index/config.ini.php"))) {
            $indexconfig = parse_ini_file(App::appSystemPath("index/config.ini.php"), true, INI_SCANNER_TYPED);
        }
        else {
            $indexconfig = array();
        }
        */

        return parent::checkPhpSettings();
    }
}

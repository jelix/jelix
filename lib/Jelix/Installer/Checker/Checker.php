<?php
/**
* check a jelix installation
*
* @author   Laurent Jouanneau
* @contributor Bastien Jaillot
* @contributor Olivier Demah, Brice Tence, Julien Issler
* @copyright 2007-2014 Laurent Jouanneau, 2008 Bastien Jaillot, 2009 Olivier Demah, 2010 Brice Tence, 2011 Julien Issler
* @link     http://www.jelix.org
* @licence  GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
* @since 1.0b2
*/
namespace Jelix\Installer\Checker;
use \Jelix\Core\App as App;

/**
 * check an installation of a jelix application
 * @since 1.0b2
 */
class Checker extends CheckerBase {

    protected function _otherCheck() {
        $this->checkAppPaths();
        $this->loadBuildFile();
    }

    function checkAppPaths(){
        $ok = true;
        if(!defined('JELIX_LIB_PATH') || !App::isInit()){
            throw new \Exception($this->messages->get('path.core'));
        }

        if(!file_exists(App::tempBasePath()) || !is_writable(App::tempBasePath())){
            $this->error('path.temp');
            $ok=false;
        }
        if(!file_exists(App::logPath()) || !is_writable(App::logPath())){
            $this->error('path.log');
            $ok=false;
        }
        if(!file_exists(App::varPath())){
            $this->error('path.var');
            $ok=false;
        }
        if(!file_exists(App::appConfigPath())){
            $this->error('path.config');
            $ok=false;
        }
        if(!file_exists(App::configPath())){
            $this->error('path.config');
            $ok=false;
        }
        elseif ($this->checkForInstallation) {
            if (!is_writable(App::configPath())) {
                $this->error('path.config.writable');
                $ok = false;
            }
            if (file_exists(App::configPath('profiles.ini.php'))
                && !is_writable(App::configPath('profiles.ini.php'))) {
                $this->error('path.profiles.writable');
                $ok = false;
            }
            if (file_exists(App::configPath('installer.ini.php'))
                && !is_writable(App::configPath('installer.ini.php'))) {
                $this->error('path.installer.writable');
                $ok = false;
            }
        }

        if(!file_exists(App::wwwPath())){
            $this->error('path.www');
            $ok=false;
        }

        foreach($this->otherPaths as $path) {
            $realPath = \jFile::parseJelixPath( $path );
            if (!file_exists($realPath)) {
                $this->error('path.custom.not.exists', array($path));
                $ok = false;
            }
            else if(!is_writable($realPath)) {
                $this->error('path.custom.writable', array($path));
                $ok = false;
            }
            else {
                $this->ok('path.custom.ok', array($path));
            }
        }

        if($ok)
            $this->ok('paths.ok');
        else
            throw new \Exception($this->messages->get('too.critical.error'));

        return $ok;
    }

    protected function loadBuildFile() {
        $composerFile = __DIR__.'/../composer.json';
        if (!file_exists($composerFile)){
            throw new \Exception($this->messages->get('build.not.found'));
        } else {
            $content = json_decode(file_get_contents($composerFile));
            preg_match('/([0-9\.]+)/', $content->require->php, $m);
            echo "version:".$m[1];
            $this->buildProperties['PHP_VERSION_TARGET'] = $m[1];
        }
    }

    protected function checkPhpSettings(){

        /*
        if (file_exists(App::mainConfigFile())) {
            $defaultconfig = parse_ini_file(App::mainConfigFile(), true);
        }
        else {
            $defaultconfig = array();
        }
        if (file_exists(App::appConfigPath("index/config.ini.php"))) {
            $indexconfig = parse_ini_file(App::appConfigPath("index/config.ini.php"), true);
        }
        else {
            $indexconfig = array();
        }
        */

        return parent::checkPhpSettings();
    }
}

<?php
/**
* @author     Laurent Jouanneau
* @copyright  2011-2015 Laurent Jouanneau
* @link       http://jelix.org
* @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

namespace Jelix\DevHelper;

/**
 * configuration for commands
 */
class CommandConfig {

    /**
     * @var string the web site of the project or your company. value readed from jelix-app.json/project.xml
     */
    public $infoWebsite='';

    /**
     * @var string the licence of generated files. value readed from jelix-app.json/project.xml
     */
    public $infoLicence='All rights reserved';

    /**
     * @var string link to the licence. value readed from jelix-app.json/project.xml
     */
    public $infoLicenceUrl='';

    /**
     * @var string the creator's name inserted in new files headers
     */
    public $infoCreatorName='_auto';

    /**
     * @var string the creator's mail inserted in new file headers
     */
    public $infoCreatorMail='_auto';

    /**
     * @var string copyright of new files. value readed from jelix-app.json/project.xml
     */
    public $infoCopyright='_auto';

    /**
     * @var string default timezone for new app
     */
    public $infoTimezone='Europe/Paris';

    /**
     * @var string default locale for new app
     */
    public $infoLocale='en_US';

    /**
     * @var boolean true = a chmod is done on new files and directories
     */
    public $doChmod = false;

    /**
     * @var integer chmod value on new files
     */
    public $chmodFileValue = 0644;

    /**
     * @var integer chmod value on new dir
     */
    public $chmodDirValue = 0755;

    /**
     * @var boolean true = a chown is done on new files and directories
     */
    public $doChown = false;

    /**
     * @var string define the user owner of new files/dir
     */
    public $chownUser = '';

    /**
     * @var string define the group owner of new files/dir
     */
    public $chownGroup = '';

    /**
     * @var string the lang code for help messages
     */
    public $helpLang = 'en';

    /**
     * @var boolean true = debug mode
     */
    public $debugMode = false;

    /**
     * @var boolean true = verbose mode, -v flag is implicit.
     */
    public $verboseMode = false;

    public $layoutTempPath = '%appdir%/temp/';
    public $layoutWwwPath = '%appdir%/www/';
    public $layoutVarPath = '%appdir%/var/';
    public $layoutLogPath = '%appdir%/var/log/';
    public $layoutConfigPath = '%appdir%/var/config/';
    public $layoutScriptsPath = '%appdir%/scripts/';

    /*
    // linux layout example
    // jelix is stored in /usr/local/lib/jelix-1.3/
    // apps are stored in /usr/local/lib/jelix-apps/%appname%/ (=%appdir%)
    public $layoutTempPath = '/var/tmp/jelix-apps/%appname%/';
    public $layoutWwwPath = '/var/www/jelix-apps/%appname%/';
    public $layoutVarPath = '/var/lib/jelix-apps/%appname%/';
    public $layoutLogPath = '/var/log/jelix-apps//%appname%/';
    public $layoutConfigPath = '/etc/jelix-apps/%appname%/';
    public $layoutScriptsPath = '%appdir%/scripts/';
    */

    /**
     * @var string the web site of the project or your company, used in a new project
     */
    public $newAppInfoWebsite='';

    /**
     * @var string the licence of generated files, for a new project
     */
    public $newAppInfoLicence='All rights reserved';

    /**
     * @var string link to the licence, for a new project
     */
    public $newAppInfoLicenceUrl='';

    /**
     * @var string copyright of new projects
     */
    public $newAppInfoCopyright='_auto';

    /**
     * @var string
     */
    public $newAppInfoLocale='en_US';

    /**
     * name of the application. cannot be indicated into configuration files
     */
    public $appName = '';

    function initAppPaths($applicationDir) {
        $applicationDir = rtrim($applicationDir, '/');
        $applicationDir = rtrim($applicationDir, '\\');
        $appname = basename($applicationDir);
        $search = array( '%appdir%', '%appname%');
        $replace = array($applicationDir, $appname);
        \Jelix\Core\App::initPaths(
            $applicationDir.'/',
            str_replace($search, $replace, $this->layoutWwwPath),
            str_replace($search, $replace, $this->layoutVarPath),
            str_replace($search, $replace, $this->layoutLogPath),
            str_replace($search, $replace, $this->layoutConfigPath),
            str_replace($search, $replace, $this->layoutScriptsPath)
        );
        \Jelix\Core\App::setTempBasePath(str_replace($search, $replace, $this->layoutTempPath));
    }

    /**
     * fill some properties from informations stored into the project.xml or jelix-app.json file.
     * @return string the application name
     */
    function loadFromProject($projectFile) {

        $infos = new \Jelix\Core\Infos\AppInfos(\Jelix\Core\App::appPath());
        if (!$infos->exists()){
            throw new Exception("cannot load jelix-app.json or project.xml");
        }

        $this->infoLicence = $infos->license;
        $this->infoLicenceUrl = $infos->licenseURL;
        $this->infoCopyright = $infos->copyright;
        $this->infoWebsite = $infos->homepageURL;
        $this->appName = $infos->name;
        return $infos->name;
    }

    /**
     * fill some properties from informations stored in an ini file.
     * @param string $iniFile the filename
     * @param string $appname the application name
     */
    function loadFromIni($iniFile, $appname='') {
        if (!file_exists($iniFile)) {
            return;
        }
        $ini = parse_ini_file($iniFile, true, INI_SCANNER_TYPED);
        foreach ($ini as $key=>$value) {
            if (!is_array($value) && isset($this->$key)) {
                if ($key == 'infoCopyright' || $key == 'newAppInfoCopyright' ) {
                    $value = str_replace('%YEAR%', date('Y'), $value);
                }
                $this->$key = $value;
            }
        }
        if ($appname && isset($ini[$appname]) && is_array($ini[$appname])) {
            foreach ($ini[$appname] as $key=>$value) {
                if (isset($this->$key)) {
                    if ($key == 'infoCopyright' || $key == 'newAppInfoCopyright' ) {
                        $value = str_replace('%YEAR%', date('Y'), $value);
                    }
                    $this->$key = $value;
                }
            }
        }
    }

    function generateUndefinedProperties($toCreateApp=false) {
        if ($toCreateApp) {
            $domainname = $this->getDomainName($this->newAppInfoWebsite);
            if ($domainname == '') {
                $domainname = $this->getDomainName($this->infoWebsite);
            }
            $this->infoWebsite = $this->newAppInfoWebsite;
        }
        else {
            $domainname = $this->getDomainName($this->infoWebsite);
        }
        if ($domainname == '') {
            $domainname = $this->appName;
        }

        if ($toCreateApp || $this->newAppInfoCopyright == '_auto') {
            $this->newAppInfoCopyright = date('Y') . ' ' . $domainname;
        }

        if ($toCreateApp || $this->infoCopyright == '_auto') {
            $this->infoCopyright = $this->newAppInfoCopyright;
        }
        if ($this->infoCreatorName == '_auto') {
            $this->infoCreatorName = $domainname;
        }
        if ($this->infoCreatorMail == '_auto') {
            if ($domainname != $this->appName) {
                $this->infoCreatorMail = 'contact@'.$domainname;
            }
            else {
                $this->infoCreatorMail = '';
            }
        }
        if ($toCreateApp) {
            $this->infoLicence = $this->newAppInfoLicence;
            $this->infoLicenceUrl = $this->newAppInfoLicenceUrl;
            $this->infoLocale = $this->newAppInfoLocale;
        }
    }

    protected function getDomainName($url) {
        if (preg_match("/^(https?:\\/\\/)?(www\\.)?(.*)$/", $url, $m)) {
            list($domainname) = explode('/', $m[3]);
            return $domainname;
        }
        else {
            return '';
        }
    }

    public function copyAppInfo($onlyUndefined = true) {
        if (! $onlyUndefined  || $this->infoCopyright == '_auto') {
            $this->infoCopyright = $this->newAppInfoCopyright;
        }
        $this->infoWebsite = $this->newAppInfoWebsite;
        $this->infoLicence = $this->newAppInfoLicence;
        $this->infoLicenceUrl = $this->newAppInfoLicenceUrl;
        $this->infoLocale = $this->newAppInfoLocale;
    }
}

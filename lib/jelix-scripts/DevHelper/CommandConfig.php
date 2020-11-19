<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2011-2015 Laurent Jouanneau
 *
 * @see       http://jelix.org
 * @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\DevHelper;

/**
 * configuration for commands.
 */
class CommandConfig
{
    /**
     * @var string the suffix part of generated name of new modules.
     *             Value readed from project.xml. May be something like '@yourwebsite.undefined'
     */
    public $infoIDSuffix = '_auto';

    /**
     * @var string the web site of the project or your company. value readed from project.xml
     */
    public $infoWebsite = '';

    /**
     * @var string the licence of generated files. value readed from project.xml
     */
    public $infoLicence = 'All rights reserved';

    /**
     * @var string link to the licence. value readed from project.xml
     */
    public $infoLicenceUrl = '';

    /**
     * @var string the creator's name inserted in new files headers
     */
    public $infoCreatorName = '_auto';

    /**
     * @var string the creator's mail inserted in new file headers
     */
    public $infoCreatorMail = '_auto';

    /**
     * @var string copyright of new files. value readed from project.xml
     */
    public $infoCopyright = '_auto';

    /**
     * @var string default timezone for new app
     */
    public $infoTimezone = 'Europe/Paris';

    /**
     * @var string default locale for new app
     */
    public $infoLocale = 'en_US';

    /**
     * @var bool true = a chmod is done on new files and directories
     */
    public $doChmod = false;

    /**
     * @var int chmod value on new files
     */
    public $chmodFileValue = 0644;

    /**
     * @var int chmod value on new dir
     */
    public $chmodDirValue = 0755;

    /**
     * @var bool true = a chown is done on new files and directories
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
     * @var bool true = debug mode
     */
    public $debugMode = false;

    /**
     * @var bool true = verbose mode, -v flag is implicit
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
     * @var string the suffix part of generated name of modules in a new project
     */
    public $newAppInfoIDSuffix = '_auto';

    /**
     * @var string the web site of the project or your company, used in a new project
     */
    public $newAppInfoWebsite = '';

    /**
     * @var string the licence of generated files, for a new project
     */
    public $newAppInfoLicence = 'All rights reserved';

    /**
     * @var string link to the licence, for a new project
     */
    public $newAppInfoLicenceUrl = '';

    /**
     * @var string copyright of new projects
     */
    public $newAppInfoCopyright = '_auto';

    /**
     * @var string
     */
    public $newAppInfoLocale = 'en_US';

    /**
     * name of the application. cannot be indicated into configuration files.
     */
    public $appName = '';

    public function __construct()
    {
        $this->chmodFileValue = octdec('644');
        $this->chmodDirValue = octdec('755');
    }

    public function initAppPaths($applicationDir)
    {
        $applicationDir = rtrim($applicationDir, '/');
        $applicationDir = rtrim($applicationDir, '\\');
        $appname = basename($applicationDir);
        $search = array('%appdir%', '%appname%');
        $replace = array($applicationDir, $appname);
        \jApp::initPaths(
            $applicationDir.'/',
            str_replace($search, $replace, $this->layoutWwwPath),
            str_replace($search, $replace, $this->layoutVarPath),
            str_replace($search, $replace, $this->layoutLogPath),
            str_replace($search, $replace, $this->layoutConfigPath),
            str_replace($search, $replace, $this->layoutScriptsPath)
        );
        \jApp::setTempBasePath(str_replace($search, $replace, $this->layoutTempPath));
    }

    /**
     * fill some properties from informations stored into the project.xml file.
     *
     * @param mixed $projectFile
     *
     * @return string the application name
     */
    public function loadFromProject($projectFile)
    {
        $doc = new \DOMDocument();

        if (!$doc->load($projectFile)) {
            throw new \Exception('cannot load project.xml');
        }

        if ($doc->documentElement->namespaceURI != JELIX_NAMESPACE_BASE.'project/1.0') {
            throw new \Exception('bad namespace in project.xml');
        }

        $info = $doc->getElementsByTagName('info');
        $info = $info->item(0);
        $id = $info->getAttribute('id');
        list($name, $suffix) = explode('@', $id);
        if ($suffix == '') {
            $suffix = $name;
        }
        $this->infoIDSuffix = '@'.$suffix;
        if ($info->getAttribute('name')) {
            $name = $info->getAttribute('name');
        }

        $licence = $info->getElementsByTagName('license');
        if (!$licence) {
            $licence = $info->getElementsByTagName('licence');
        }
        if ($licence->length) {
            $licence = $licence->item(0);
            $this->infoLicence = $licence->textContent;
            $this->infoLicenceUrl = $licence->getAttribute('URL');
        }

        $copyright = $info->getElementsByTagName('copyright');
        if ($copyright->length) {
            $copyright = $copyright->item(0);
            $this->infoCopyright = $copyright->textContent;
        }

        $website = $info->getElementsByTagName('homepageURL');
        if ($website->length) {
            $website = $website->item(0);
            $this->infoWebsite = $website->textContent;
        }
        $this->appName = $name;

        return $name;
    }

    /**
     * fill some properties from informations stored in an ini file.
     *
     * @param string $iniFile the filename
     * @param string $appname the application name
     */
    public function loadFromIni($iniFile, $appname = '')
    {
        if (!file_exists($iniFile)) {
            return;
        }
        $ini = parse_ini_file($iniFile, true, INI_SCANNER_TYPED);
        foreach ($ini as $key => $value) {
            if (!is_array($value) && isset($this->{$key})) {
                $this->{$key} = $this->_parseIniValue($key, $value);
            }
        }
        if ($appname && isset($ini[$appname]) && is_array($ini[$appname])) {
            foreach ($ini[$appname] as $key => $value) {
                if (isset($this->{$key})) {
                    $this->{$key} = $this->_parseIniValue($key, $value);
                }
            }
        }
    }

    protected function _parseIniValue($key, $value)
    {
        if ($key == 'infoCopyright' || $key == 'newAppInfoCopyright') {
            $value = str_replace('%YEAR%', date('Y'), $value);
        } elseif ($key == 'chmodFileValue' || $key == 'chmodDirValue') {
            if (!is_string($value)) {
                $value = (string) $value;
            }
            $value = octdec($value);
        }

        return $value;
    }

    public function generateUndefinedProperties($toCreateApp = false)
    {
        if ($toCreateApp) {
            $domainname = $this->getDomainName($this->newAppInfoWebsite);
            if ($domainname == '') {
                $domainname = $this->getDomainName($this->infoWebsite);
            }
            $this->infoWebsite = $this->newAppInfoWebsite;
        } else {
            $domainname = $this->getDomainName($this->infoWebsite);
        }
        if ($domainname == '') {
            $domainname = $this->appName;
        }

        if ($toCreateApp || $this->newAppInfoIDSuffix == '_auto' || $this->newAppInfoIDSuffix == '') {
            $this->newAppInfoIDSuffix = '@'.$domainname;
        }

        if ($toCreateApp || $this->newAppInfoCopyright == '_auto') {
            $this->newAppInfoCopyright = date('Y').' '.$domainname;
        }

        if ($toCreateApp || $this->infoIDSuffix == '_auto' || $this->infoIDSuffix == '') {
            $this->infoIDSuffix = $this->newAppInfoIDSuffix;
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
            } else {
                $this->infoCreatorMail = '';
            }
        }
        if ($toCreateApp) {
            $this->infoLicence = $this->newAppInfoLicence;
            $this->infoLicenceUrl = $this->newAppInfoLicenceUrl;
            $this->infoLocale = $this->newAppInfoLocale;
        }
    }

    protected function getDomainName($url)
    {
        if (preg_match('/^(https?:\\/\\/)?(www\\.)?(.*)$/', $url, $m)) {
            list($domainname) = explode('/', $m[3]);

            return $domainname;
        }

        return '';
    }

    public function copyAppInfo($onlyUndefined = true)
    {
        if (!$onlyUndefined || $this->infoIDSuffix == '_auto' || $this->infoIDSuffix == '') {
            $this->infoIDSuffix = $this->newAppInfoIDSuffix;
        }
        if (!$onlyUndefined || $this->infoCopyright == '_auto') {
            $this->infoCopyright = $this->newAppInfoCopyright;
        }
        $this->infoWebsite = $this->newAppInfoWebsite;
        $this->infoLicence = $this->newAppInfoLicence;
        $this->infoLicenceUrl = $this->newAppInfoLicenceUrl;
        $this->infoLocale = $this->newAppInfoLocale;
    }
}

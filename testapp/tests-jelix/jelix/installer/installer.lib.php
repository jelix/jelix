<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2009-2012 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
* @since 1.2
*/

class testInstallerEntryPoint extends jInstallerEntryPoint {

    function __construct($mainConfigIni,
                         $localConfigIni,
                         $configFile, $file, $type, $configContent) {
        $this->type = $type;
        $this->isCliScript = ($type == 'cmdline');

        if (is_object($configFile)) {
            $this->epConfigIni = $configFile;
            $this->localEpConfigIni = new testInstallerIniFileModifier($configFile->getFileName());
            $this->configFile = $configFile->getFileName();
        }
        else {
            $this->epConfigIni = new testInstallerIniFileModifier($configFile);
            $this->localEpConfigIni = new testInstallerIniFileModifier($configFile);
            $this->configFile = $configFile;
        }

        $this->fullConfigIni = new \Jelix\IniFile\MultiIniModifier($localConfigIni,
            new \Jelix\IniFile\MultiIniModifier($this->epConfigIni, $this->localEpConfigIni));

        $this->scriptName =  ($this->isCliScript?$file:'/'.$file);
        $this->file = $file;
        $this->mainConfigIni = $mainConfigIni;
        $this->localConfigIni = $localConfigIni;
        $compiler = new \Jelix\Core\Config\Compiler($this->configFile,
                                                    $this->scriptName,
                                                    $this->isCliScript);
        $this->config = $compiler->read(true, $configContent);
        $this->modulesInfos = $compiler->getModulesInfos();
    }

    function getEpId() {
        return str_replace('.php', '', $this->file);
    }

    function setModuleData($name, $modInfos) {
        $this->modulesInfos[$name] = $modInfos;
        $this->config->_allModulesPathList[$name]='/';
    }
}

class testInstallerModuleInfos extends \Jelix\Core\Infos\ModuleInfos {

    function __construct($path, $xmlstring) {
        $p = rtrim($path, '/');
        $this->path = $p.'/';
        $this->name = basename($p);

        $config = \Jelix\Core\App::config();
        if ($config) {
            $locale = $config->locale;
        }
        else {
            $locale = '';
        }
        $parser = new testInstallerModuleParser($this->path.'module.xml', 'en');
        $parser->parse2($this, $xmlstring);
    }
}

class testInstallerModuleParser extends \Jelix\Core\Infos\ModuleXmlParser {

    public function parse2(\Jelix\Core\Infos\InfosAbstract $object, $xmlstring){
        $xml = new XMLreader();
        $xml->xml($xmlstring, '', LIBXML_COMPACT);

        while ($xml->read()) {
            if($xml->nodeType == \XMLReader::ELEMENT) {
                $method = 'parse' . ucfirst($xml->name);
                if (method_exists ($this, $method)) {
                    $this->$method($xml, $object);
                }
            }
        }
        $xml->close();
        return $object;
    }
}

/**
 *
 */
class testInstallReporter implements jIInstallReporter {
    use \Jelix\Installer\ReporterTrait;

    public $startCounter = 0;

    public $endCounter = 0;

    public $messages = array();

    function start() {
        $this->startCounter ++;
    }

    function message($message, $type='') {
        $this->addMessageType($type);
        $this->messages[] = array($message, $type);
    }

    function end() {
        $this->endCounter ++;
    }
}


/**
 * ini file modifier without file load/save supports
 */
class testInstallerIniFileModifier extends \Jelix\IniFile\IniModifier {

    function __construct($filename) {
        $this->filename = $filename;
    }

    public function save($chmod=null) {
        $this->modified = false;
    }

    public function saveAs($filename) {}
}

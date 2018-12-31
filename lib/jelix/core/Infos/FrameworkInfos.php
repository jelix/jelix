<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2014-2018 Laurent Jouanneau
 * @link       http://jelix.org
 * @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Core\Infos;

use Jelix\IniFile\IniModifier;

class FrameworkInfos {

    /**
     * @var IniModifier
     */
    protected $iniFile;

    /**
     * @var IniModifier
     */
    protected $iniLocalFile;

    /**
     * @var EntryPoint[]  keys are filename
     */
    protected $entrypoints = array();

    /**
     * @var EntryPoint[]  keys are filename
     */
    protected $localEntrypoints = array();

    /**
     * FrameworkInfos constructor.
     * @param string $frameworkFile the path to the framework.ini.php file
     * @param string $frameworkFile the path to the localframework.ini.php file
     */
    public function __construct($frameworkFile, $localFrameworkFile = '') {
        $this->iniFile = new IniModifier($frameworkFile, ';<' . '?php die(\'\');?' . '>');
        $this->readIniFile($this->iniFile);

        if ($localFrameworkFile != '') {
            $this->iniLocalFile = new IniModifier($localFrameworkFile, ';<' . '?php die(\'\');?' . '>');
            $this->readIniFile($this->iniLocalFile, true);
        }
    }

    protected function readIniFile(IniModifier $iniFile, $isLocal=false) {
        foreach ($iniFile->getSectionList() as $section) {
            if (!preg_match("/^entrypoint\\:(.*)$/", $section, $m)) {
                continue;
            }
            $name = $m[1];
            $configValue = $iniFile->getValue('config', $section);
            $typeValue = $iniFile->getValue('type', $section);
            if ($typeValue === null) {
                $typeValue = '';
            }
            if ($configValue) {
                if ($isLocal) {
                    $this->addLocalEntryPointInfo($name, $configValue, $typeValue);
                }
                else {
                    $this->addEntryPointInfo($name, $configValue, $typeValue);
                }
            }
        }
    }

    /**
     * @param string $name
     * @return EntryPoint|null
     */
    public function getEntryPointInfo($id)
    {
        if (strpos($id, '.php') !== false) {
            $id = substr($id, 0, -4);
        }

        if (isset($this->localEntrypoints[$id])) {
            return $this->localEntrypoints[$id];
        }

        if (isset($this->entrypoints[$id])) {
            return $this->entrypoints[$id];
        }

        return null;
    }

    /**
     * @return EntryPoint[]
     */
    public function getEntryPoints() {
        return array_merge($this->entrypoints, $this->localEntrypoints);
    }

    public function addEntryPointInfo($fileName, $configFileName, $type = 'classic')
    {
        if (strpos($fileName, '.php') !== false) {
            $fileName = substr($fileName, 0, -4);
        }

        $this->entrypoints[$fileName] = new EntryPoint($fileName, $configFileName, $type);
        return $this->entrypoints[$fileName];
    }

    public function addLocalEntryPointInfo($fileName, $configFileName, $type = 'classic')
    {
        if (!$this->iniLocalFile) {
            throw new \UnexpectedValueException("no local framework ini file has been given to FrameworkInfos");
        }
        if (strpos($fileName, '.php') !== false) {
            $fileName = substr($fileName, 0, -4);
        }

        $this->localEntrypoints[$fileName] = new EntryPoint($fileName, $configFileName, $type);
        return $this->localEntrypoints[$fileName];
    }

    public function removeEntryPointInfo($fileName) {
        if (strpos($fileName, '.php') !== false) {
            $id = substr($fileName, 0, -4);
        }
        else {
            $id = $fileName;
            $fileName .= '.php';
        }
        unset($this->entrypoints[$id]);
        unset($this->localEntrypoints[$id]);
        $this->iniFile->removeSection('entrypoint:'.$fileName);
        if ($this->iniLocalFile) {
            $this->iniLocalFile->removeSection('entrypoint:'.$fileName);
        }
    }

    public function save()
    {
        $this->updateIni();
        $this->iniFile->save();
        if ($this->iniLocalFile && $this->iniLocalFile->isModified()) {
            $this->iniLocalFile->save();
        }
    }

    protected function updateIni() {
        foreach ($this->entrypoints as $item) {
            $this->iniFile->setValues(array(
                'config' => $item->getConfigFile(),
                'type' => $item->getType()
            ), 'entrypoint:'.$item->getFile());
        }
        if ($this->iniLocalFile) {
            foreach ($this->localEntrypoints as $item) {
                $this->iniLocalFile->setValues(array(
                    'config' => $item->getConfigFile(),
                    'type' => $item->getType()
                ), 'entrypoint:'.$item->getFile());
            }
        }
    }

    /**
     * create a new FrameworkInfos object
     *
     * @return FrameworkInfos
     */
    public static function load()
    {
        return new self(
            \jApp::appConfigPath('framework.ini.php'),
            \jApp::varConfigPath('localframework.ini.php'));
    }
}

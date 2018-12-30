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
     * @var EntryPoint[]  keys are filename
     */
    protected $entrypoints = array();

    /**
     * FrameworkInfos constructor.
     * @param string $frameworkFile the path to the framework.ini.php file
     */
    public function __construct($frameworkFile) {
        $this->iniFile = new IniModifier($frameworkFile, ';<' . '?php die(\'\');?' . '>');

        foreach ($this->iniFile->getSectionList() as $section) {
            if (!preg_match("/^entrypoint\\:(.*)$/", $section, $m)) {
                continue;
            }
            $name = $m[1];
            $configValue = $this->iniFile->getValue('config', $section);
            $typeValue = $this->iniFile->getValue('type', $section);
            if ($typeValue === null) {
                $typeValue = '';
            }
            if ($configValue) {
                $this->addEntryPointInfo($name, $configValue, $typeValue);
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
        if (isset($this->entrypoints[$id])) {
            return $this->entrypoints[$id];
        }

        return null;
    }

    /**
     * @return EntryPoint[]
     */
    public function getEntryPoints() {
        return $this->entrypoints;
    }

    public function addEntryPointInfo($fileName, $configFileName, $type = 'classic')
    {
        if (strpos($fileName, '.php') !== false) {
            $fileName = substr($fileName, 0, -4);
        }

        $this->entrypoints[$fileName] = new EntryPoint($fileName, $configFileName, $type);
        return $this->entrypoints[$fileName];
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
        $this->iniFile->removeSection('entrypoint:'.$fileName);
    }

    public function save()
    {
        foreach ($this->entrypoints as $item) {
            $this->iniFile->setValues(array(
                'config' => $item->getConfigFile(),
                'type' => $item->getType()
            ), 'entrypoint:'.$item->getId());
        }
        return $this->iniFile->save();
    }

    /**
     * create a new FrameworkInfos object
     *
     * @return FrameworkInfos
     */
    public static function load()
    {
        return new FrameworkInfos(\jApp::appConfigPath('framework.ini.php'));
    }
}

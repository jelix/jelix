<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2014-2018 Laurent Jouanneau
 *
 * @see       http://jelix.org
 * @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Core\Infos;

use Jelix\IniFile\IniModifier;

class FrameworkInfos
{
    /**
     * @var IniModifier
     */
    protected $iniFile;

    /**
     * @var IniModifier
     */
    protected $iniLocalFile;

    /**
     * @var EntryPoint[] keys are filename
     */
    protected $entrypoints = array();

    /**
     * @var EntryPoint[] keys are filename
     */
    protected $localEntrypoints = array();

    /**
     * @var string
     */
    protected $defaultEntryPoint = '';

    /**
     * FrameworkInfos constructor.
     *
     * @param string $frameworkFile      the path to the framework.ini.php file
     * @param string $frameworkFile      the path to the localframework.ini.php file
     * @param mixed  $localFrameworkFile
     */
    public function __construct($frameworkFile, $localFrameworkFile = '')
    {
        $this->iniFile = new IniModifier($frameworkFile, ';<'.'?php die(\'\');?'.'>');
        $this->readIniFile($this->iniFile);

        if ($localFrameworkFile != '') {
            $this->iniLocalFile = new IniModifier($localFrameworkFile, ';<'.'?php die(\'\');?'.'>');
            $this->readIniFile($this->iniLocalFile, true);
        }

        if (!$this->defaultEntryPoint && count($this->entrypoints)) {
            $ep = $this->getEntryPointInfo('index');
            if (!$ep) {
                $epid = array_keys($this->entrypoints)[0];
                $ep = $this->getEntryPointInfo($epid);
            }
            $this->defaultEntryPoint = $ep->getId();
        }
    }

    protected function readIniFile(IniModifier $iniFile, $isLocal = false)
    {
        foreach ($iniFile->getSectionList() as $section) {
            if (!preg_match('/^entrypoint\\:(.*)$/', $section, $m)) {
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
                    $ep = $this->addLocalEntryPointInfo($name, $configValue, $typeValue);
                } else {
                    $ep = $this->addEntryPointInfo($name, $configValue, $typeValue);
                }
                if ($iniFile->getValue('default', $section)) {
                    $this->defaultEntryPoint = $ep->getId();
                }
            }
        }
    }

    /**
     * @param mixed $id
     *
     * @return null|EntryPoint
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
     * @return null|EntryPoint
     */
    public function getDefaultEntryPointInfo()
    {
        if ($this->defaultEntryPoint) {
            return $this->getEntryPointInfo($this->defaultEntryPoint);
        }

        return null;
    }

    /**
     * @return EntryPoint[]
     */
    public function getEntryPoints()
    {
        return array_merge($this->entrypoints, $this->localEntrypoints);
    }

    public function addEntryPointInfo($fileName, $configFileName, $type = 'classic')
    {
        if (strpos($fileName, '.php') !== false) {
            $fileName = substr($fileName, 0, -4);
        }

        $this->entrypoints[$fileName] = new EntryPoint($fileName, $configFileName, $type);
        if (!$this->defaultEntryPoint) {
            $this->defaultEntryPoint = $fileName;
        }

        return $this->entrypoints[$fileName];
    }

    public function addLocalEntryPointInfo($fileName, $configFileName, $type = 'classic')
    {
        if (!$this->iniLocalFile) {
            throw new \UnexpectedValueException('no local framework ini file has been given to FrameworkInfos');
        }
        if (strpos($fileName, '.php') !== false) {
            $fileName = substr($fileName, 0, -4);
        }

        $this->localEntrypoints[$fileName] = new EntryPoint($fileName, $configFileName, $type);
        $this->localEntrypoints[$fileName]->setAsLocal();
        return $this->localEntrypoints[$fileName];
    }

    public function removeEntryPointInfo($fileName)
    {
        if (strpos($fileName, '.php') !== false) {
            $id = substr($fileName, 0, -4);
        } else {
            $id = $fileName;
            $fileName .= '.php';
        }
        unset($this->entrypoints[$id], $this->localEntrypoints[$id]);

        if ($this->defaultEntryPoint == $id) {
            if (count($this->entrypoints)) {
                if (isset($this->entrypoints['index'])) {
                    $this->defaultEntryPoint = 'index';
                } else {
                    $this->defaultEntryPoint = array_keys($this->entrypoints)[0];
                }
            } else {
                $this->defaultEntryPoint = '';
            }
        }
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

    protected function updateIni()
    {
        foreach ($this->entrypoints as $item) {
            $this->updateFrameworkIniSection($this->iniFile, $item);
        }
        if ($this->iniLocalFile) {
            foreach ($this->localEntrypoints as $item) {
                $this->updateFrameworkIniSection($this->iniLocalFile, $item);
            }
        }
    }

    /**
     * @param IniModifier $ini
     * @param EntryPoint $ep
     * @return void
     */
    protected function updateFrameworkIniSection($ini, $ep)
    {
        $sectionName = 'entrypoint:'.$ep->getFile();
        $values = array(
            'config' => $ep->getConfigFile(),
            'type' => $ep->getType(),
        );
        $previous = $ini->getValues($sectionName);

        if ($ep->getId() == $this->defaultEntryPoint) {
            $values['default'] = true;
            $defaultChanged = !$previous || !isset($previous['default']) || !$previous['default'];
        } else {
            unset($values['default']);
            $defaultChanged = !$previous || (isset($previous['default']) && $previous['default']);
        }

        // if the section is already there, we should not do a setValues, else
        // the file will be marked as "modified" and will be rewritten.
        // we don't want that for framework.ini.php if we are in a local mode.
        if (!$previous ||
            $defaultChanged ||
            $values['config'] != $previous['config'] ||
            $values['type'] != $previous['type'])
        {
            $ini->setValues($values, $sectionName);
        }
    }

    /**
     * create a new FrameworkInfos object.
     *
     * @return FrameworkInfos
     */
    public static function load()
    {
        return new self(
            \jApp::appSystemPath('framework.ini.php'),
            \jApp::varConfigPath('localframework.ini.php')
        );
    }
}

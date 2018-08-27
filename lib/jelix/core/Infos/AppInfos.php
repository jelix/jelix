<?php
/**
* @author     Laurent Jouanneau
* @copyright  2014-2018 Laurent Jouanneau
* @link       http://jelix.org
* @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/
namespace Jelix\Core\Infos;

class AppInfos extends InfosAbstract {

    /**
     * @var EntryPoint[]  key=filename
     */
    public $entrypoints = array();

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

    public function addEntryPointInfo($fileName, $configFileName, $type = 'classic')
    {
        if (strpos($fileName, '.php') !== false) {
            $fileName = substr($fileName, 0, -4);
        }

        $this->entrypoints[$fileName] = new EntryPoint($fileName, $configFileName, $type);
    }

    public function save()
    {
        if ($this->isXmlFile()) {
            $writer = new ProjectXmlWriter($this->getFilePath());
            return $writer->write($this);
        }
        return false;
    }

}
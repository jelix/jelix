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

    /**
     * create a new AppInfos object, loaded from a file that is into the
     * given directory
     *
     * @param string $directoryPath the path to the directory
     * @return AppInfos
     */
    public static function load($directoryPath) {
        /*if (file_exists($directoryPath.'/jelix-app.json')) {
            $parser = new AppJsonParser($directoryPath.'/jelix-app.json');
            return $parser->parse();
        }
        else*/
        if (file_exists($directoryPath.'/project.xml')) {
            $parser = new ProjectXmlParser($directoryPath.'/project.xml');
            return $parser->parse();
        }

        //throw new \Exception('No project.xml or jelix-app.json file into '.$directoryPath);
        throw new \Exception('No project.xml file into '.$directoryPath);
    }

}
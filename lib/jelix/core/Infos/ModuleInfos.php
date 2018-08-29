<?php
/**
* @author     Laurent Jouanneau
* @copyright  2014-2018 Laurent Jouanneau
* @link     http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

namespace Jelix\Core\Infos;

class ModuleInfos extends InfosAbstract {

    /**
     * @var array of array('name'=>'', 'version'=>'')
     */
    public $dependencies = array();

    /**
     * list of incompatibilities of the module
     * @var array of array('name'=>'', 'version'=>'')
     */
    public $incompatibilities = array();

    /**
     * @var array of path
     */
    public $autoloaders = array();

    /**
     * @var array  classname => file path relative to the module directory
     */
    public $autoloadClasses = array();

    /**
     * @var array  pattern => directory path relative to the module directory
     */
    public $autoloadClassPatterns = array();

    /**
     * @var array namespace name => psr0 path, 0 => array( fallback path )
     */
    public $autoloadPsr0Namespaces = array();

    /**
     * @var array namespace name => psr4 path, 0 => array( fallback path )
     */
    public $autoloadPsr4Namespaces = array();

    /**
     *  @var array  of strings (path)
     */
    public $autoloadIncludePath = array();

    public function save() {
        if ($this->isXmlFile()) {
            $writer = new ModuleXmlWriter($this->getFilePath());
            return $writer->write($this);
        }
        return false;
    }


    /**
     * create a new ModuleInfos object, loaded from a file that is into the
     * given directory
     *
     * @param string $directoryPath the path to the directory
     * @return ModuleInfos
     */
    public static function load($directoryPath) {
        if (!file_exists($directoryPath.'/module.xml')) {
            throw new \Exception('No module.xml file into '.$directoryPath);
        }
        $parser = new ModuleXmlParser($directoryPath.'/module.xml');
        return $parser->parse();
    }
}

<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2014-2018 Laurent Jouanneau
 *
 * @see     http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Core\Infos;

/**
 * Information about the module, retrieved from its module.xml file.
 */
class ModuleInfos extends InfosAbstract
{
    /**
     * list of module dependencies.
     *
     * @var array[] items are: array('name'=>'', 'version'=>'', 'optional'=>true/false)
     */
    public $dependencies = array();

    /**
     * list of incompatibilities of the module.
     *
     * @var array[] items are array('name'=>'', 'version'=>'')
     */
    public $incompatibilities = array();

    /**
     * List of files that initialize an autoloader.
     *
     * @var string[]
     */
    public $autoloaders = array();

    /**
     * List of classes and their relative path to the module.
     *
     * @var string[] Keys are class name, values are path
     */
    public $autoloadClasses = array();

    /**
     * List of directories where to find classes having a specific name pattern.
     *
     * @var array[] key is a regexp. Value is: array ('<rel_dirpath>', '<suffix>')
     */
    public $autoloadClassPatterns = array();

    /**
     * List of namespaces and corresponding path following PSR-0.
     *
     * Each namespace may have several directories where to search.
     *
     * @var array[]
     *              'namespace name' => array( array ('<rel_dirpath>', '<suffix>')),
     *              0 => array( array ('<rel_dirpath>', '<suffix>'),... )
     */
    public $autoloadPsr0Namespaces = array();

    /**
     * List of namespaces and corresponding path following PSR-4.
     *
     * Each namespace may have several directories where to search.
     *
     * @var array[]
     *              'namespace name' => array( array ('<rel_dirpath>', '<suffix>')),
     *              0 => array( array ('<rel_dirpath>', '<suffix>'),... )
     */
    public $autoloadPsr4Namespaces = array();

    /**
     * list of relative path to directories where a class could be found.
     *
     * @var array[] items are  array ('<rel_dirpath>', '<suffix>')
     */
    public $autoloadIncludePath = array();

    public function save()
    {
        if ($this->isXmlFile()) {
            $writer = new ModuleXmlWriter($this->getFilePath());

            return $writer->write($this);
        }

        $writer = new ModuleJsonWriter($this->getFilePath());

        return $writer->write($this);
    }

    /**
     * create a new ModuleInfos object, loaded from a file that is into the
     * given directory.
     *
     * @param string $directoryPath the path to the directory
     *
     * @return ModuleInfos
     */
    public static function load($directoryPath)
    {
        if (file_exists($directoryPath.'/jelix-module.json')) {
            $parser = new ModuleJsonParser($directoryPath.'/jelix-module.json');

            return $parser->parse();
        }
        if (file_exists($directoryPath.'/module.xml')) {
            $parser = new ModuleXmlParser($directoryPath.'/module.xml');

            return $parser->parse();
        }

        throw new \Exception('No module.xml or jelix-module.json file into '.$directoryPath);
    }
}

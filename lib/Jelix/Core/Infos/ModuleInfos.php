<?php
/**
* @author     Laurent Jouanneau
* @copyright  2014 Laurent Jouanneau
* @link     http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

namespace Jelix\Core\Infos;

class ModuleInfos extends InfosAbstract {

    public $type = 'module';

    /**
     * list of module dependencies
     * @var array[]  items are: array('name'=>'', 'version'=>'')
     */
    public $dependencies = array();

    /**
     * List of alternative module dependencies
     * @var array[]  items are array of array('name'=>'', 'version'=>'')
     */
    public $alternativeDependencies = array();

    /**
     * list of incompatibilities of the module
     * @var array[]  items are array('name'=>'', 'version'=>'')
     */
    public $incompatibilities = array();

    /**
     * List of files that initialize an autoloader
     * @var string[]
     */
    public $autoloaders = array();

    /**
     * List of classes and their relative path to the module
     * @var string[]  Keys are class name, values are path
     */
    public $autoloadClasses = array();

    /**
     * List of directories where to find classes having a specific name pattern
     *
     * @var array[]  key is a regexp. Value is: array ('<rel_dirpath>', '<suffix>')
     */
    public $autoloadClassPatterns = array();

    /**
     * List of namespaces and corresponding path following PSR-0
     *
     * Each namespace may have several directories where to search.
     *
     * @var array[]
     *      'namespace name' => array( array ('<rel_dirpath>', '<suffix>')),
     *      0 => array( array ('<rel_dirpath>', '<suffix>'),... )
     */
    public $autoloadPsr0Namespaces = array(0=>array());

    /**
     * List of namespaces and corresponding path following PSR-4
     *
     * Each namespace may have several directories where to search.
     *
     * @var array[]
     *      'namespace name' => array( array ('<rel_dirpath>', '<suffix>')),
     *      0 => array( array ('<rel_dirpath>', '<suffix>'),... )
     */
    public $autoloadPsr4Namespaces = array(0=>array());

    /**
     * list of relative path to directories where a class could be found
     *
     * @var array[] items are  array ('<rel_dirpath>', '<suffix>')
     */
    public $autoloadIncludePath = array();

    /**
     * @param string $path path to the module directory
     */
    function __construct($path) {
        $p = rtrim($path, '/');
        $this->path = $p.'/';
        // by default, the module name is the directory name of the module
        $this->name = basename($p);

        $config = \Jelix\Core\App::config();
        if ($config) {
            $locale = $config->locale;
        }
        else {
            $locale = '';
        }

        if (file_exists($this->path.'jelix-module.json')) {
            $parser = new ModuleJsonParser($this->path.'jelix-module.json', $locale);
        }
        else if (file_exists($this->path.'module.xml')) {
            $this->isXml = true;
            $parser = new ModuleXmlParser($this->path.'module.xml', $locale);
        }
        else {
            return;
        }
        $this->_exists = true;
        $parser->parse($this);
    }

    public function getFile() {
        if ($this->isXml) {
            return 'module.xml';
        }
        return 'jelix-module.json';
    }

}

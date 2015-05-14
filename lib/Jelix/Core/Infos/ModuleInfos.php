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
     * @var array of array('name'=>'', 'version'=>'')
     */
    public $dependencies = array();

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
    public $autoloadPsr0Namespaces = array(0=>array());

    /**
     * @var array namespace name => psr4 path, 0 => array( fallback path )
     */
    public $autoloadPsr4Namespaces = array(0=>array());

    /**
     *  @var array  of strings (path)
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
}

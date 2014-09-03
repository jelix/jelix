<?php
/**
* @author     Laurent Jouanneau
* @copyright  2014 Laurent Jouanneau
* @link     http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

namespace Jelix\Core\Infos;

class ModuleInfos extends InfosAbstract {

    public $type = 'library';

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
        $this->path = $path;

        $config = \Jelix\Core\App::config();
        if ($config) {
            $locale = $config->locale;
        }
        else {
            $locale = '';
        }

        if (file_exists($path.'composer.json')) {
            $parser = new ComposerJsonParser($path.'composer.json', $locale);
        }
        else if (file_exists($path.'module.xml')) {
            $this->isXml = true;
            $parser = new ModuleXmlParser($path.'module.xml', $locale);
        }
        else {
            return;
        }

        $parser->parse($this);

    }

}

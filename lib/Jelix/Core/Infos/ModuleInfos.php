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

    public $autoloaders = array();

    public $autoloadClasses = array();

    public $autoloadClassPatterns = array();

    public $autoloadNamespaces = array();

    public $autoloadPsr4Namespaces = array();

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

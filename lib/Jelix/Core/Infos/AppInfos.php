<?php
/**
* @author     Laurent Jouanneau
* @copyright  2014 Laurent Jouanneau
* @link       http://jelix.org
* @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/
namespace Jelix\Core\Infos;

class AppInfos extends InfosAbstract {

    public $type = 'application';

    public $configPath = '';
    public $logPath = '';
    public $varPath = '';
    public $wwwPath = '';
    public $tempPath = '';

    /**
     * @var array  key=filename value=array('config'=>'file', 'type'=>'classic/cmdline...')
     */
    public $entrypoints = array();

    /**
     * @param string $path path to the app directory. If not given
     *              call \Jelix\Core\App to retrieve it.
     */
    function __construct($path = '') {
        $this->path = rtrim($path, '/').'/';

        $config = \Jelix\Core\App::config();
        if ($config) {
            $locale = $config->locale;
        }
        else {
            $locale = '';
        }

        if (!$path) {
            $path = \Jelix\Core\App::appPath();
            if (!$path) {
                throw new \Exception ("Jelix Application is not initialized with Jelix\\Core\\App");
            }
        }

        if (file_exists($path.'composer.json')) {
            $parser = new ComposerJsonParser($path.'composer.json', $locale);
        }
        else if (file_exists($path.'project.xml')) {
            $this->isXml = true;
            $parser = new AppXmlParser($path.'project.xml', $locale);
        }
        else {
            return;
        }

        $this->_exists = true;
        $parser->parse($this);
    }
}
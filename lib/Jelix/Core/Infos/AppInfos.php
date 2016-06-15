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

        $this->path = rtrim($path, '/').'/';

        if (file_exists($this->path.'jelix-app.json')) {
            $parser = new AppJsonParser($this->path.'jelix-app.json', $locale);
        }
        else if (file_exists($this->path.'project.xml')) {
            $this->isXml = true;
            $parser = new ProjectXmlParser($this->path.'project.xml', $locale);
        }
        else {
            return;
        }

        $this->_exists = true;
        $parser->parse($this);
    }

    public function getEntryPointInfo($name) {
        if (strpos($name, '.php') === false) {
           $name .= '.php';
        }
        if (isset($this->entrypoints[$name])) {
            return $this->entrypoints[$name];
        }

        return null;
    }

    public function addEntryPointInfo($fileName, $configFileName, $type) {
        $this->entrypoints[$fileName] = $entrypoint = array('file'=>$fileName,
                                                    'config'=>$configFileName, 'type'=>$type);
        if ($this->isXmlFile()) {
            $doc = new \DOMDocument();

            if (!$doc->load($this->path.'project.xml')) {
                throw new \Exception("addEntryPointInfo: cannot load project.xml");
            }
            if ($doc->documentElement->namespaceURI != JELIX_NAMESPACE_BASE.'project/1.0'){
                throw new \Exception("addEntryPointInfo: bad namespace in project.xml");
            }

            $eplist = $doc->documentElement->getElementsByTagName("entrypoints");
            if (!$eplist->length) {
                $ep = $doc->createElementNS(JELIX_NAMESPACE_BASE.'project/1.0', 'entrypoints');
                $doc->documentElement->appendChild($ep);
            }
            else {
                $ep = $eplist->item(0);
                foreach($ep->getElementsByTagName("entry") as $entry){
                    if ($entry->getAttribute("file") == $fileName){
                        $entryType = $entry->getAttribute("type") ?: 'classic';
                        if ($entryType != $type) {
                            throw new \Exception("There is already an entrypoint with the same name but with another type ($epId, $epType)");
                        }
                        return;
                    }
                }
            }

            $ep->appendChild(new \DOMText("\n        "));
            $elem = $doc->createElementNS(JELIX_NAMESPACE_BASE.'project/1.0', 'entry');
            $elem->setAttribute("file", $fileName);
            $elem->setAttribute("config", $configFileName);
            $elem->setAttribute("type", $type);
            $ep->appendChild($elem);
            $ep->appendChild(new \DOMText("\n    "));

            $doc->save($this->path.'project.xml');
        }
        else {
            $json = @json_decode(file_get_contents($this->path.'jelix-app.json'), true);
            if (!is_array($json)) {
                throw new \Exception($this->path ."jelix-app.json is not a JSON file");
            }
            if (!isset($json['entrypoints'])) {
                $json['entrypoints'] = array();
            }
            $json['entrypoints'][] = $entrypoint;
            file_put_contents($this->path.'jelix-app.json', json_encode($json));
        }
    }

    public function getFile() {
        if ($this->isXml) {
            return 'project.xml';
        }
        return 'jelix-app.json';
    }
}
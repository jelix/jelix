<?php
/**
* @author     Laurent Jouanneau
* @copyright  2014 Laurent Jouanneau
* @link       http://jelix.org
* @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/
namespace Jelix\Core\Infos;

/**
 *
 */
class ComposerJsonParser {

    /**
     * @var string the path of the json file to read
     */
    protected $path;

    /**
     * @var string the json content
     */
    protected $json;

    /**
     * @param string $path the path of the xml file to read, with trailing slash
     */
    public function __construct($path, $locale) {
        if (!file_exists($path)) {
            throw new \Exception($this->path ." does not exist");
        }
        $this->path = $path;
        $this->locale = substr($locale, 0, 2);
        $this->json = @json_decode(file_get_contents($this->path), true);
        if (!is_array($this->json)) {
            throw new \Exception($this->path ." is not a JSON file");
        }
    }

    /**
     *
     */
    public function parse(InfosAbstract $object){

        $json = array_merge(array(
            "name"=> "",
            "type"=> "",
            "version"=> "",
            "description"=> "",
            "keywords" => array(),
            "homepage"=> "",
            "license"=> "",
            "type"=> "",
            "authors" => array(),
            "require" => array(),
            "archive" => array(),
            "replace" => array(),
            "autoload" => array(),
            "extras" => array(),
            "minimum-stability"=> ""
        ),$this->json);

        $json['autoload'] = array_merge(array(
            'files'=>array(),
            'classmap'=>array(),
            'psr-0'=>array(),
            'psr-4'=>array(),
        ),$json['autoload']);


        $object->id                 = $json['name'];
        $object->name               = $json['name'];
        $object->version            = $json['version'];
        $object->label              = $json['name'];
        $object->description        = $json['description'];
        $object->keywords           = $json['keywords'];
        $object->authors            = $json['authors'];
        //$object->notes              = $json[''];
        $object->homepageURL        = $json['homepage'];
        //$object->updateURL          = $json[''];
        $object->license            = $json['license'];
        //$object->licenseURL         = $json[''];
        //$object->copyright          = $json[''];

        /**
        * @var array of array('type'=>'module/plugin','version'=>'','id'=>'','name'=>'')
        */

        foreach($json['require'] as $name=>$version) {
            $object->dependencies[] = array(
                'type' => ($name == 'php'?$name:'module'),
                'version'=> $version,
                'id' => $name,
                'name' => $name
            );
        }

        $object->replace = $json['replace'];
        $object->archive = $json['archive'];
        $object->type = $json['type'];

        // module
        if (isset($json['autoload']['psr-4'])) {
            foreach($json['autoload']['psr-4'] as $ns => $dir) {
                if(!is_array($dir)) {
                    $dir = array($dir);
                }
                if ($ns == '') {
                    $object->autoloadPsr4Namespaces[0] = $dir;
                }
                else {
                    $object->autoloadPsr4Namespaces[trim($ns,'\\')] = $dir;
                }
            }
        }

        if (isset($json['autoload']['psr-0'])) {
            foreach($json['autoload']['psr-0'] as $ns => $dir) {
                if(!is_array($dir)) {
                    $dir = array($dir);
                }
                if ($ns == '') {
                    $object->autoloadPsr0Namespaces[0] = $dir;
                }
                else {
                    $object->autoloadPsr0Namespaces[trim($ns,'\\')] = $dir;
                }
            }
        }

        if (isset ($json['autoload']['classmap'])) {
            $basepath = $this->path;
            foreach($json['autoload']['classmap'] as $path) {
                $classes = \Jelix\External\ClassMapGenerator::createMap($basepath.$path);
                // remove directory base path
                $classes = array_map(function($c) use ($basepath) {
                    if (strpos($c, $basepath) === 0) {
                        return substr($c, strlen($basepath));
                    }
                    return $c;
                }, $classes);
                $object->autoloadClasses = array_merge($object->autoloadClasses, $classes);
            }
        }

        if (isset($json['autoload']['files'])) {
            $object->autoloaders            = $json['autoload']['files'];
        }
        if (isset($json['autoload']['include-path'])) {
            $object->autoloadIncludePath    = $json['autoload']['include-path'];
        }

        // app
        if (isset($json['extra']['jelix'])) {
            $j = $json['extra']['jelix'];
            if(isset($j['configPath'])) {
                $object->configPath = $j['configPath'];
            }
            if(isset($j['logPath'])) {
                $object->logPath    = $j['logPath'];
            }
            if(isset($j['varPath'])) {
                $object->varPath    = $j['varPath'];
            }
            if(isset($j['wwwPath'])) {
                $object->wwwPath    = $j['wwwPath'];
            }
            if(isset($j['tempPath'])) {
                $object->tempPath   = $j['tempPath'];
            }
            if(isset($j['entrypoints']) && is_array($j['entrypoints'])) {
                foreach($j['entrypoints'] as $ep) {
                    $object->entrypoints[$ep['file']] = array(
                                                        'config'=>$ep['config'],
                                                        'type'=>(isset($ep['type'])?$ep['type']:'classic'));
                }
            }

            if ( isset($j['moduleWebAlias'])) {
                $object->webAlias = $j['moduleWebAlias'];
            }
        }

        if (isset($object->webAlias) && $object->webAlias == '') {
            $object->webAlias = preg_replace("/[^a-z0-9_]/", "-", $object->name);
        }
        return $object;
    }
}

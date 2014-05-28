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
     * @var the path of the json file to read
     */
    protected $path;

    /**
     * @param string $path the path of the xml file to read
     */
    public function __construct($path, $locale) {
        $this->path = $path;
        $this->locale = substr($locale, 0, 2);
    }

    /**
     *
     */
    public function parse(InfosAbstract $object){

        $json = @json_decode(file_get_contents($this->path), true);
        if (!is_array($json)) {
            throw new \Exception($this->path ." is not a JSON file");
        }

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
        ),$json);
        
        $json['autoload'] = array_merge(array(
            'files'=>array(),
            'classmap'=>array(),
            'psr-0'=>array(),
            'psr-4'=>array(),
        ),$json['autoload']);
        
        
        $object->id                 = $json['name'];
        $object->name               = $json['name'];
        //$object->createDate         = $json[''];
        $object->version            = $json['version'];
        //$object->versionDate        = $json[''];
        //$object->versionStability   = $json[''];
        $object->label              = $json['name'];
        $object->description        = $json['description'];
        $object->keywords           = $json['keywords'];
        // array('name'=>'','nickname'=>'','email'=>'','active'=>'',)
        $object->creators           = $json['authors'];
        // array('name'=>'','nickname'=>'','email'=>'','active'=>'','since'=>'', 'role'=>'')
        //$object->contributors       = $json[''];
        //$object->notes              = $json[''];
        $object->homepageURL        = $json['homepage'];
        //$object->updateURL          = $json[''];
        $object->license            = $json['license'];
        //$object->licenseURL         = $json[''];
        //$object->copyright          = $json[''];

        /**
        * @var array of
        * array('type'=>'jelix', 'maxversion'=>'','minversion'=>'','edition'=>'')
        * or array('type'=>'module/plugin','maxversion'=>'','minversion'=>'','id'=>'','name'=>'')
        */

        foreach($json['require'] as $name=>$version) {
            $object->dependencies[] = array(
                'type' => ($name == 'php'?$name:'module'),
                'minversion'=> $version,
                'maxversion'=>'',
                'id' => $name,
                'name' => $name
            );
        }

        $object->replace = $json['replace'];
        $object->archive = $json['archive'];
        $object->type = $json['type'];

// FIXME
        // module
        $object->autoloaders            = $json['autoload']['files'];
        $object->autoloadClasses        = $json['autoload']['classmap'];
        $object->autoloadNamespaces     = $json['autoload']['psr-0'];
        $object->autoloadPsr4Namespaces = $json['autoload']['psr-4'];
        //$object->autoloadIncludePath    = $json;

        // app
        if (isset($json['extras']['jelix'])) {
            $j = $json['extras']['jelix'];
            $object->configPath = $j['configPath'];
            $object->logPath    = $j['logPath'];
            $object->varPath    = $j['varPath'];
            $object->wwwPath    = $j['wwwPath'];
            $object->tempPath   = $j['tempPath'];
        }

        return $object;
    }
}

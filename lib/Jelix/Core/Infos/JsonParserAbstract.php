<?php
/**
* @author     Laurent Jouanneau
* @copyright  2014-2015 Laurent Jouanneau
* @link       http://jelix.org
* @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/
namespace Jelix\Core\Infos;

/**
 *
 */
abstract class JsonParserAbstract {

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
            throw new \Exception($path ." does not exist");
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
            "version"=> "",
            "date" => "",
            "stability"=>"",
            "label" => "",
            "description"=> "",
            "homepage"=> "",
            "copyright"=>"",
            "authors" => array(),
        ),$this->json);

        $object->name               = $json['name'];
        $object->version            = $json['version'];
        $object->label              = $json['label'] ?: $json['name'];
        $object->description        = $json['description'];
        $object->authors            = $json['authors'];
        $object->homepageURL        = $json['homepage'];
        if (isset($json['license'])) {
            $object->license        = $json['license'];
        }
        else if (isset($json['licence'])) {
            $object->license        = $json['licence'];
        }
        if (isset($json['licenseURL'])) {
            $object->licenseURL     = $json['licenseURL'];
        }
        else if (isset($json['licenceURL'])) {
            $object->licenseURL     = $json['licenceURL'];
        }

        $object->versionStability   = $json['stability'];
        $object->versionDate        = $json['date'];
        $object->copyright          = $json['copyright'];

        return $object;
    }
}

<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2014-2018 Laurent Jouanneau
 *
 * @see       http://jelix.org
 * @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Core\Infos;

abstract class JsonParserAbstract
{
    /**
     * @var string the path of the json file to read
     */
    protected $path;

    /**
     * @param string $path the path of the json file to read, with trailing slash
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @return InfosAbstract
     */
    abstract protected function createInfos();

    public function parse()
    {
        $json = @json_decode(file_get_contents($this->path), true);
        if (!is_array($json)) {
            throw new \Exception($this->path.' is not a JSON file');
        }
        $infos = $this->createInfos();
        $this->_parse($json, $infos);

        return $infos;
    }

    public function parseFromString(array $json)
    {
        $infos = $this->createInfos();
        $this->_parse($json, $infos);

        return $infos;
    }

    protected function _parse(array $json, InfosAbstract $infos)
    {
        $json = array_merge(array(
            'id' => '',
            'name' => '',
            'version' => '',
            'date' => '',
            'stability' => '',
            'label' => '',
            'description' => '',
            'homepage' => '',
            'copyright' => '',
            'createDate' => '',
            'authors' => array(),
        ), $json);

        $infos->id = $json['id'];
        $infos->name = $json['name'];
        $infos->version = $json['version'];

        if (is_array($json['label'])) {
            $infos->label = $json['label'];
        } elseif ($json['label'] != '') {
            $infos->label = array('en' => $json['label']);
        }

        if (is_array($json['description'])) {
            $infos->description = $json['description'];
        } elseif ($json['description'] != '') {
            $infos->description = array('en' => $json['description']);
        }

        if (is_array($json['authors'])) {
            if (isset($json['authors']['name'])) {
                $infos->author = array($json['authors']);
            } else {
                $infos->author = $json['authors'];
            }
        } else {
            $infos->author = array(array('name' => $json['authors']));
        }

        $infos->homepageURL = $json['homepage'];
        $infos->createDate = $json['createDate'];
        if (isset($json['license'])) {
            $infos->license = $json['license'];
        } elseif (isset($json['licence'])) {
            $infos->license = $json['licence'];
        }
        if (isset($json['licenseURL'])) {
            $infos->licenseURL = $json['licenseURL'];
        } elseif (isset($json['licenceURL'])) {
            $infos->licenseURL = $json['licenceURL'];
        }

        $infos->versionStability = $json['stability'];
        $infos->versionDate = $json['date'];
        $infos->copyright = $json['copyright'];

        return $infos;
    }
}

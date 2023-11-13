<?php
/**
 * @author Laurent Jouanneau
 * @copyright 2018-2023 Laurent Jouanneau
 *
 * @see      http://jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Core\Infos;

abstract class JsonWriterAbstract
{
    /**
     * @var string the path of the xml file to read
     */
    protected $path;

    /**
     * @param string $path the path of the xml file to read
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    public function write(InfosAbstract $infos, $intoFile = true)
    {
        if (!$infos->isXmlFile()) {
            return false;
        }
        $json = array();
        $this->writeInfo($json, $infos);
        $this->writeData($json, $infos);
        if ($intoFile) {
            file_put_contents($infos->getFilePath(), json_encode($json, JSON_PRETTY_PRINT));

            return true;
        }

        return json_encode($json, JSON_PRETTY_PRINT);
    }

    /**
     * @param array         $json
     * @param InfosAbstract $infos
     */
    abstract protected function writeData(&$json, $infos);

    /**
     * @param array         $json
     * @param InfosAbstract $infos
     */
    protected function writeInfo(&$json, $infos)
    {
        if ($infos->id) {
            $json['id'] = $infos->id;
        }
        if ($infos->name) {
            $json['name'] = $infos->name;
        }
        if ($infos->createDate) {
            $json['createDate'] = $infos->createDate;
        }
        if ($infos->version) {
            $json['version'] = $infos->version;
        }
        if ($infos->versionDate) {
            $json['date'] = $infos->versionDate;
        }
        if ($infos->versionStability) {
            $json['stability'] = $infos->versionStability;
        }
        if ($infos->copyright) {
            $json['copyright'] = $infos->copyright;
        }
        if ($infos->label) {
            $json['label'] = $infos->label;
        }
        if ($infos->description) {
            $json['description'] = $infos->description;
        }
        if ($infos->author) {
            $json['authors'] = $infos->author;
        }
        if ($infos->license) {
            $json['license'] = $infos->license;
        }
        if ($infos->licenseURL) {
            $json['licenseURL'] = $infos->licenseURL;
        }
        if ($infos->homepageURL) {
            $json['homepage'] = $infos->homepageURL;
        }
        if ($infos->updateURL) {
            $json['updateURL'] = $infos->updateURL;
        }
    }
}

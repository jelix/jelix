<?php
/**
 * @author Laurent Jouanneau
 * @copyright 2018 Laurent Jouanneau
 *
 * @see      http://jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Core\Infos;

abstract class XmlWriterAbstract
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
        $doc = $this->getEmptyDocument();
        $doc->substituteEntities = false;
        $doc->formatOutput = true;
        $doc->encoding = 'utf-8';
        $this->writeInfo($doc, $infos);
        $this->writeData($doc, $infos);
        if ($intoFile) {
            $doc->save($infos->getFilePath());

            return true;
        }

        return $doc->saveXML();
    }

    /**
     * @return \DOMDocument
     */
    abstract protected function getEmptyDocument();

    /**
     * @param \DOMDocument  $doc
     * @param InfosAbstract $infos
     */
    abstract protected function writeData($doc, $infos);

    /**
     * @param \DOMDocument  $doc
     * @param InfosAbstract $infos
     */
    protected function writeInfo($doc, $infos)
    {
        $info = $doc->createElement('info');
        if ($infos->id) {
            $info->setAttribute('id', $infos->id);
        }
        if ($infos->name) {
            $info->setAttribute('name', $infos->name);
        }
        if ($infos->createDate) {
            $info->setAttribute('createdate', $infos->createDate);
        }
        $doc->documentElement->appendChild($info);

        if ($infos->version) {
            $version = $doc->createElement('version');
            $version->textContent = $infos->version;
            if ($infos->versionDate) {
                $version->setAttribute('date', $infos->versionDate);
            }
            if ($infos->versionStability) {
                $version->setAttribute('stability', $infos->versionStability);
            }
            $info->appendChild($version);
        }

        foreach ($infos->label as $lang => $label) {
            $lab = $doc->createElement('label');
            $lab->textContent = $label;
            $lab->setAttribute('lang', $lang);
            $info->appendChild($lab);
        }

        foreach ($infos->description as $lang => $description) {
            $desc = $doc->createElement('description');
            $desc->textContent = $description;
            $desc->setAttribute('lang', $lang);
            $info->appendChild($desc);
        }
        if ($infos->license) {
            $licence = $doc->createElement('licence');
            $licence->textContent = $infos->license;
            if ($infos->licenseURL) {
                $licence->setAttribute('URL', $infos->licenseURL);
            }
            $info->appendChild($licence);
        }
        if ($infos->copyright) {
            $elem = $doc->createElement('copyright');
            $elem->textContent = $infos->copyright;
            $info->appendChild($elem);
        }
        foreach ($infos->author as $author) {
            $lab = $doc->createElement('author');
            $lab->setAttribute('name', $author->name);
            if ($author->email) {
                $lab->setAttribute('email', $author->email);
            }
            if ($author->role) {
                $lab->setAttribute('role', $author->role);
            }
            $info->appendChild($lab);
        }
        if ($infos->homepageURL) {
            $elem = $doc->createElement('homepageURL');
            $elem->textContent = $infos->homepageURL;
            $info->appendChild($elem);
        }
        if ($infos->updateURL) {
            $elem = $doc->createElement('updateURL');
            $elem->textContent = $infos->updateURL;
            $info->appendChild($elem);
        }
    }
}

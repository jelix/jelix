<?php
/**
 * @author Laurent Jouanneau
 * @copyright 2018 Laurent Jouanneau
 * @link      http://jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Core\Infos;

class ProjectXmlWriter extends XmlWriterAbstract
{

    protected function getEmptyDocument() {
        $doc = new \DOMDocument('1.1', 'utf-8');
        $doc->loadXML(
            "<project xmlns=\"http://jelix.org/ns/project/1.0\"></project>");
        return $doc;
    }

    /**
     * @param \DOMDocument $doc
     * @param AppInfos $infos
     */
    protected function writeData($doc, $infos) {
        $entrypoints = $doc->createElement('entrypoints');

        foreach($infos->entrypoints as $ep) {
            $elem = $doc->createElement('entry');
            $elem->setAttribute('file', $ep->getFile());
            $elem->setAttribute('config', $ep->getConfigFile());
            if ($ep->getType() != '' && $ep->getType() != 'classic') {
                $elem->setAttribute('type', $ep->getType());
            }
            $entrypoints->appendChild($elem);
        }
        $doc->documentElement->appendChild($entrypoints);
    }

}

<?php
/**
 * @author Laurent Jouanneau
 * @copyright 2018 Laurent Jouanneau
 *
 * @see      http://jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Core\Infos;

class ProjectXmlWriter extends XmlWriterAbstract
{
    protected function getEmptyDocument()
    {
        $doc = new \DOMDocument('1.1', 'utf-8');
        $doc->loadXML(
            '<project xmlns="http://jelix.org/ns/project/1.0"></project>'
        );

        return $doc;
    }

    /**
     * @param \DOMDocument $doc
     * @param AppInfos     $infos
     */
    protected function writeData($doc, $infos)
    {
    }
}

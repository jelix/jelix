<?php
/**
 * @author    Vincent Viaud
 * @contributor Laurent Jouanneau
 * @copyright 2010 Vincent Viaud, 2012 FoxMaSk, 2014 Laurent Jouanneau
 * @link      http://havefnubb.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Core\Infos;

/**
 * Class to parse the project.xml file of an application
 */
class ProjectXmlParser extends XmlParserAbstract {


    protected function parseDirectories (\XMLReader $xml, InfosAbstract $object) {
        $property = $xml->name;

        while ($xml->read()) {

            if ($xml->nodeType == \XMLReader::END_ELEMENT && 'directories' == $xml->name) {
                break;
            }

            if ($xml->nodeType == \XMLReader::ELEMENT) {
                $type = $xml->name;
                $xml->read();
                $object->{$type.'Path'} = $xml->value;
            }
        }
    }

    protected function parseEntrypoints (\XMLReader $xml, InfosAbstract $object) {

        $property = $xml->name;

        while ($xml->read()) {

            if ($xml->nodeType == \XMLReader::END_ELEMENT && 'entrypoints' == $xml->name) {
                break;
            }

            if ($xml->nodeType == \XMLReader::ELEMENT) {
                $id = $config = '';
                $type = 'classic';
                while ($xml->moveToNextAttribute()) {
                    if ($xml->name == 'file') {
                        $id = $xml->value;
                    }
                    else if ($xml->name == 'config') {
                        $config = $xml->value;
                    }
                    else if ($xml->name == 'type') {
                        $type = $xml->value;
                    }
                }
                if ($id) {
                    if (strpos($id, '.php') === false) {
                        $id .= '.php';
                    }
                    $object->entrypoints[$id] = array('config'=>$config,
                                                      'file'=>$id,
                                                      'type'=>$type);
                }
            }
        }
    }
}

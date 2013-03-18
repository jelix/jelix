<?php
/**
 * @package   jelix
 * @subpackage core
 * @author    Vincent Viaud
 * @contributor 2013 foxmask
 * @copyright 2010 Vincent Viaud
 * @link      
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

abstract class jFilexml {

    public function parse($path, $object){
        $xml = new XMLreader();
        $xml->open($path, '', LIBXML_COMPACT );

        while ($xml->read()) {
            if($xml->nodeType == XMLReader::ELEMENT) {
                $method = 'parse' . ucfirst($xml->name);
                if (method_exists ($this, $method)) {
                    $this->$method($xml, $object);
                }
            }
        }

        $xml->close();
        return $object;
    }


    protected function parseInfo (XMLReader $xml, $object) {

        if (XMLReader::ELEMENT == $xml->nodeType && 'info' == $xml->name) {

            $object->id = $xml->getAttribute('id');
            $object->name = $xml->getAttribute('name');
            $object->createDate = $xml->getAttribute('createdate');
            $gJConfig = jApp::config();
            while ($xml->read()) {

                if (XMLReader::END_ELEMENT == $xml->nodeType && 'info' == $xml->name) {
                    break;
                }

                if($xml->nodeType == XMLReader::ELEMENT) {

                    $property = $xml->name;

                    if ('label' == $property || 'description' == $property) {
                        if ($xml->getAttribute('lang') == $gJConfig->locale) {
                            $xml->read();
                            $object->$property = $xml->value;
                        }
                    } elseif ('creator' == $property || 'contributor' == $property) {
                        $person = array();
                        while ($xml->moveToNextAttribute()) {
                            $attrName = $xml->name;
                            $person[$attrName] = $xml->value;
                        }
                        $property .= 's';
                        array_push($object->$property, $person);

                    } else {
                        while ($xml->moveToNextAttribute()) {
                            $attrProperty = $property . ucfirst($xml->name);
                            $object->$attrProperty = $xml->value;
                        }
                        $xml->read();
                        $object->$property = $xml->value;
                    }
                }
            }
        }
        return $object;
    }

    protected function parseDependencies (XMLReader $xml, $object) {
        if (XMLReader::ELEMENT == $xml->nodeType && 'dependencies' == $xml->name) {

            $property = $xml->name;

            while ($xml->read()) {

                if ($xml->nodeType == XMLReader::END_ELEMENT && 'dependencies' == $xml->name) {
                    break;
                }

                if ($xml->nodeType == XMLReader::ELEMENT) {

                    $dependency = array();
                    $dependency['type'] = $xml->name;

                    while ($xml->moveToNextAttribute()) {
                        $attrName = $xml->name;
                        $dependency[$attrName] = $xml->value;
                    }

                    array_push($object->$property, $dependency);
                }
            }
        }

        return $object;
    }
}


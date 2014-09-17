<?php
/**
 * @author    Vincent Viaud
 * @contributor Laurent Jouanneau
 * @copyright 2010 Vincent Viaud, 2014 Laurent Jouanneau
 * @link      http://havefnubb.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Core\Infos;

abstract class XmlParserAbstract {

    /**
     * @var the path of the xml file to read
     */
    protected $path;

    /**
     * @var the locale code for language
     */
    protected $locale;

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
        $xml = new \XMLreader();
        $xml->open($this->path, '', LIBXML_COMPACT );

        while ($xml->read()) {
            if($xml->nodeType == \XMLReader::ELEMENT) {
                $method = 'parse' . ucfirst($xml->name);
                if (method_exists ($this, $method)) {
                    $this->$method($xml, $object);
                }
            }
        }
        $xml->close();
        return $object;
    }

    protected function parseInfo (\XMLReader $xml, InfosAbstract $object) {

        $object->id = (string)$xml->getAttribute('id');
        // the name has always to be the directory name
        //$object->name = (string)$xml->getAttribute('name');
        $object->createDate = (string)$xml->getAttribute('createdate');

        $locale = array('label'=>$this->locale, 'description'=>$this->locale);

        while ($xml->read()) {

            if (\XMLReader::END_ELEMENT == $xml->nodeType && 'info' == $xml->name) {
                break;
            }

            if($xml->nodeType == \XMLReader::ELEMENT) {

                $property = $xml->name;

                if ('label' == $property || 'description' == $property) {
                    if ($xml->getAttribute('lang') == $locale[$property] ||
                        $locale[$property] == '') {

                        $xml->read();
                        $object->$property = $xml->value;
                        if ($locale[$property] == '') {
                            // let's mark we readed the element corresponding to the locale
                            $locale[$property] = '__readed__';
                        }
                    }
                }
                elseif ('author' == $property || 'creator' == $property || 'contributor' == $property) {
                    $person = array();
                    while ($xml->moveToNextAttribute()) {
                        $attrName = $xml->name;
                        $person[$attrName] = $xml->value;
                    }
                    array_push($object->authors, $person);
                }
                else { // <version> <license> <copyright> <homepageURL> <updateURL>
                    while ($xml->moveToNextAttribute()) {
                        $attrProperty = $property . ucfirst($xml->name);
                        $object->$attrProperty = $xml->value;
                    }
                    $xml->read();
                    if ($property == 'version') {
                        $object->$property = $this->fixVersion($xml->value);
                    }
                    else {
                        $object->$property = $xml->value;
                    }
                }
            }
        }
        return $object;
    }

    protected function parseDependencies (\XMLReader $xml, InfosAbstract $object) {

        $property = $xml->name;

        while ($xml->read()) {

            if ($xml->nodeType == \XMLReader::END_ELEMENT && 'dependencies' == $xml->name) {
                break;
            }

            if ($xml->nodeType == \XMLReader::ELEMENT) {

                $dependency = array('type'=>$xml->name, 'name'=>'', 'version'=>'');
                $dependency['type'] = $xml->name;
                if ($xml->name == 'jelix') {
                    $dependency['type'] = 'module';
                    $dependency['name'] = 'jelix';
                }

                while ($xml->moveToNextAttribute()) {
                    $attrName = $xml->name;
                    if ($attrName == 'minversion' && $xml->value != '') { // old attribute
                        $v = '>='.$this->fixVersion($xml->value);
                        if ($dependency['version'] != '') {
                            $v = ','.$v;
                        }
                        $dependency['version'] .= $v;
                    }
                    else if ($attrName == 'maxversion' && $xml->value != '') { // old attribute
                        $v = '<='.$this->fixVersion($xml->value);
                        if ($dependency['version'] != '') {
                            $v = ','.$v;
                        }
                        $dependency['version'] .= $v;
                    }
                    else if ($attrName == 'version' && $xml->value != '') {
                        $dependency['version'] = $this->fixVersion($xml->value);
                    }
                    else {
                        $dependency[$attrName] = $xml->value;
                    }
                }
                array_push($object->$property, $dependency);
            }
        }
        return $object;
    }

    /**
     * Fix version for non built lib
     */
    protected function fixVersion($version) {
        $v = str_replace('__LIB_VERSION_MAX__', \Jelix\Core\Framework::versionMax(), $version);
        $v = str_replace('__LIB_VERSION__', \Jelix\Core\Framework::version(), $v);
        return $v;
    }
}


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
        // we don't read the name attribute for the module name as in previous jelix version, it has always to be the directory name
        if ($object->type == 'application') {
            $object->name = (string)$xml->getAttribute('name');
        }

        $object->createDate = (string)$xml->getAttribute('createdate');

        $locale = array('label'=>$this->locale, 'description'=>$this->locale);

        while ($xml->read()) {

            if (\XMLReader::END_ELEMENT == $xml->nodeType && 'info' == $xml->name) {
                break;
            }

            if($xml->nodeType == \XMLReader::ELEMENT) {

                $property = $xml->name;
                $attributes = array();
                $textContent = '';
                if ($xml->hasAttributes) {
                    while ($xml->moveToNextAttribute()) {
                        $attributes[$xml->name] = $xml->value;
                    }
                    $xml->moveToElement();
                }
                if (!$xml->isEmptyElement) {
                    $xml->read();
                    $textContent = $xml->value;
                }

                if ('label' == $property || 'description' == $property) {
                    if (isset($attributes['lang']) && $attributes['lang'] == $locale[$property] ||
                        $locale[$property] == '') {
                        $object->$property = $textContent;
                        if ($locale[$property] == '') {
                            // let's mark we readed the element corresponding to the locale
                            $locale[$property] = '__readed__';
                        }
                    }
                }
                elseif ('author' == $property || 'creator' == $property || 'contributor' == $property) {
                    array_push($object->authors, $attributes);
                }
                elseif ('licence' == $property) { // we support licence and license, but store always as license
                    foreach($attributes as $attr => $val) {
                        $attrProperty = 'license' . ucfirst($attr);
                        $object->$attrProperty = $val;
                    }
                    $object->license = $textContent;
                }
                else { // <version> <license> <copyright> <homepageURL> <updateURL>
                    // read attributes 'date', 'stability' etc ... and store them into versionDate, versionStability
                    foreach($attributes as $attr => $val) {
                        $attrProperty = $property . ucfirst($attr);
                        $object->$attrProperty = $val;
                    }

                    if ($property == 'version') {
                        $object->$property = $this->fixVersion($textContent);
                    }
                    else {
                        $object->$property = $textContent;
                    }
                }
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
        $v = str_replace('__VERSION__', \Jelix\Core\App::version(), $v);
        return $v;
    }
}


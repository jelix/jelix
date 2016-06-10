<?php
/**
* @author      Laurent Jouanneau
* @copyright   2016 Laurent Jouanneau
*
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
namespace Jelix\Routing\UrlMapping;

/**
 * allow to modify the urls.xml file
 */
class XmlMapModifier
{
    /**
     * @var XmlEntryPoint
     */
    protected $currentEntryPoint = null;

    function __construct($file, $createIfNotExists=false) {
        $this->file = $file;
        $this->document = new \DOMDocument();
        if (!file_exists($file)) {
            if (!$createIfNotExists) {
                throw new \Exception("Url mapping file does not exists -- ".$file);
            }
            $this->document->loadXML('<'.'?xml version="1.0" encoding="utf-8"?>'."\n".
                    '<urls xmlns="http://jelix.org/ns/urls/1.1">'."\n</urls>");
        }
        else {
            $this->document->load($file);
        }
        $this->currentEntryPoint = $this->getEntryPoint('index');
    }

    function save() {
        $this->document->save($this->file);
    }

    /**
     * @param array $options options are
     *          default=true/(false) 
     *          https=true/(false)
     *          noentrypoint=true/(false)
     *          optionalTrailingSlash=true/(false)
     * @return XmlEntryPoint
     */
    function addEntryPoint($name, $type="classic", $options=array()) {
        $ep = $this->getEntryPoint($name, $type);
        if (!$ep) {
            $xmlep = $this->document->createElement('entrypoint');
            $xmlep->setAttribute('name', $name);
            $xmlep->setAttribute('type', $type);
            $sep = $this->document->createTextNode("    ");
            $sep2 = $this->document->createTextNode("\n");
            $this->document->documentElement->appendChild($sep);
            $this->document->documentElement->appendChild($xmlep);
            $this->document->documentElement->appendChild($sep2);
            $ep = new XmlEntryPoint($xmlep);
        }
        $ep->setOptions($options);

        if ($ep->isDefault()) {
            // remove default atttribute from other entrypoint of the same type
            $entrypoints = $this->getEntryPointsOfType($type);
            foreach($entrypoints as $ep2) {
                if ($name == $ep2->getAttribute('name')) {
                    continue;
                }
                $x = new XmlEntryPoint($ep2);
                if ($x->isDefault()) {
                    $ep2->removeAttribute('default');
                }
            }
        }
        return $ep;
    }

    public function setCurrentEntryPoint($name, $type="classic") {
        $this->currentEntryPoint = $this->getEntryPoint($name);
    }

    /**
     * @return XmlEntryPoint
     */
    public function getEntryPoint($name) {
        if (($pos = strpos($name, '.php')) !== false) {
            $name = substr(0, $pos, $name);
        }
        $list = $this->document->getElementsByTagName('entrypoint');
        foreach($this->document->documentElement->childNodes as $item) {
            if ($item->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            
            if (preg_match('/^.*entrypoint$/',$item->localName) &&
                $item->getAttribute('name') == $name) {
                return new XmlEntryPoint($item);
            }
        }
        return null;
    }

    protected function getEntryPointsOfType($type="classic") {
        $results = array();
        $list = $this->document->getElementsByTagName('entrypoint');
        foreach($list as $item) {
            if ($item->getAttribute('type') == '' && $type == 'classic') {
                $results[] = $item;
            }
            if ($item->getAttribute('type') == $type) {
                $results[] = $item;
            }
        }
        // legacy
        $list = $this->document->getElementsByTagName($type.'entrypoint');
        foreach($list as $item) {
            $results[] = $item;
        }
        return $results;
    }
}
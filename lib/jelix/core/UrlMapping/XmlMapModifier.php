<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2016 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Routing\UrlMapping;

/**
 * allow to modify the urls.xml file.
 */
class XmlMapModifier
{
    /**
     * @var string the filename
     */
    protected $file;

    /**
     * @var \DOMDocument the DOM document representing the file
     */
    protected $document;

    /**
     * @var XmlEntryPoint
     */
    protected $currentEntryPoint;

    /**
     * @var bool indicate if the content is modified, to save or not the file.
     */
    protected $modified = false;


    public function __construct($file, $createIfNotExists = false)
    {
        $this->file = $file;
        $this->document = new \DOMDocument();
        if (!file_exists($file)) {
            if (!$createIfNotExists) {
                throw new \Exception('Url mapping file does not exists -- '.$file);
            }
            $this->document->loadXML('<'.'?xml version="1.0" encoding="utf-8"?>'."\n".
                    '<urls xmlns="http://jelix.org/ns/urls/1.0">'."\n</urls>");
        } else {
            $this->document->load($file);
        }
        $this->currentEntryPoint = $this->getEntryPoint('index');
    }

    public function save()
    {
        $this->document->save($this->file);
    }

    /**
     * @param array $options options are
     *                       default=true/(false)
     *                       https=true/(false)
     *                       noentrypoint=true/(false)
     *                       optionalTrailingSlash=true/(false)
     * @param mixed $name
     * @param mixed $type
     *
     * @return XmlEntryPoint
     */
    public function addEntryPoint($name, $type = 'classic', $options = array())
    {
        $ep = $this->getEntryPoint($name, $type);
        if (!$ep) {
            $xmlep = $this->document->createElement('entrypoint');
            $xmlep->setAttribute('name', $name);
            $xmlep->setAttribute('type', $type);
            $sep = $this->document->createTextNode('    ');
            $sep2 = $this->document->createTextNode("\n");
            $this->document->documentElement->appendChild($sep);
            $this->document->documentElement->appendChild($xmlep);
            $this->document->documentElement->appendChild($sep2);
            $ep = new XmlEntryPoint($this, $xmlep);
        }
        $ep->setOptions($options);

        return $ep;
    }

    public function removeEntryPoint($name)
    {
        $ep = $this->getEntryPoint($name);
        if ($ep) {

            if (($pos = strpos($name, '.php')) !== false) {
                $name = substr($name, 0, $pos);
            }
            foreach ($this->document->documentElement->childNodes as $item) {
                if ($item->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }

                if (preg_match('/^.*entrypoint$/', $item->localName)
                    && $item->getAttribute('name') == $name
                ) {
                    $item->remove();
                    break;
                }
            }
        }
    }

    public function setNewDefaultEntryPoint($name, $type)
    {
        $entrypoints = $this->getEntryPointsOfType($type);
        foreach ($entrypoints as $ep2) {
            if ($name == $ep2->getAttribute('name')) {
                $ep2->setAttribute('default', 'true');

                continue;
            }
            $ep2->removeAttribute('default');
        }
    }

    /**
     * @param mixed $type
     *
     * @return XmlEntryPoint
     */
    public function getDefaultEntryPoint($type)
    {
        $entrypoints = $this->getEntryPointsOfType($type);
        foreach ($entrypoints as $ep2) {
            if ($ep2->getAttribute('default') == 'true') {
                return new XmlEntryPoint($this, $ep2);
            }
        }
        if (count($entrypoints) == 1) {
            return new XmlEntryPoint($this, $entrypoints[0]);
        }

        return null;
    }

    public function setCurrentEntryPoint($name, $type = 'classic')
    {
        $this->currentEntryPoint = $this->getEntryPoint($name);
    }

    /**
     * @param mixed $name
     *
     * @return XmlEntryPoint
     */
    public function getEntryPoint($name)
    {
        if (($pos = strpos($name, '.php')) !== false) {
            $name = substr($name, 0, $pos);
        }
        foreach ($this->document->documentElement->childNodes as $item) {
            if ($item->nodeType != XML_ELEMENT_NODE) {
                continue;
            }

            if (preg_match('/^.*entrypoint$/', $item->localName)
                && $item->getAttribute('name') == $name) {
                return new XmlEntryPoint($this, $item);
            }
        }

        return null;
    }

    /**
     * @param string $type
     *
     * @return \DomElement[]
     */
    protected function getEntryPointsOfType($type = 'classic')
    {
        $results = array();
        $list = $this->document->getElementsByTagName('entrypoint');
        foreach ($list as $item) {
            if ($item->getAttribute('type') == '' && $type == 'classic') {
                $results[] = $item;
            }
            if ($item->getAttribute('type') == $type) {
                $results[] = $item;
            }
        }
        // legacy
        $list = $this->document->getElementsByTagName($type.'entrypoint');
        foreach ($list as $item) {
            $results[] = $item;
        }

        return $results;
    }

    public function removeUrlModuleInOtherEntryPoint($module, XmlEntryPoint $except)
    {
        $list = $this->getEntryPointsOfType($except->getType());
        foreach ($list as $ep) {
            if ($ep->getAttribute('name') == $except->getName()) {
                continue;
            }
            $xmlEp = new XmlEntryPoint($this, $ep);
            $xmlEp->removeUrlModule($module);
        }
    }
}

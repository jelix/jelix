<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2016-2022 Laurent Jouanneau
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

    /**
     * @param string $file
     * @param boolean $createIfNotExists
     * @throws \Exception
     */
    public function __construct($file, $createIfNotExists = false)
    {
        $this->file = $file;
        $this->document = new \DOMDocument();
        if (!file_exists($file)) {
            if (!$createIfNotExists) {
                throw new \Exception('Url mapping file does not exists -- '.$file);
            }
            $this->modified = true;
            $this->document->loadXML('<'.'?xml version="1.0" encoding="utf-8"?>'."\n".
                    '<urls xmlns="http://jelix.org/ns/urls/1.0">'."\n</urls>");
        } else {
            $this->document->load($file);
        }

        $this->currentEntryPoint = $this->getEntryPointByNameOrAlias('index');
    }

    public function setAsModified()
    {
        $this->modified = true;
    }

    public function save()
    {
        if ($this->modified) {
            $this->document->save($this->file);
        }
    }

    /**
     * @param array $options options are
     *                       default=true/(false)
     *                       https=true/(false)
     *                       noentrypoint=true/(false)
     *                       optionalTrailingSlash=true/(false)
     * @param string $name
     * @param string $type
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
            $this->modified = true;
        }
        $ep->setOptions($options);

        return $ep;
    }

    /**
     * @param string $name
     * @since 1.7.11
     */
    public function removeEntryPoint($name)
    {
        $ep = $this->getEntryPoint($name);
        if ($ep) {

            $xmlEp = $ep->getDomElement();
            $parent = $xmlEp->parentNode;
            if ($xmlEp->previousSibling && $xmlEp->previousSibling->nodeType == XML_TEXT_NODE) {
                // remove indentation
                $parent->removeChild($xmlEp->previousSibling);
            }
            $parent->removeChild($xmlEp);
            $this->modified = true;
        }
    }

    public function setNewDefaultEntryPoint($name, $type)
    {
        $entrypoints = $this->getXMLEntryPointsOfType($type);
        foreach ($entrypoints as $ep2) {
            if ($name == $ep2->getAttribute('name')) {
                $ep2->setAttribute('default', 'true');

                continue;
            }
            $ep2->removeAttribute('default');
        }
        $this->modified = true;
    }

    /**
     * @param mixed $type
     *
     * @return XmlEntryPoint
     */
    public function getDefaultEntryPoint($type)
    {
        $entrypoints = $this->getXMLEntryPointsOfType($type);
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
     * @param mixed $name
     *
     * @return XmlEntryPoint
     */
    public function getEntryPointByNameOrAlias($name)
    {
        if (($pos = strpos($name, '.php')) !== false) {
            $name = substr($name, 0, $pos);
        }
        foreach ($this->document->documentElement->childNodes as $item) {
            if ($item->nodeType != XML_ELEMENT_NODE) {
                continue;
            }

            if (preg_match('/^.*entrypoint$/', $item->localName)) {

                if ($item->getAttribute('name') == $name) {
                    return new XmlEntryPoint($this, $item);
                }

                $aliasesStr = $item->getAttribute('alias');
                if ($aliasesStr) {
                    $aliasesArr = preg_split('/\s*,\s*/', $aliasesStr);
                    if (in_array($name, $aliasesArr)) {
                        return new XmlEntryPoint($this, $item);
                    }
                }
            }
        }

        return null;
    }

    /**
     * @param string $type
     *
     * @return \DomElement[]
     */
    protected function getXMLEntryPointsOfType($type = 'classic')
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

    /**
     * Return the list of entrypoints having the given type
     * @param string $type
     * @return XmlEntryPoint[]
     */
    public function getEntryPointsOfType($type = 'classic')
    {
        $list = $this->getXMLEntryPointsOfType($type);
        $mm = $this;
        return array_map(function($elem) use($mm) {
            return new XmlEntryPoint($mm, $elem);
        }, $list);
    }

    public function removeUrlModuleInOtherEntryPoint($module, XmlEntryPoint $except)
    {
        $list = $this->getXMLEntryPointsOfType($except->getType());
        foreach ($list as $ep) {
            if ($ep->getAttribute('name') == $except->getName()) {
                continue;
            }
            $xmlEp = new XmlEntryPoint($this, $ep);
            $xmlEp->removeUrlModule($module);
        }
    }

    public function removeAllUrlOfModule($module)
    {
        foreach ($this->document->documentElement->childNodes as $item) {
            if ($item->nodeType != XML_ELEMENT_NODE) {
                continue;
            }

            if (preg_match('/^.*entrypoint$/', $item->localName)) {
                $ep = new XmlEntryPoint($this, $item);
                $ep->removeAllUrlsOfModule($module);
            }
        }
    }

}

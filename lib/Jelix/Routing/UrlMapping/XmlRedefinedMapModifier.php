<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018-2022 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Routing\UrlMapping;

/**
 * allow to modify the urls.xml file.
 */
class XmlRedefinedMapModifier extends XmlMapModifier
{
    /**
     * @var XmlMapModifier
     */
    protected $originalMap;

    public function __construct(XmlMapModifier $originalMapFile, $redefinedMapFile)
    {
        $this->originalMap = $originalMapFile;
        parent::__construct($redefinedMapFile, true);
    }

    public function setNewDefaultEntryPoint($name, $type)
    {
        // nothing. we don't support defaults in redefined map file
    }

    public function getEntryPoint($name)
    {
        $ep = parent::getEntryPoint($name);
        if ($ep) {
            return $ep;
        }
        $ep = $this->originalMap->getEntryPoint($name);
        if ($ep) {
            $domEp = $ep->getDomElement();
            $domEp = $domEp->cloneNode(false);
            $domEp = $this->document->importNode($domEp);
            $sep = $this->document->createTextNode('    ');
            $sep2 = $this->document->createTextNode("\n");
            $this->document->documentElement->appendChild($sep);
            $this->document->documentElement->appendChild($domEp);
            $this->document->documentElement->appendChild($sep2);

            return new XmlEntryPoint($this, $domEp);
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    protected function getXMLEntryPointsOfType($type = 'classic')
    {
        $list = parent::getXMLEntryPointsOfType($type);
        $hashedList = array();
        foreach ($list as $domEp) {
            $hashedList[$domEp->getAttribute('name')] = $domEp;
        }
        $listOrig = $this->originalMap->getXMLEntryPointsOfType($type);
        foreach ($listOrig as $domEp) {
            if (!isset($hashedList[$domEp->getAttribute('name')])) {
                $domEp = $domEp->cloneNode(false);
                $domEp = $this->document->importNode($domEp, false);
                $sep = $this->document->createTextNode('    ');
                $sep2 = $this->document->createTextNode("\n");
                $this->document->documentElement->appendChild($sep);
                $this->document->documentElement->appendChild($domEp);
                $this->document->documentElement->appendChild($sep2);
                $hashedList[$domEp->getAttribute('name')] = $domEp;
            }
        }

        return array_values($hashedList);
    }
}

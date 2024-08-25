<?php

/**
 * @author      Laurent Jouanneau
 * @copyright   2022 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Routing\UrlMapping;

use Jelix\Routing\UrlMapping\MapEntry\AbstractEntry;

class EntryPointUrlModifier
{
    /**
     * @var string
     */
    protected $module;

    /**
     * @var XmlMapModifier
     */
    protected $mapModifier;

    /**
     * @param string $module
     * @param XmlMapModifier $mapModifier
     */
    function __construct(XmlMapModifier $mapModifier, $module)
    {
        $this->module = $module;
        $this->mapModifier = $mapModifier;
    }

    /**
     * @param string $entryPointName
     * @param AbstractEntry[] $urlMap
     * @return EntryPointUrlModifier
     */
    public function havingName($entryPointName, $urlMap)
    {
        return $this->_havingName($entryPointName, $urlMap, true);
    }

    /**
     * @param string $entryPointName
     * @param AbstractEntry[] $urlMap
     * @param bool $errorIfMissing
     * @return $this
     * @throws \Exception
     */
    protected function _havingName($entryPointName, & $urlMap, $errorIfMissing = true)
    {
        $ep = $this->mapModifier->getEntryPointByNameOrAlias($entryPointName);
        if ($ep) {
            foreach($urlMap as $entry) {
                $entry->addToEntryPoint($ep, $this->module);
            }
        }
        else if ($errorIfMissing) {
            throw new \Exception('No entry point with the name '.$entryPointName);
        }
        return $this;
    }

    /**
     * @param string $entryPointType
     * @param AbstractEntry[] $urlMap
     * @return EntryPointUrlModifier
     */
    public function havingType($entryPointType, $urlMap)
    {
        return $this->_havingType($entryPointType, $urlMap, true);
    }

    /**
     * @param string $entryPointType
     * @param AbstractEntry[] $urlMap
     * @param bool $errorIfMissing
     * @return $this
     * @throws \Exception
     */
    protected function _havingType($entryPointType, & $urlMap, $errorIfMissing = true)
    {
        $epList = $this->mapModifier->getEntryPointsOfType($entryPointType);
        if (count($epList)) {
            foreach($urlMap as $entry) {
                $entry->addToEntryPoint($epList[0], $this->module);
            }
        }
        else if ($errorIfMissing) {
            throw new \Exception('No entry point with the type '.$entryPointType);
        }
        return $this;
    }

    /**
     * @param string $entryPointName
     * @param AbstractEntry[] $urlMap
     * @return EntryPointUrlModifier
     */
    public function havingNameIfExists($entryPointName, $urlMap)
    {
        return $this->_havingName($entryPointName, $urlMap, false);
    }

    /**
     * @param string $entryPointType
     * @param AbstractEntry[] $urlMap
     * @return EntryPointUrlModifier
     */
    public function havingTypeIfExists($entryPointType, $urlMap)
    {
        return $this->_havingType($entryPointType, $urlMap, false);
    }

}
<?php
/**
 * @author      Laurent Jouanneau
 *
 * @copyright   2022 Laurent Jouanneau
 *
 * @see         https://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Routing\UrlMapping\MapEntry;

use Jelix\Routing\UrlMapping\XmlEntryPoint;

abstract class AbstractEntry
{

    protected $pathInfo;

    protected $https = false;

    protected $defaultUrl = false;

    public function __construct($pathInfo)
    {
        $this->pathInfo = $pathInfo;
    }

    public function getPathInfo()
    {
        return $this->pathInfo;
    }


    public function forceHttps()
    {
        $this->https = true;
    }

    public function isForHttpsOnly()
    {
        return $this->https;
    }

    public function setAsDefault()
    {
        $this->defaultUrl = true;
    }

    public function isDefault()
    {
        return $this->defaultUrl;
    }

    /**
     * Declare the url described by the map entry, into the given entrypoint
     * for the given module
     *
     * @param XmlEntryPoint $ep
     * @param string $module
     */
    abstract public function addToEntryPoint(XmlEntryPoint $ep, $module);

    /**
     * remove the url described by the map entry, from the given entrypoint
     *
     * @param XmlEntryPoint $ep
     * @param string $module
     */
    abstract public function removeFromEntryPoint(XmlEntryPoint $ep, $module);

}

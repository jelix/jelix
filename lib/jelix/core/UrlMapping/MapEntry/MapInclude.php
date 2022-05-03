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

class MapInclude extends AbstractEntry
{
    /**
     * @var string
     */
    protected $includedFile;

    public function __construct($includedFile, $pathInfo = '')
    {
        parent::__construct($pathInfo);
        $this->includedFile = $includedFile;
    }

    public function setAsDefault()
    {
        throw new \Exception('map include cannot set as default');
    }

    /**
     * @return string
     */
    public function getIncludedFile(): string
    {
        return $this->includedFile;
    }

    /**
     * {@inheritDoc}
     */
    public function addToEntryPoint(XmlEntryPoint $ep, $module)
    {
        $pathInfo = ($this->pathInfo ?: '/'.$module);
        $ep->addUrlInclude($pathInfo, $module, $this->includedFile,
            array(
                'https' => $this->isForHttpsOnly(),
                //noentrypoint
            ), false);
    }

    /**
     * {@inheritDoc}
     */
    public function removeFromEntryPoint(XmlEntryPoint $ep, $module)
    {
        $ep->removeUrlInclude($module, $this->includedFile);
    }

}

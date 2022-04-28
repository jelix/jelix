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

class ModuleUrl extends AbstractEntry
{
    public function __construct($pathInfo)
    {
        parent::__construct($pathInfo);
    }

    /**
     * {@inheritDoc}
     */
    public function addToEntryPoint(XmlEntryPoint $ep, $module)
    {
        $ep->addUrlModule($this->pathInfo, $module,
            array(
                 'default' => $this->isDefault(),
                 'https' => $this->isForHttpsOnly(),
                //noentrypoint
            ));
    }

    /**
     * {@inheritDoc}
     */
    public function removeFromEntryPoint(XmlEntryPoint $ep, $module)
    {
        $ep->removeUrlModule($module);
    }
}

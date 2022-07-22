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

    /**
     * {@inheritDoc}
     */
    public function addToEntryPoint(XmlEntryPoint $ep, $module)
    {
        $pathInfo = ($this->pathInfo ?: '/'.$module);
        $ep->addUrlModule($pathInfo, $module,
            array(
                 'default' => $this->isDefault(),
                 'https' => $this->isForHttpsOnly(),
                //noentrypoint
            ), false);
    }

    /**
     * {@inheritDoc}
     */
    public function removeFromEntryPoint(XmlEntryPoint $ep, $module)
    {
        $ep->removeUrlModule($module);
    }
}

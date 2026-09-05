<?php
/**
 * @author      Laurent Jouanneau
 *
 * @copyright   2026 Laurent Jouanneau
 *
 * @see         https://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Routing\UrlMapping\MapEntry;

use Jelix\Routing\UrlMapping\XmlEntryPoint;

class ControllerUrl extends AbstractEntry
{
    protected $controller;

    public function __construct($controller, $pathInfo = '')
    {
        parent::__construct($pathInfo);
        $this->controller = $controller;
    }

    /**
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * {@inheritDoc}
     */
    public function addToEntryPoint(XmlEntryPoint $ep, $module)
    {
        $pathInfo = ($this->pathInfo ?: '/'.$module);
        $ep->addUrlController($pathInfo, $module, $this->controller,
            array(
                'default' => $this->isDefault(),
                'https' => $this->isForHttpsOnly(),
                // noentrypoint
            ));
    }

    /**
     * {@inheritDoc}
     */
    public function removeFromEntryPoint(XmlEntryPoint $ep, $module)
    {
        $ep->removeUrlController($module, $this->controller);
    }
}

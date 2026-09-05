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

class UrlHandler extends AbstractEntry
{
    protected $action;

    protected $actionOverride;

    protected $handler;

    public function __construct($pathInfo, $handler, $action = '', $actionOverride = '')
    {
        parent::__construct($pathInfo);
        $this->handler = $handler;
        $this->action = $action;
        $this->actionOverride = $actionOverride;
    }

    /**
     * @return string
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getActionOverride()
    {
        return $this->actionOverride;
    }

    /**
     * {@inheritDoc}
     */
    public function addToEntryPoint(XmlEntryPoint $ep, $module)
    {
        $pathInfo = ($this->pathInfo ?: '/'.$module);
        $ep->addUrlHandler($pathInfo, $module, $this->handler, $this->action,
            array(
                'default' => $this->isDefault(),
                'https' => $this->isForHttpsOnly(),
                'actionoverride' => $this->actionOverride,
                // noentrypoint
            ));
    }

    /**
     * {@inheritDoc}
     */
    public function removeFromEntryPoint(XmlEntryPoint $ep, $module)
    {
        $ep->removeUrlHandler($module, $this->handler, $this->action);
    }
}

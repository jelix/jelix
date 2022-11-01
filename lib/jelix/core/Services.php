<?php

/**
 * @package    jelix
 * @subpackage core
 *
 * @author      Laurent Jouanneau
 *
 * @copyright   2022 Laurent Jouanneau
 *
 * @see      http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Core;
use Jelix\Events\EventDispatcher;
use Jelix\Events\ListenerProvider;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * It allows to access to some useful components.
 *
 * Warning: this is a work in progress. Api could change in the future.
 *
 * @experimental
 * @since 1.8
 */
class Services
{
    /**
     * @var EventDispatcherInterface
     */
    protected $_eventDispatcher;

    /**
     * Get the event dispatcher component
     * @return EventDispatcherInterface
     */
    function eventDispatcher()
    {
        if (!$this->_eventDispatcher) {
            $listenerProvider = new ListenerProvider(\jApp::config());
            $this->_eventDispatcher = new EventDispatcher($listenerProvider);
        }
        return $this->_eventDispatcher;
    }
}
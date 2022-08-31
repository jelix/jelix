<?php

use Testapp\Tests\EventForTest;

/**
 * @package     testapp
 * @subpackage  jelix_tests module
 * @author      Laurent Jouanneau
 * @copyright   2019-2022 Laurent Jouanneau
 * @link        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

class testEventsTestappListener extends jEventListener
{

    /**
     * @param jEvent $event
     */
    function onTestEventResponse($event)
    {
        if (isset(eventResponseToReturn::$responses['testapp'])) {
            $event->add(eventResponseToReturn::$responses['testapp']);
        }
    }

    function onTestEventObject(EventForTest $event)
    {
        $event->setDummyValue('onTestEventObject called');
    }
}

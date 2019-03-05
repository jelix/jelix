<?php
/**
 * @package     testapp
 * @subpackage  jelix_tests module
 * @author      Laurent Jouanneau
 * @contributor
 * @copyright   2019 Laurent Jouanneau
 * @link        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

class testEventsTestappListener extends jEventListener{

    /**
     * @param jEvent $event
     */
    function onTestEventResponse($event) {
        if (isset(eventResponseToReturn::$responses['testapp'])) {
            $event->add(eventResponseToReturn::$responses['testapp']);
        }
    }

}

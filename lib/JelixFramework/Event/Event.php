<?php

/**
 * @author      Gérald Croes, Patrice Ferlet, Laurent Jouanneau
 *
 * @copyright 2001-2005 CopixTeam, 2005-2023 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Event;

use Jelix\Core\App;

/**
 * Class which represents an event in the event system.
 */
class Event implements \Jelix\Event\EventInterface
{
    /**
     * The name of the event.
     *
     * @var string name
     */
    protected $_name;

    /**
     * the event parameters.
     */
    protected $_params;

    /**
     * @var mixed[][]
     */
    protected $_responses = array();

    /**
     * New event.
     *
     * @param string $name   the event name
     * @param array  $params an associative array which contains parameters for the listeners
     * @author Copix Team
     */
    public function __construct($name, $params = array())
    {
        $this->_name = $name;
        $this->_params = &$params;
    }

    /**
     * get a user param.
     *
     * @param string $name the parameter name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getParam($name);
    }

    /**
     * set a user param.
     *
     * @param string $name  the parameter name
     * @param mixed  $value the value
     *
     * @return mixed
     */
    public function __set($name, $value)
    {
        return $this->_params[$name] = $value;
    }

    /**
     * gets the name of the event
     *    will be used internally for optimisations.
     * @author Copix Team
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * gets the given param.
     *
     * @param string $name the param name
     *
     * @return null|string the value or null if the parameter does not exist
     *
     * @deprecated since Jelix 1.6
     * @author Copix Team
     */
    public function getParam($name)
    {
        if (isset($this->_params[$name])) {
            $ret = $this->_params[$name];
        } else {
            $ret = null;
        }

        return $ret;
    }

    /**
     * return all parameters.
     *
     * @return array parameters
     *
     * @since 1.6.30
     */
    public function getParameters()
    {
        return $this->_params;
    }

    /**
     * Adds data in the responses list.
     *
     * if it is an array, specific items can be retrieved with getResponseByKey()
     * getBoolResponseByKey(), or inResponse()
     *
     * @param mixed $response a single response
     * @author Copix Team
     */
    public function add($response)
    {
        $this->_responses[] = &$response;
    }

    /**
     * look in all the responses if we have a parameter having value as its answer.
     *
     * eg, we want to know if we have failed = true in some responses, we call
     * inResponse('failed', true, $results), and we have into $results all
     * responses that have an item 'failed' equals to true.
     *
     * @param string  $responseKey the response item we're looking for
     * @param mixed   $value       the value we're looking for
     * @param mixed[] $response    returned array : all full responses arrays that have
     *                             the given value
     *
     * @return bool whether or not we have founded the response value
     * @author Copix Team
     */
    public function inResponse($responseKey, $value, &$response)
    {
        $founded = false;
        $response = array();

        foreach ($this->_responses as $key => $listenerResponse) {
            if (
                is_array($listenerResponse)
                && isset($listenerResponse[$responseKey])
                && $listenerResponse[$responseKey] == $value
            ) {
                $founded = true;
                $response[] = &$this->_responses[$key];
            }
        }

        return $founded;
    }

    /**
     * get all responses value for the given key.
     *
     * @param string $responseKey
     *
     * @return null|array list of values or null if no responses for the given item
     *
     * @since 1.6.22
     */
    public function getResponseByKey($responseKey)
    {
        $response = array();

        foreach ($this->_responses as $key => $listenerResponse) {
            if (
                is_array($listenerResponse)
                && isset($listenerResponse[$responseKey])
            ) {
                $response[] = &$listenerResponse[$responseKey];
            }
        }
        if (count($response)) {
            return $response;
        }

        return null;
    }

    const RESPONSE_AND_OPERATOR = 0;

    const RESPONSE_OR_OPERATOR = 1;

    /**
     * get a response value as boolean.
     *
     * if there are multiple response for the same key, a OR or a AND operation
     * is made between all of response values.
     *
     * @param string $responseKey
     * @param int    $operator    const RESPONSE_AND_OPERATOR or RESPONSE_OR_OPERATOR
     *
     * @return null|bool
     *
     * @since 1.6.22
     */
    protected function getBoolResponseByKey($responseKey, $operator = 0)
    {
        $response = null;

        foreach ($this->_responses as $key => $listenerResponse) {
            if (
                is_array($listenerResponse)
                && isset($listenerResponse[$responseKey])
            ) {
                $value = (bool) $listenerResponse[$responseKey];
                if ($response === null) {
                    $response = $value;
                } elseif ($operator === self::RESPONSE_AND_OPERATOR) {
                    $response = $response && $value;
                } elseif ($operator === self::RESPONSE_OR_OPERATOR) {
                    $response = $response || $value;
                }
            }
        }

        return $response;
    }

    /**
     * says if all responses items for the given key, are equals to true.
     *
     * @param string $responseKey
     *
     * @return null|bool null if there are no responses
     *
     * @since 1.6.22
     */
    public function allResponsesByKeyAreTrue($responseKey)
    {
        return $this->getBoolResponseByKey($responseKey, self::RESPONSE_AND_OPERATOR);
    }

    /**
     * says if all responses items for the given key, are equals to false.
     *
     * @param string $responseKey
     *
     * @return null|bool null if there are no responses
     *
     * @since 1.6.22
     */
    public function allResponsesByKeyAreFalse($responseKey)
    {
        $res = $this->getBoolResponseByKey($responseKey, self::RESPONSE_OR_OPERATOR);
        if ($res === null) {
            return $res;
        }

        return !$res;
    }

    /**
     * gets all the responses.
     *
     * @return mixed[][] associative array
     * @author Copix Team
     */
    public function getResponse()
    {
        return $this->_responses;
    }

    //------------------------------------- static methods

    /**
     * Send a notification to all modules.
     * 
     * Possibility to use your own event object, derived from jEvent, and having
     * its own methods and properties. It allows to listeners to give returned data
     * in a better way than using the `add` method.
     *
     * Prefer to use `App::services()->eventDispatcher()->dispatch($event)` for event objects.
     * @param string|Event $eventName     the event name or an event object
     * @param mixed  $params
     *
     * @return Event|object
     */
    public static function notify($eventName, $params = array())
    {
        if (is_object($eventName)) {
            $event = $eventName;
        } else {
            $event = new Event($eventName, $params);

        }

        return App::services()->eventDispatcher()->dispatch($event);
    }

}

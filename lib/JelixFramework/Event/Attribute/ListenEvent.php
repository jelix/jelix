<?php

/**
 * @author    Laurent Jouanneau
 *
 * @copyright 2024 Laurent Jouanneau
 *
 * @see       https://www.jelix.org
 * @licence   MIT
 */
namespace Jelix\Event\Attribute;

/**
 * PHP attribute to declare a method as a listener event, with a simple event name
 *
 * Parameter of the attribute is the event name to listen.
 * The listener will be called for event that have the given name
 *
 * You can declare the attribute on the same method to register the method
 * for several events.
 */
#[\Attribute]
class ListenEvent
{
    public function __construct(
        public readonly string $eventName
    ) {
    }
}

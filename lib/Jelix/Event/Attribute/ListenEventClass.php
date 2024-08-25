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
 * PHP attribute to declare a method as a listener event with an event class name
 *
 * The listener will be called for event that have the given class name.
 *
 * You can use the attribute several times for the same method. In this
 * case the event class name should be a parameter of these attributes. The
 * method parameter should not be typed.
 *
 * If there is only one event class name, the event class name can be readed
 * from the typed parameter of the method.
 */
#[\Attribute]
class ListenEventClass
{
    protected $className;

    public function __construct(string $className = null)
    {
        $this->className = $className;
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function setClassName(string $className)
    {
        $this->className = $className;
    }
}

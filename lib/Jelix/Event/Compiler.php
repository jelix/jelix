<?php
/**
 * @author    Laurent Jouanneau
 * @copyright 2005-2024 Laurent Jouanneau
 *
 * @see       https://www.jelix.org
 * @licence   http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Event;

use ReflectionNamedType;

/**
 * @internal
 */
class Compiler
{
    /**
     * list of listeners for each event.
     * key = event name, value = array($listenerClass, $methodToCall, $classPath, $selector)
     *
     * @var array
     */
    private $eventList;

    public function __construct()
    {
        $this->eventList = array();
    }

    /**
     * Read list of declared event listeners from an events.xml file.
     *
     * It doesn't take care about disabledListeners configuration, as this
     * configuration parameter can be different from an entrypoint to another.
     *
     * @param string $path path of the directory containing the $fileName file
     * @param string $fileName the name of the file (events.xml)
     * @param string $module the module name
     * @return true
     */
    public function compileListenersFile($path, $fileName, $module)
    {
        $sourceFile = $path.$fileName;
        if (is_readable($path.$fileName)) {
            $xml = simplexml_load_file($sourceFile);

            if (isset($xml->listener)) {
                foreach ($xml->listener as $listener) {
                    $listenerName = (string) $listener['name'];
                    if (strpos($listenerName, '\\') !== false) {
                        // the listener is an autoloadable class
                        $selector = $listenerName;
                        $listenerClass = $listenerName;
                        $classPath = false;
                        if ($listener->count() === 0) {
                            // try to load the class sources, and read event mapping
                            // from PHP attributes
                            $this->readEventMappingFromClass($listenerClass);
                        }
                        else {
                            // just read event names from the XML
                            $this->readEventsFromList($listener, array($listenerClass, 'performEvent', $classPath, $selector));
                        }
                    }
                    else {
                        // the listener is a name of a class stored into the classes/ directory
                        // so it should be loaded with jClasses
                        $selector = $module.'~'.$listenerName;
                        $listenerClass = $listenerName.'Listener';
                        $classPath = $path . 'classes/' . $listenerName . '.listener.php';
                        $this->readEventsFromList($listener, array($listenerClass, 'performEvent', $classPath, $selector));
                    }
                }
            }
        }

        return true;
    }

    /**
     * Read events list supported by a listener, from the given XML list
     *
     * @param \SimpleXMLElement $listener
     * @return void
     */
    protected function readEventsFromList($listenerXml, array $listenerData)
    {
        foreach ($listenerXml->event as $eventListened) {
            $name = (string) $eventListened['name'];

            // key = event name ,  value = list of file listener
            $this->eventList[$name][] = $listenerData;
        }
    }

    /**
     * Read events list supported by a listener, from PHP attributes of the listener class
     * @param string $class class name
     * @return void
     * @throws \ReflectionException
     */
    protected function readEventMappingFromClass($class)
    {
        $refClass = new \ReflectionClass($class);
        $methods = $refClass->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            if ($method->isStatic() || $method->isConstructor() || $method->isAbstract() || $method->isDestructor()
                || $method->isClosure() || $method->isGenerator()) {
                continue;
            }

            // Read ListenEvent attributes
            $eventNames = $method->getAttributes(Attribute\ListenEvent::class);
            if (count($eventNames)) {
                if ($method->getNumberOfParameters() != 1) {
                    throw new \Exception("Listener method ".$method->getName()." does not have a single parameter");
                }

                foreach ($eventNames as $eventName) {
                    $name = $eventName->newInstance()->eventName;
                    $this->eventList[$name][] = array($class, $method->getName(), false, $class);
                }
            }

            // Read ListenEventClass attributes
            $eventClasses = $method->getAttributes(Attribute\ListenEventClass::class);
            if (count($eventClasses)) {
                if ($method->getNumberOfParameters() != 1) {
                    throw new \Exception("Listener method ".$method->getName()." does not have a single parameter");
                }

                $argClassName = '';
                $type = $method->getParameters()[0]->getType();
                if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                    $argClassName = $type->getName();
                }

                foreach ($eventClasses as $eventClass) {
                    $className = $eventClass->newInstance()->getClassName();
                    if ($className == '') {
                        if ($argClassName == '') {
                            throw new \Exception("Listener method ".$method->getName()." does not define the class of the event parameter, or class name is missing on ListenEventClass attribute");
                        }
                        $className = $argClassName;
                        $argClassName = ''; // only an empty ListenEventClass attribute is allowed
                    }
                    $this->eventList[$className][] = array($class, $method->getName(), false, $class);
                }
            }
        }
    }


    public function save($cachefile)
    {
        $content = '<?php return '.var_export($this->eventList, true).";\n";
        \jFile::write($cachefile, $content);
    }
}

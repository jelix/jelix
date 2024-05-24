<?php
/**
 * @author    Laurent Jouanneau
 * @copyright 2005-2024 Laurent Jouanneau
 *
 * @see       https://www.jelix.org
 * @licence   http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Event;

/**
 * @internal
 */
class Compiler
{
    /**
     * list of listeners for each event.
     * key = event name, value = array('moduleName', 'listenerName')
     *
     * @var array
     */
    private $eventList;

    public function __construct()
    {
        $this->eventList = array();
    }

    public function compileListenersFile($sourceFile, $module)
    {
        if (is_readable($sourceFile)) {
            $xml = simplexml_load_file($sourceFile);


            if (isset($xml->listener)) {
                foreach ($xml->listener as $listener) {
                    $listenerName = (string) $listener['name'];
                    if (strpos($listenerName, '\\') !== false) {
                        $selector = $listenerName;
                        $listenerClass = $listenerName;
                        $oldListenerName = false;
                    }
                    else {
                        $selector = $module.'~'.$listenerName;
                        $listenerClass = $listenerName.'Listener';
                        $oldListenerName = $listenerName;
                    }
                    foreach ($listener->event as $eventListened) {
                        $name = (string) $eventListened['name'];

                        // key = event name ,  value = list of file listener
                        $this->eventList[$name][] = array($module, $listenerClass, $oldListenerName, $selector);
                    }
                }
            }
        }

        return true;
    }

    public function save($cachefile)
    {
        $content = '<?php return '.var_export($this->eventList, true).";\n?>";
        \jFile::write($cachefile, $content);
    }
}

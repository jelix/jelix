<?php
/**
* @author      Laurent Jouanneau
* @copyright   2008-2014 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
namespace Jelix\Installer\Reporter;

/**
 * simple text reporter
 */
class Console implements \Jelix\Installer\ReporterInterface {
    /**
     * @var string error, notice or warning
     */
    protected $level;

    function __construct($level= 'notice') {
       $this->level = $level;
    }

    function start() {
        if ($this->level == 'notice')
            echo "Installation start..\n";
    }

    /**
     * displays a message
     * @param string $message the message to display
     * @param string $type the type of the message : 'error', 'notice', 'warning', ''
     */
    function message($message, $type='') {
        if (($type == 'error' && $this->level != '')
            || ($type == 'warning' && $this->level != 'notice' && $this->level != '')
            || (($type == 'notice' || $type =='') && $this->level == 'notice'))
        echo ($type != ''?'['.$type.'] ':'').$message."\n";
    }

    /**
     * called when the installation is finished
     * @param array $results an array which contains, for each type of message,
     * the number of messages
     */
    function end($results) {
        if ($this->level == 'notice')
            echo "Installation ended.\n";
    }
}

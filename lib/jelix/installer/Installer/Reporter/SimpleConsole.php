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
class SimpleConsole implements ReporterInterface {
    use ReporterTrait;

    /**
     * @var string error, notice or warning
     */
    protected $level;

    protected $title = '';

    function __construct($level= 'notice', $title='Installation') {
       $this->level = $level;
       $this->title = $title;
    }

    function start() {
        if ($this->level == 'notice') {
            echo $this->title." is starting\n";
        }
    }

    /**
     * displays a message
     * @param string $message the message to display
     * @param string $type the type of the message : 'error', 'notice', 'warning', ''
     */
    function message($message, $type='') {
        $this->addMessageType($type);
        if (($type == 'error' && $this->level != '')
            || ($type == 'warning' && $this->level != 'notice' && $this->level != '')
            || (($type == 'notice' || $type =='') && $this->level == 'notice'))
        echo ($type != ''?'['.$type.'] ':'').$message."\n";
    }

    /**
     * called when the installation is finished
     */
    function end() {
        if ($this->level == 'notice') {
            echo $this->title." is finished\n";
        }
    }
}

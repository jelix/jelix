<?php
/**
 * @package     jelix
 * @subpackage  installer
 *
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer\Reporter;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * simple text reporter.
 */
class Console implements ReporterInterface
{
    use ReporterTrait;

    /**
     * @var string error, notice or warning
     */
    protected $level;

    protected $title = '';

    /**
     * @var OutputInterface
     */
    protected $output;

    public function __construct(OutputInterface $output, $level = 'notice', $title = 'Installation')
    {
        $this->level = $level;
        $this->title = $title;
        $this->output = $output;
    }

    public function start()
    {
        if ($this->level == 'notice') {
            $this->output->writeln($this->title.' is starting');
        }
    }

    /**
     * displays a message.
     *
     * @param string $message the message to display
     * @param string $type    the type of the message : 'error', 'notice', 'warning', ''
     */
    public function message($message, $type = '')
    {
        $this->addMessageType($type);
        if (
            ($type == 'error' && $this->level != '')
            || ($type == 'warning' && $this->level != 'notice' && $this->level != '')
            || (($type == 'notice' || $type == '') && $this->level == 'notice')
        ) {
            if ($type == 'error') {
                $header = '[<error>'.$type.'</error>] ';
            } elseif ($type == 'warning') {
                $header = '[<fg=yellow>'.$type.'</>] ';
            } else {
                $header = '';
            }

            $this->output->writeln($header.$message);
        }
    }

    /**
     * called when the installation is finished.
     */
    public function end()
    {
        if ($this->level == 'notice') {
            $this->output->writeln($this->title.' is finished');
        }
    }
}

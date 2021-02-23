<?php
/**
 * @package    jelix
 * @subpackage core_log
 *
 * @author     Laurent Jouanneau
 * @copyright  2019 Laurent Jouanneau
 *
 * @see       http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Logger\Output;

/**
 * logger sending message to stdout.
 */
class Stdout extends Stderr
{
    protected $fileOutput = 'php://stdout';

    public function __construct()
    {
        $this->config = \Jelix\Core\App::config()->stdoutLogger;
    }
}

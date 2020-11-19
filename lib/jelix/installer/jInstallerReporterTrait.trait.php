<?php
/**
 * @package     jelix
 * @subpackage  installer
 *
 * @author      Laurent Jouanneau
 * @copyright   2016 Laurent Jouanneau
 *
 * @see        http://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * @deprecated
 */
trait jInstallerReporterTrait
{
    private $messageCounter = array();

    protected function addMessageType($type)
    {
        if (!isset($this->messageCounter[$type])) {
            $this->messageCounter[$type] = 1;

            return;
        }
        ++$this->messageCounter[$type];
    }

    public function getMessageCounter($type)
    {
        if (!isset($this->messageCounter[$type])) {
            return 0;
        }

        return $this->messageCounter[$type];
    }
}

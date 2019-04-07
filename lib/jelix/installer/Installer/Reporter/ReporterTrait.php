<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2016-2018 Laurent Jouanneau
 *
 * @see        http://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

namespace Jelix\Installer\Reporter;

trait ReporterTrait
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

    public function resetMessageCounter()
    {
        $this->messageCounter = array();
    }
}

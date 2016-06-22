<?php
/**
* @package     jelix
* @subpackage  installer
* @author      Laurent Jouanneau
* @copyright   2016 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
namespace Jelix\Installer { 
// enclose namespace here because this file is inserted into jelix_check_server.php by a build tool

trait ReporterTrait {

    private $messageCounter = array();

    protected function addMessageType($type) {
        if (!isset($this->messageCounter[$type])) {
            $this->messageCounter[$type] = 1;
            return;
        }
        $this->messageCounter[$type]++;
    }

    public function getMessageCounter($type) {
        if (!isset($this->messageCounter[$type])) {
            return 0;
        }
        return $this->messageCounter[$type];
    }
}

}// end of namespace
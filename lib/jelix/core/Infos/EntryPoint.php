<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2018 Laurent Jouanneau
 * @link       http://jelix.org
 * @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Core\Infos;

class EntryPoint {

    protected $id;

    protected $type;

    protected $configFile;

    function __construct($id, $configFile, $type='classic') {
        $this->id = $id;
        $this->type = $type;
        $this->configFile = $configFile;
    }

    function getFile() {
        return $this->id.'.php';
    }

    function getId() {
        return $this->id;
    }

    function getType() {
        return $this->type;
    }

    function getConfigFile() {
        return $this->configFile;
    }

    function setConfigFile($file) {
        $this->configFile = $file;
    }
}
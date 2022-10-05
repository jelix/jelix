<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2018 Laurent Jouanneau
 *
 * @see       http://jelix.org
 * @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Core\Infos;

class EntryPoint
{
    protected $id;

    protected $type;

    protected $configFile;

    protected $local = false;

    public function __construct($id, $configFile, $type = 'classic')
    {
        $this->id = $id;
        $this->type = ($type == '' ? 'classic' : $type);
        $this->configFile = $configFile;
    }

    public function getFile()
    {
        return $this->id.'.php';
    }

    public function getId()
    {
        return $this->id;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getConfigFile()
    {
        return $this->configFile;
    }

    public function setConfigFile($file)
    {
        $this->configFile = $file;
    }

    public function setAsLocal($asLocal = true)
    {
        $this->local = $asLocal;
    }

    public function isLocal()
    {
        return $this->local;
    }
}

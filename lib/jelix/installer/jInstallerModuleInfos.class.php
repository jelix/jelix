<?php
/**
* @package     jelix
* @subpackage  installer
* @author      Laurent Jouanneau
* @contributor 
* @copyright   2009-2010 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * container for module properties
 */
class jInstallerModuleInfos {
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $access;
    /**
     * @var string
     */
    public $dbProfile;
    /**
     * @var string
     */
    public $isInstalled;
    /**
     * @var string
     */
    public $version;
    /**
     * @var string
     */
    public $sessionId;

    /**
     * @var array parameters for installation
     */
    public $parameters = array();

    /**
     * @param string $name the name of the module
     * @param jInstallerEntryPoint $entryPoint  the entry point on which the module is attached
     */
    function __construct($name, $entryPoint) {
        $this->name = $name;
        $config = $entryPoint->config;
        $this->access = $config->modules[$name.'.access'];
        $this->dbProfile = $config->modules[$name.'.dbprofile'];
        $this->isInstalled = $config->modules[$name.'.installed'];
        $this->version = $config->modules[$name.'.version'];
        $this->sessionId = $config->modules[$name.'.sessionid'];

        if (isset($config->modules[$name.'.installparam'])) {
            $params = explode(';', $config->modules[$name.'.installparam']);
            foreach($params as $param) {
                $kp = explode("=", $param);
                if (count($kp) > 1)
                    $this->parameters[$kp[0]] = $kp[1];
                else
                    $this->parameters[$kp[0]] = true;
            }
        }
    }

    function serializeParameters() {
        $p = '';
        foreach($this->parameters as $name=>$v) {
            if ($v === true || $v == '')
                $p.=';'.$name;
            else
                $p .= ';'.$name.'='.$v;
        }
        return substr($p, 1);
    }
}

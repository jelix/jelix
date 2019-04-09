<?php
/**
 * @package     jelix
 * @subpackage  core_request
 *
 * @author      Laurent Jouanneau
 * @contributor Loic Mathaud
 * @contributor Thibault Piront (nuKs)
 * @contributor Christophe Thiriot
 *
 * @copyright   2005-2012 Laurent Jouanneau, 2006-2007 Loic Mathaud
 * @copyright   2007 Thibault Piront
 * @copyright   2008 Christophe Thiriot
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * a request object for scripts used in a command line.
 *
 * @package     jelix
 * @subpackage  core_request
 *
 * @deprecated
 */
class jCmdLineRequest extends jRequest
{
    public $type = 'cmdline';

    public $defaultResponseType = 'cmdline';

    public $authorizedResponseClass = 'jResponseCmdline';

    protected $onlyDefaultAction = false;

    protected $startModule = '';

    protected $startAction = '';

    /**
     * If you want to have a CLI script dedicated to the default action,
     * tell it by given true, so you haven't to indicate the action
     * on the command line. It means of course you couldn't execute any other
     * actions with this script.
     *
     * @param bool   $onlyDefaultAction
     * @param string $module            module for the default action
     * @param string $action            action for the default action
     */
    public function __construct($onlyDefaultAction = false, $module = '', $action = '')
    {
        $this->onlyDefaultAction = $onlyDefaultAction;
        if ($onlyDefaultAction && ($module == '' || $action == '')) {
            throw new Exception('No default module and action has been given to jCmdLineRequest');
        }
        $this->startAction = $action;
        $this->startModule = $module;
    }

    protected function _initUrlData()
    {
        $this->urlScriptPath = '/';
        $this->urlScriptName = $this->urlScript = $_SERVER['SCRIPT_NAME'];
        $this->urlPathInfo = '';
    }

    protected function _initParams()
    {
        $argv = $_SERVER['argv'];
        $scriptName = array_shift($argv); // shift the script name

        $mod = $this->startModule;
        $act = $this->startAction;

        if ($this->onlyDefaultAction) {
            if ($_SERVER['argc'] > 1 && $argv[0] == 'help') {
                $argv[0] = $mod.'~'.$act;
                $mod = 'jelix';
                $act = 'help:index';
            }
        } else {
            // note: we cannot use jSelectorAct to parse the action
            // because in the opt edition, jSelectorAct needs an initialized jCoordinator
            // and this is not the case here. see bug #725.

            if ($_SERVER['argc'] != 1) {
                $argsel = array_shift($argv); // get the module~action selector
                if ($argsel == 'help') {
                    $mod = 'jelix';
                    $act = 'help:index';
                } elseif (($pos = strpos($argsel, '~')) !== false) {
                    $mod = substr($argsel, 0, $pos);
                    $act = substr($argsel, $pos + 1);
                } else {
                    $act = $argsel;
                }
            }
        }
        $this->params = $argv;
        $this->params['module'] = $mod;
        $this->params['action'] = $act;
    }

    /**
     * return the ip address of the user.
     *
     * @return string the ip
     */
    public function getIP()
    {
        return '127.0.0.1';
    }

    public function isAllowedResponse($response)
    {
        return $response instanceof $this->authorizedResponseClass;
    }
}

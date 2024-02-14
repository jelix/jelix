<?php
/**
 * @package    jelix
 * @subpackage jauth
 *
 * @author     Laurent Jouanneau
 * @contributor
 *
 * @copyright   2005-2006 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
class loginswCtrl extends jController
{
    public $sensitiveParameters = array('password');

    public $pluginParams = array(
        '*' => array('auth.required' => false),
    );

    public function in()
    {
        $conf = jApp::coord()->getPlugin('auth')->config;

        if (!jAuth::login($this->param('login'), $this->param('password'))) {
            sleep(intval($conf['on_error_sleep']));
            $result = 'BAD';
        } else {
            $result = 'OK';
        }

        $rep = $this->getResponse('text');
        $rep->content = $result;

        return $rep;
    }

    public function out()
    {
        jAuth::logout();
        $rep = $this->getResponse('text');
        $rep->content = 'LOGOUT';

        return $rep;
    }
}

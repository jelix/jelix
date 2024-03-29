<?php
/**
 * @package   jelix
 * @subpackage master_admin
 *
 * @author    Laurent Jouanneau
 * @copyright 2008 Laurent Jouanneau
 *
 * @see      http://jelix.org
 * @licence  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public Licence, see LICENCE file
 */
class defaultCtrl extends jController
{
    public $pluginParams = array(
        '*' => array('auth.required' => true),
    );

    public function index()
    {
        $resp = $this->getResponse('html');
        $resp->title = jLocale::get('gui.dashboard.title');
        $resp->body->assignZone('MAIN', 'dashboard');
        $user = jAuth::getUserSession();
        $driver = jAuth::getDriver();
        if (method_exists($driver, 'checkPassword')
            && $driver->checkPassword($user->login, $user->password)
        ) {
            jMessage::add(jLocale::get('gui.message.admin.password'), 'error');
        }
        $resp->body->assign('selectedMenuItem', 'dashboard');

        return $resp;
    }
}

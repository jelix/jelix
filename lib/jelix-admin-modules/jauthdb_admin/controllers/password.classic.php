<?php

/**
 * @package   admin
 * @subpackage jauthdb_admin
 *
 * @author    Laurent Jouanneau
 * @copyright 2009-2022 Laurent Jouanneau
 *
 * @see      http://jelix.org
 *
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public Licence
 */
class passwordCtrl extends jController
{
    public $sensitiveParameters = array('pwd', 'pwd_confirm');

    public $pluginParams = array(
        '*' => array('jacl2.rights.or' => array('auth.users.change.password', 'auth.user.change.password')),
    );

    protected function isPersonalView()
    {
        return !jAcl2::check('auth.users.change.password');
    }

    public function index()
    {
        $login = $this->param('j_user_login');
        if ($login === null) {
            return $this->redirect('master_admin~default:index');
        }

        $personalView = $this->isPersonalView();
        if (($personalView && $login != jAuth::getUserSession()->login)
            || !jAuth::canChangePassword($login)
        ) {
            jMessage::add(jLocale::get('jacl2~errors.action.right.needed'), 'error');

            return $this->redirect('master_admin~default:index');
        }

        if (\jApp::isModuleEnabled('jcommunity')) {
            // jcommunity provides its own password change forms
            return $this->redirect('master_admin~default:index');
        }

        $rep = $this->getResponse('html');

        $form = jForms::create('jauthdb_admin~password_change', $login);
        $tpl = new jTpl();
        $tpl->assign('id', $login);
        $tpl->assign('form', $form);
        $tpl->assign('formOptions', []);
        $tpl->assign('randomPwd', jAuth::getRandomPassword());
        $tpl->assign('personalview', $personalView);
        if ($personalView) {
            $tpl->assign('viewaction', 'user:index');
        } else {
            $tpl->assign('viewaction', 'default:view');
        }
        $rep->body->assign('MAIN', $tpl->fetch('password_change'));

        return $rep;
    }

    public function update()
    {
        $login = $this->param('j_user_login');

        $personalView = $this->isPersonalView();
        if (($personalView && $login != jAuth::getUserSession()->login)
            || !jAuth::canChangePassword($login)
        ) {
            jMessage::add(jLocale::get('jacl2~errors.action.right.needed'), 'error');

            return $this->redirect('master_admin~default:index');
        }

        if (\jApp::isModuleEnabled('jcommunity')) {
            // jcommunity provides its own password change forms
            return $this->redirect('master_admin~default:index');
        }

        $form = jForms::fill('jauthdb_admin~password_change', $login);
        if (!$form || !$form->check()) {
            return $this->redirect('password:index', ['j_user_login' => $login]);
        }
        $pwd = $form->getData('pwd');
        if (jAuth::changePassword($login, $pwd)) {
            jForms::destroy('jauthdb_admin~password_change', $login);
            jMessage::add(jLocale::get('crud.message.change.password.ok', $login), 'notice');
            if ($personalView) {
                return $this->redirect('user:index', ['j_user_login' => $login]);
            } else {
                return $this->redirect('default:view', ['j_user_login' => $login]);
            }
        }

        $form->setErrorOn('pwd', jLocale::get('crud.message.change.password.notok'));

        return $this->redirect('password:index', ['j_user_login' => $login]);
    }
}

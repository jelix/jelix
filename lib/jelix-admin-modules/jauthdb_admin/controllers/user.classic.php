<?php

/**
 * @package   admin
 * @subpackage jauthdb_admin
 *
 * @author    Laurent Jouanneau
 * @copyright 2009-2023 Laurent Jouanneau
 *
 * @see      http://jelix.org
 *
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public Licence
 */

/**
 * controller to allow a user to edit his own profile in the admin.
 */
class userCtrl extends jController
{
    public $pluginParams = array(
        'view' => array('jacl2.right' => 'auth.user.view'),
        'preupdate' => array('jacl2.rights.and' => array('auth.user.view', 'auth.user.modify')),
        'editupdate' => array('jacl2.rights.and' => array('auth.user.view', 'auth.user.modify')),
        'saveupdate' => array('jacl2.rights.and' => array('auth.user.view', 'auth.user.modify')),
    );
    /**
     * selector of the dao to use for the crud.
     *
     * @var string
     */
    protected $dao = '';

    /**
     * selector of the form to use to edit and display a record.
     *
     * @var string
     */
    protected $form = '';

    /**
     * the jDb profile to use with the dao.
     */
    protected $dbProfile = '';

    protected $authConfig;

    protected $uploadsDirectory = '';

    public function __construct($request)
    {
        parent::__construct($request);
        $plugin = jApp::coord()->getPlugin('auth');
        $driver = $plugin->config['driver'];
        $hasDao = isset($plugin->config[$driver]['dao'], $plugin->config[$driver]['compatiblewithdb']) && $plugin->config[$driver]['compatiblewithdb'];
        if (($driver == 'Db') || $hasDao) {
            $this->authConfig = $plugin->config[$driver];
            $this->dao = $this->authConfig['dao'];
            if (isset($this->authConfig['form'])) {
                $this->form = $this->authConfig['form'];
            }
            $this->dbProfile = $this->authConfig['profile'];
            if (isset($this->authConfig['uploadsDirectory'])) {
                $this->uploadsDirectory = $this->authConfig['uploadsDirectory'];
            }
        }
    }

    public function index()
    {
        $login = $this->param('j_user_login');
        if ($login === null) {

            return $this->redirect('master_admin~default:index');
        }

        if ($login != jAuth::getUserSession()->login) {
            jMessage::add(jLocale::get('jacl2~errors.action.right.needed'), 'error');

            return $this->redirect('master_admin~default:index');
        }

        $dao = jDao::create($this->dao, $this->dbProfile);
        $daoUser = $dao->getByLogin($login);
        if (!$daoUser) {
            jMessage::add(jLocale::get('crud.message.bad.id', $login), 'error');

            return $this->redirect('master_admin~default:index');
        }

        $rep = $this->getResponse('html');

        // we're using a form to display a record, to have the opportunity to have
        // labels with each values.
        $form = jForms::create($this->form, $login);
        $form->initFromDao($daoUser, null, $this->dbProfile);

        $tpl = new jTpl();
        $tpl->assign('id', $login);
        $tpl->assign('form', $form);
        $tpl->assign('formOptions', []);
        $tpl->assign('personalview', true);
        $tpl->assign('otherInfo', jEvent::notify('jauthdbAdminGetViewInfo', array('form' => $form, 'tpl' => $tpl, 'himself' => true))->getResponse());
        $form->deactivate('password');
        $form->deactivate('password_confirm');
        $tpl->assign('canUpdate', jAcl2::check('auth.user.modify'));
        $tpl->assign('canChangePass', jAcl2::check('auth.user.change.password') && jAuth::canChangePassword($login));
        $rep->body->assign('MAIN', $tpl->fetch('user_view'));

        return $rep;
    }

    /**
     * prepare a form in order to edit an existing record, and redirect to the editupdate action.
     */
    public function preupdate()
    {
        $login = $this->param('j_user_login');

        if ($login === null) {

            return $this->redirect('master_admin~default:index');
        }

        if ($login != jAuth::getUserSession()->login) {
            jMessage::add(jLocale::get('jacl2~errors.action.right.needed'), 'error');

            return $this->redirect('master_admin~default:index');
        }

        $dao = jDao::create($this->dao, $this->dbProfile);
        $daoUser = $dao->getByLogin($login);
        if (!$daoUser) {
            jMessage::add(jLocale::get('crud.message.bad.id', $login), 'error');

            return $this->redirect('master_admin~default:index');
        }

        $form = jForms::create($this->form, $login);

        try {
            $rec = $form->initFromDao($daoUser, null, $this->dbProfile);
            foreach ($rec->getPrimaryKeyNames() as $pkn) {
                $c = $form->getControl($pkn);
                if ($c !== null) {
                    $c->setReadOnly(true);
                }
            }
        } catch (Exception $e) {

            return $this->redirect('default:view', ['j_user_login' => $login]);
        }

        jEvent::notify('jauthdbAdminPrepareUpdate', array('form' => $form, 'himself' => true));
        $form->setReadOnly('login');
        $form->deactivate('password');
        $form->deactivate('password_confirm');

        return $this->redirect('user:editupdate', ['j_user_login' => $login]);
    }

    /**
     * displays a forms to edit an existing record. The form should be
     * prepared with the preupdate before, so a refresh of the page
     * won't cause a reset of the form.
     */
    public function editupdate()
    {
        $login = $this->param('j_user_login');
        $form = jForms::get($this->form, $login);
        if ($form === null || $login === null) {

            return $this->redirect('master_admin~default:index');
        }

        if ($login != jAuth::getUserSession()->login) {
            jMessage::add(jLocale::get('jacl2~errors.action.right.needed'), 'error');

            return $this->redirect('master_admin~default:index');
        }

        $rep = $this->getResponse('html');

        $tpl = new jTpl();
        $tpl->assign('id', $login);
        $tpl->assign('form', $form);
        $tpl->assign('formOptions', []);
        $tpl->assign('saveaction', 'user:saveupdate');
        $tpl->assign('viewaction', 'user:index');
        jEvent::notify('jauthdbAdminEditUpdate', array('form' => $form, 'tpl' => $tpl, 'himself' => true));
        $form->deactivate('password'); //for security
        $form->deactivate('password_confirm');
        $form->setReadOnly('login');
        $rep->body->assign('MAIN', $tpl->fetch('user_edit'));

        return $rep;
    }

    /**
     * save data of a form in a new record.
     */
    public function saveupdate()
    {
        $login = $this->param('j_user_login');

        if ($login != jAuth::getUserSession()->login) {
            jMessage::add(jLocale::get('jacl2~errors.action.right.needed'), 'error');

            return $this->redirect('master_admin~default:index');
        }

        $dao = jDao::create($this->dao, $this->dbProfile);
        /** @var jDaoRecordBase $daoUser */
        $daoUser = $dao->getByLogin($login);
        if (!$daoUser) {
            jMessage::add(jLocale::get('crud.message.bad.id', $login), 'error');

            return $this->redirect('master_admin~default:index');
        }

        $form = jForms::get($this->form, $login);

        if ($form === null || $login === null) {

            return $this->redirect('master_admin~default:index');
        }

        jEvent::notify(
            'jauthdbAdminBeforeCheckUpdateForm',
            array('form' => $form, 'himself' => true)
        );

        $form->initFromRequest();

        $evresp = array();
        if ($form->check()
            && !jEvent::notify('jauthdbAdminCheckUpdateForm', array('form' => $form, 'himself' => true))
                ->inResponse('check', false, $evresp)
        ) {
            $form->prepareObjectFromControls($daoUser, $daoUser->getProperties());

            // we call jAuth instead of using jDao, to allow jAuth to do
            // all process, events...
            jAuth::updateUser($daoUser);

            jEvent::notify('jauthdbAdminAfterUpdate', array('form' => $form, 'user' => $daoUser));

            // it will save files that are not already saved by listeners of jauthdbAdminAfterUpdate
            $form->saveAllFiles($this->uploadsDirectory);

            jMessage::add(jLocale::get('crud.message.update.ok', $login), 'notice');
            jForms::destroy($this->form, $login);

            return $this->redirect('user:index', ['j_user_login' => $login]);
        }

        return $this->redirect('user:editupdate', ['j_user_login' => $login]);
    }
}

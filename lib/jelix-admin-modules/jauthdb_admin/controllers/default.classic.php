<?php

/**
 * @package     jelix
 * @subpackage  jauthdb_admin
 *
 * @author    Laurent Jouanneau
 * @copyright 2009-2024 Laurent Jouanneau
 *
 * @see      http://jelix.org
 *
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public Licence
 */

use Jelix\Forms\Forms;

/**
 * controller to manage all users.
 */
class defaultCtrl extends jController
{
    public $sensitiveParameters = array('password', 'password_confirm', 'pwd', 'pwd_confirm');

    public $pluginParams = array(
        'index' => array('jacl2.right' => 'auth.users.list'),
        'autocomplete' => array('jacl2.right' => 'auth.users.list'),
        'view' => array('jacl2.right' => 'auth.users.view'),
        'precreate' => array('jacl2.rights.and' => array('auth.users.view', 'auth.users.create')),
        'create' => array('jacl2.rights.and' => array('auth.users.view', 'auth.users.create')),
        'savecreate' => array('jacl2.rights.and' => array('auth.users.view', 'auth.users.create')),
        'preupdate' => array('jacl2.rights.and' => array('auth.users.view', 'auth.users.modify')),
        'editupdate' => array('jacl2.rights.and' => array('auth.users.view', 'auth.users.modify')),
        'saveupdate' => array('jacl2.rights.and' => array('auth.users.view', 'auth.users.modify')),
        'deleteconfirm' => array('jacl2.rights.and' => array('auth.users.view', 'auth.users.delete')),
        'delete' => array('jacl2.rights.and' => array('auth.users.view', 'auth.users.delete')),
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

    protected $listPageSize = 20;

    protected $authConfig;

    protected $uploadsDirectory = '';

    protected $propertiesForList = array('login');

    protected $filteredProperties = array('login');

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
            if (isset($this->authConfig['listProperties'])) {
                $this->propertiesForList = preg_split('/ *, */', $this->authConfig['listProperties']);
            }
            if (isset($this->authConfig['filteredProperties'])) {
                $this->filteredProperties = preg_split('/ *, */', $this->authConfig['filteredProperties']);
            }
            $this->dbProfile = $this->authConfig['profile'];
            if (isset($this->authConfig['uploadsDirectory'])) {
                $this->uploadsDirectory = $this->authConfig['uploadsDirectory'];
            }
        }
    }

    /**
     * list all users.
     */
    public function index()
    {
        $offset = $this->intParam('offset', 0, true);

        $rep = $this->getResponse('html');

        if ($this->form == '') {
            $rep->body->assign('MAIN', 'no form defined in the auth plugin');
            $rep->setHttpStatus(500, 'Internal Server Error');

            return $rep;
        }

        $tpl = new jTpl();

        $dao = jDao::get($this->dao, $this->dbProfile);

        if (isset($_SESSION['AUTHDB_CRUD_LISTORDER'])) {
            $listOrder = $_SESSION['AUTHDB_CRUD_LISTORDER'];
        } else {
            $listOrder = array('login' => 'asc');
        }

        if (($lo = $this->param('listorder'))
            && (in_array($lo, $this->propertiesForList))
        ) {
            if (isset($listOrder[$lo]) && $listOrder[$lo] == 'asc') {
                $listOrder[$lo] = 'desc';
            } elseif (isset($listOrder[$lo]) && $listOrder[$lo] == 'desc') {
                unset($listOrder[$lo]);
            } else {
                $listOrder[$lo] = 'asc';
            }
            $_SESSION['AUTHDB_CRUD_LISTORDER'] = $listOrder;
        }

        $cond = jDao::createConditions();
        foreach ($listOrder as $name => $order) {
            $cond->addItemOrder($name, $order);
        }

        $filter = trim($this->param('filter', ''));
        if ($filter && count($this->filteredProperties)) {
            if (count($this->filteredProperties) == 1) {
                $cond->addCondition($this->filteredProperties[0], 'LIKE', '%' . $filter . '%');
            } else {
                $cond->startGroup('OR');
                foreach ($this->filteredProperties as $prop) {
                    $cond->addCondition($prop, 'LIKE', '%' . $filter . '%');
                }
                $cond->endGroup();
            }
        }

        $tpl->assign('list', $dao->findBy($cond, $offset, $this->listPageSize));

        //$pk = $dao->getPrimaryKeyNames();
        // deprecated. for compatibility with old template from theme, let's indicate the 'login' property
        //$tpl->assign('primarykey', $pk[0]);
        $tpl->assign('primarykey', 'login');
        $tpl->assign('showfilter', count($this->filteredProperties));
        $tpl->assign('filter', $filter);
        $tpl->assign('listOrder', $listOrder);
        $tpl->assign('propertiesList', $this->propertiesForList);
        $tpl->assign('controls', Forms::create($this->form, '___$$$___')->getControls());
        $tpl->assign('listPageSize', $this->listPageSize);
        $tpl->assign('page', $offset);
        $tpl->assign('recordCount', $dao->countAll());
        $tpl->assign('cancreate', jAcl2::check('auth.users.create'));
        $tpl->assign('canview', jAcl2::check('auth.users.view'));
        $rep->body->assign('MAIN', $tpl->fetch('crud_list'));
        $rep->body->assign('selectedMenuItem', 'users');
        Forms::destroy($this->form, '___$$$___');

        return $rep;
    }

    /**
     * displays a user.
     */
    public function view()
    {
        $login = $this->param('j_user_login');
        if ($login === null) {
            jMessage::add(jLocale::get('crud.message.bad.id', 'null'), 'error');

            return $this->redirect('default:index');
        }
        $dao = jDao::create($this->dao, $this->dbProfile);
        $daorec = $dao->getByLogin($login);
        if (!$daorec) {
            jMessage::add(jLocale::get('crud.message.bad.id', $login), 'error');

            return $this->redirect('default:index');
        }

        $rep = $this->getResponse('html');

        // we're using a form to display a record, to have the opportunity to have
        // labels with each values.
        $form = Forms::create($this->form, $login);
        $form->initFromDao($daorec, null, $this->dbProfile);

        $tpl = new jTpl();
        $tpl->assign('id', $login);
        $tpl->assign('form', $form);
        $tpl->assign('formOptions', []);
        $tpl->assign('canDelete', (jAuth::getUserSession()->login != $login)
            && jAcl2::check('auth.users.delete'));
        $tpl->assign('canUpdate', jAcl2::check('auth.users.modify'));
        $tpl->assign('canChangePass', jAcl2::check('auth.users.change.password')
            && jAuth::canChangePassword($login));
        $tpl->assign('otherLinks', array());
        $tpl->assign('otherInfo', jEvent::notify(
            'jauthdbAdminGetViewInfo',
            array('form' => $form, 'tpl' => $tpl, 'himself' => false)
        )->getResponse());
        $form->deactivate('password');
        $form->deactivate('password_confirm');
        $rep->body->assign('MAIN', $tpl->fetch('crud_view'));

        return $rep;
    }

    /**
     * prepare a form to create a record.
     */
    public function precreate()
    {
        $form = Forms::create($this->form);
        $form->deactivate('password', false);
        $form->deactivate('password_confirm', false);
        jEvent::notify('jauthdbAdminPrepareCreate', array('form' => $form));

        return $this->redirect('default:create');
    }

    /**
     * display a form to create a record.
     */
    public function create()
    {
        $form = Forms::get($this->form);
        if ($form == null) {
            $form = Forms::create($this->form);
        }
        $rep = $this->getResponse('html');

        $tpl = new jTpl();
        $tpl->assign('id', null);
        $tpl->assign('form', $form);
        $tpl->assign('formOptions', []);
        $tpl->assign('randomPwd', jAuth::getRandomPassword());
        $tpl->assign('otherInfo', jEvent::notify(
            'jauthdbAdminEditCreate',
            array('form' => $form, 'tpl' => $tpl)
        )->getResponse());

        $rep->body->assign('MAIN', $tpl->fetch('crud_edit'));

        return $rep;
    }

    /**
     * save data of a form in a new record.
     */
    public function savecreate()
    {
        $form = Forms::get($this->form);
        if ($form == null) {
            jMessage::add(jLocale::get('crud.message.bad.form'), 'error');

            return $this->redirect('default:index');
        }

        jEvent::notify('jauthdbAdminBeforeCheckCreateForm', array('form' => $form));

        $form->initFromRequest();

        $login = trim($form->getData('login'));
        if (jAuth::getUser($login)) {
            $form->setErrorOn('login', jLocale::get('crud.message.create.existing.user', $login));

            return $this->redirect('default:create');
        }

        $evresp = array();
        if (
            $form->check()
            && !jEvent::notify('jauthdbAdminCheckCreateForm', array('form' => $form))
                ->inResponse('check', false, $evresp)
        ) {
            $props = jDao::createRecord($this->dao, $this->dbProfile)->getProperties();

            $user = jAuth::createUserObject($form->getData('login'), $form->getData('password'));

            $form->setData('password', $user->password);
            $form->prepareObjectFromControls($user, $props);

            jAuth::saveNewUser($user);
            jEvent::notify('jauthdbAdminAfterCreate', array('form' => $form, 'user' => $user));

            // it will save files that are not already saved by listeners of jauthdbAdminAfterCreate
            $form->saveAllFiles($this->uploadsDirectory);

            Forms::destroy($this->form);
            jMessage::add(jLocale::get('crud.message.create.ok', $user->login), 'notice');

            return $this->redirect('default:view', ['j_user_login' => $user->login]);
        }

        return $this->redirect('default:create');
    }

    /**
     * prepare a form in order to edit an existing record, and redirect to the editupdate action.
     */
    public function preupdate()
    {
        $login = $this->param('j_user_login');

        if ($login === null) {
            jMessage::add(jLocale::get('crud.message.bad.id', 'null'), 'error');

            return $this->redirect('default:index');
        }

        $dao = jDao::create($this->dao, $this->dbProfile);
        $daoUser = $dao->getByLogin($login);
        if (!$daoUser) {
            jMessage::add(jLocale::get('crud.message.bad.id', $login), 'error');

            return $this->redirect('default:index');
        }

        $form = Forms::create($this->form, $login);

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

        jEvent::notify('jauthdbAdminPrepareUpdate', array('form' => $form, 'himself' => false));
        $form->setReadOnly('login');
        $form->deactivate('password');
        $form->deactivate('password_confirm');

        return $this->redirect('default:editupdate', ['j_user_login' => $login]);
    }

    /**
     * displays a forms to edit an existing record. The form should be
     * prepared with the preupdate before, so a refresh of the page
     * won't cause a reset of the form.
     */
    public function editupdate()
    {
        $login = $this->param('j_user_login');
        $form = Forms::get($this->form, $login);
        if ($form === null || $login === null) {
            jMessage::add(jLocale::get('crud.message.bad.id', $login), 'error');

            return $this->redirect('default:index');
        }
        $rep = $this->getResponse('html');

        $tpl = new jTpl();
        $tpl->assign('id', $login);
        $tpl->assign('form', $form);
        $tpl->assign('formOptions', []);
        $tpl->assign('otherInfo', jEvent::notify(
            'jauthdbAdminEditUpdate',
            array('form' => $form, 'tpl' => $tpl, 'himself' => false)
        )->getResponse());
        $form->deactivate('password'); //for security
        $form->deactivate('password_confirm');
        $form->setReadOnly('login');
        $rep->body->assign('MAIN', $tpl->fetch('crud_edit'));

        return $rep;
    }

    /**
     * save data of a form in a new record.
     */
    public function saveupdate()
    {
        $login = $this->param('j_user_login');

        if ($login === null) {
            jMessage::add(jLocale::get('crud.message.bad.id', 'null'), 'error');

            return $this->redirect('default:index');
        }

        $dao = jDao::create($this->dao, $this->dbProfile);
        /** @var \Jelix\Dao\AbstractDaoRecord $daoUser */
        $daoUser = $dao->getByLogin($login);
        if (!$daoUser) {
            jMessage::add(jLocale::get('crud.message.bad.id', $login), 'error');

            return $this->redirect('default:index');
        }

        $form = Forms::get($this->form, $login);

        if ($form === null) {
            jMessage::add(jLocale::get('crud.message.bad.form'), 'error');

            return $this->redirect('default:index');
        }

        jEvent::notify('jauthdbAdminBeforeCheckUpdateForm', array('form' => $form, 'himself' => false));

        $form->initFromRequest();

        $evresp = array();
        if (
            $form->check()
            && !jEvent::notify('jauthdbAdminCheckUpdateForm', array('form' => $form, 'himself' => false))
                ->inResponse('check', false, $evresp)
        ) {
            $form->prepareObjectFromControls($daoUser, $daoUser->getProperties());

            // we call jAuth instead of using jDao, to allow jAuth to do
            // all process, events...
            jAuth::updateUser($daoUser);

            jEvent::notify('jauthdbAdminAfterUpdate', array('form' => $form, 'user' => $daoUser));

            // it will save files that are not saved by listeners of jauthdbAdminAfterUpdate
            $form->saveAllFiles($this->uploadsDirectory);

            jMessage::add(jLocale::get('crud.message.update.ok', $login), 'notice');
            Forms::destroy($this->form, $login);

            return $this->redirect('default:view', ['j_user_login' => $login]);
        }

        return $this->redirect('default:editupdate', ['j_user_login' => $login]);
    }

    public function confirmdelete()
    {
        $login = $this->param('j_user_login');
        if ($login === null) {
            jMessage::add(jLocale::get('crud.message.bad.id', 'null'), 'error');

            return $this->redirect('default:index');
        }

        $dao = jDao::create($this->dao, $this->dbProfile);
        /** @var \Jelix\Dao\AbstractDaoRecord $daoUser */
        $daoUser = $dao->getByLogin($login);
        if (!$daoUser) {
            jMessage::add(jLocale::get('crud.message.bad.id', $login), 'error');

            return $this->redirect('default:index');
        }

        $rep = $this->getResponse('html');

        $tpl = new jTpl();
        $tpl->assign('id', $login);
        $rep->body->assign('MAIN', $tpl->fetch('crud_delete'));

        return $rep;
    }

    /**
     * delete a record.
     */
    public function delete()
    {
        $login = $this->param('j_user_login');
        $pwd = $this->param('pwd_confirm');

        if (jAuth::verifyPassword(jAuth::getUserSession()->login, $pwd) == false) {
            jMessage::add(jLocale::get('crud.message.delete.invalid.pwd'), 'error');

            return $this->redirect('default:confirmdelete', ['j_user_login' => $login]);
        }

        if ($login !== null && jAuth::getUserSession()->login != $login) {
            if (jAuth::removeUser($login)) {
                jMessage::add(jLocale::get('crud.message.delete.ok', $login), 'notice');
            } else {
                jMessage::add(jLocale::get('crud.message.delete.notok'), 'error');
                return $this->redirect('default:view', ['j_user_login' => $login]);
            }
        } else {
            jMessage::add(jLocale::get('crud.message.delete.notok'), 'error');
        }

        return $this->redirect('default:index');
    }

    public function autocomplete()
    {
        $rep = $this->getResponse('json');
        $term = $this->param('term');
        if (strlen($term) < 2) {
            $rep->data = array();

            return $rep;
        }

        $dao = jDao::get($this->dao, $this->dbProfile);
        $cond = jDao::createConditions();
        $cond->addItemOrder('login', 'asc');
        $list = $dao->findBy($cond);
        $users = array();
        foreach ($list as $prop) {
            if (strstr($prop->login, $term) || $term === '') {
                $users[] = $prop->login;
            }
        }
        $rep->data = $users;

        return $rep;
    }
}

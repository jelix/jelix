<?php
/**
* @package   admin
* @subpackage jauthdb_admin
* @author    Laurent Jouanneau
* @copyright 2009 Laurent Jouanneau
* @link      http://jelix.org
* @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public Licence
*/

class defaultCtrl extends jController {
   
    public $pluginParams=array(
        'index'        =>array('jacl2.right'=>'auth.user.list'),
        'view'         =>array('jacl2.right'=>'auth.user.view'),
        'precreate'    =>array('jacl2.rights.and'=>array('auth.user.view','auth.user.create')),
        'create'       =>array('jacl2.rights.and'=>array('auth.user.view','auth.user.create')),
        'savecreate'   =>array('jacl2.rights.and'=>array('auth.user.view','auth.user.create')),
        'preupdate'    =>array('jacl2.rights.and'=>array('auth.user.view','auth.user.modify')),
        'editupdate'   =>array('jacl2.rights.and'=>array('auth.user.view','auth.user.modify')),
        'saveupdate'   =>array('jacl2.rights.and'=>array('auth.user.view','auth.user.modify')),
        'deleteconfirm'=>array('jacl2.rights.and'=>array('auth.user.view','auth.user.delete')),
        'delete'       =>array('jacl2.rights.and'=>array('auth.user.view','auth.user.delete')),
        
        //'auth.user.change.password'
    );
    /**
     * selector of the dao to use for the crud.
     * @var string
     */
    protected $dao = '';

    /**
     * selector of the form to use to edit and display a record
     * @var string
     */
    protected $form ='';

    /**
     * the jDb profile to use with the dao
     */
    protected $dbProfile = '';

    protected $listPageSize = 20;

    protected $authConfig = null;
    
    protected $uploadsDirectory='';

    function __construct ($request){
        parent::__construct($request);
        $plugin = $GLOBALS['gJCoord']->getPlugin('auth');
        if ($plugin->config['driver'] == 'Db') {
            $this->authConfig = $plugin->config['Db'];
            $this->dao = $this->authConfig['dao'];
            if(isset($this->authConfig['form']))
                $this->form = $this->authConfig['form'];
            $this->dbProfile = $this->authConfig['profile'];
            if(isset($this->authConfig['uploadsDirectory']))
                $this->uploadsDirectory =  $this->authConfig['uploadsDirectory'];
        }
    }

    /**
     * you can do your own data check of a form by redefining this method.
     * You can also do some other things. It is called only if the $form->check() is ok.
     * and before the save of the data.
     * @param jFormsBase $form the current form
     * @param boolean $calltype   true for an update, false for a create
     * @return boolean true if it is ok.
     */
    protected function _checkData($form, $calltype){
        return true;
    }

    /**
     * list all records
     */
    function index(){
        $offset = $this->intParam('offset',0,true);

        $rep = $this->getResponse('html');
        
        if ($this->form == '') {
            $rep->body->assign('MAIN', 'no form defined in the auth plugin');
            return $rep;
        }

        $tpl = new jTpl();

        $dao = jDao::get($this->dao, $this->dbProfile);

        $cond = jDao::createConditions();
        $cond->addItemOrder('login', 'asc');
        $tpl->assign('list', $dao->findBy($cond,$offset,$this->listPageSize));
        
        $pk = $dao->getPrimaryKeyNames();
        $tpl->assign('primarykey', $pk[0]);

        $tpl->assign('controls', jForms::create($this->form, '___$$$___')->getControls());
        $tpl->assign('listPageSize', $this->listPageSize);
        $tpl->assign('page',$offset);
        $tpl->assign('recordCount',$dao->countAll());
        $tpl->assign('cancreate', jAcl2::check('auth.user.create'));
        $tpl->assign('canview', jAcl2::check('auth.user.view'));
        $rep->body->assign('MAIN', $tpl->fetch('crud_list'));
        jForms::destroy($this->form,  '___$$$___');
        return $rep;
    }

    /**
     * displays a record
     */
    function view(){
        $id = $this->param('id');
        if( $id === null ){
            $rep = $this->getResponse('redirect');
            $rep->action = 'default:index';
            return $rep;
        }
        $rep = $this->getResponse('html');

        // we're using a form to display a record, to have the portunity to have
        // labels with each values. We need also him to load easily values of some
        // of controls with initControlFromDao (to use in _view method).
        $form = jForms::create($this->form, $id);
        $form->initFromDao($this->dao, $id, $this->dbProfile);
        
        $tpl = new jTpl();
        $tpl->assign('id', $id);
        $tpl->assign('form',$form);
        $tpl->assign('otherInfo', jEvent::notify('jauthdbAdminGetViewInfo', array('form'=>$form, 'tpl'=>$tpl))->getResponse());
        $tpl->assign('canDelete', (jAuth::getUserSession()->login != $id) &&  jAcl2::check('auth.user.delete'));
        $tpl->assign('canUpdate', jAcl2::check('auth.user.modify'));
        $tpl->assign('canChangePass', jAcl2::check('auth.user.change.password'));
        $rep->body->assign('MAIN', $tpl->fetch('crud_view'));
        return $rep;
    }

    /**
     * prepare a form to create a record.
     */
    function precreate() {
        $form = jForms::create($this->form);
        jEvent::notify('jauthdbAdminPrepareCreate', array('form'=>$form));
        $rep = $this->getResponse('redirect');
        $rep->action = 'default:create';
        return $rep;
    }

    /**
     * display a form to create a record
     */
    function create(){
        $form = jForms::get($this->form);
        if($form == null){
            $form = jForms::create($this->form);
        }
        $rep = $this->getResponse('html');

        $tpl = new jTpl();
        $tpl->assign('id', null);
        $tpl->assign('form',$form);
        jEvent::notify('jauthdbAdminEditCreate', array('form'=>$form, 'tpl'=>$tpl));
        
        $rep->body->assign('MAIN', $tpl->fetch('crud_edit'));
        return $rep;
    }

    /**
     * save data of a form in a new record
     */
    function savecreate(){
        $form =  jForms::get($this->form);
        $form->initFromRequest();
        $rep = $this->getResponse('redirect');
        if($form == null){
            $rep->action = 'default:index';
            return $rep;
        }
        $evresp = array();
        if($form->check()  && !jEvent::notify('jauthdbAdminCheckCreateForm', array('form'=>$form))->inResponse('check', false, $evresp)){
            extract($form->prepareDaoFromControls($this->dao,null,$this->dbProfile), 
                EXTR_PREFIX_ALL, "form");

            jAuth::saveNewUser($form_daorec);

            $form->saveAllFiles($this->uploadsDirectory);
            jForms::destroy($this->form);
            
            $rep->action = 'default:view';
            $rep->params['id'] = $form_daorec->login;
            return $rep;
        } else {
            $rep->action = 'default:create';
            return $rep;
        }
    }


    /**
     * prepare a form in order to edit an existing record, and redirect to the editupdate action
     */
    function preupdate(){
        $id = $this->param('id');
        $rep = $this->getResponse('redirect');

        if( $id === null ){
            $rep->action = 'default:index';
            return $rep;
        }
        $rep->params['id'] = $id;

        $form = jForms::create($this->form, $id);

        try {
            $rec = $form->initFromDao($this->dao, null, $this->dbProfile);
            foreach($rec->getPrimaryKeyNames() as $pkn) {
                $c = $form->getControl($pkn);
                if($c !==null) {
                    $c->setReadOnly(true);
                }
            }
        }catch(Exception $e){
            $rep->action = 'default:view';
            return $rep;
        }

        jEvent::notify('jauthdbAdminPrepareUpdate', array('form'=>$form));
        $form->setReadOnly('login');

        $rep->action = 'default:editupdate';        
        return $rep;
    }


    /**
     * displays a forms to edit an existing record. The form should be
     * prepared with the preupdate before, so a refresh of the page
     * won't cause a reset of the form
     */
    function editupdate(){
        $id = $this->param('id');
        $form = jForms::get($this->form,$id);
        if( $form === null || $id === null){
            $rep = $this->getResponse('redirect');
            $rep->action = 'default:index';
            return $rep;
        }
        $rep = $this->getResponse('html');

        $tpl = new jTpl();
        $tpl->assign('id', $id);
        $tpl->assign('form',$form);
        jEvent::notify('jauthdbAdminEditUpdate', array('form'=>$form, 'tpl'=>$tpl));

        $form->setReadOnly('login');
        $rep->body->assign('MAIN', $tpl->fetch('crud_edit'));
        return $rep;
    }


    /**
     * save data of a form in a new record
     */
    function saveupdate(){
        $rep = $this->getResponse('redirect');
        $id = $this->param('id');

        $form = jForms::get($this->form,$id);
        $form->initFromRequest();

        if( $form === null || $id === null){
            $rep->action = 'default:index';
            return $rep;
        }
        $evresp = array();
        if($form->check() && !jEvent::notify('jauthdbAdminCheckUpdateForm', array('form'=>$form))->inResponse('check', false, $evresp)){
            extract($form->prepareDaoFromControls($this->dao,$id,$this->dbProfile), 
                EXTR_PREFIX_ALL, "form");

            // we call jAuth instead of using jDao, to allow jAuth to do
            // all process, events...
            jAuth::updateUser($form_daorec);

            $form->saveAllFiles($this->uploadsDirectory);
            $rep->action = 'default:view';

            jForms::destroy($this->form, $id);
        } else {
            $rep->action = 'default:editupdate';
        }
        $rep->params['id'] = $id;
        return $rep;
    }


    function confirmdelete(){
        $id = $this->param('id');
        if($id === null){
            $rep = $this->getResponse('redirect');
            $rep->action = 'default:index';
            return $rep;
        }
        $rep = $this->getResponse('html');

        $tpl = new jTpl();
        $tpl->assign('id', $id);
        $rep->body->assign('MAIN', $tpl->fetch('crud_delete'));
        return $rep;
    }

    /**
     * delete a record
     */
    function delete(){
        $id = $this->param('id');
        $pwd = $this->param('pwd_confirm');
        $rep = $this->getResponse('redirect');

        if (jAuth::verifyPassword(jAuth::getUserSession()->login, $pwd) == false) {
            jMessage::add(jLocale::get('crud.message.delete.invalid.pwd', 'error'));
            $rep->action = 'default:confirmdelete';
            $rep->params['id'] = $id;
            return $rep;
        }
        
        if( $id !== null && jAuth::getUserSession()->login != $id){
            if(jAuth::removeUser($id)) {
                jMessage::add(jLocale::get('crud.message.delete.ok'));
                $rep->action = 'default:index';
            }
            else{
                jMessage::add(jLocale::get('crud.message.delete.notok', 'error'));
                $rep->action = 'default:view';
                $rep->params['id'] = $id;
            }
        }
        else {
            jMessage::add(jLocale::get('crud.message.delete.notok', 'error'));
            $rep->action = 'default:index';
        }
        return $rep;
    }


}


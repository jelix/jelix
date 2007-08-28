<?php
/**
* @package    jelix
* @subpackage controllers
* @author     Laurent Jouanneau
* @contributor
* @copyright  2007 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*
*/

/**
 * a base class for crud controllers
 * @package    jelix
 * @subpackage controllers
 * @since 1.0b3
 */
class jControllerDaoCrud extends jController {

    /**
     * selector of the dao to use for the crud.
     * It should be filled by child controller.
     * @var string
     */
    protected $dao = '';
    
    /**
     * selector of the dao to use to edit and display a record
     * It should be filled by child controller.
     * @var string
     */
    protected $form ='';

    /**
     * list of properties to show in the list page
     * if empty list (default), it shows all properties
     * @var array
     */
    protected $propertiesForList = array();

    protected $listTemplate = '';
    
    protected $editTemplate = '';
    
    protected $readTemplate = '';

    /**
     * number of record to display in the list page
     * @var integer
     */
    protected $listPageSize = 20;

    /**
     * list all records
     */
    function index(){
        $offset = $this->intParam('first',0,true);
    
        $rep = $this->getResponse('html');
        
        $dao = jDao::get($this->dao);
        $results = $dao->findBy(jDao::createConditions(),$offset,$this->listPageSize);
        
        $tpl = new jTpl();
        $tpl->assign('list',$results);
        $rep->body->assign('MAIN', $tpl->fetch($this->listTemplate));

        return $rep;
    }
}


?>
<?php
/**
* @package    jelix
* @subpackage dao
* @author     Croes Gérald, Laurent Jouanneau
* @contributor Laurent Jouanneau
* @copyright  2001-2005 CopixTeam, 2005-2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*
* Une partie du code est issue de la classe CopixDAOSearchConditions
* du framework Copix 2.3dev20050901. http://www.copix.org
* il est sous Copyright 2001-2005 CopixTeam (licence LGPL)
* Auteurs initiaux : Gerald Croes et Laurent Jouanneau
* Adaptée et améliorée pour Jelix par Laurent Jouanneau
*/

/**
 * content a sub group of conditions
 * @package  jelix
 * @subpackage dao
 */
class jDaoCondition {
    /**
    * the parent group if any
    */
    public $parent = null;

    /**
    * the conditions in this group
    */
    public $conditions = array ();

    /**
    * the sub groups
    */
    public $group = array ();

    /**
    * the kind of group (AND/OR)
    */
    public $glueOp;

    function __construct ($glueOp='AND', $parent =null ){
        $this->parent = $parent;
        $this->glueOp = $glueOp;
    }
}

/**
 * container for all criteria of a query
 * @package  jelix
 * @subpackage dao
*/
class jDaoConditions {
    /**
    * @var jDaoCondition
    */
    public $condition;

    /**
    * the orders we wants the list to be
    */
    public $order = array ();

    /**
    * the condition we actually are browsing
    */
    private $_currentCondition;

    function __construct ($glueOp = 'AND'){
        $this->condition = new jDaoCondition ($glueOp);
        $this->_currentCondition = $this->condition;
    }

    function addItemOrder($field_id, $way='ASC'){
        $this->order[$field_id]=$way;
    }

    /**
    * says if the condition is empty
    */
    function isEmpty (){
        return (count ($this->condition->group) == 0) &&
        (count ($this->condition->conditions) == 0) &&
        (count ($this->order) == 0) ;
    }

    /**
    * starts a condition group
    */
    function startGroup ($glueOp = 'AND'){
        $cond= new jDaoCondition ($glueOp, $this->_currentCondition);
        $this->_currentCondition->group[] = $cond;
        $this->_currentCondition = $cond;
    }

    /**
    * ends a condition group
    */
    function endGroup (){
        if ($this->_currentCondition->parent !== null){
            $this->_currentCondition = $this->_currentCondition->parent;
        }
    }

    /**
    * adds a condition
    */
    function addCondition ($field_id, $operator, $value, $expr = false){
        $this->_currentCondition->conditions[] = array (
           'field_id'=>$field_id,
           'value'=>$value,
           'operator'=>$operator, 'expr'=>$expr);
    }

}


?>
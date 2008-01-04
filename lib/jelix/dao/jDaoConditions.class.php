<?php
/**
* @package    jelix
* @subpackage dao
* @author     Croes Gérald, Laurent Jouanneau
* @contributor Laurent Jouanneau
* @copyright  2001-2005 CopixTeam, 2005-2006 Laurent Jouanneau
* This classes was get originally from the Copix project (CopixDAOSearchConditions, Copix 2.3dev20050901, http://www.copix.org)
* Some lines of code are copyrighted 2001-2005 CopixTeam (LGPL licence).
* Initial authors of this Copix classes are Gerald Croes and Laurent Jouanneau,
* and this classes was adapted for Jelix by Laurent Jouanneau
*
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
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

    /**
     * @param string $glueOp the logical operator which links each conditions : AND or OR
     */
    function __construct ($glueOp = 'AND'){
        $this->condition = new jDaoCondition ($glueOp);
        $this->_currentCondition = $this->condition;
    }

    /**
     * add an order clause
     * @param string $field_id   the property name used to order results
     * @param string $way        the order type : asc or desc
     */
    function addItemOrder($field_id, $way='ASC'){
        $this->order[$field_id]=$way;
    }

    /**
    * says if there are no conditions nor order
    * @return boolean  false if there isn't condition
    */
    function isEmpty (){
        return (count ($this->condition->group) == 0) &&
        (count ($this->condition->conditions) == 0) &&
        (count ($this->order) == 0) ;
    }

    /**
    * says if there are no conditions
    * @return boolean  false if there isn't condition
    * @since 1.0
    */
    function hasConditions (){
        return (count ($this->condition->group) || count ($this->condition->conditions));
    }

    /**
    * starts a new condition group
    * @param string $glueOp the logical operator which links each conditions in the group : AND or OR
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
    * @param string $field_id  the property name on which the condition applies
    * @param string $operator  the sql operator
    * @param string $value     the value which is compared to the property
    * @param boolean $foo      parameter for internal use : don't use it or set to false
    */
    function addCondition ($field_id, $operator, $value, $foo = false){
        $this->_currentCondition->conditions[] = array (
           'field_id'=>$field_id,
           'value'=>$value,
           'operator'=>$operator, 'isExpr'=>$foo);
    }
}
?>
<?php
/**
* @package     jelix
* @subpackage  forms
* @author      Laurent Jouanneau
* @contributor
* @copyright   2006-2009 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/


/**
 * choice
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlChoice extends jFormsControlGroups {

    public $type="choice";

    /**
     * list of item. Each value is an array which contains corresponding controls of the item
     * an item could not have controls, in this case its value is an empty array
     */
    public $items = array();

    public $itemsNames = array();

    function check(){
        $val = $this->container->data[$this->ref];
        if($val !== "" && $val !== null && isset($this->items[$val])) {
            $rv = null;
            foreach($this->items[$val] as $ctrl) {
                if (($rv2 = $ctrl->check()) !== null) {
                    $rv = $rv2;
                }
            }
            return $rv;
        } else if ($this->required) {
            return $this->container->errors[$this->ref] = jForms::ERRDATA_INVALID;
        }
        return null;
    }

    function createItem($value, $label) {
        $this->items[$value] = array();
        $this->itemsNames[$value]= $label;
    }

    function addChildControl($control, $itemValue = '') {
        $this->childControls[$control->ref] = $control;
        $this->items[$itemValue][$control->ref] = $control;
    }

    function setData($value) {
        parent::setData($value);
        // we deactivate controls which are not selected
        foreach($this->items as $item => $list) {
            $ro = ($item != $value);
            foreach($list as $ref=>$ctrl) {
                $this->form->setReadOnly($ref, $ro);
            }
        }
    }

    function setValueFromRequest($request) {
        $this->setData($request->getParam($this->ref,''));
        if(isset($this->items[$this->container->data[$this->ref]])){
            foreach($this->items[$this->container->data[$this->ref]] as $name=>$ctrl) {
                $ctrl->setValueFromRequest($request);
            }
        }
    }
}


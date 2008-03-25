<?php
/**
* @package     jelix
* @subpackage  forms
* @author      Laurent Jouanneau
* @contributor
* @copyright   2006-2007 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * this object is a container for form data
 * @package     jelix
 * @subpackage  forms
 */
class jFormsDataContainer {
    /**
     * @var array
     */
    public $data = array();
    /**
     * the instance id of the form
     * @var string 
     */
    public $formId;
    /**
     * the selector of the xml file of the form
     * @var jSelectorForm
     */
    public $formSelector;

    /**
     * list of errors detected in data
     * @var array
     */
    public $errors = array();

    /**
     *
     * @param jSelectorForm $formSelector
     * @param string $formId
     */
    function __construct($formSelector,$formId){
        $this->formId = $formId;
        $this->formSelector =$formSelector;
    }

    function unsetData($name){
        unset($this->data[$name]);
    }

    function clear(){
        $this->data = array();
        $this->errors = array();
    }
}

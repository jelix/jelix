<?php
/**
* @package     jelix
* @subpackage  forms
* @author      Julien Issler
* @contributor
* @copyright   2008 Julien Issler
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 *
 * @package     jelix
 * @subpackage  forms
 */
class jFormsControlDatetime extends jFormsControlDate {
    public $type = 'datetime';
    public $enableSeconds = false;

    public function __construct($ref){
        $this->ref = $ref;
        $this->datatype = new jDatatypeDateTime();
    }

    function setValueFromRequest($request) {
        $value = $request->getParam($this->ref,'');
        if($value['year'] === '' && $value['month'] === '' && $value['day'] === '' && $value['hour'] === '' && $value['minutes'] === '' && (!$this->enableSeconds || $value['seconds'] === ''))
            $this->setData('');
        else{
            if($value['seconds']==='')
                $value['seconds'] = '00';
            $this->setData($value['year'].'-'.$value['month'].'-'.$value['day'].' '.$value['hour'].':'.$value['minutes'].':'.$value['seconds']);
        }
    }
}
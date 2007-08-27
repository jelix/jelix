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
 * Interface for objects which provides a source of data to fill some controls in a form,
 * like menulist, listbox etc...
 * @package     jelix
 * @subpackage  forms
 */
interface jIFormDatasource {
    /**
     * load and returns data to fill a control. The returned array should be 
     * an associative array  key => label
     * @return array the datas
     */
    public function getDatas();

    /**
     * Return the label corresponding to the given key
     * @param string $key the key 
     * @return string the label
     */
    public function getLabel($key);
}

/**
 * A datasource which is based on static values.
 * @package     jelix
 * @subpackage  forms
 */
class jFormStaticDatasource implements jIFormDatasource {
    /**
     * associative array which contains keys and labels
     * @var array
     */
    public $datas = array();

    public function getDatas(){
        return $this->datas;
    }

    public function getLabel($key){
        if(isset($this->datas[$key]))
            return $this->datas[$key];
        else
            return null;
    }
}


/**
 * A datasource which is based on a dao
 * @package     jelix
 * @subpackage  forms
 */
class jFormDaoDatasource implements jIFormDatasource {

    protected $selector;
    protected $method;
    protected $labelProperty;
    protected $keyProperty;

    protected $dao = null;

    function __construct ($selector ,$method , $label, $key){
        $this->selector  = $selector;
        $this->method = $method ;
        $this->labelProperty = $label;
        if($key == ''){
            $rec = jDao::createRecord($this->selector);
            $pfields = $rec->getPrimaryKeyNames();
            $key = $pfields[0];
        }
        $this->keyProperty = $key;
    }

    public function getDatas(){
        if($this->dao === null) $this->dao = jDao::get($this->selector);
        $found = $this->dao->{$this->method}();
        $result=array();
        foreach($found as $obj){
            $result[$obj->{$this->keyProperty}] = $obj->{$this->labelProperty};
        }
        return $result;
    }

    public function getLabel($key){
        if($this->dao === null) $this->dao = jDao::get($this->selector);
        $rec = $this->dao->get($key);
        if($rec)
            return $rec->{$this->labelProperty};
        else
            return null;
    }

}

?>
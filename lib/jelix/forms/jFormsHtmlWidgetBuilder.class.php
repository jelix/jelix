<?php
/**
* @package     jelix
* @subpackage  forms
* @author      Laurent Jouanneau
* @contributor Julien Issler, Dominique Papin, Claudio Bernardes
* @copyright   2006-2011 Laurent Jouanneau
* @copyright   2008-2011 Julien Issler, 2008 Dominique Papin
* @copyright   2012 Claudio Bernardes
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * HTML form builder
 * @package     jelix
 * @subpackage  jelix-plugins
 * @link http://developer.jelix.org/wiki/rfc/jforms-controls-plugins
 */
class jFormsWidgetBuilder {

}

abstract class jFormsHtmlWidgetBuilder extends jFormsWidgetBuilder  {
    protected $builder;
    protected $ctrl;
    protected $jsContent;
    protected $_endt = '/>';
    protected $jFormsJsVarName;
    protected $isRootControl;
    
    public function getIsRootControl() {
        return $this->isRootControl;
    }

    protected function __construct(&$ctrl, &$builder) {
        $this->builder = $builder;
        $this->ctrl = $ctrl;
        $this->jFormsJsVarName = $builder->getjFormsJsVarName();
        $this->isRootControl = $builder->getIsRootControl();
    }

    protected function getClass() { }

    protected function getId() { return $this->builder->getName().'_'.$this->ctrl->ref; }

    protected function getLabelAttributes() {
        $attr = array();
        
        $attr['hint'] = ($this->ctrl->hint == '' ? '' : ' title="'.htmlspecialchars($this->ctrl->hint).'"');
        $attr['idLabel'] = ' id="'.$this->getId().'_label"';
        $attr['reqHtml'] = ($this->ctrl->required == true?'<span class="jforms-required-star">*</span>':'');
        $attr['class'] = 'jforms-label';
        $attr['class'] .= (isset($this->builder->getForm()->getContainer()->errors[$this->ctrl->ref]) ?' jforms-error':'');
        $attr['class'] .= ($this->ctrl->required == false || $this->ctrl->isReadOnly()?'':' jforms-required');

        return $attr;
    }
    
    protected function getControlAttributes() {
        $attr = array();

        $ro = $this->ctrl->isReadOnly();
        $attr['name'] = $this->ctrl->ref;
        $attr['id'] = $this->builder->getName().'_'.$this->ctrl->ref;

        if ($ro)
            $attr['readonly'] = 'readonly';
        else
            unset($attr['readonly']);
        if (!isset($attr['title']) && $this->ctrl->hint) {
            $attr['title'] = $this->ctrl->hint;
        }

        $class = 'jforms-ctrl-'.$this->ctrl->type;
        $class .= ($this->ctrl->required == false || $ro?'':' jforms-required');
        $class .= (isset($this->builder->getForm()->getContainer()->errors[$this->ctrl->ref]) ?' jforms-error':'');
        $class .= ($ro && $this->ctrl->type != 'captcha'?' jforms-readonly':'');
        if (isset($attr['class']))
            $attr['class'].= ' '.$class;
        else
            $attr['class'] = $class;

        return $attr;
    }
    
    protected function getCommonJS() {
        if($this->ctrl->required){
            $this->jsContent .= "c.required = true;\n";
            if($this->ctrl->alertRequired){
                $this->jsContent .= "c.errRequired=". $this->escJsStr($this->ctrl->alertRequired).";\n";
            }
            else {
                $this->jsContent .= "c.errRequired=".$this->escJsStr(jLocale::get('jelix~formserr.js.err.required', $this->ctrl->label)).";\n";
            }
        }

        if($this->ctrl->alertInvalid){
            $this->jsContent .= "c.errInvalid=".$this->escJsStr($this->ctrl->alertInvalid).";\n";
        }
        else {
            $this->jsContent .= "c.errInvalid=".$this->escJsStr(jLocale::get('jelix~formserr.js.err.invalid', $this->ctrl->label)).";\n";
        }

        if ($this->isRootControl) $this->jsContent .= $this->jFormsJsVarName.".tForm.addControl(c);\n";
        
    }
    
    protected function escJsStr($str) {
        return '\''.str_replace(array("'","\n"),array("\\'", "\\n"), $str).'\'';
    }
    
    protected function _outputAttr(&$attributes) {
        foreach($attributes as $name=>$val) {
            echo ' '.$name.'="'.htmlspecialchars($val).'"';
        }
    }
  
    abstract function outputLabel();
    abstract function outputHelp();
    abstract function outputJs();
    abstract function outputControl();
}

class dateHtmlWidgetBuilder extends jFormsHtmlWidgetBuilder {
    public function __construct(&$ctrl, &$builder) {
        parent::__construct($ctrl, $builder);
    }
    
    function outputLabel() {
        $attr = $this->getLabelAttributes();
        echo '<span class="',$attr['class'],'"',$attr['idLabel'],$attr['hint'],'>',htmlspecialchars($this->ctrl->label),$attr['reqHtml'],"</span>\n";
    }
    
    function outputControl() {
        $attr = $this->getControlAttributes();

        $attr['id'] = $this->builder->getName().'_'.$this->ctrl->ref.'_';
        
        $v = array('year'=>'','month'=>'','day'=>'');
        if(preg_match('#^(\d{4})?-(\d{2})?-(\d{2})?$#',$this->builder->getForm()->getData($this->ctrl->ref),$matches)){
            if(isset($matches[1]))
                $v['year'] = $matches[1];
            if(isset($matches[2]))
                $v['month'] = $matches[2];
            if(isset($matches[3]))
                $v['day'] = $matches[3];
        }
        $f = jLocale::get('jelix~format.date');
        for($i=0;$i<strlen($f);$i++){
            if($f[$i] == 'Y')
                $this->_outputDateControlYear($this->ctrl, $attr, $v['year']);
            else if($f[$i] == 'm')
                $this->_outputDateControlMonth($this->ctrl, $attr, $v['month']);
            else if($f[$i] == 'd')
                $this->_outputDateControlDay($this->ctrl, $attr, $v['day']);
            else
                echo ' ';
        }
    }
    
    function outputHelp() {}
    
    function outputJs() {
        $this->jsContent .= "c = new ".$this->jFormsJsVarName."ControlDate('".$this->ctrl->ref."', ".$this->escJsStr($this->ctrl->label).");\n";
        $this->jsContent .= "c.multiFields = true;\n";
        $minDate = $this->ctrl->datatype->getFacet('minValue');
        $maxDate = $this->ctrl->datatype->getFacet('maxValue');
        if($minDate)
            $this->jsContent .= "c.minDate = '".$minDate->toString(jDateTime::DB_DFORMAT)."';\n";
        if($maxDate)
            $this->jsContent .= "c.maxDate = '".$maxDate->toString(jDateTime::DB_DFORMAT)."';\n";
        $this->getCommonJs();
    }
    
    protected function _outputDateControlDay($ctrl, $attr, $value){
        $attr['name'] = $this->ctrl->ref.'[day]';
        $attr['id'] .= 'day';
        if(jApp::config()->forms['controls.datetime.input'] == 'textboxes'){
            $attr['value'] = $value;
            echo '<input type="text" size="2" maxlength="2"';
            $this->_outputAttr($attr);
            echo $this->_endt;
        }
        else{
            echo '<select';
            $this->_outputAttr($attr);
            echo '><option value="">'.htmlspecialchars(jLocale::get('jelix~jforms.date.day.label')).'</option>';
            for($i=1;$i<32;$i++){
                $k = ($i<10)?'0'.$i:$i;
                echo '<option value="'.$k.'"'.($k == $value?' selected="selected"':'').'>'.$k.'</option>';
            }
            echo '</select>';
        }
    }

    protected function _outputDateControlMonth($ctrl, $attr, $value){
        $attr['name'] = $this->ctrl->ref.'[month]';
        $attr['id'] .= 'month';
        if(jApp::config()->forms['controls.datetime.input'] == 'textboxes') {
            $attr['value'] = $value;
            echo '<input type="text" size="2" maxlength="2"';
            $this->_outputAttr($attr);
            echo $this->_endt;
        }
        else{
            $monthLabels = jApp::config()->forms['controls.datetime.months.labels'];
            echo '<select';
            $this->_outputAttr($attr);
            echo '><option value="">'.htmlspecialchars(jLocale::get('jelix~jforms.date.month.label')).'</option>';
            for($i=1;$i<13;$i++){
                $k = ($i<10)?'0'.$i:$i;
                if($monthLabels == 'names')
                    $l = htmlspecialchars(jLocale::get('jelix~date_time.month.'.$k.'.label'));
                else if($monthLabels == 'shortnames')
                    $l = htmlspecialchars(jLocale::get('jelix~date_time.month.'.$k.'.shortlabel'));
                else
                    $l = $k;
                echo '<option value="'.$k.'"'.($k == $value?' selected="selected"':'').'>'.$l.'</option>';
            }
            echo '</select>';
        }
    }

    protected function _outputDateControlYear($ctrl, $attr, $value){
        $attr['name'] = $this->ctrl->ref.'[year]';
        $attr['id'] .= 'year';
        if(jApp::config()->forms['controls.datetime.input'] == 'textboxes') {
            $attr['value'] = $value;
            echo '<input type="text" size="4" maxlength="4"';
            $this->_outputAttr($attr);
            echo $this->_endt;
        }
        else{
            $minDate = $ctrl->datatype->getFacet('minValue');
            $maxDate = $ctrl->datatype->getFacet('maxValue');
            if($minDate && $maxDate){
                echo '<select';
                $this->_outputAttr($attr);
                echo '><option value="">'.htmlspecialchars(jLocale::get('jelix~jforms.date.year.label')).'</option>';
                for($i=$minDate->year;$i<=$maxDate->year;$i++)
                    echo '<option value="'.$i.'"'.($i == $value?' selected="selected"':'').'>'.$i.'</option>';
                echo '</select>';
            }
            else{
                $attr['value'] = $value;
                echo '<input type="text" size="4" maxlength="4"';
                $this->_outputAttr($attr);
                echo $this->_endt;
            }
        }
    }

}



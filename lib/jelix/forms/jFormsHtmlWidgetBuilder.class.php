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

    public function __construct($args) {
        $this->ctrl = $args[0];
        $this->builder = $args[1];

        $this->jFormsJsVarName = $this->builder->getjFormsJsVarName();
        $this->isRootControl = $this->builder->getIsRootControl();
    }
    
    public function getIsRootControl() {
        return $this->isRootControl;
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
    
    protected function _escJsStr($str) {
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


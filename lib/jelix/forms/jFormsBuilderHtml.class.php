<?php
/**
* @package     jelix
* @subpackage  forms
* @author      Laurent Jouanneau
* @contributor Julien Issler, Dominique Papin
* @copyright   2006-2012 Laurent Jouanneau
* @copyright   2008-2011 Julien Issler, 2008 Dominique Papin
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 *
 */
require(JELIX_LIB_PATH.'forms/jFormsHtmlWidgetBuilder.class.php');

/**
 * HTML form builder
 * @package     jelix
 * @subpackage  jelix-plugins
 */
class jFormsBuilderHtml extends jFormsBuilderBase {
    protected $formType = '_html';

    protected $jFormsJsVarName = 'jForms';

    protected $options;

    protected $isRootControl = true;

    public function getjFormsJsVarName() {
        return $this->jFormsJsVarName;
    }
    
    public function getIsRootControl() {
        return $this->isRootControl;
    }
    
    public function outputAllControls() {

        echo '<table class="jforms-table" border="0">';
        foreach( $this->_form->getRootControls() as $ctrlref=>$ctrl){
            if($ctrl->type == 'submit' || $ctrl->type == 'reset' || $ctrl->type == 'hidden') continue;
            if(!$this->_form->isActivated($ctrlref)) continue;
            if($ctrl->type == 'group') {
                echo '<tr><td colspan="2">';
                $this->outputControl($ctrl);
                echo '</td></tr>';
            }else{
                echo '<tr><th scope="row">';
                $this->outputControlLabel($ctrl);
                echo '</th><td>';
                $this->outputControl($ctrl);
                echo "</td></tr>\n";
            }
        }
        echo '</table> <div class="jforms-submit-buttons">';
        if ( $ctrl = $this->_form->getReset() ) {
            if(!$this->_form->isActivated($ctrl->ref)) continue;
            $this->outputControl($ctrl);
            echo ' ';
        }
        foreach( $this->_form->getSubmits() as $ctrlref=>$ctrl){
            if(!$this->_form->isActivated($ctrlref)) continue;
            $this->outputControl($ctrl);
            echo ' ';
        }
        echo "</div>\n";
    }

    public function outputMetaContent($t) {
        $resp= jApp::coord()->response;
        if($resp === null || $resp->getType() !='html'){
            return;
        }
        $config = jApp::config();
        $www = $config->urlengine['jelixWWWPath'];
        $bp = $config->urlengine['basePath'];
        $resp->addJSLink($www.'js/jforms_light.js');
        $resp->addCSSLink($www.'design/jform.css');
        $heConf = &$config->htmleditors;
        foreach($t->_vars as $k=>$v){
            if($v instanceof jFormsBase && count($edlist = $v->getHtmlEditors())) {
                foreach($edlist as $ed) {

                    if(isset($heConf[$ed->config.'.engine.file'])){
                        $file = $heConf[$ed->config.'.engine.file'];
                        if(is_array($file)){
                            foreach($file as $url) {
                                $resp->addJSLink($bp.$url);
                            }
                        }else
                            $resp->addJSLink($bp.$file);
                    }

                    if(isset($heConf[$ed->config.'.config']))
                        $resp->addJSLink($bp.$heConf[$ed->config.'.config']);

                    $skin = $ed->config.'.skin.'.$ed->skin;
                    if(isset($heConf[$skin]) && $heConf[$skin] != '')
                        $resp->addCSSLink($bp.$heConf[$skin]);
                }
            }
        }
    }

    protected function outputHeaderScript(){
                echo '<script type="text/javascript">
//<![CDATA[
'.$this->jFormsJsVarName.'.tForm = new jFormsForm(\''.$this->_name.'\');
'.$this->jFormsJsVarName.'.tForm.setErrorDecorator(new '.$this->options['errorDecorator'].'());
'.$this->jFormsJsVarName.'.declareForm(jForms.tForm);
//]]>
</script>';
    }

    /**
     * output the header content of the form
     * @param array $params some parameters <ul>
     *      <li>"errDecorator"=>"name of your javascript object for error listener"</li>
     *      <li>"method" => "post" or "get". default is "post"</li>
     *      </ul>
     */
    public function outputHeader($params){
        $this->options = array_merge(array('errorDecorator'=>$this->jFormsJsVarName.'ErrorDecoratorHtml',
            'method'=>'post'), $params);
        if (isset($params['attributes']))
            $attrs = $params['attributes'];
        else
            $attrs = array();

        echo '<form';
        if (preg_match('#^https?://#',$this->_action)) {
            $urlParams = $this->_actionParams;
            $attrs['action'] = $this->_action;
        } else {
            $url = jUrl::get($this->_action, $this->_actionParams, 2); // returns the corresponding jurl
            $urlParams = $url->params;
            $attrs['action'] = $url->getPath();
        }
        $attrs['method'] = $this->options['method'];
        $attrs['id'] = $this->_name;

        if($this->_form->hasUpload())
            $attrs['enctype'] = "multipart/form-data";

        $this->_outputAttr($attrs);
        echo '>';

        $this->outputHeaderScript();

        $hiddens = '';
        foreach ($urlParams as $p_name => $p_value) {
            $hiddens .= '<input type="hidden" name="'. $p_name .'" value="'. htmlspecialchars($p_value). '"'.$this->_endt. "\n";
        }

        foreach ($this->_form->getHiddens() as $ctrl) {
            if(!$this->_form->isActivated($ctrl->ref)) continue;
            $hiddens .= '<input type="hidden" name="'. $ctrl->ref.'" id="'.$this->_name.'_'.$ctrl->ref.'" value="'. htmlspecialchars($this->_form->getData($ctrl->ref)). '"'.$this->_endt. "\n";
        }

        if($this->_form->securityLevel){
            $tok = $this->_form->createNewToken();
            $hiddens .= '<input type="hidden" name="__JFORMS_TOKEN__" value="'.$tok.'"'.$this->_endt. "\n";
        }

        if($hiddens){
            echo '<div class="jforms-hiddens">',$hiddens,'</div>';
        }

        $errors = $this->_form->getContainer()->errors;
        if(count($errors)){
            $ctrls = $this->_form->getControls();
            echo '<ul id="'.$this->_name.'_errors" class="jforms-error-list">';
            $errRequired='';
            foreach($errors as $cname => $err){
                if(!$this->_form->isActivated($ctrls[$cname]->ref)) continue;
                if ($err === jForms::ERRDATA_REQUIRED) {
                    if ($ctrls[$cname]->alertRequired){
                        echo '<li>', $ctrls[$cname]->alertRequired,'</li>';
                    }
                    else {
                        echo '<li>', jLocale::get('jelix~formserr.js.err.required', $ctrls[$cname]->label),'</li>';
                    }
                }else if ($err === jForms::ERRDATA_INVALID) {
                    if($ctrls[$cname]->alertInvalid){
                        echo '<li>', $ctrls[$cname]->alertInvalid,'</li>';
                    }else{
                        echo '<li>', jLocale::get('jelix~formserr.js.err.invalid', $ctrls[$cname]->label),'</li>';
                    }
                }
                elseif ($err === jForms::ERRDATA_INVALID_FILE_SIZE) {
                    echo '<li>', jLocale::get('jelix~formserr.js.err.invalid.file.size', $ctrls[$cname]->label),'</li>';
                }
                elseif ($err === jForms::ERRDATA_INVALID_FILE_TYPE) {
                    echo '<li>', jLocale::get('jelix~formserr.js.err.invalid.file.type', $ctrls[$cname]->label),'</li>';
                }
                elseif ($err === jForms::ERRDATA_FILE_UPLOAD_ERROR) {
                    echo '<li>', jLocale::get('jelix~formserr.js.err.file.upload', $ctrls[$cname]->label),'</li>';
                }
                elseif ($err != '') {
                    echo '<li>', $err,'</li>';
                }
            }
            echo '</ul>';
        }
    }

    protected $jsContent = '';

    protected $lastJsContent = '';

    public function outputFooter(){
        echo '<script type="text/javascript">
//<![CDATA[
(function(){var c, c2;
'.$this->jsContent.$this->lastJsContent.'
})();
//]]>
</script>';
        echo '</form>';
    }

    public function outputControlLabel($ctrl){
        $pluginName = $ctrl->type . $this->formType;
        $className = $pluginName . 'FormWidget';

        $plugin = jApp::loadPlugin($pluginName, 'formwidget', '.formwidget.php', $className, array($ctrl, $this));
        if (!is_null($plugin)) {
            $plugin->outputLabel();
        } else { //To remove when the migration is complete
            if($ctrl->type == 'hidden' || $ctrl->type == 'group' || $ctrl->type == 'button') return;
            $required = ($ctrl->required == false || $ctrl->isReadOnly()?'':' jforms-required');
            $reqhtml = ($required?'<span class="jforms-required-star">*</span>':'');
            $inError = (isset($this->_form->getContainer()->errors[$ctrl->ref]) ?' jforms-error':'');
            $hint = ($ctrl->hint == ''?'':' title="'.htmlspecialchars($ctrl->hint).'"');
            $id = $this->_name.'_'.$ctrl->ref;
            $idLabel = ' id="'.$id.'_label"';
            if($ctrl->type == 'output' || $ctrl->type == 'checkboxes' || $ctrl->type == 'radiobuttons' || $ctrl->type == 'date' || $ctrl->type == 'datetime' || $ctrl->type == 'choice'){
                echo '<span class="jforms-label',$required,$inError,'"',$idLabel,$hint,'>',htmlspecialchars($ctrl->label),$reqhtml,"</span>\n";
            }else if($ctrl->type != 'submit' && $ctrl->type != 'reset'){
                echo '<label class="jforms-label',$required,$inError,'" for="',$id,'"',$idLabel,$hint,'>',htmlspecialchars($ctrl->label),$reqhtml,"</label>\n";
            }
        }
    }

    public function outputControl($ctrl, $attributes=array()){
        $pluginName = $ctrl->type . $this->formType;
        $className = $pluginName . 'FormWidget';

        $plugin = jApp::loadPlugin($pluginName, 'formwidget', '.formwidget.php', $className, array($ctrl, $this));
        if (!is_null($plugin)) {
            $plugin->outputControl();
            $plugin->outputHelp();
            $this->jsContent .= $plugin->getJs(); //the js content for the control, it's displayed at the form footer
            $this->lastJsContent .= $plugin->getLastJs();
            
        } else { //To remove when the migration is complete
            if($ctrl->type == 'hidden') return;
            $ro = $ctrl->isReadOnly();
            $attributes['name'] = $ctrl->ref;
            $attributes['id'] = $this->_name.'_'.$ctrl->ref;
    
            if ($ro)
                $attributes['readonly'] = 'readonly';
            else
                unset($attributes['readonly']);
            if (!isset($attributes['title']) && $ctrl->hint) {
                $attributes['title'] = $ctrl->hint;
            }
    
            $class = 'jforms-ctrl-'.$ctrl->type;
            $class .= ($ctrl->required == false || $ro?'':' jforms-required');
            $class .= (isset($this->_form->getContainer()->errors[$ctrl->ref]) ?' jforms-error':'');
            $class .= ($ro && $ctrl->type != 'captcha'?' jforms-readonly':'');
            if (isset($attributes['class']))
                $attributes['class'].= ' '.$class;
            else
                $attributes['class'] = $class;

            $this->{'output'.$ctrl->type}($ctrl, $attributes);
            echo "\n";
            $this->{'js'.$ctrl->type}($ctrl);
            $this->outputHelp($ctrl);
        }
    }

    protected function _outputAttr(&$attributes) {
        foreach($attributes as $name=>$val) {
            echo ' '.$name.'="'.htmlspecialchars($val).'"';
        }
    }

    protected function escJsStr($str) {
        return '\''.str_replace(array("'","\n"),array("\\'", "\\n"), $str).'\'';
    }

    protected function commonJs($ctrl) {

        if($ctrl->required){
            $this->jsContent .="c.required = true;\n";
            if($ctrl->alertRequired){
                $this->jsContent .="c.errRequired=".$this->escJsStr($ctrl->alertRequired).";\n";
            }
            else {
                $this->jsContent .="c.errRequired=".$this->escJsStr(jLocale::get('jelix~formserr.js.err.required', $ctrl->label)).";\n";
            }
        }

        if($ctrl->alertInvalid){
            $this->jsContent .="c.errInvalid=".$this->escJsStr($ctrl->alertInvalid).";\n";
        }
        else {
            $this->jsContent .="c.errInvalid=".$this->escJsStr(jLocale::get('jelix~formserr.js.err.invalid', $ctrl->label)).";\n";
        }

        if ($this->isRootControl) $this->jsContent .= $this->jFormsJsVarName.".tForm.addControl(c);\n";
    }

    protected function outputChoice($ctrl, &$attr) {
        echo '<ul class="jforms-choice jforms-ctl-'.$ctrl->ref.'" >',"\n";

        $value = $this->_form->getData($ctrl->ref);
        if(is_array($value)){
            if(isset($value[0]))
                $value = $value[0];
            else
                $value='';
        }

        $i=0;
        $attr['name'] = $ctrl->ref;
        $id = $this->_name.'_'.$ctrl->ref.'_';
        $attr['type']='radio';
        unset($attr['class']);
        $readonly = (isset($attr['readonly']) && $attr['readonly']!='');

        $this->jsChoiceInternal($ctrl);
        $this->jsContent .="c2 = c;\n";
        $this->isRootControl = false;
        foreach( $ctrl->items as $itemName=>$listctrl){
            if (!$ctrl->isItemActivated($itemName))
                continue;
            echo '<li><label><input';
            $attr['id'] = $id.$i;
            $attr['value'] = $itemName;
            if ($itemName==$value)
                $attr['checked'] = 'checked';
            else
                unset($attr['checked']);
            $this->_outputAttr($attr);
            echo ' onclick="'.$this->jFormsJsVarName.'.getForm(\'',$this->_name,'\').getControl(\'',$ctrl->ref,'\').activate(\'',$itemName,'\')"', $this->_endt;
            echo htmlspecialchars($ctrl->itemsNames[$itemName]),"</label>\n";

            $displayedControls = false;
            foreach($listctrl as $ref=>$c) {
                if(!$this->_form->isActivated($ref) || $c->type == 'hidden') continue;
                $displayedControls = true;
                echo ' <span class="jforms-item-controls">';
                $this->outputControlLabel($c);
                echo ' ';
                $this->outputControl($c);
                echo "</span>\n";
                $this->jsContent .="c2.addControl(c, ".$this->escJsStr($itemName).");\n";
            }
            if(!$displayedControls) {
                $this->jsContent .="c2.items[".$this->escJsStr($itemName)."]=[];\n";
            }

            echo "</li>\n";
            $i++;
        }
        echo "</ul>\n";
        $this->isRootControl = true;
    }

    protected function jsChoice($ctrl) {
        $value = $this->_form->getData($ctrl->ref);
        if(is_array($value)){
            if(isset($value[0]))
                $value = $value[0];
            else
                $value='';
        }
        $this->jsContent .= "c2.activate('".$value."');\n";
    }

    protected function jsChoiceInternal($ctrl) {

        $this->jsContent .="c = new ".$this->jFormsJsVarName."ControlChoice('".$ctrl->ref."', ".$this->escJsStr($ctrl->label).");\n";

        $this->commonJs($ctrl);
    }

    protected function outputHelp($ctrl) {
        if ($ctrl->help) {
            if($ctrl->type == 'checkboxes' || ($ctrl->type == 'listbox' && $ctrl->multiple)){
                $name=$ctrl->ref.'[]';
            }else{
                $name=$ctrl->ref;
            }
            // additionnal &nbsp, else background icon is not shown in webkit
            echo '<span class="jforms-help" id="'. $this->_name.'_'.$ctrl->ref.'-help">&nbsp;<span>'.htmlspecialchars($ctrl->help).'</span></span>';
        }
    }
}

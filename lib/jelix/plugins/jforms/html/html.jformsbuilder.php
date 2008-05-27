<?php
/**
* @package     jelix
* @subpackage  forms
* @author      Laurent Jouanneau
* @contributor Julien Issler
* @copyright   2006-2008 Laurent Jouanneau
* @copyright   2008 Julien Issler
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * HTML form builder
 * @package     jelix
 * @subpackage  jelix-plugins
 */
abstract class htmlJformsBuilder extends jFormsBuilderBase {

    public function outputAllControls() {

        echo '<table class="jforms-table" border="0">';
        foreach( $this->_form->getControls() as $ctrlref=>$ctrl){
            if($ctrl->type == 'submit' || $ctrl->type == 'reset' || $ctrl->type == 'hidden') continue;
            if(!$this->_form->isActivated($ctrlref)) continue;
            echo '<tr><th scope="row">';
            $this->outputControlLabel($ctrl);
            echo '</th><td>';
            $this->outputControl($ctrl);
            echo '</td></tr>';
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
        echo '</div>';
    }

    public function outputMetaContent($t) {
        global $gJCoord, $gJConfig;
        $resp= $gJCoord->response;
        if($resp === null || $resp->getType() !='html'){
            return;
        }
        $www =$gJConfig->urlengine['jelixWWWPath'];
        $bp =$gJConfig->urlengine['basePath'];
        $resp->addJSLink($www.'js/jforms.js');
        $resp->addCSSLink($www.'design/jform.css');
        foreach($t->_vars as $k=>$v){
            if($v instanceof jFormsBase && count($edlist = $v->getHtmlEditors())) {
                foreach($edlist as $ed) {
                    if(isset($gJConfig->htmleditors[$ed->config.'.engine.file'])){
                        if(is_array($gJConfig->htmleditors[$ed->config.'.engine.file'])){
                            foreach($gJConfig->htmleditors[$ed->config.'.engine.file'] as $url) {
                                $resp->addJSLink($bp.$url);
                            }
                        }else
                            $resp->addJSLink($bp.$gJConfig->htmleditors[$ed->config.'.engine.file']);
                    }
                    if(isset($gJConfig->htmleditors[$ed->config.'.config']))
                        $resp->addJSLink($bp.$gJConfig->htmleditors[$ed->config.'.config']);
                    if(isset($gJConfig->htmleditors[$ed->config.'.skin.'.$ed->skin]))
                        $resp->addCSSLink($bp.$gJConfig->htmleditors[$ed->config.'.skin.'.$ed->skin]);
                }
            }
        }
    }

    /**
     * output the header content of the form
     * @param array $params some parameters 0=>name of the javascript error decorator
     *    1=> name of the javascript help decorator
     *    2=> name of method
     */
    public function outputHeader($params){
        $url = jUrl::get($this->_action, $this->_actionParams, 2); // retourne le jurl correspondant
        $method = (strtolower($params[2])=='get')?'get':'post';
        echo '<form action="',$url->getPath(),'" method="'.$method.'" id="', $this->_name,'"';
        if($this->_form->hasUpload())
            echo ' enctype="multipart/form-data">';
        else
            echo '>';

        if(count($url->params) || count($this->_form->getHiddens())){
            echo '<div class="jforms-hiddens">';
            foreach ($url->params as $p_name => $p_value) {
                echo '<input type="hidden" name="', $p_name ,'" value="', htmlspecialchars($p_value), '"',$this->_endt, "\n";
            }
            foreach ($this->_form->getHiddens() as $ctrl) {
                if(!$this->_form->isActivated($ctrl->ref)) continue;
                echo '<input type="hidden" name="', $ctrl->ref,'" id="',$this->_name,'_',$ctrl->ref,'" value="', htmlspecialchars($this->_form->getData($ctrl->ref)), '"',$this->_endt, "\n";
            }
            echo '</div>';
        }
        echo '<script type="text/javascript">
//<![CDATA[
', $this->getJavascriptCheck($params[0],$params[1]),'
//]]>
</script>';
        $errors = $this->_form->getContainer()->errors;
        if(count($errors)){
            $ctrls = $this->_form->getControls();
            echo '<ul class="jforms-error-list">';
            $errRequired='';
            foreach($errors as $cname => $err){
                if($err == jForms::ERRDATA_REQUIRED) {
                    if($ctrls[$cname]->alertRequired){
                        echo '<li>', $ctrls[$cname]->alertRequired,'</li>';
                    }else{
                        echo '<li>', jLocale::get('jelix~formserr.js.err.required', $ctrls[$cname]->label),'</li>';
                    }
                }elseif ($err != '' && $err != jForms::ERRDATA_INVALID) {
                    echo '<li>', $err,'</li>';
                }else{
                    if($ctrls[$cname]->alertInvalid){
                        echo '<li>', $ctrls[$cname]->alertInvalid,'</li>';
                    }else{
                        echo '<li>', jLocale::get('jelix~formserr.js.err.invalid', $ctrls[$cname]->label),'</li>';
                    }
                }

            }
            echo '</ul>';
        }
    }

    public function outputFooter(){
        echo '</form>';
    }

    public function outputControlLabel($ctrl){
        if($ctrl->type == 'hidden') return;
        $required = ($ctrl->required == ''|| $ctrl->readonly?'':' jforms-required');
        $inError = (isset($this->_form->getContainer()->errors[$ctrl->ref]) ?' jforms-error':'');
        $hint = ($ctrl->hint == ''?'':' title="'.htmlspecialchars($ctrl->hint).'"');
        if($ctrl->type == 'output' || $ctrl->type == 'checkboxes' || $ctrl->type == 'radiobuttons'){
            echo '<span class="jforms-label',$required,$inError,'"',$hint,'>',htmlspecialchars($ctrl->label),'</span>';
        }else if($ctrl->type != 'submit' && $ctrl->type != 'reset'){
            $id = $this->_name.'_'.$ctrl->ref;
            echo '<label class="jforms-label',$required,$inError,'" for="'.$id.'"',$hint,'>'.htmlspecialchars($ctrl->label).'</label>';
        }
    }

    public function outputControl($ctrl){
        if($ctrl->type == 'hidden') return;
        $id = ' name="'.$ctrl->ref.'" id="'.$this->_name.'_'.$ctrl->ref.'"';
        $class = ($ctrl->required == ''|| $ctrl->readonly?'':' jforms-required');
        $class.= (isset($this->_form->getContainer()->errors[$ctrl->ref]) ?' jforms-error':'');
        if($class !='') $class = ' class="'.$class.'"';
        $readonly = ($ctrl->readonly?' readonly="readonly"':'');
        $hint = ($ctrl->hint == ''?'':' title="'.htmlspecialchars($ctrl->hint).'"');

        $this->{'output'.$ctrl->type}($ctrl, $id, $class, $readonly, $hint);

        $this->outputHelp($ctrl);
    }

    protected function outputInput($ctrl, $id, $class, $readonly, $hint) {
        $value = $this->_form->getData($ctrl->ref);
        $size = ($ctrl->size == 0?'' : ' size="'.$ctrl->size.'"');
        $maxl= $ctrl->datatype->getFacet('maxLength');
        if($maxl !== null)
            $maxl=' maxlength="'.$maxl.'"';
        else
            $maxl='';
        echo '<input type="text"',$id,$readonly,$hint,$class,$size,$maxl,' value="',htmlspecialchars($value),'"',$this->_endt;
    }

    protected function outputCheckbox($ctrl, $id, $class, $readonly, $hint) {
        $value = $this->_form->getData($ctrl->ref);

        if($ctrl->valueOnCheck == $value){
            $v=' checked="checked"';
        }else{
            $v='';
        }
        echo '<input type="checkbox"',$id,$readonly,$hint,$class,$v,' value="',$ctrl->valueOnCheck,'"',$this->_endt;
    }

    protected function outputCheckboxes($ctrl, $id, $class, $readonly, $hint) {
        $i=0;
        $id=$this->_name.'_'.$ctrl->ref.'_';
        $attrs=' name="'.$ctrl->ref.'[]" id="'.$id;
        $value = $this->_form->getData($ctrl->ref);

        if(is_array($value) && count($value) == 1)
            $value = $value[0];
        $span ='<span class="jforms-chkbox jforms-ctl-'.$ctrl->ref.'"><input type="checkbox"';

        if(is_array($value)){
            foreach($ctrl->datasource->getData($this->_form) as $v=>$label){
                echo $span,$attrs,$i,'" value="',htmlspecialchars($v),'"';
                if(in_array($v,$value))
                    echo ' checked="checked"';
                echo $readonly,$class,$this->_endt,'<label for="',$id,$i,'">',htmlspecialchars($label),'</label></span>';
                $i++;
            }
        }else{
            foreach($ctrl->datasource->getData($this->_form) as $v=>$label){
                echo $span,$attrs,$i,'" value="',htmlspecialchars($v),'"';
                if($v == $value)
                    echo ' checked="checked"';
                echo $readonly,$class,$this->_endt,'<label for="',$id,$i,'">',htmlspecialchars($label),'</label></span>';
                $i++;
            }
        }
    }

    protected function outputRadiobuttons($ctrl, $id, $class, $readonly, $hint) {
        $i=0;
        $id=' name="'.$ctrl->ref.'" id="'.$this->_name.'_'.$ctrl->ref.'_';
        $value = $this->_form->getData($ctrl->ref);
        if(is_array($value)){
            if(isset($value[0]))
                $value = $value[0];
            else
                $value='';
        }
        $span ='<span class="jforms-radio jforms-ctl-'.$ctrl->ref.'"><input type="radio"';
        foreach($ctrl->datasource->getData($this->_form) as $v=>$label){
            echo $span,$id,$i,'" value="',htmlspecialchars($v),'"',($v==$value?' checked="checked"':''),$readonly,$class,$this->_endt;
            echo '<label for="',$this->_name,'_',$ctrl->ref,'_',$i,'">',htmlspecialchars($label),'</label></span>';
            $i++;
        }
    }

    protected function outputMenulist($ctrl, $id, $class, $readonly, $hint) {
        echo '<select',$id,$hint,$class,' size="1">';
        $value = $this->_form->getData($ctrl->ref);
        if(is_array($value)){
            if(isset($value[0]))
                $value = $value[0];
            else
                $value='';
        }
        if (!$ctrl->required) {
            echo '<option value=""',($value==''?' selected="selected"':''),'></option>';
        }
        foreach($ctrl->datasource->getData($this->_form) as $v=>$label){
            echo '<option value="',htmlspecialchars($v),'"',($v==$value?' selected="selected"':''),'>',htmlspecialchars($label),'</option>';
        }
        echo '</select>';
    }

    protected function outputListbox($ctrl, $id, $class, $readonly, $hint) {
        if($ctrl->multiple){
            echo '<select name="',$ctrl->ref,'[]" id="',$this->_name,'_',$ctrl->ref,'"',$hint,$class,' size="',$ctrl->size,'" multiple="multiple">';
            $value = $this->_form->getData($ctrl->ref);

            if(is_array($value) && count($value) == 1)
                $value = $value[0];

            if(is_array($value)){
                foreach($ctrl->datasource->getData($this->_form) as $v=>$label){
                    echo '<option value="',htmlspecialchars($v),'"',(in_array($v,$value)?' selected="selected"':''),'>',htmlspecialchars($label),'</option>';
                }
            }else{
                foreach($ctrl->datasource->getData($this->_form) as $v=>$label){
                    echo '<option value="',htmlspecialchars($v),'"',($v==$value?' selected="selected"':''),'>',htmlspecialchars($label),'</option>';
                }
            }
            echo '</select>';
        }else{
            $value = $this->_form->getData($ctrl->ref);

            if(is_array($value)){
                if(count($value) >= 1)
                    $value = $value[0];
                else
                    $value ='';
            }

            echo '<select',$id,$hint,$class,' size="',$ctrl->size,'">';
            foreach($ctrl->datasource->getData($this->_form) as $v=>$label){
                echo '<option value="',htmlspecialchars($v),'"',($v==$value?' selected="selected"':''),'>',htmlspecialchars($label),'</option>';
            }
            echo '</select>';
        }
    }

    protected function outputTextarea($ctrl, $id, $class, $readonly, $hint) {
        $value = $this->_form->getData($ctrl->ref);
        $rows = ' rows="'.$ctrl->rows.'" cols="'.$ctrl->cols.'"';
        echo '<textarea',$id,$readonly,$hint,$class,$rows,'>',htmlspecialchars($value),'</textarea>';
    }

    protected function outputHtmleditor($ctrl, $id, $class, $readonly, $hint) {
        $engine = $GLOBALS['gJConfig']->htmleditors[$ctrl->config.'.engine.name'];
        echo '<script type="text/javascript">
//<![CDATA[
jelix_',$engine,'_',$ctrl->config.'("',$this->_name,'_',$ctrl->ref,'","',$this->_name,'");
//]]>
</script>';

        $value = $this->_form->getData($ctrl->ref);
        $rows = ' rows="'.$ctrl->rows.'" cols="'.$ctrl->cols.'"';
        echo '<textarea',$id,$readonly,$hint,$class,$rows,'>',htmlspecialchars($value),'</textarea>';
    }

    protected function outputSecret($ctrl, $id, $class, $readonly, $hint) {
        $size = ($ctrl->size == 0?'': ' size="'.$ctrl->size.'"');
        echo '<input type="password"',$id,$readonly,$hint,$class,$size,' value="',htmlspecialchars($this->_form->getData($ctrl->ref)),'"',$this->_endt;
    }

    protected function outputSecretconfirm($ctrl, $id, $class, $readonly, $hint) {
        $size = ($ctrl->size == 0?'': ' size="'.$ctrl->size.'"');
        echo '<input type="password"',$id,$readonly,$hint,$class,$size,' value="',htmlspecialchars($this->_form->getData($ctrl->ref)),'"',$this->_endt;
    }

    protected function outputOutput($ctrl, $id, $class, $readonly, $hint) {
            $value = $this->_form->getData($ctrl->ref);
            echo '<input type="hidden"',$id,' value="',htmlspecialchars($value),'"',$this->_endt;
            echo '<span class="jforms-value"',$hint,'>',htmlspecialchars($value),'</span>';
    }

    protected function outputUpload($ctrl, $id, $class, $readonly, $hint) {
            if($ctrl->maxsize){
                echo '<input type="hidden" name="MAX_FILE_SIZE" value="',$ctrl->maxsize,'"',$this->_endt;
            }
            echo '<input type="file"',$id,$readonly,$hint,$class,' value=""',$this->_endt; // ',htmlspecialchars($this->_form->getData($ctrl->ref)),'

    }

    protected function outputSubmit($ctrl, $id, $class, $readonly, $hint) {
            if($ctrl->standalone){
                echo '<input type="submit"',$id,$hint,' class="jforms-submit" value="',htmlspecialchars($ctrl->label),'"/>';
            }else{
                foreach($ctrl->datasource->getData($this->_form) as $v=>$label){
                    // because IE6 sucks with <button type=submit> (see ticket #431), we must use input :-(
                    echo '<input type="submit" name="',$ctrl->ref,'" id="',$this->_name,'_',$ctrl->ref,'_',htmlspecialchars($v),'"',
                        $hint,' class="jforms-submit" value="',htmlspecialchars($label),'"/> ';
                }
            }
    }

    protected function outputReset($ctrl, $id, $class, $readonly, $hint) {
        echo '<button type="reset"',$id,$hint,' class="jforms-reset">',htmlspecialchars($ctrl->label),'</button>';
    }

    protected function outputCaptcha($ctrl, $id, $class, $readonly, $hint) {
        $ctrl->initExpectedValue($this->_form);
        echo '<span class="jforms-captcha-question">',htmlspecialchars($ctrl->question),'</span> ';
        echo '<input type="text"',$id,$hint,$class,' value=""',$this->_endt;
    }

    protected function outputHelp($ctrl) {
        if ($ctrl->hasHelp) {
            if($ctrl->type == 'checkboxes' || ($ctrl->type == 'listbox' && $ctrl->multiple)){
                $name=$ctrl->ref.'[]';
            }else{
                $name=$ctrl->ref;
            }
            echo '<span class="jforms-help"><a href="javascript:jForms.showHelp(\''. $this->_name.'\',\''.$name.'\')">?</a></span>';
        }
    }

    abstract public function getJavascriptCheck($errDecorator,$helpDecorator);
}

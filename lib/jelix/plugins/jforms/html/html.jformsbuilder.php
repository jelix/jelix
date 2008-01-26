<?php

/**
 * HTML form builder
 * @package     jelix
 * @subpackage  jelix-plugins
 */
abstract class htmlJformsBuilder extends jFormsBuilderBase {

    /**
     * output the header content of the form
     * @param array $params some parameters 0=>name of the javascript error decorator
     *    1=> name of the javascript help decorator
     *    2=> name of method
     */
    public function outputHeader($params){
        $url = jUrl::get($this->_action, $this->_actionParams, 2); // retourne le jurl correspondant
        echo '<form action="',$url->scriptName,$url->pathInfo,'" method="'.$params[2].'" id="', $this->_name,'" onsubmit="return jForms.verifyForm(this)"';
        if($this->_form->hasUpload())
            echo ' enctype="multipart/form-data">';
        else
            echo '>';

        if(count($url->params)){
            echo '<div>';
            foreach ($url->params as $p_name => $p_value) {
                echo '<input type="hidden" name="', $p_name ,'" value="', htmlspecialchars($p_value), '"',$this->_endt, "\n";
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
        $required = ($ctrl->required == ''?'':' jforms-required');
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
        $id = ' name="'.$ctrl->ref.'" id="'.$this->_name.'_'.$ctrl->ref.'"';
        $readonly = ($ctrl->readonly?' readonly="readonly"':'');
        $hint = ($ctrl->hint == ''?'':' title="'.htmlspecialchars($ctrl->hint).'"');
        $class = (isset($this->_form->getContainer()->errors[$ctrl->ref]) ?' class="jforms-error"':'');
        switch($ctrl->type){
        case 'input':
            $value = $this->_form->getData($ctrl->ref);
            $size = ($ctrl->size == 0?'' : ' size="'.$ctrl->size.'"');
            echo '<input type="text"',$id,$readonly,$hint,$class,$size,' value="',htmlspecialchars($value),'"',$this->_endt;
            break;
        case 'checkbox':
            $value = $this->_form->getData($ctrl->ref);

            if($ctrl->valueOnCheck == $value){
                $v=' checked="checked"';
            }else{
                $v='';
            }
            echo '<input type="checkbox"',$id,$readonly,$hint,$class,$v,' value="',$ctrl->valueOnCheck,'"',$this->_endt;
            break;
        case 'checkboxes':
            $i=0;
            $id=$this->_name.'_'.$ctrl->ref.'_';
            $attrs=' name="'.$ctrl->ref.'[]" id="'.$id;
            $value = $this->_form->getData($ctrl->ref);

            if(is_array($value) && count($value) == 1)
                $value = $value[0];
            $span ='<span class="jforms-chkbox jforms-ctl-'.$ctrl->ref.'"><input type="checkbox"';

            if(is_array($value)){
                foreach($ctrl->datasource->getDatas() as $v=>$label){
                    echo $span,$attrs,$i,'" value="',htmlspecialchars($v),'"';
                    if(in_array($v,$value))
                        echo ' checked="checked"';
                    echo $readonly,$class,$this->_endt,'<label for="',$id,$i,'">',htmlspecialchars($label),'</label></span>';
                    $i++;
                }
            }else{
                foreach($ctrl->datasource->getDatas() as $v=>$label){
                    echo $span,$attrs,$i,'" value="',htmlspecialchars($v),'"';
                    if($v == $value)
                        echo ' checked="checked"';
                    echo $readonly,$class,$this->_endt,'<label for="',$id,$i,'">',htmlspecialchars($label),'</label></span>';
                    $i++;
                }
            }
            break;
        case 'radiobuttons':
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
            foreach($ctrl->datasource->getDatas() as $v=>$label){
                echo $span,$id,$i,'" value="',htmlspecialchars($v),'"',($v==$value?' checked="checked"':''),$readonly,$class,$this->_endt;
                echo '<label for="',$this->_name,'_',$ctrl->ref,'_',$i,'">',htmlspecialchars($label),'</label></span>';
                $i++;
            }
            break;
        case 'menulist':
            echo '<select',$id,$readonly,$hint,$class,' size="1">';
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
            foreach($ctrl->datasource->getDatas() as $v=>$label){
                echo '<option value="',htmlspecialchars($v),'"',($v==$value?' selected="selected"':''),'>',htmlspecialchars($label),'</option>';
            }
            echo '</select>';
            break;
        case 'listbox':
            if($ctrl->multiple){
                echo '<select name="',$ctrl->ref,'[]" id="',$this->_name,'_',$ctrl->ref,'"',$readonly,$hint,$class,' size="',$ctrl->size,'" multiple="multiple">';
                $value = $this->_form->getData($ctrl->ref);

                if(is_array($value) && count($value) == 1)
                    $value = $value[0];

                if(is_array($value)){
                    foreach($ctrl->datasource->getDatas() as $v=>$label){
                        echo '<option value="',htmlspecialchars($v),'"',(in_array($v,$value)?' selected="selected"':''),'>',htmlspecialchars($label),'</option>';
                    }
                }else{
                    foreach($ctrl->datasource->getDatas() as $v=>$label){
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

                echo '<select',$id,$readonly,$hint,$class,' size="',$ctrl->size,'">';
                foreach($ctrl->datasource->getDatas() as $v=>$label){
                    echo '<option value="',htmlspecialchars($v),'"',($v==$value?' selected="selected"':''),'>',htmlspecialchars($label),'</option>';
                }
                echo '</select>';
            }
            break;
        case 'textarea':
            $value = $this->_form->getData($ctrl->ref);
            $rows = ($ctrl->rows == 0?'': ' rows="'.$ctrl->rows.'"');
            $cols = ($ctrl->cols == 0?'': ' cols="'.$ctrl->cols.'"');
            echo '<textarea',$id,$readonly,$hint,$class,$rows,$cols,'>',htmlspecialchars($value),'</textarea>';
            break;
        case 'secret':
        case 'secretconfirm':
            $size = ($ctrl->size == 0?'': ' size="'.$ctrl->size.'"');
            echo '<input type="password"',$id,$readonly,$hint,$class,$size,' value="',htmlspecialchars($this->_form->getData($ctrl->ref)),'"',$this->_endt;
            break;
        case 'output':
            $value = $this->_form->getData($ctrl->ref);
            echo '<input type="hidden"',$id,' value="',htmlspecialchars($value),'"',$this->_endt;
            echo '<span class="jforms-value"',$hint,'>',htmlspecialchars($value),'</span>';
            break;
        case 'upload':
            if($ctrl->maxsize){
                echo '<input type="hidden" name="MAX_FILE_SIZE" value="',$ctrl->maxsize,'"',$this->_endt;
            }
            echo '<input type="file"',$id,$readonly,$hint,$class,' value=""',$this->_endt; // ',htmlspecialchars($this->_form->getData($ctrl->ref)),'
            break;
        case 'submit':
            if($ctrl->standalone){
                echo '<input type="submit"',$id,$hint,' class="jforms-submit" value="',htmlspecialchars($ctrl->label),'"/>';
            }else{
                foreach($ctrl->datasource->getDatas() as $v=>$label){
                    // because IE6 sucks with <button type=submit> (see ticket #431), we must use input :-(
                    echo '<input type="submit" name="',$ctrl->ref,'" id="',$this->_name,'_',$ctrl->ref,'_',htmlspecialchars($v),'"',
                        $hint,' class="jforms-submit" value="',htmlspecialchars($label),'"/> ';
                }
            }
            break;
        case 'reset':
            echo '<button type="reset"',$id,$hint,' class="jforms-reset">',htmlspecialchars($ctrl->label),'</button>';
            break;
        }

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
?>
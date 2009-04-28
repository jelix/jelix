<?php
/**
* @package     jelix
* @subpackage  forms
* @author      Laurent Jouanneau
* @contributor Julien Issler, Dominique Papin
* @copyright   2006-2009 Laurent Jouanneau
* @copyright   2008 Julien Issler, 2008 Dominique Papin
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * HTML form builder
 * @package     jelix
 * @subpackage  jelix-plugins
 */
class htmllightJformsBuilder extends jFormsBuilderBase {

    protected $options;

    protected $isRootControl = true;

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
                echo '</td></tr>';
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
        $resp->addJSLink($www.'js/jforms_light.js');
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
     * @param array $params some parameters <ul>
     *      <li>"errDecorator"=>"name of your javascript object for error listener"</li>
     *      <li>"helpDecorator"=>"name of your javascript object for help listener"</li>
     *      <li>"method" => "post" or "get". default is "post"</li>
     *      </ul>
     */
    public function outputHeader($params){
        $this->options = array_merge(array('errorDecorator'=>'jFormsErrorDecoratorAlert',
                 'helpDecorator'=>'jFormsHelpDecoratorAlert', 'method'=>'post'), $params);

        if (preg_match('#^https?://#',$this->_action)) {
            $urlParams = $this->_actionParams;
            echo '<form action="',$this->_action,'" method="'.$this->options['method'].'" id="', $this->_name,'"';
        } else {
            $url = jUrl::get($this->_action, $this->_actionParams, 2); // retourne le jurl correspondant
            $urlParams = $url->params;
            echo '<form action="',$url->getPath(),'" method="'.$this->options['method'].'" id="', $this->_name,'"';
        }
        if($this->_form->hasUpload())
            echo ' enctype="multipart/form-data">';
        else
            echo '>';

        echo '<script type="text/javascript">
//<![CDATA[
jForms.tForm = new jFormsForm(\''.$this->_name.'\');
jForms.tForm.setErrorDecorator(new '.$this->options['errorDecorator'].'());
jForms.tForm.setHelpDecorator(new '.$this->options['helpDecorator'].'());
jForms.declareForm(jForms.tForm);
//]]>
</script>';

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
            echo '<ul class="jforms-error-list">';
            $errRequired='';
            foreach($errors as $cname => $err){
                if(!$this->_form->isActivated($ctrls[$cname]->ref)) continue;
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

    protected $jsContent = '';

    public function outputFooter(){
        echo '<script type="text/javascript">
//<![CDATA[
(function(){var c, c2;
'.$this->jsContent.'
})();
//]]>
</script>';
        echo '</form>';
    }

    public function outputControlLabel($ctrl){
        if($ctrl->type == 'hidden' || $ctrl->type == 'group') return;
        $required = ($ctrl->required == false || $ctrl->isReadOnly()?'':' jforms-required');
        $inError = (isset($this->_form->getContainer()->errors[$ctrl->ref]) ?' jforms-error':'');
        $hint = ($ctrl->hint == ''?'':' title="'.htmlspecialchars($ctrl->hint).'"');
        if($ctrl->type == 'output' || $ctrl->type == 'checkboxes' || $ctrl->type == 'radiobuttons' || $ctrl->type == 'date' || $ctrl->type == 'datetime'){
            echo '<span class="jforms-label',$required,$inError,'"',$hint,'>',htmlspecialchars($ctrl->label),'</span>';
        }else if($ctrl->type != 'submit' && $ctrl->type != 'reset'){
            $id = $this->_name.'_'.$ctrl->ref;
            echo '<label class="jforms-label',$required,$inError,'" for="',$id,'"',$hint,'>',htmlspecialchars($ctrl->label),'</label>';
        }
    }

    public function outputControl($ctrl){
        if($ctrl->type == 'hidden') return;
        $ro = $ctrl->isReadOnly();
        $id = ' name="'.$ctrl->ref.'" id="'.$this->_name.'_'.$ctrl->ref.'"';
        $class = ($ctrl->required == false || $ro?'':' jforms-required');
        $class.= (isset($this->_form->getContainer()->errors[$ctrl->ref]) ?' jforms-error':'');
        $class.= ($ro && $ctrl->type != 'captcha'?' jforms-readonly':'');
        $readonly = ($ro?' readonly="readonly"':'');
        if($class !='') $class = ' class="'.$class.'"';
        $hint = ($ctrl->hint == ''?'':' title="'.htmlspecialchars($ctrl->hint).'"');
        $this->{'output'.$ctrl->type}($ctrl, $id, $class, $readonly, $hint);
        $this->{'js'.$ctrl->type}($ctrl);
        $this->outputHelp($ctrl);
    }

    protected function escJsStr($str) {
        return '\''.str_replace(array("'","\n"),array("\\'", "\\n"), $str).'\'';
    }

    protected function commonJs($ctrl) {
        if($ctrl->help){
            $this->jsContent .="c.help=".$this->escJsStr($ctrl->help).";\n";
        }

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

        if ($this->isRootControl) $this->jsContent .="jForms.tForm.addControl(c);\n";
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

    protected function jsInput($ctrl) {

        $datatype = array('jDatatypeBoolean'=>'Boolean','jDatatypeDecimal'=>'Decimal','jDatatypeInteger'=>'Integer','jDatatypeHexadecimal'=>'Hexadecimal',
                        'jDatatypeDateTime'=>'Datetime','jDatatypeDate'=>'Date','jDatatypeTime'=>'Time',
                        'jDatatypeUrl'=>'Url','jDatatypeEmail'=>'Email','jDatatypeIPv4'=>'Ipv4','jDatatypeIPv6'=>'Ipv6');
        $isLocale = false;
        $data_type_class = get_class($ctrl->datatype);
        if(isset($datatype[$data_type_class]))
            $dt = $datatype[$data_type_class];
        else if ($ctrl->datatype instanceof jDatatypeLocaleTime)
            { $dt = 'Time'; $isLocale = true; }
        else if ($ctrl->datatype instanceof jDatatypeLocaleDate)
            { $dt = 'LocaleDate'; $isLocale = true; }
        else if ($ctrl->datatype instanceof jDatatypeLocaleDateTime)
            { $dt = 'LocaleDatetime'; $isLocale = true; }
        else
            $dt = 'String';

        $this->jsContent .="c = new jFormsControl".$dt."('".$ctrl->ref."', ".$this->escJsStr($ctrl->label).");\n";
        if ($isLocale)
            $this->jsContent .="c.lang='".$GLOBALS['gJConfig']->locale."';\n";

        $maxl= $ctrl->datatype->getFacet('maxLength');
        if($maxl !== null)
            $this->jsContent .="c.maxLength = '$maxl';\n";

        $minl= $ctrl->datatype->getFacet('minLength');
        if($minl !== null)
            $this->jsContent .="c.minLength = '$minl';\n";

        $this->commonJs($ctrl);
    }

    protected function _outputDateControlDay($ctrl, $id, $value, $class, $readonly, $hint){
        if($GLOBALS['gJConfig']->forms['controls.datetime.input'] == 'textboxes')
            echo '<input type="text" size="2" maxlength="2" id="'.$id.'day" name="'.$ctrl->ref.'[day]"'.$readonly.$hint.$class.' value="'.$value.'"'.$this->_endt;
        else{
            echo '<select id="'.$id.'day" name="'.$ctrl->ref.'[day]"'.$readonly.$hint.$class.'><option value="">'.htmlspecialchars(jLocale::get('jelix~jforms.date.day.label')).'</option>';
            for($i=1;$i<32;$i++){
                $k = ($i<10)?'0'.$i:$i;
                echo '<option value="'.$k.'"'.($k == $value?' selected="selected"':'').'>'.$k.'</option>';
            }
            echo '</select>';
        }
    }

    protected function _outputDateControlMonth($ctrl, $id, $value, $class, $readonly, $hint){
        if($GLOBALS['gJConfig']->forms['controls.datetime.input'] == 'textboxes')
            echo '<input type="text" size="2" maxlength="2" id="'.$id.'month" name="'.$ctrl->ref.'[month]"'.$readonly.$hint.$class.' value="'.$value.'"'.$this->_endt;
        else{
            $monthLabels = $GLOBALS['gJConfig']->forms['controls.datetime.months.labels'];
            echo '<select id="'.$id.'month" name="'.$ctrl->ref.'[month]"'.$readonly.$hint.$class.'><option value="">'.htmlspecialchars(jLocale::get('jelix~jforms.date.month.label')).'</option>';
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

    protected function _outputDateControlYear($ctrl, $id, $value, $class, $readonly, $hint){
        if($GLOBALS['gJConfig']->forms['controls.datetime.input'] == 'textboxes')
            echo '<input type="text" size="4" maxlength="4" id="'.$id.'year" name="'.$ctrl->ref.'[year]"'.$readonly.$hint.$class.' value="'.$value.'"'.$this->_endt;
        else{
            $minDate = $ctrl->datatype->getFacet('minValue');
            $maxDate = $ctrl->datatype->getFacet('maxValue');
            if($minDate && $maxDate){
                echo '<select id="'.$id.'year" name="'.$ctrl->ref.'[year]"'.$readonly.$hint.$class.'><option value="">'.htmlspecialchars(jLocale::get('jelix~jforms.date.year.label')).'</option>';
                for($i=$minDate->year;$i<=$maxDate->year;$i++)
                    echo '<option value="'.$i.'"'.($i == $value?' selected="selected"':'').'>'.$i.'</option>';
                echo '</select>';
            }
            else
                echo '<input type="text" size="4" maxlength="4" id="'.$id.'year" name="'.$ctrl->ref.'[year]"'.$readonly.$hint.$class.' value="'.$value.'"'.$this->_endt;
        }
    }

    protected function _outputDateControlHour($ctrl, $id, $value, $class, $readonly, $hint){
        if($GLOBALS['gJConfig']->forms['controls.datetime.input'] == 'textboxes')
            echo '<input type="text" id="'.$id.'hour" name="'.$ctrl->ref.'[hour]" value="'.$value.'"'.$this->_endt;
        else{
            echo '<select id="'.$id.'hour" name="'.$ctrl->ref.'[hour]"'.$readonly.$hint.$class.'><option value="">'.htmlspecialchars(jLocale::get('jelix~jforms.time.hour.label')).'</option>';
            for($i=0;$i<24;$i++){
                $k = ($i<10)?'0'.$i:$i;
                echo '<option value="'.$k.'"'.( (string) $k === $value?' selected="selected"':'').'>'.$k.'</option>';
            }
            echo '</select>';
        }
    }

    protected function _outputDateControlMinutes($ctrl, $id, $value, $class, $readonly, $hint){
        if($GLOBALS['gJConfig']->forms['controls.datetime.input'] == 'textboxes')
            echo '<input type="text" id="'.$id.'minutes" name="'.$ctrl->ref.'[minutes]" value="'.$value.'"'.$this->_endt;
        else{
            echo '<select id="'.$id.'minutes" name="'.$ctrl->ref.'[minutes]"'.$readonly.$hint.$class.'><option value="">'.htmlspecialchars(jLocale::get('jelix~jforms.time.minutes.label')).'</option>';
            for($i=0;$i<60;$i++){
                $k = ($i<10)?'0'.$i:$i;
                echo '<option value="'.$k.'"'.( (string) $k === $value?' selected="selected"':'').'>'.$k.'</option>';
            }
            echo '</select>';
        }
    }

    protected function _outputDateControlSeconds($ctrl, $id, $value, $class, $readonly, $hint){
        if(!$ctrl->enableSeconds)
            echo '<input type="hidden" id="'.$id.'seconds" name="'.$ctrl->ref.'[seconds]" value="'.$value.'"'.$this->_endt;
        else if($GLOBALS['gJConfig']->forms['controls.datetime.input'] == 'textboxes')
            echo '<input type="text" id="'.$id.'seconds" name="'.$ctrl->ref.'[seconds]" value="'.$value.'"'.$this->_endt;
        else{
            echo '<select id="'.$id.'seconds" name="'.$ctrl->ref.'[seconds]"'.$readonly.$hint.$class.'><option value="">'.htmlspecialchars(jLocale::get('jelix~jforms.time.seconds.label')).'</option>';
            for($i=0;$i<60;$i++){
                $k = ($i<10)?'0'.$i:$i;
                echo '<option value="'.$k.'"'.( (string) $k === $value?' selected="selected"':'').'>'.$k.'</option>';
            }
            echo '</select>';
        }
    }

    protected function outputDate($ctrl, $id, $class, $readonly, $hint){
        $id = $this->_name.'_'.$ctrl->ref.'_';
        $v = array('year'=>'','month'=>'','day'=>'');
        if(preg_match('#^(\d{4})?-(\d{2})?-(\d{2})?$#',$this->_form->getData($ctrl->ref),$matches)){
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
                $this->_outputDateControlYear($ctrl, $id, $v['year'], $class, $readonly, $hint);
            else if($f[$i] == 'm')
                $this->_outputDateControlMonth($ctrl, $id, $v['month'], $class, $readonly, $hint);
            else if($f[$i] == 'd')
                $this->_outputDateControlDay($ctrl, $id, $v['day'], $class, $readonly, $hint);
            else
                echo ' ';
        }
    }

    protected function jsDate($ctrl){
        $this->jsContent .= "c = new jFormsControlDate('".$ctrl->ref."', ".$this->escJsStr($ctrl->label).");\n";
        $this->jsContent .= "c.multiFields = true;\n";
        $minDate = $ctrl->datatype->getFacet('minValue');
        $maxDate = $ctrl->datatype->getFacet('maxValue');
        if($minDate)
            $this->jsContent .= "c.minDate = '".$minDate->toString(jDateTime::BD_DFORMAT)."';\n";
        if($maxDate)
            $this->jsContent .= "c.maxDate = '".$maxDate->toString(jDateTime::BD_DFORMAT)."';\n";
        $this->commonJs($ctrl);
    }

    protected function outputDatetime($ctrl, $id, $class, $readonly, $hint){
        $id = $this->_name.'_'.$ctrl->ref.'_';
        $v = array('year'=>'','month'=>'','day'=>'','hour'=>'','minutes'=>'','seconds'=>'');
        if(preg_match('#^(\d{4})?-(\d{2})?-(\d{2})? (\d{2})?:(\d{2})?(:(\d{2})?)?$#',$this->_form->getData($ctrl->ref),$matches)){
            if(isset($matches[1]))
                $v['year'] = $matches[1];
            if(isset($matches[2]))
                $v['month'] = $matches[2];
            if(isset($matches[3]))
                $v['day'] = $matches[3];
            if(isset($matches[4]))
                $v['hour'] = $matches[4];
            if(isset($matches[5]))
                $v['minutes'] = $matches[5];
            if(isset($matches[7]))
                $v['seconds'] = $matches[7];
        }
        $f = jLocale::get('jelix~format.datetime');
        for($i=0;$i<strlen($f);$i++){
            if($f[$i] == 'Y')
                $this->_outputDateControlYear($ctrl, $id, $v['year'], $class, $readonly, $hint);
            else if($f[$i] == 'm')
                $this->_outputDateControlMonth($ctrl, $id, $v['month'], $class, $readonly, $hint);
            else if($f[$i] == 'd')
                $this->_outputDateControlDay($ctrl, $id, $v['day'], $class, $readonly, $hint);
            else if($f[$i] == 'H')
                $this->_outputDateControlHour($ctrl, $id, $v['hour'], $class, $readonly, $hint);
            else if($f[$i] == 'i')
                $this->_outputDateControlMinutes($ctrl, $id, $v['minutes'], $class, $readonly, $hint);
            else if($f[$i] == 's')
                $this->_outputDateControlSeconds($ctrl, $id, $v['seconds'], $class, $readonly, $hint);
            else
                echo ' ';
        }
    }

    protected function jsDatetime($ctrl){
        $this->jsContent .= "c = new jFormsControlDatetime('".$ctrl->ref."', ".$this->escJsStr($ctrl->label).");\n";
        $this->jsContent .= "c.multiFields = true;\n";
        $minDate = $ctrl->datatype->getFacet('minValue');
        $maxDate = $ctrl->datatype->getFacet('maxValue');
        if($minDate)
            $this->jsContent .= "c.minDate = '".$minDate->toString(jDateTime::BD_DTFORMAT)."';\n";
        if($maxDate)
            $this->jsContent .= "c.maxDate = '".$maxDate->toString(jDateTime::BD_DTFORMAT)."';\n";
        $this->commonJs($ctrl);
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

    protected function jsCheckbox($ctrl) {

        $this->jsContent .="c = new jFormsControlBoolean('".$ctrl->ref."', ".$this->escJsStr($ctrl->label).");\n";

        $this->commonJs($ctrl);
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
            $value = array_map(create_function('$v', 'return (string) $v;'),$value);
            foreach($ctrl->datasource->getData($this->_form) as $v=>$label){
                echo $span,$attrs,$i,'" value="',htmlspecialchars($v),'"';
                if(in_array((string) $v,$value,true))
                    echo ' checked="checked"';
                echo $readonly,$class,$this->_endt,'<label for="',$id,$i,'">',htmlspecialchars($label),'</label></span>';
                $i++;
            }
        }else{
            $value = (string) $value;
            foreach($ctrl->datasource->getData($this->_form) as $v=>$label){
                echo $span,$attrs,$i,'" value="',htmlspecialchars($v),'"';
                if((string) $v === $value)
                    echo ' checked="checked"';
                echo $readonly,$class,$this->_endt,'<label for="',$id,$i,'">',htmlspecialchars($label),'</label></span>';
                $i++;
            }
        }
    }

    protected function jsCheckboxes($ctrl) {

        $this->jsContent .="c = new jFormsControlString('".$ctrl->ref."[]', ".$this->escJsStr($ctrl->label).");\n";

        $this->commonJs($ctrl);
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
        $value = (string) $value;
        $span ='<span class="jforms-radio jforms-ctl-'.$ctrl->ref.'"><input type="radio"';
        foreach($ctrl->datasource->getData($this->_form) as $v=>$label){
            echo $span,$id,$i,'" value="',htmlspecialchars($v),'"',((string) $v===$value?' checked="checked"':''),$readonly,$class,$this->_endt;
            echo '<label for="',$this->_name,'_',$ctrl->ref,'_',$i,'">',htmlspecialchars($label),'</label></span>';
            $i++;
        }
    }

    protected function jsRadiobuttons($ctrl) {

        $this->jsContent .="c = new jFormsControlString('".$ctrl->ref."', ".$this->escJsStr($ctrl->label).");\n";

        $this->commonJs($ctrl);
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
        $value = (string) $value;
        if (!$ctrl->required) {
            echo '<option value=""',($value===''?' selected="selected"':''),'>',htmlspecialchars($ctrl->emptyItemLabel),'</option>';
        }
        foreach($ctrl->datasource->getData($this->_form) as $v=>$label){
            echo '<option value="',htmlspecialchars($v),'"',((string) $v===$value?' selected="selected"':''),'>',htmlspecialchars($label),'</option>';
        }
        echo '</select>';
    }

    protected function jsMenulist($ctrl) {

        $this->jsContent .="c = new jFormsControlString('".$ctrl->ref."', ".$this->escJsStr($ctrl->label).");\n";

        $this->commonJs($ctrl);
    }

    protected function outputListbox($ctrl, $id, $class, $readonly, $hint) {
        if($ctrl->multiple){
            echo '<select name="',$ctrl->ref,'[]" id="',$this->_name,'_',$ctrl->ref,'"',$hint,$class,' size="',$ctrl->size,'" multiple="multiple">';
            $value = $this->_form->getData($ctrl->ref);

            if(is_array($value) && count($value) == 1)
                $value = $value[0];

            if(is_array($value)){
                $value = array_map(create_function('$v', 'return (string) $v;'),$value);
                foreach($ctrl->datasource->getData($this->_form) as $v=>$label){
                    echo '<option value="',htmlspecialchars($v),'"',(in_array((string) $v,$value,true)?' selected="selected"':''),'>',htmlspecialchars($label),'</option>';
                }
            }else{
                $value = (string) $value;
                foreach($ctrl->datasource->getData($this->_form) as $v=>$label){
                    echo '<option value="',htmlspecialchars($v),'"',((string) $v===$value?' selected="selected"':''),'>',htmlspecialchars($label),'</option>';
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

            $value = (string) $value;
            echo '<select',$id,$hint,$class,' size="',$ctrl->size,'">';
            foreach($ctrl->datasource->getData($this->_form) as $v=>$label){
                echo '<option value="',htmlspecialchars($v),'"',((string) $v===$value?' selected="selected"':''),'>',htmlspecialchars($label),'</option>';
            }
            echo '</select>';
        }
    }

    protected function jsListbox($ctrl) {
        if($ctrl->multiple){
            $this->jsContent .= "c = new jFormsControlString('".$ctrl->ref."[]', ".$this->escJsStr($ctrl->label).");\n";
            $this->jsContent .= "c.multiple = true;\n";
        } else {
            $this->jsContent .="c = new jFormsControlString('".$ctrl->ref."', ".$this->escJsStr($ctrl->label).");\n";
        }

        $this->commonJs($ctrl);
    }

    protected function outputTextarea($ctrl, $id, $class, $readonly, $hint) {
        $value = $this->_form->getData($ctrl->ref);
        $rows = ' rows="'.$ctrl->rows.'" cols="'.$ctrl->cols.'"';
        echo '<textarea',$id,$readonly,$hint,$class,$rows,'>',htmlspecialchars($value),'</textarea>';
    }

    protected function jsTextarea($ctrl) {
        $this->jsContent .="c = new jFormsControlString('".$ctrl->ref."', ".$this->escJsStr($ctrl->label).");\n";

        $maxl= $ctrl->datatype->getFacet('maxLength');
        if($maxl !== null)
            $this->jsContent .="c.maxLength = '$maxl';\n";

        $minl= $ctrl->datatype->getFacet('minLength');
        if($minl !== null)
            $this->jsContent .="c.minLength = '$minl';\n";

        $this->commonJs($ctrl);
    }

    protected function outputHtmleditor($ctrl, $id, $class, $readonly, $hint) {
        $value = $this->_form->getData($ctrl->ref);
        $rows = ' rows="'.$ctrl->rows.'" cols="'.$ctrl->cols.'"';
        echo '<textarea',$id,$readonly,$hint,$class,$rows,'>',htmlspecialchars($value),'</textarea>';
    }

    protected function jsHtmleditor($ctrl) {
        $this->jsTextarea($ctrl);
        $engine = $GLOBALS['gJConfig']->htmleditors[$ctrl->config.'.engine.name'];
        $this->jsContent .= 'jelix_'.$engine.'_'.$ctrl->config.'("'.$this->_name.'_'.$ctrl->ref.'","'.$this->_name."\");\n";
    }

    protected function outputSecret($ctrl, $id, $class, $readonly, $hint) {
        $size = ($ctrl->size == 0?'': ' size="'.$ctrl->size.'"');
        $maxl = $ctrl->datatype->getFacet('maxLength');
        if($maxl !== null)
            $maxl = ' maxlength="'.$maxl.'"';
        else
            $maxl = '';
        echo '<input type="password"',$id,$readonly,$hint,$class,$size,$maxl,' value="',htmlspecialchars($this->_form->getData($ctrl->ref)),'"',$this->_endt;
    }

    protected function jsSecret($ctrl) {
        $this->jsContent .="c = new jFormsControlSecret('".$ctrl->ref."', ".$this->escJsStr($ctrl->label).");\n";

        $maxl= $ctrl->datatype->getFacet('maxLength');
        if($maxl !== null)
            $this->jsContent .="c.maxLength = '$maxl';\n";

        $minl= $ctrl->datatype->getFacet('minLength');
        if($minl !== null)
            $this->jsContent .="c.minLength = '$minl';\n";

        $this->commonJs($ctrl);
    }

    protected function outputSecretconfirm($ctrl, $id, $class, $readonly, $hint) {
        $size = ($ctrl->size == 0?'': ' size="'.$ctrl->size.'"');
        echo '<input type="password"',$id,$readonly,$hint,$class,$size,' value="',htmlspecialchars($this->_form->getData($ctrl->ref)),'"',$this->_endt;
    }

    protected function jsSecretconfirm($ctrl) {
        $this->jsContent .="c = new jFormsControlConfirm('".$ctrl->ref."', ".$this->escJsStr($ctrl->label).");\n";
        $this->commonJs($ctrl);
    }

    protected function outputOutput($ctrl, $id, $class, $readonly, $hint) {
        $value = $this->_form->getData($ctrl->ref);
        echo '<input type="hidden"',$id,' value="',htmlspecialchars($value),'"',$this->_endt;
        echo '<span class="jforms-value"',$hint,'>',htmlspecialchars($value),'</span>';
    }

    protected function jsOutput($ctrl) {
    }

    protected function outputUpload($ctrl, $id, $class, $readonly, $hint) {
        if($ctrl->maxsize){
            echo '<input type="hidden" name="MAX_FILE_SIZE" value="',$ctrl->maxsize,'"',$this->_endt;
        }
        echo '<input type="file"',$id,$readonly,$hint,$class,' value=""',$this->_endt; // ',htmlspecialchars($this->_form->getData($ctrl->ref)),'

    }

    protected function jsUpload($ctrl) {
        $this->jsContent .="c = new jFormsControlString('".$ctrl->ref."', ".$this->escJsStr($ctrl->label).");\n";

        $this->commonJs($ctrl);
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

    protected function jsSubmit($ctrl) {
        // no javascript
    }

    protected function outputReset($ctrl, $id, $class, $readonly, $hint) {
        echo '<button type="reset"',$id,$hint,' class="jforms-reset">',htmlspecialchars($ctrl->label),'</button>';
    }

    protected function jsReset($ctrl) {
        // no javascript
    }

    protected function outputCaptcha($ctrl, $id, $class, $readonly, $hint) {
        $ctrl->initExpectedValue();
        echo '<span class="jforms-captcha-question">',htmlspecialchars($ctrl->question),'</span> ';
        echo '<input type="text"',$id,$hint,$class,' value=""',$this->_endt;
    }

    protected function jsCaptcha($ctrl) {
        $this->jsTextarea($ctrl);
    }

    protected function outputGroup($ctrl, $id, $class, $readonly, $hint) {
        echo '<fieldset><legend>',htmlspecialchars($ctrl->label),"</legend>\n";
        echo '<table class="jforms-table-group" border="0">',"\n";
        foreach( $ctrl->getChildControls() as $ctrlref=>$c){
            if($c->type == 'submit' || $c->type == 'reset' || $c->type == 'hidden') continue;
            if(!$this->_form->isActivated($ctrlref)) continue;
            echo '<tr><th scope="row">';
            $this->outputControlLabel($c);
            echo "</th>\n<td>";
            $this->outputControl($c);
            echo "</td></tr>\n";
        }
        echo "</table></fieldset>";
    }

    protected function jsGroup($ctrl) {
        //no javacript
    }

    protected function outputChoice($ctrl, $id, $class, $readonly, $hint) {
        echo '<ul class="jforms-choice jforms-ctl-'.$ctrl->ref.'" >',"\n";

        $value = $this->_form->getData($ctrl->ref);
        if(is_array($value)){
            if(isset($value[0]))
                $value = $value[0];
            else
                $value='';
        }

        $i=0;
        $id=' name="'.$ctrl->ref.'" id="'.$this->_name.'_'.$ctrl->ref.'_';
        $this->jsChoiceInternal($ctrl);
        $this->jsContent .="c2 = c;\n";
        $this->isRootControl = false;
        foreach( $ctrl->items as $itemName=>$listctrl){
            echo '<li><label><input type="radio"',$id,$i,'" value="',htmlspecialchars($itemName),'"';
            echo ($itemName==$value?' checked="checked"':''),$readonly;
            echo ' onclick="jForms.getForm(\'',$this->_name,'\').getControl(\'',$ctrl->ref,'\').activate(\'',$itemName,'\')"', $this->_endt;
            echo htmlspecialchars($ctrl->itemsNames[$itemName]),'</label> ';

            $displayedControls = false;
            foreach($listctrl as $ref=>$c) {
                if(!$this->_form->isActivated($ref) || $c->type == 'hidden') continue;
                $displayedControls = true;
                echo ' <span class="jforms-item-controls">';
                // we remove readonly status so when a user change the choice and
                // javascript is deactivated, it can still change the value of the control
                $ro = $c->isReadOnly();
                if($ro && $readonly == '') $c->setReadOnly(false);
                $this->outputControlLabel($c);
                echo ' ';
                $this->outputControl($c);
                if($ro) $c->setReadOnly(true);
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

        $this->jsContent .="c = new jFormsControlChoice('".$ctrl->ref."', ".$this->escJsStr($ctrl->label).");\n";

        $this->commonJs($ctrl);
    }

    protected function outputHelp($ctrl) {
        if ($ctrl->help) {
            if($ctrl->type == 'checkboxes' || ($ctrl->type == 'listbox' && $ctrl->multiple)){
                $name=$ctrl->ref.'[]';
            }else{
                $name=$ctrl->ref;
            }
            echo '<span class="jforms-help"><a href="javascript:jForms.showHelp(\''. $this->_name.'\',\''.$name.'\')">?</a></span>';
        }
    }
}

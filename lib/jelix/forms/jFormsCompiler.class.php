<?php
/**
* @package    jelix
* @subpackage forms
* @author     Laurent Jouanneau
* @contributor
* @copyright   2006-2007 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 *
 */
require_once(JELIX_LIB_FORMS_PATH.'jFormsControl.class.php');

/**
 * generates form class from an xml file describing the form
 * @package     jelix
 * @subpackage  forms
 */
class jFormsCompiler implements jISimpleCompiler {

    protected $sourceFile;


    public function compile($selector){
        global $gJCoord;
        $sel = clone $selector;

        $this->sourceFile = $selector->getPath();
        $cachefile = $selector->getCompiledFilePath();
        $cacheHtmlBuilderFile = $selector->getCompiledBuilderFilePath ('html');

        // chargement du fichier XML
        $doc = new DOMDocument();

        if(!$doc->load($this->sourceFile)){
            throw new jException('jelix~formserr.invalid.xml.file',array($this->sourceFile));
        }

        if($doc->documentElement->namespaceURI != JELIX_NAMESPACE_BASE.'forms/1.0'){
           throw new jException('jelix~formserr.namespace.wrong',array($this->sourceFile));
        }

        $xml = simplexml_import_dom($doc);

        $source=array();
        $source[]='<?php ';
        $source[]='class '.$selector->getClass().' extends jFormsBase {';
        $source[]='    protected $_builders = array( ';
        $source[]='    \'html\'=>array(\''.$cacheHtmlBuilderFile.'\',\''.$selector->getClass().'_builder_html\'), ';
        $source[]='    );';
        $source[]=' public function __construct($sel, &$container, $reset = false){';
        $source[]='          parent::__construct($sel, $container, $reset); ';

        $srcHtmlBuilder=array();
        $srcHtmlBuilder[]='<?php class '.$selector->getClass().'_builder_html extends jFormsHtmlBuilderBase {';
        $srcHtmlBuilder[]=' public function __construct($form, $action, $actionParams){';
        $srcHtmlBuilder[]='          parent::__construct($form, $action, $actionParams); ';
        $srcHtmlBuilder[]='  }';

        $srcjs=array();
        $srcjs[]='$js="jForms.tForm = new jFormsForm(\'".$this->_name."\');\n";';
        $srcjs[]='$js.="jForms.tForm.setErrorDecorator(new ".$errorDecoratorName."());\n";';
        $srcjs[]='$js.="jForms.tForm.setHelpDecorator(new ".$helpDecoratorName."());\n";';

        foreach($xml->children() as $controltype=>$control){
            $source[] = $this->generatePHPControl($controltype, $control);
            $srcjs[] =  $this->generateJsControl($controltype, $control);
        }
        $source[]="  }\n} ?>";

        jFile::write($cachefile, implode("\n", $source));
        $srcjs[]='$js.="jForms.declareForm(jForms.tForm);\n";';

        $srcHtmlBuilder[]=' public function getJavascriptCheck($errorDecoratorName, $helpDecoratorName){';
        $srcHtmlBuilder[]= implode("\n", $srcjs);
        $srcHtmlBuilder[]=' return $js; }';
        $srcHtmlBuilder[]='} ?>';

        jFile::write($cacheHtmlBuilderFile, implode("\n", $srcHtmlBuilder));
        return true;
    }


    protected function generatePHPControl($controltype, $control){

        $source = array();
        $class = 'jFormsControl'.$controltype;

        if(!class_exists($class,false)){
            throw new jException('jelix~formserr.unknow.tag',array($controltype,$this->sourceFile));
        }

        if(!isset($control['ref'])){
            throw new jException('jelix~formserr.attribute.missing',array('ref',$controltype,$this->sourceFile));
        }

        // instancie the class
        $source[]='$ctrl= new '.$class.'(\''.(string)$control['ref'].'\');';
        // generating the datatype object
        if(isset($control['type'])){
            if($controltype != 'input'){
                throw new jException('jelix~formserr.attribute.not.allowed',array('type',$controltype,$this->sourceFile));
            }

            $dt = (string)$control['type'];
            if(!in_array(strtolower($dt), array('string','boolean','decimal','integer','hexadecimal',
                                                'datetime','date','time','localedatetime','localedate','localetime', 
                                                'url','email','ipv4','ipv6'))){
               throw new jException('jelix~formserr.datatype.unknow',array($dt,$controltype,$this->sourceFile));
            }
            if($dt != 'string')
                $source[]='$ctrl->datatype= new jDatatype'.$dt.'();';
        }else if($controltype == 'checkbox') {
            $source[]='$ctrl->datatype= new jDatatypeBoolean();';
        }
        
        // readonly support
        if(isset($control['readonly'])){
            if($controltype == 'output' || $controltype == 'submit'){
                throw new jException('jelix~formserr.attribute.not.allowed',array('readonly',$controltype,$this->sourceFile));
            }
            if('true' == (string)$control['readonly'])
                $source[]='$ctrl->readonly=true;';
        }
        // required support
        if(isset($control['required'])){
            if($controltype == 'checkbox' || $controltype == 'output' || $controltype == 'submit'){
                throw new jException('jelix~formserr.attribute.not.allowed',array('required',$controltype,$this->sourceFile));
            }
            if('true' == (string)$control['required'])
                $source[]='$ctrl->required=true;';
        }
        // defaultvalue support
        if(isset($control['defaultvalue'])){
            if($controltype != 'input' && $controltype != 'textarea' && $controltype != 'output'
                 && $controltype != 'checkbox'){
                throw new jException('jelix~formserr.attribute.not.allowed',array('defaultvalue',$controltype,$this->sourceFile));
            }
            $source[]='$ctrl->defaultValue=\''.str_replace('\'','\\\'',(string)$control['defaultvalue']) .'\';';
        }

        // label support
        if(!isset($control->label)){
            throw new jException('jelix~formserr.tag.missing',array('label',$controltype,$this->sourceFile));
        }

        if(isset($control->label['locale'])){
            $label='';
            $labellocale=(string)$control->label['locale'];
            $source[]='$ctrl->label=jLocale::get(\''.$labellocale.'\');';
        }else{
            $label=(string)$control->label;
            $labellocale='';
            $source[]='$ctrl->label=\''.str_replace("'","\\'",$label).'\';';
        }
        if(isset($control->help)){
            $source[]='$ctrl->hasHelp=true;';
        }
        if(isset($control->hint)){
            if(isset($control->hint['locale'])){
                $source[]='$ctrl->hint=jLocale::get(\''.(string)$control->hint['locale'].'\');';
            }else{
                $source[]='$ctrl->hint=\''.str_replace("'","\\'",(string)$control->hint).'\';';
            }
        }
        $alertInvalid='';
        $alertRequired='';
        if(isset($control->alert)){
            foreach($control->alert as $alert){
                if(isset($alert['locale'])){
                    $msg='jLocale::get(\''.(string)$alert['locale'].'\');';
                }else{
                    $msg='\''.str_replace("'","\\'",(string)$alert).'\';';
                }

                if(isset($alert['type'])){
                    if((string)$alert['type'] == 'required')
                        $alertRequired = '$ctrl->alertRequired='.$msg;
                    else
                        $alertInvalid = '$ctrl->alertInvalid='.$msg;
                } else {
                    $alertInvalid = '$ctrl->alertInvalid='.$msg;
                }
            }
            if($alertRequired !='') $source[]=$alertRequired;
            if($alertInvalid !='') $source[]=$alertInvalid;
        }
        $hasCtrl2 = false;
        $hasSelectedValues = false;
        switch($controltype){
            case 'checkboxes':
            case 'radiobuttons':
            case 'menulist':
            case 'listbox':
                // support of static datas or daos
                if(isset($control['selectedvalue']) && isset($control->selectedvalues)){
                    throw new jException('jelix~formserr.attribute.not.allowed',array('selectedvalue',$controltype,$this->sourceFile));
                }
                if(isset($control->selectedvalues) && isset($control->selectedvalues->value)){
                    if( ($controltype == 'listbox' && isset($control['multiple']) && 'true' != (string)$control['multiple'])
                        || $controltype == 'radiobuttons' || $controltype == 'menulist'
                        ){
                        throw new jException('jelix~formserr.defaultvalues.not.allowed',$this->sourceFile);
                    }
                    $str =' array(';
                    foreach($control->selectedvalues->value as $value){
                        $str.="'". str_replace("'","\\'", (string)$value) ."',";
                    }
                    $source[]='$ctrl->selectedValues='.$str.');';
                    $hasSelectedValues = true;
                }elseif(isset($control['selectedvalue'])){
                    $source[]='$ctrl->selectedValues=array(\''. str_replace("'","\\'", (string)$control['selectedvalue']) .'\');';
                    $hasSelectedValues = true;
                }
            case 'submit':
                // recuperer les <items> attr label|labellocale value
                if(isset($control['dao'])){
                    $daoselector = (string)$control['dao'];
                    $daomethod = (string)$control['daomethod'];
                    $daolabel = (string)$control['daolabelproperty'];
                    if(isset($control['daovalueproperty']))
                        $daovalue = (string)$control['daovalueproperty'];
                    else
                        $daovalue = '';
                    $source[]='$ctrl->datasource = new jFormDaoDatasource(\''.$daoselector.'\',\''.
                                    $daomethod.'\',\''.$daolabel.'\',\''.$daovalue.'\');';
                    if($controltype == 'submit'){
                        $source[]='$ctrl->standalone=false;';
                    }
                }elseif(isset($control->item)){
                    if($controltype == 'submit'){
                        $source[]='$ctrl->standalone=false;';
                    }
                    $source[]='$ctrl->datasource= new jFormStaticDatasource();';
                    $source[]='$ctrl->datasource->datas = array(';
                    $selectedvalues=array();
                    foreach($control->item as $item){
                        $value ="'".str_replace("'","\\'",(string)$item['value'])."'=>";
                        if(isset($item['locale'])){
                            $source[] = $value."jLocale::get('".(string)$item['locale']."'),";
                        }elseif( "" != (string)$item){
                            $source[] = $value."'".str_replace("'","\\'",(string)$item)."',";
                        }else{
                            $source[] = $value."'".str_replace("'","\\'",(string)$item['value'])."',";
                        }

                        if(isset($item['selected'])){
                            if($hasSelectedValues || $controltype == 'submit'){
                                throw new jException('jelix~formserr.selected.attribute.not.allowed',$this->sourceFile);
                            }
                            if((string)$item['selected']== 'true'){
                                $selectedvalues[]=(string)$item['value'];
                            }
                        }
                    }
                    $source[]=");";
                    if(count($selectedvalues)){
                        if(count($selectedvalues)>1 && 
                                (($controltype == 'listbox' && isset($control['multiple']) && 'true' != (string)$control['multiple'])
                                || $controltype == 'radiobuttons' || $controltype == 'menulist')  ){
                            throw new jException('jelix~formserr.multiple.selected.not.allowed',$this->sourceFile);
                        }
                        $source[]='$ctrl->selectedValues='.var_export($selectedvalues,true).';';
                    }
                }else{
                    $source[]='$ctrl->datasource= new jFormStaticDatasource();';
                }

               break;
            case 'secret':
                if(isset($control->confirm)) {
                    $label='';
                    if(isset($control->confirm['locale'])){
                        $label = "jLocale::get('".(string)$control->confirm['locale']."');";
                    }elseif( "" != (string)$control->confirm) {
                        $label = "'".str_replace("'","\\'",(string)$control->confirm)."';";
                    }else{
                        throw new jException('jelix~formserr.content.missing',array('confirm',$this->sourceFile));
                    }
                    $source[]='$ctrl2 = new jFormsControlSecretConfirm(\''.(string)$control['ref'].'_confirm\');';
                    $source[]='$ctrl2->primarySecret = \''.(string)$control['ref'].'\';';
                    $source[]='$ctrl2->label='.$label;
                    $source[]='$ctrl2->required = $ctrl->required;';
                    $source[]='$ctrl2->readonly = $ctrl->readonly;';
                    if($alertInvalid!='')
                        $source[]='$ctrl2->alertInvalid = $ctrl->alertInvalid;';
                    if($alertRequired!='')
                        $source[]='$ctrl2->alertRequired = $ctrl->alertRequired;';
                    
                    if(isset($control->help)){
                        $source[]='$ctrl2->hasHelp=true;';
                    }
                    if(isset($control->hint)){
                        $source[]='$ctrl2->hint=$ctrl->hint;';
                    }
                    $hasCtrl2 = true;
                }
                break;
        }

        if(isset($control['multiple'])){
            if($controltype != 'listbox'){
                throw new jException('jelix~formserr.attribute.not.allowed',array('multiple',$controltype,$this->sourceFile));
            }
            if('true' == (string)$control['multiple'])
                $source[]='$ctrl->multiple=true;';
        }
        if(isset($control['size'])){
            if($controltype != 'listbox'){
                throw new jException('jelix~formserr.attribute.not.allowed',array('size',$controltype,$this->sourceFile));
            }
            $size = intval((string)$control['size']);
            if($size < 2) $size = 2;
            $source[]='$ctrl->size='.$size.';';
        }

        if(isset($control['valueoncheck']) || isset($control['valueonuncheck'])){
            if($controltype != 'checkbox'){
                throw new jException('jelix~formserr.attribute.not.allowed',array('valueon*',$controltype,$this->sourceFile));
            }
            if(isset($control['valueoncheck']))
                $source[]='$ctrl->valueOnCheck=\''.str_replace("'","\\'", (string)$control['valueoncheck']) ."';";
            if(isset($control['valueonuncheck']))
                $source[]='$ctrl->valueOnUncheck=\''.str_replace("'","\\'", (string)$control['valueonuncheck']) ."';";
        }

        if(isset($control['maxsize'])){
            if($controltype != 'upload'){
                throw new jException('jelix~formserr.attribute.not.allowed',array('upload',$controltype,$this->sourceFile));
            }
            $source[]='$ctrl->maxsize='.intval((string)$control['maxsize']).';';
        }
        if(isset($control['mimetype'])){
            if($controltype != 'upload'){
                throw new jException('jelix~formserr.attribute.not.allowed',array('upload',$controltype,$this->sourceFile));
            }
            $mime = split('[,; ]',(string)$control['mimetype']);
            $mime = array_diff($mime, array('')); // we remove all ''

            $source[]='$ctrl->mimetype='.var_export($mime,true).';';
        }

        $source[]='$this->addControl($ctrl);';
        if($hasCtrl2)
            $source[]='$this->addControl($ctrl2);';
        return implode("\n", $source);
    }

    protected function generateJsControl($controltype, $control){
        // in this method, we generate a PHP script which will generate a javascript script ;-)

        if($controltype == 'submit')
            return '';

        if(isset($control->confirm) && $controltype == 'secret') {
            $hasConfirm = true;
        }else{
            $hasConfirm = false;
        }

        $source = array();

        if(isset($control['type'])){
            $dt = (string)$control['type'];
        }else if($controltype == 'checkbox')
            $dt = 'boolean';
        else
            $dt = 'string';

        if(isset($control->label['locale'])){
            $source[]='$label = str_replace("\'","\\\'",jLocale::get(\''.(string)$control->label['locale'].'\'));';
        }else{
            $source[]='$label = str_replace("\'","\\\'",\''.str_replace("'","\\'",(string)$control->label).'\');';
        }
        if($controltype == 'checkboxes' || ($controltype == 'listbox' && isset($control['multiple']) && 'true' == (string)$control['multiple']))
            $source[]='$js.="jForms.tControl = new jFormsControl(\''.(string)$control['ref'].'[]\', \'".$label."\', \''.$dt.'\');\n";';
        else{
            $source[]='$js.="jForms.tControl = new jFormsControl(\''.(string)$control['ref'].'\', \'".$label."\', \''.$dt.'\');\n";';
            if($hasConfirm){
                if(isset($control->confirm['locale'])){
                    $source[]='$label2 = str_replace("\'","\\\'",jLocale::get(\''.(string)$control->confirm['locale'].'\'));';                
                }else{
                    $source[]='$label2 = str_replace("\'","\\\'",\''.str_replace("'","\\'",(string)$control->confirm).'\');';
                }
                $source[]='$js.="jForms.tControl2 = new jFormsControl(\''.(string)$control['ref'].'_confirm\', \'".$label2."\', \''.$dt.'\');\n";';
            }
        }

        if($dt == 'localedate' || $dt =='localedatetime' || $dt =='localetime'){
            $source[]='$js.="jForms.tControl.lang=\'".$GLOBALS[\'gJConfig\']->locale."\';\n";';
        }

        if(isset($control['readonly']) && 'true' == (string)$control['readonly']){
            $source[]='$js.="jForms.tControl.readonly = true;\n";';
            if($hasConfirm) $source[]='$js.="jForms.tControl2.readonly = true;\n";';
        }
        if(isset($control['required']) && 'true' == (string)$control['required']){
            $source[]='$js.="jForms.tControl.required = true;\n";';
            if($hasConfirm) $source[]='$js.="jForms.tControl2.required = true;\n";';
        }

        if(isset($control->help)){
            if(isset($control->help['locale'])){
                $help='str_replace("\'","\\\'",jLocale::get(\''.(string)$control->help['locale'].'\'))';
            }else{
                $help='str_replace("\'","\\\'",\''.str_replace("'","\\'",(string)$control->help).'\')';
            }
            $source[]='$js.="jForms.tControl.help=\'".'.$help.'."\';\n";';
            if($hasConfirm) $source[]='$js.="jForms.tControl2.help=jForms.tControl.help;\n";';
        }

        $alertInvalid='str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))';
        $alertRequired='str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))';

        if(isset($control->alert)){
            foreach($control->alert as $alert){
                if(isset($alert['locale'])){
                    $msg='str_replace("\'","\\\'",jLocale::get(\''.(string)$alert['locale'].'\'))';
                }else{
                    $msg='str_replace("\'","\\\'",\''.str_replace("'","\\'",(string)$alert).'\')';
                }

                if(isset($alert['type'])){
                    if((string)$alert['type'] == 'required')
                        $alertRequired = $msg;
                    else
                        $alertInvalid = $msg;
                } else {
                    $alertInvalid = $msg;
                }
            }
        }

        $source[]='$js.="jForms.tControl.errRequired=\'".'.$alertRequired.'."\';\n";';
        $source[]='$js.="jForms.tControl.errInvalid =\'".'.$alertInvalid.'."\';\n";';
        if($hasConfirm){
            $alertInvalid='str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label2))';
            $alertRequired='str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label2))';
            $source[]='$js.="jForms.tControl2.errRequired=\'".'.$alertRequired.'."\';\n";';
            $source[]='$js.="jForms.tControl2.errInvalid =\'".'.$alertInvalid.'."\';\n";';
        }

        if(isset($control['multiple']) && 'true' == (string)$control['multiple']){
            $source[]='$js.="jForms.tControl.multiple = true;\n";';
        }
        $source[]='$js.="jForms.tForm.addControl( jForms.tControl);\n";';
        if($hasConfirm) {
            $source[]='$js.="jForms.tControl2.isConfirmField=true;\njForms.tControl2.confirmFieldOf=\''.(string)$control['ref'].'\';\n";';
            $source[]='$js.="jForms.tForm.addControl( jForms.tControl2);\n";';
        }

        return implode("\n", $source);
    }
}

?>
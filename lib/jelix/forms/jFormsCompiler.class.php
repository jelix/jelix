<?php
/**
* @package    jelix
* @subpackage forms
* @author     Laurent Jouanneau
* @contributor Loic Mathaud, Dominique Papin
* @contributor Uriel Corfa Emotic SARL
* @copyright   2006-2007 Laurent Jouanneau
* @copyright   2007 Loic Mathaud, 2007 Dominique Papin
* @copyright   2007 Emotic SARL
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require(JELIX_LIB_FORMS_PATH.'jIFormsBuilderCompiler.iface.php');

/**
 * generates form class from an xml file describing the form
 * @package     jelix
 * @subpackage  forms
 */
class jFormsCompiler implements jISimpleCompiler {

    protected $sourceFile;


    public function compile($selector){
        global $gJCoord;
        global $gJConfig;
        $sel = clone $selector;

        $this->sourceFile = $selector->getPath();

        $source=array();
        $source[]='<?php ';
        $source[]='class '.$selector->getClass().' extends jFormsBase {';
        $source[]='    protected $_builders = array( ';

        $srcBuilders=array();
        $buildersCompilers = array();
        foreach($gJConfig->_pluginsPathList_jforms as $buildername => $pluginPath) {
            require_once($pluginPath.$buildername.'.jformscompiler.php');
            $classname = $buildername.'JformsCompiler';
            $buildersCompilers[$buildername] = new $classname();

            $srcBuilders[$buildername]=array();
            $srcBuilders[$buildername][] = '<?php ';
            $srcBuilders[$buildername][] = ' require_once(\''.$pluginPath.$buildername.'.jformsbuilder.php\'); ';
            $srcBuilders[$buildername][] = ' class '.$selector->getClass().'_builder_'.$buildername.' extends '.$buildername.'JformsBuilder'.' {';
            $srcBuilders[$buildername][] = ' public function __construct($form, $action, $actionParams){';
            $srcBuilders[$buildername][] = '          parent::__construct($form, $action, $actionParams); ';
            $srcBuilders[$buildername][] = '  }';
            $srcBuilders[$buildername][] = $buildersCompilers[$buildername]->startCompile();

            $source[]='    \''.$buildername.'\'=>array(\''.$selector->getCompiledBuilderFilePath($buildername).'\',\''.$selector->getClass().'_builder_'.$buildername.'\'), ';
        }

        // chargement du fichier XML
        $doc = new DOMDocument();

        if(!$doc->load($this->sourceFile)){
            throw new jException('jelix~formserr.invalid.xml.file',array($this->sourceFile));
        }

        $source[]='    );';
        $source[]=' public function __construct($sel, &$container, $reset = false){';
        $source[]='          parent::__construct($sel, $container, $reset); ';

        $this->generatePHPContent($doc, $source, $srcBuilders, $buildersCompilers);

        $source[]="  }\n} ?>";
        jFile::write($selector->getCompiledFilePath(), implode("\n", $source));

        foreach($gJConfig->_pluginsPathList_jforms as $buildername => $pluginPath) {
            $srcBuilders[$buildername][]= $buildersCompilers[$buildername]->endCompile();
            $srcBuilders[$buildername][]= '} ?>';
            jFile::write($selector->getCompiledBuilderFilePath($buildername), implode("\n", $srcBuilders[$buildername]));
        }
        return true;
    }

    protected function generatePHPContent($doc, &$source, &$srcBuilders, &$buildersCompilers){
        global $gJConfig;
        if($doc->documentElement->namespaceURI != JELIX_NAMESPACE_BASE.'forms/1.0'){
           throw new jException('jelix~formserr.namespace.wrong',array($this->sourceFile));
        }

        $xml = simplexml_import_dom($doc);

        if (count($xml->reset) > 1 )
            throw new jException('jelix~formserr.notunique.tag',array('reset',$this->sourceFile));

        foreach($xml->children() as $controltype=>$control){
            $source[] = $this->generatePHPControl($controltype, $control);
            foreach($gJConfig->_pluginsPathList_jforms as $buildername => $pluginPath) {
                $srcBuilders[$buildername][]= $buildersCompilers[$buildername]->generateControl($controltype, $control);
            }
        }
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
            if($controltype == 'output' || $controltype == 'submit' || $controltype == 'reset'){
                throw new jException('jelix~formserr.attribute.not.allowed',array('readonly',$controltype,$this->sourceFile));
            }
            if('true' == (string)$control['readonly'])
                $source[]='$ctrl->readonly=true;';
        }
        // required support
        if(isset($control['required'])){
            if($controltype == 'checkbox' || $controltype == 'output' || $controltype == 'submit' || $controltype == 'reset'){
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
        // minlength support
        if(isset($control['minlength'])){
            if($controltype != 'textarea' &&
                ($controltype != 'input'|| ($controltype == 'input' && isset($control['type']) && $control['type'] != 'string'))){
                throw new jException('jelix~formserr.attribute.not.allowed',array('minlength',$controltype,$this->sourceFile));
            }
            $source[]='$ctrl->datatype->addFacet(\'minLength\','.intval((string)$control['minlength']).');';
        }
        // maxlength support
        if(isset($control['maxlength'])){
            if($controltype != 'textarea' &&
                ($controltype != 'input'|| ($controltype == 'input' && isset($control['type']) && $control['type'] != 'string'))){
                throw new jException('jelix~formserr.attribute.not.allowed',array('maxlength',$controltype,$this->sourceFile));
            }
            $source[]='$ctrl->datatype->addFacet(\'maxLength\','.intval((string)$control['maxlength']).');';
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
        if(isset($control['size'])){
            if (!in_array($controltype, array('listbox', 'input', 'secret'))) {
                throw new jException('jelix~formserr.attribute.not.allowed',array('size',$controltype,$this->sourceFile));
            }
            $size = intval((string)$control['size']);
            if($size < 2) $size = 2;
            $source[]='$ctrl->size='.$size.';';
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
                    $source[]='$ctrl->defaultValue='.$str.');';
                    $hasSelectedValues = true;
                }elseif(isset($control['selectedvalue'])){
                    $source[]='$ctrl->defaultValue=array(\''. str_replace("'","\\'", (string)$control['selectedvalue']) .'\');';
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
                }elseif(isset($control['dsclass'])){
                    $dsclass = (string)$control['dsclass'];
                    $class = new jSelectorClass($dsclass);
                    $source[]='jClasses::inc(\''.$dsclass.'\');';
                    $source[]='$datasource = new '.$class->className.'($this->id());';
                    $source[]='if ($datasource instanceof jIFormDatasource){$ctrl->datasource=$datasource;}';
                    $source[]='else{$ctrl->datasource=new jFormStaticDatasource();}';
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
                        $source[]='$ctrl->defaultValue='.var_export($selectedvalues,true).';';
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
                    if (isset($control['size'])) {
                        $source[]='$ctrl2->size=$ctrl->size;';
                    }
                    $hasCtrl2 = true;
                }
                break;
        }

        if (isset($control['rows'])) {
            if ($controltype != 'textarea') {
                throw new jException('jelix~formserr.attribute.not.allowed',array('rows',$controltype,$this->sourceFile));
            }
            $rows = intval((string)$control['rows']);
            if($rows < 2) $rows = 2;
            $source[]='$ctrl->rows='.$rows.';';
        }

        if (isset($control['cols'])) {
            if ($controltype != 'textarea') {
                throw new jException('jelix~formserr.attribute.not.allowed',array('cols',$controltype,$this->sourceFile));
            }
            $cols = intval((string)$control['cols']);
            if($cols < 2) $cols = 2;
            $source[]='$ctrl->cols='.$cols.';';
        }

        if(isset($control['multiple'])){
            if($controltype != 'listbox'){
                throw new jException('jelix~formserr.attribute.not.allowed',array('multiple',$controltype,$this->sourceFile));
            }
            if('true' == (string)$control['multiple'])
                $source[]='$ctrl->multiple=true;';
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
                throw new jException('jelix~formserr.attribute.not.allowed',array('upload', $controltype, $this->sourceFile));
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
}

?>

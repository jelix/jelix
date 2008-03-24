<?php
/**
* @package    jelix
* @subpackage forms
* @author     Laurent Jouanneau
* @contributor Loic Mathaud, Dominique Papin
* @contributor Uriel Corfa Emotic SARL
* @copyright   2006-2008 Laurent Jouanneau
* @copyright   2007 Loic Mathaud, 2007 Dominique Papin
* @copyright   2007 Emotic SARL
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require(JELIX_LIB_PATH.'forms/jIFormsBuilderCompiler.iface.php');

/**
 * generates form class from an xml file describing the form
 * @package     jelix
 * @subpackage  forms
 */
class jFormsCompiler implements jISimpleCompiler {

    protected $sourceFile;

    protected $doubleControl;

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
        $this->doubleControl = false;
        $source = array();
        $class = 'jFormsControl'.$controltype;

        $attributes = array();
        foreach ($control->attributes() as $name=>$value){
            $attributes[$name]=(string)$value;
        }

        if(!class_exists($class,false)){
            throw new jException('jelix~formserr.unknow.tag',array($controltype,$this->sourceFile));
        }

        if(!isset($attributes['ref'])){
            throw new jException('jelix~formserr.attribute.missing',array('ref',$controltype,$this->sourceFile));
        }

        // instancie the class
        $source[]='$ctrl= new '.$class.'(\''.$attributes['ref'].'\');';
        unset($attributes['ref']);

        $name='generate'.$controltype;
        $this->$name($source, $control, $attributes);

        if(count($attributes)) {
            reset($attributes);
            throw new jException('jelix~formserr.attribute.not.allowed',array(key($attributes),$controltype,$this->sourceFile));
        }

        $source[]='$this->addControl($ctrl);';
        if ($this->doubleControl)
            $source[]='$this->addControl($ctrl2);';
        return implode("\n", $source);
    }

    protected function generateInput(&$source, $control, &$attributes) {
        $type='string';
        if(isset($attributes['type'])){
            $type = strtolower($attributes['type']);
            if(!in_array($type, array('string','boolean','decimal','integer','hexadecimal',
                                                'datetime','date','time','localedatetime','localedate','localetime',
                                                'url','email','ipv4','ipv6','html'))){
                throw new jException('jelix~formserr.datatype.unknow',array($type,'input',$this->sourceFile));
            }

            if($type != 'string')
                $source[]='$ctrl->datatype= new jDatatype'.$type.'();';
            unset($attributes['type']);
        }
        $this->attrReadonly($source, $attributes);
        $this->attrRequired($source, $attributes);
        $this->attrDefaultvalue($source, $attributes);
        if(isset($attributes['minlength'])){
            if($type != 'string' && $type != 'html'){
                throw new jException('jelix~formserr.attribute.not.allowed',array('minlength','input',$this->sourceFile));
            }
            $source[]='$ctrl->datatype->addFacet(\'minLength\','.intval($attributes['minlength']).');';
            unset($attributes['minlength']);
        }
        if(isset($attributes['maxlength'])){
            if($type != 'string' && $type != 'html'){
                throw new jException('jelix~formserr.attribute.not.allowed',array('maxlength','input',$this->sourceFile));
            }
            $source[]='$ctrl->datatype->addFacet(\'maxLength\','.intval($attributes['maxlength']).');';
            unset($attributes['maxlength']);
        }
        $this->readLabel($source, $control, 'input');
        $this->readHelpHintAlert($source, $control);
        $this->attrSize($source, $attributes);
    }

    protected function generateTextarea(&$source, $control, &$attributes) {
        if(isset($attributes['type'])){
            if ( $attributes['type'] != 'html') {
                throw new jException('jelix~formserr.datatype.unknow',array($attributes['type'],'textarea',$this->sourceFile));
            }
            $source[]='$ctrl->datatype= new jDatatypeHtml();';
            unset($attributes['type']);
        }
        $this->attrReadonly($source, $attributes);
        $this->attrRequired($source, $attributes);
        $this->attrDefaultvalue($source, $attributes);

        if(isset($attributes['minlength'])){
            $source[]='$ctrl->datatype->addFacet(\'minLength\','.intval($attributes['minlength']).');';
            unset($attributes['minlength']);
        }
        if(isset($attributes['maxlength'])){
            $source[]='$ctrl->datatype->addFacet(\'maxLength\','.intval($attributes['maxlength']).');';
            unset($attributes['maxlength']);
        }
        $this->readLabel($source, $control, 'textarea');
        $this->readHelpHintAlert($source, $control);
        if (isset($attributes['rows'])) {
            $rows = intval($attributes['rows']);
            if($rows < 2) $rows = 2;
            $source[]='$ctrl->rows='.$rows.';';
            unset($attributes['rows']);
        }

        if (isset($attributes['cols'])) {
            $cols = intval($attributes['cols']);
            if($cols < 2) $cols = 2;
            $source[]='$ctrl->cols='.$cols.';';
            unset($attributes['cols']);
        }
    }

    protected function generateOutput(&$source, $control, &$attributes) {
        $this->attrDefaultvalue($source, $attributes);
        $this->readLabel($source, $control, 'output');
        $this->readHelpHintAlert($source, $control);

    }

    protected function generateSubmit(&$source, $control, &$attributes) {
        $this->readLabel($source, $control, 'submit');
        $this->readHelpHintAlert($source, $control);
        $this->readDatasource($source, $control, 'submit', $attributes);
    }

    protected function generateReset(&$source, $control, &$attributes) {
        $this->attrReadonly($source, $attributes);
        $this->readLabel($source, $control, 'reset');
        $this->readHelpHintAlert($source, $control);

    }

    protected function generateCheckbox(&$source, $control, &$attributes) {
        $source[]='$ctrl->datatype= new jDatatypeBoolean();';
        $this->attrDefaultvalue($source, $attributes);
        $this->readLabel($source, $control, 'checkbox');
        $this->readHelpHintAlert($source, $control);
        if(isset($attributes['valueoncheck'])){
            $source[]='$ctrl->valueOnCheck=\''.str_replace("'","\\'", $attributes['valueoncheck']) ."';";
            unset($attributes['valueoncheck']);
        }
        if(isset($attributes['valueonuncheck'])){
            $source[]='$ctrl->valueOnUncheck=\''.str_replace("'","\\'", $attributes['valueonuncheck']) ."';";
            unset($attributes['valueonuncheck']);
        }
    }

    protected function generateHidden(&$source, $control, &$attributes) {
        $this->attrDefaultvalue($source, $attributes);
    }

    protected function generateCheckboxes(&$source, $control, &$attributes) {
        $this->attrReadonly($source, $attributes);
        $this->attrRequired($source, $attributes);
        $this->readLabel($source, $control, 'checkboxes');
        $this->readHelpHintAlert($source, $control);
        $hasSelectedValues = $this->readSelectedValue($source, $control, 'checkboxes', $attributes);
        $this->readDatasource($source, $control, 'checkboxes', $attributes, $hasSelectedValues);
    }

    protected function generateRadiobuttons(&$source, $control, &$attributes) {
        $this->attrReadonly($source, $attributes);
        $this->attrRequired($source, $attributes);
        $this->readLabel($source, $control, 'radiobuttons');
        $this->readHelpHintAlert($source, $control);
        $hasSelectedValues = $this->readSelectedValue($source, $control, 'radiobuttons', $attributes);
        $this->readDatasource($source, $control, 'radiobuttons', $attributes, $hasSelectedValues);
    }

    protected function generateMenulist(&$source, $control, &$attributes) {
        $this->attrReadonly($source, $attributes);
        $this->attrRequired($source, $attributes);
        $this->readLabel($source, $control, 'menulist');
        $this->readHelpHintAlert($source, $control);
        $hasSelectedValues = $this->readSelectedValue($source, $control, 'menulist', $attributes);
        $this->readDatasource($source, $control, 'menulist', $attributes, $hasSelectedValues);
    }

    protected function generateListbox(&$source, $control, &$attributes) {
        $this->attrReadonly($source, $attributes);
        $this->attrRequired($source, $attributes);
        $this->readLabel($source, $control, 'listbox');
        $this->readHelpHintAlert($source, $control);
        $this->attrSize($source, $attributes);
        $hasSelectedValues = $this->readSelectedValue($source, $control, 'listbox', $attributes);
        $this->readDatasource($source, $control, 'listbox', $attributes, $hasSelectedValues);
        if(isset($attributes['multiple'])){
            if('true' == $attributes['multiple'])
                $source[]='$ctrl->multiple=true;';
            unset($attributes['multiple']);
        }
    }

    protected function generateSecret(&$source, $control, &$attributes) {
        $this->attrReadonly($source, $attributes);
        $this->attrRequired($source, $attributes);
        $this->readLabel($source, $control, 'secret');
        list($alertInvalid, $alertRequired)=$this->readHelpHintAlert($source, $control);
        $this->attrSize($source, $attributes);

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
            $this->doubleControl = true;
        }
    }

    protected function generateUpload(&$source, $control, &$attributes) {
        $this->attrReadonly($source, $attributes);
        $this->attrRequired($source, $attributes);
        $this->readLabel($source, $control, 'input');
        $this->readHelpHintAlert($source, $control);

        if(isset($attributes['maxsize'])){
            $source[]='$ctrl->maxsize='.intval($attributes['maxsize']).';';
            unset($attributes['maxsize']);
        }

        if(isset($attributes['mimetype'])){
            $mime = split('[,; ]',$attributes['mimetype']);
            $mime = array_diff($mime, array('')); // we remove all ''
            $source[]='$ctrl->mimetype='.var_export($mime,true).';';
            unset($attributes['mimetype']);
        }
    }

    protected function attrReadonly(&$source, &$attributes) {
        if(isset($attributes['readonly'])){
            if('true' == $attributes['readonly'])
                $source[]='$ctrl->readonly=true;';
            unset($attributes['readonly']);
        }
    }

    protected function attrRequired(&$source, &$attributes) {
        if(isset($attributes['required'])){
            if('true' == $attributes['required'])
                $source[]='$ctrl->required=true;';
            unset($attributes['required']);
        }
    }

    protected function attrDefaultvalue(&$source, &$attributes) {
        if(isset($attributes['defaultvalue'])){
            $source[]='$ctrl->defaultValue=\''.str_replace('\'','\\\'',$attributes['defaultvalue']).'\';';
            unset($attributes['defaultvalue']);
        }
    }

    protected function attrSize(&$source, &$attributes) {
        if(isset($attributes['size'])){
            $size = intval($attributes['size']);
            if($size < 2) $size = 2;
            $source[]='$ctrl->size='.$size.';';
            unset($attributes['size']);
        }
    }

    protected function readLabel(&$source, $control, $controltype) {
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
    }

    protected function readHelpHintAlert(&$source, $control) {
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
        return array($alertInvalid, $alertRequired);
    }

    protected function readSelectedValue(&$source, $control, $controltype, &$attributes) {
        // support of static datas or daos
        if(isset($attributes['selectedvalue']) && isset($control->selectedvalues)){
            throw new jException('jelix~formserr.attribute.not.allowed',array('selectedvalue',$controltype,$this->sourceFile));
        }
        $hasSelectedValues = false;
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
        }elseif(isset($attributes['selectedvalue'])){
            $source[]='$ctrl->defaultValue=array(\''. str_replace("'","\\'", (string)$control['selectedvalue']) .'\');';
            $hasSelectedValues = true;
            unset($attributes['selectedvalue']);
        }
        return $hasSelectedValues;
    }

    protected function readDatasource(&$source, $control, $controltype, &$attributes, $hasSelectedValues=false) {
        // recuperer les <items> attr label|labellocale value
        if(isset($attributes['dao'])){
            if(isset($attributes['daovalueproperty'])) {
                $daovalue = $attributes['daovalueproperty'];
                unset($attributes['daovalueproperty']);
            } else
                $daovalue = '';
            $source[]='$ctrl->datasource = new jFormDaoDatasource(\''.$attributes['dao'].'\',\''.
                            $attributes['daomethod'].'\',\''.$attributes['daolabelproperty'].'\',\''.$daovalue.'\');';
            unset($attributes['dao']);
            unset($attributes['daomethod']);
            unset($attributes['daolabelproperty']);
            if($controltype == 'submit'){
                $source[]='$ctrl->standalone=false;';
            }
        }elseif(isset($attributes['dsclass'])){
            $dsclass = $attributes['dsclass'];
            unset($attributes['dsclass']);
            $class = new jSelectorClass($dsclass);
            $source[]='jClasses::inc(\''.$dsclass.'\');';
            $source[]='$datasource = new '.$class->className.'($this->id());';
            $source[]='if ($datasource instanceof jIFormDatasource){$ctrl->datasource=$datasource;}';
            $source[]='else{$ctrl->datasource=new jFormStaticDatasource();}';
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
                $source[]='$ctrl->defaultValue='.var_export($selectedvalues,true).';';
            }
        }else{
            $source[]='$ctrl->datasource= new jFormStaticDatasource();';
        }
    }
}

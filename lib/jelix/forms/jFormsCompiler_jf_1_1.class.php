<?php
/**
* @package    jelix
* @subpackage forms
* @author     Laurent Jouanneau
* @contributor Loic Mathaud, Dominique Papin
* @contributor Uriel Corfa Emotic SARL
* @copyright   2006-2008 Laurent Jouanneau
* @copyright   2007 Loic Mathaud, 2007-2008 Dominique Papin
* @copyright   2007 Emotic SARL
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
require_once(JELIX_LIB_PATH.'forms/jFormsCompiler_jf_1_0.class.php');
/**
 * generates form class from an xml file describing the form
 * @package     jelix
 * @subpackage  forms
 */
class jFormsCompiler_jf_1_1 extends jFormsCompiler_jf_1_0 {

    const NS = 'http://jelix.org/ns/forms/1.1';

    protected $allowedInputType = array('string','boolean','decimal','integer','hexadecimal',
                                      'datetime','date','time','localedatetime','localedate','localetime',
                                      'url','email','ipv4','ipv6','html');

    protected function generateTextarea(&$source, $control, &$attributes) {
        if(isset($attributes['type'])){
            if ( $attributes['type'] != 'html') {
                throw new jException('jelix~formserr.datatype.unknow',array($attributes['type'],'textarea',$this->sourceFile));
            }
            $source[]='$ctrl->datatype= new jDatatypeHtml();';
            unset($attributes['type']);
        }
        return $this->_generateTextareaHtmlEditor($source, $control, $attributes);
    }

    protected function _generateTextareaHtmlEditor(&$source, $control, &$attributes) {
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
        return false;
    }

    protected function generateHtmleditor(&$source, $control, &$attributes) {
        $this->_generateTextareaHtmlEditor($source, $control, $attributes);

        if (isset($attributes['config'])) {
            $source[]='$ctrl->config=\''.str_replace("'","\\'",$attributes['config']).'\';';
            unset($attributes['config']);
        }
        if (isset($attributes['skin'])) {
            $source[]='$ctrl->skin=\''.str_replace("'","\\'",$attributes['skin']).'\';';
            unset($attributes['skin']);
        }
        return false;
    }

    protected function generateHidden(&$source, $control, &$attributes) {
        $this->attrDefaultvalue($source, $attributes);
        return false;
    }

    protected function generateCaptcha(&$source, $control, &$attributes) {
        $this->readLabel($source, $control, 'captcha');
        $this->readHelpHintAlert($source, $control);
        return false;
    }

    protected function readDatasource(&$source, $control, $controltype, &$attributes, $hasSelectedValues=false) {

        if(isset($control->datasource)) {
            $attrs = array();
            foreach ($control->datasource->attributes() as $name=>$value){
                $attrs[$name]=(string)$value;
            }

            if(isset($attrs['dao'])) {
                if ( isset($attrs['profile']))
                    $profile = ',\''.$attrs['profile'].'\'';
                else
                    $profile = ',\'\'';
                if(isset($attrs['valueproperty'])) {
                    $daovalue = $attrs['valueproperty'];
                } else
                    $daovalue = '';
                if(!isset($attrs['method']))
                    throw new jException('jelix~formserr.attribute.missing',array('method', 'datasource',$this->sourceFile));
                if(!isset($attrs['labelproperty']))
                    throw new jException('jelix~formserr.attribute.missing',array('method', 'datasource',$this->sourceFile));

                if(isset($attrs['criteria']))
                    $criteria=',\''.$attrs['criteria'].'\',null';
                elseif(isset($attrs['criteriafrom']))
                    $criteria=',null,\''.$attrs['criteriafrom'].'\'';
                else
                    $criteria=',null,null';
                if ( isset($attrs['labelseparator']))
                    $labelSeparator = ',\''.$attrs['labelseparator'].'\'';
                else
                    $labelSeparator = '';

                $source[]='$ctrl->datasource = new jFormsDaoDatasource(\''.$attrs['dao'].'\',\''.
                                 $attrs['method'].'\',\''.$attrs['labelproperty'].'\',\''.$daovalue.'\''.$profile.$criteria.$labelSeparator.');';
                if($controltype == 'submit'){
                    $source[]='$ctrl->standalone=false;';
                }
            }else if(isset($attrs['class'])) {
                $class = new jSelectorClass($attrs['class']);
                $source[]='jClasses::inc(\''.$attrs['class'].'\');';
                $source[]='$datasource = new '.$class->className.'($this->id());';
                $source[]='if ($datasource instanceof jIFormsDatasource){$ctrl->datasource=$datasource;}';
                $source[]='else{$ctrl->datasource=new jFormsStaticDatasource();}';
                if($controltype == 'submit'){
                    $source[]='$ctrl->standalone=false;';
                }
            } else {
                throw new jException('jelix~formserr.attribute.missing',array('class/dao', 'datasource',$this->sourceFile));
            }
        }elseif(isset($control->item)){
            // get all <items> and their label|labellocale attributes + their values
            if($controltype == 'submit'){
                $source[]='$ctrl->standalone=false;';
            }
            $source[]='$ctrl->datasource= new jFormsStaticDatasource();';
            $source[]='$ctrl->datasource->data = array(';
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
            $source[]='$ctrl->datasource= new jFormsStaticDatasource();';
        }
    }
}

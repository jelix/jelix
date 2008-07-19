<?php
/**
* @package     jelix
* @subpackage  forms
* @author      Laurent Jouanneau
* @contributor
* @copyright   2006-2008 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * HTML form compiler
 * @package     jelix
 * @subpackage  jelix-plugins
 */
class htmlJformsCompiler implements jIFormsBuilderCompiler {

    public function __construct($mainCompiler) {
        // nothing to do here. The two available main format of jforms are compatible here for javascript generation.

    }

    public function startCompile() {
        $srcjs= ' public function getJavascriptCheck($errorDecoratorName, $helpDecoratorName){'."\n";
        $srcjs.='$js="jForms.tForm = new jFormsForm(\'".$this->_name."\');\n";'."\n";
        $srcjs.='$js.="jForms.tForm.setErrorDecorator(new ".$errorDecoratorName."());\n";'."\n";
        $srcjs.='$js.="jForms.tForm.setHelpDecorator(new ".$helpDecoratorName."());\n";';
        return  $srcjs;
    }

    public function generateControl($controltype, $control) {
       // in this method, we generate a PHP script which will generate a javascript script ;-)

        if($controltype == 'submit' || $controltype == 'reset' || $controltype == 'hidden' )
            return '';

        $source = array();

        if ($controltype == 'group') {
            foreach($control->children() as $ctrltype=>$ctrl){
                if($ctrltype == 'label')
                    continue;
                $source[] = $this->_generateControl($ctrltype, $ctrl);
                $source[]='$js.="jForms.tForm.addControl(jForms.tControl);\n";';
            }
            return implode("\n", $source);

        } else if ($controltype == 'choice') {

            $source[]=$this->_generateControl($controltype, $control);
            $source[]='$js.="jForms.tControl2 = jForms.tControl;\n";';

            foreach($control->item as $item){
                $value = (string)$item['value'];
                $hasChild = false;
                foreach($item->children() as $ctrltype=>$ctrl){
                    if($ctrltype == 'label')
                        continue;
                    $hasChild = true;
                    $source[] = $this->_generateControl($ctrltype, $ctrl);
                    $source[]='$js.="jForms.tControl2.addControl(jForms.tControl, \''.str_replace("'","\\'",$value).'\');\n";';
                }
                if (!$hasChild) {
                    $source[]='$js.="jForms.tControl2.items[\''.str_replace("'","\\'",$value).'\']=[];\n";';
                }
            }

            $source[]='$js.="jForms.tForm.addControl(jForms.tControl2);\n";';
        } else {

            $source[]= $this->_generateControl($controltype, $control);
            $source[]='$js.="jForms.tForm.addControl(jForms.tControl);\n";';
        }

        return implode("\n", $source);
    }

    protected function _generateControl($controltype, $control) {
       // in this method, we generate a PHP script which will generate a javascript script ;-)

        if($controltype == 'submit' || $controltype == 'reset' || $controltype == 'hidden' )
            return '';

        $source = array();

        $hasConfirm = false;
        $isLocale = false;

        if($controltype == 'secret') {
            $hasConfirm = isset($control->confirm);
            $dt = 'Secret';
        } else if ($controltype == 'checkbox') {
            $dt = 'Boolean';
        } else if ($controltype == 'choice') {
            $dt = 'Choice';
        } else if (isset($control['type'])) {
            $dt = $dt2 = ucfirst((string)$control['type']);
            if($dt == 'Html') $dt = 'String';
            else if ($dt == 'Localetime') { $dt = 'Time'; $isLocale = true; }
            else if ($dt == 'Localedate' || $dt =='Localedatetime' ) $isLocale = true;
        } else
            $dt = 'String';

        if(isset($control->label['locale'])){
            $source[]='$label = jLocale::get(\''.(string)$control->label['locale'].'\');';
        }else{
            $source[]='$label = \''.str_replace("'","\\'",(string)$control->label).'\';';
        }
        if($controltype == 'checkboxes' || ($controltype == 'listbox' && isset($control['multiple']) && 'true' == (string)$control['multiple']))
            $source[]='$js.="jForms.tControl = new jFormsControl'.$dt.'(\''.(string)$control['ref'].'[]\', \'".str_replace("\'","\\\'",$label)."\');\n";';
        else{
            $source[]='$js.="jForms.tControl = new jFormsControl'.$dt.'(\''.(string)$control['ref'].'\', \'".str_replace("\'","\\\'",$label)."\');\n";';
            if($hasConfirm){
                if(isset($control->confirm['locale'])){
                    $source[]='$label2 = jLocale::get(\''.(string)$control->confirm['locale'].'\');';
                }else{
                    $source[]='$label2 = \''.str_replace("'","\\'",(string)$control->confirm).'\';';
                }
                $source[]='$js.="jForms.tControl.confirmField = new jFormsControlSecretConfirm(\''.(string)$control['ref'].'_confirm\', \'".str_replace("\'","\\\'",$label2)."\');\n";';
            }
        }

        if($isLocale){
            $source[]='$js.="jForms.tControl.lang=\'".$GLOBALS[\'gJConfig\']->locale."\';\n";';
        }

        if(isset($control['required']) && 'true' == (string)$control['required']){
            $source[]='$js.="jForms.tControl.required = true;\n";';
        }
        if(isset($control['maxlength'])){
            $source[]='$js.="jForms.tControl.maxLength = '.intval($control['maxlength']).';\n";';
        }
        if(isset($control['minlength'])){
            $source[]='$js.="jForms.tControl.minLength = '.intval($control['minlength']).';\n";';
        }
        if(isset($control->help)){
            if(isset($control->help['locale'])){
                $help='str_replace("\'","\\\'",jLocale::get(\''.(string)$control->help['locale'].'\'))';
            }else{
                $help='str_replace("\'","\\\'",\''.str_replace("'","\\'",(string)$control->help).'\')';
            }
            $source[]='$js.="jForms.tControl.help=\'".'.$help.'."\';\n";';
            if($hasConfirm) $source[]='$js.="jForms.tControl.confirmField.help=jForms.tControl.help;\n";';
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
            $source[]='$js.="jForms.tControl.confirmField.errRequired=\'".'.$alertRequired.'."\';\n";';
            $source[]='$js.="jForms.tControl.confirmField.errInvalid =\'".'.$alertInvalid.'."\';\n";';
        }

        if(isset($control['multiple']) && 'true' == (string)$control['multiple']){
            $source[]='$js.="jForms.tControl.multiple = true;\n";';
        }
        return implode("\n", $source);
    }


    public function endCompile() {
        $srcjs='$js.="jForms.declareForm(jForms.tForm);\n";'."\n";
        return $srcjs.' return $js; }';
    }

}

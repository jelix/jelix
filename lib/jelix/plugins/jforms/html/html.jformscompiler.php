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

        if($controltype == 'submit' || $controltype == 'reset' || $controltype == 'hidden')
            return '';

        if(isset($control->confirm) && $controltype == 'secret') {
            $hasConfirm = true;
        }else{
            $hasConfirm = false;
        }

        $source = array();

        if(isset($control['type'])){
            $dt = (string)$control['type'];
            if($dt == 'html') $dt = 'string';
        }else if($controltype == 'checkbox')
            $dt = 'boolean';
        else
            $dt = 'string';

        if(isset($control->label['locale'])){
            $source[]='$label = jLocale::get(\''.(string)$control->label['locale'].'\');';
        }else{
            $source[]='$label = \''.str_replace("'","\\'",(string)$control->label).'\';';
        }
        if($controltype == 'checkboxes' || ($controltype == 'listbox' && isset($control['multiple']) && 'true' == (string)$control['multiple']))
            $source[]='$js.="jForms.tControl = new jFormsControl(\''.(string)$control['ref'].'[]\', \'".str_replace("\'","\\\'",$label)."\', \''.$dt.'\');\n";';
        else{
            $source[]='$js.="jForms.tControl = new jFormsControl(\''.(string)$control['ref'].'\', \'".str_replace("\'","\\\'",$label)."\', \''.$dt.'\');\n";';
            if($hasConfirm){
                if(isset($control->confirm['locale'])){
                    $source[]='$label2 = jLocale::get(\''.(string)$control->confirm['locale'].'\');';
                }else{
                    $source[]='$label2 = \''.str_replace("'","\\'",(string)$control->confirm).'\';';
                }
                $source[]='$js.="jForms.tControl2 = new jFormsControl(\''.(string)$control['ref'].'_confirm\', \'".str_replace("\'","\\\'",$label2)."\', \''.$dt.'\');\n";';
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

    public function endCompile() {
        $srcjs='$js.="jForms.declareForm(jForms.tForm);\n";'."\n";
        return $srcjs.' return $js; }';
    }

}

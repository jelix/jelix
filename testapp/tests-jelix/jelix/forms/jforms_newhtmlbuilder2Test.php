<?php
/**
* @package     testapp
* @subpackage  unittest module
* @author      Laurent Jouanneau
* @copyright   2012 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(__DIR__.'/jforms_htmlbuilder2Test.php');
require_once(JELIX_LIB_PATH.'plugins/formbuilder/html/html.formbuilder.php');
require_once(JELIX_LIB_PATH.'plugins/formwidget/html/html.formwidget.php');


class testHtmlRootWidget2 extends htmlFormWidget {

    function testGetJs() {
        $js = $this->js;
        $this->js = '';
        return $js;
    }

    function testGetFinalJs() {
        $js = $this->finalJs;
        $this->finalJs = '';
        return $js;
    }

}

class testHtmlFormsBuilder2 extends htmlFormBuilder {

    public function __construct($form){
        $this->_form = $form;
        $this->rootWidget = new testHtmlRootWidget2();
    }

    function getJsContent() {
        return $this->rootWidget->testGetJs();
    }
    function clearJs() { $this->rootWidget->testGetJs(); }
    function getLastJsContent() { return $this->rootWidget->testGetFinalJs();}
}


class jforms_newHTMLBuilder2Test extends jforms_HTMLBuilder2Test {

    function setUp() {
        self::initClassicRequest(TESTAPP_URL.'index.php');
        jApp::pushCurrentModule('jelix_tests');
        $_SESSION['JFORMS'] = array();
        $this->container = new jFormsDataContainer('formtesthtmlbuilder','0');
        $this->form = new testHMLForm2('formtesthtmlbuilder', $this->container, true );
        $this->builder = new testHtmlFormsBuilder2($this->form);
    }

    function testOutputGroupWithCheckbox(){

        $group= new jFormsControlgroup('identity');
        $group->label='Your identity';
        $group->hasCheckbox = true;
        $group->defaultValue = '1';

        $ctrl= new jFormsControlinput('nom');
        $ctrl->required=true;
        $ctrl->label='Your name';
        $group->addChildControl($ctrl);

        $ctrl= new jFormsControlinput('prenom');
        $ctrl->defaultValue='robert';
        $ctrl->label='Your firstname';
        $group->addChildControl($ctrl);

        $ctrl= new jFormsControlradiobuttons('sexe');
        $ctrl->required=true;
        $ctrl->label='Vous êtes ';
        $ctrl->alertRequired='Vous devez indiquer le sexe, même si vous ne savez pas :-)';
        $ctrl->datasource= new jFormsStaticDatasource();
        $ctrl->datasource->data = array(
        'h'=>'un homme',
        'f'=>'une femme',
        'no'=>'je ne sais pas',
        );
        $group->addChildControl($ctrl);

        $ctrl= new jFormsControlinput('mail');
        $ctrl->datatype= new jDatatypeemail();
        $ctrl->label='Votre mail';
        $group->addChildControl($ctrl);

        $this->form->addControl($group);


        ob_start();$this->builder->outputControlLabel($group);$out = ob_get_clean();
        $this->assertEquals('', $out);


        $expected = '<fieldset id="'.$this->formname.'_identity" class="jforms-ctrl-group"><legend>';
        $expected .= '<input  name="identity" id="'.$this->formname.'_identity_checkbox" class="" type="checkbox" value="1" onclick="jFormsJQ.getForm(\'\').getControl(\'identity\').showActivate()" checked="true">';
        $expected .= ' <label for="'.$this->formname.'_identity_checkbox">Your identity</label></legend>'."\n"  ;
        $expected .= '<table class="jforms-table-group" border="0">'."\n";
        $expected .= '<tr><th scope="row"><label class="jforms-label jforms-required" for="'.$this->formname.'_nom" id="'.$this->formname.'_nom_label">Your name<span class="jforms-required-star">*</span></label>'."\n".'</th>'."\n";
        $expected .= '<td><input name="nom" id="'.$this->formname.'_nom" class="jforms-ctrl-input jforms-required" value="" type="text"/>'."\n".'</td></tr>'."\n";
        $expected .= '<tr><th scope="row"><label class="jforms-label" for="'.$this->formname.'_prenom" id="'.$this->formname.'_prenom_label">Your firstname</label>'."\n".'</th>'."\n";
        $expected .= '<td><input name="prenom" id="'.$this->formname.'_prenom" class="jforms-ctrl-input" value="robert" type="text"/>'."\n".'</td></tr>'."\n";
        $expected .= '<tr><th scope="row"><span class="jforms-label jforms-required" id="'.$this->formname.'_sexe_label">Vous êtes <span class="jforms-required-star">*</span></span>'."\n".'</th>'."\n";
        $expected .= '<td><span class="jforms-radio jforms-ctl-sexe"><input type="radio" name="sexe" id="'.$this->formname.'_sexe_0" class="jforms-ctrl-radiobuttons jforms-required" value="h"/><label for="'.$this->formname.'_sexe_0">un homme</label></span> <br/>'."\n";
        $expected .= '<span class="jforms-radio jforms-ctl-sexe"><input type="radio" name="sexe" id="'.$this->formname.'_sexe_1" class="jforms-ctrl-radiobuttons jforms-required" value="f"/><label for="'.$this->formname.'_sexe_1">une femme</label></span> <br/>'."\n";
        $expected .= '<span class="jforms-radio jforms-ctl-sexe"><input type="radio" name="sexe" id="'.$this->formname.'_sexe_2" class="jforms-ctrl-radiobuttons jforms-required" value="no"/><label for="'.$this->formname.'_sexe_2">je ne sais pas</label></span> <br/>'."\n\n".'</td></tr>'."\n";
        $expected .= '<tr><th scope="row"><label class="jforms-label" for="'.$this->formname.'_mail" id="'.$this->formname.'_mail_label">Votre mail</label>'."\n".'</th>'."\n";
        $expected .= '<td><input name="mail" id="'.$this->formname.'_mail" class="jforms-ctrl-input" value="" type="text"/>'."\n".'</td></tr>'."\n</table></fieldset>\n";


        ob_start();$this->builder->outputControl($group);$out = ob_get_clean();
        $this->assertEquals($expected, $out);
        $this->assertEquals('c = new jFormsJQControlGroup(\'identity\', \'Your identity\');
c.errRequired=\'"Your identity" field is required\';
c.errInvalid=\'"Your identity" field is invalid\';
jFormsJQ.tForm.addControl(c);
c.hasCheckbox = true;
c2 = c;
c = new jFormsJQControlString(\'nom\', \'Your name\');
c.required = true;
c.errRequired=\'"Your name" field is required\';
c.errInvalid=\'"Your name" field is invalid\';
c2.addControl(c);
c = new jFormsJQControlString(\'prenom\', \'Your firstname\');
c.errRequired=\'"Your firstname" field is required\';
c.errInvalid=\'"Your firstname" field is invalid\';
c2.addControl(c);
c = new jFormsJQControlString(\'sexe\', \'Vous êtes \');
c.required = true;
c.errRequired=\'Vous devez indiquer le sexe, même si vous ne savez pas :-)\';
c.errInvalid=\'"Vous êtes " field is invalid\';
c2.addControl(c);
c = new jFormsJQControlEmail(\'mail\', \'Votre mail\');
c.errRequired=\'"Votre mail" field is required\';
c.errInvalid=\'"Votre mail" field is invalid\';
c2.addControl(c);
c2.showActivate();
', $this->builder->getJsContent());

        $group->setReadOnly(true);
        $expected = '<fieldset id="'.$this->formname.'_identity" class="jforms-ctrl-group"><legend>';
        $expected .= '<input  name="identity" id="'.$this->formname.'_identity_checkbox" readonly="readonly" class=" jforms-readonly" type="checkbox" value="1" onclick="jFormsJQ.getForm(\'\').getControl(\'identity\').showActivate()" checked="true">';
        $expected .= ' <label for="'.$this->formname.'_identity_checkbox">Your identity</label></legend>'."\n"  ;
        $expected .= '<table class="jforms-table-group" border="0">'."\n";
        $expected .= '<tr><th scope="row"><label class="jforms-label" for="'.$this->formname.'_nom" id="'.$this->formname.'_nom_label">Your name</label>'."\n".'</th>'."\n";
        $expected .= '<td><input name="nom" id="'.$this->formname.'_nom" readonly="readonly" class="jforms-ctrl-input jforms-readonly" value="" type="text"/>'."\n".'</td></tr>'."\n";
        $expected .= '<tr><th scope="row"><label class="jforms-label" for="'.$this->formname.'_prenom" id="'.$this->formname.'_prenom_label">Your firstname</label>'."\n".'</th>'."\n";
        $expected .= '<td><input name="prenom" id="'.$this->formname.'_prenom" readonly="readonly" class="jforms-ctrl-input jforms-readonly" value="robert" type="text"/>'."\n".'</td></tr>'."\n";
        $expected .= '<tr><th scope="row"><span class="jforms-label" id="'.$this->formname.'_sexe_label">Vous êtes </span>'."\n".'</th>'."\n";
        $expected .= '<td><span class="jforms-radio jforms-ctl-sexe"><input type="radio" name="sexe" id="'.$this->formname.'_sexe_0" readonly="readonly" class="jforms-ctrl-radiobuttons jforms-readonly" value="h"/><label for="'.$this->formname.'_sexe_0">un homme</label></span> <br/>'."\n";
        $expected .= '<span class="jforms-radio jforms-ctl-sexe"><input type="radio" name="sexe" id="'.$this->formname.'_sexe_1" readonly="readonly" class="jforms-ctrl-radiobuttons jforms-readonly" value="f"/><label for="'.$this->formname.'_sexe_1">une femme</label></span> <br/>'."\n";
        $expected .= '<span class="jforms-radio jforms-ctl-sexe"><input type="radio" name="sexe" id="'.$this->formname.'_sexe_2" readonly="readonly" class="jforms-ctrl-radiobuttons jforms-readonly" value="no"/><label for="'.$this->formname.'_sexe_2">je ne sais pas</label></span> <br/>'."\n\n".'</td></tr>'."\n";
        $expected .= '<tr><th scope="row"><label class="jforms-label" for="'.$this->formname.'_mail" id="'.$this->formname.'_mail_label">Votre mail</label>'."\n".'</th>'."\n";
        $expected .= '<td><input name="mail" id="'.$this->formname.'_mail" readonly="readonly" class="jforms-ctrl-input jforms-readonly" value="" type="text"/>'."\n".'</td></tr>'."\n</table></fieldset>\n";
        ob_start();$this->builder->outputControl($group);$out = ob_get_clean();
        $this->assertEquals($expected, $out);
        $this->assertEquals('c = new jFormsJQControlGroup(\'identity\', \'Your identity\');
c.readOnly = true;
c.errRequired=\'"Your identity" field is required\';
c.errInvalid=\'"Your identity" field is invalid\';
jFormsJQ.tForm.addControl(c);
c.hasCheckbox = true;
c2 = c;
c = new jFormsJQControlString(\'nom\', \'Your name\');
c.readOnly = true;
c.required = true;
c.errRequired=\'"Your name" field is required\';
c.errInvalid=\'"Your name" field is invalid\';
c2.addControl(c);
c = new jFormsJQControlString(\'prenom\', \'Your firstname\');
c.readOnly = true;
c.errRequired=\'"Your firstname" field is required\';
c.errInvalid=\'"Your firstname" field is invalid\';
c2.addControl(c);
c = new jFormsJQControlString(\'sexe\', \'Vous êtes \');
c.readOnly = true;
c.required = true;
c.errRequired=\'Vous devez indiquer le sexe, même si vous ne savez pas :-)\';
c.errInvalid=\'"Vous êtes " field is invalid\';
c2.addControl(c);
c = new jFormsJQControlEmail(\'mail\', \'Votre mail\');
c.readOnly = true;
c.errRequired=\'"Votre mail" field is required\';
c.errInvalid=\'"Votre mail" field is invalid\';
c2.addControl(c);
c2.showActivate();
', $this->builder->getJsContent());

    }

}

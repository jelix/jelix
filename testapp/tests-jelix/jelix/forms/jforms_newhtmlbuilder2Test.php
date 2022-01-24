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

    function setUp() : void {
        self::initClassicRequest(TESTAPP_URL.'index.php');
        jApp::pushCurrentModule('jelix_tests');
        if (isset($_SESSION['JFORMS_SESSION'])) {
            unset($_SESSION['JFORMS_SESSION']);
        };
        jFile::removeDir(__DIR__.'/../../../temp/jelixtests/jforms');
        $this->container = new jFormsDataContainer('formtesthtmlbuilder','0');
        $this->form = new testHMLForm2('formtesthtmlbuilder', $this->container, true );
        $this->builder = new testHtmlFormsBuilder2($this->form);
    }

    function testOutputGroupWithCheckbox(){

        $group= new jFormsControlGroup('identity');
        $group->label='Your identity';
        $group->hasCheckbox = true;
        $group->defaultValue = '1';

        $ctrl= new jFormsControlInput('nom');
        $ctrl->required=true;
        $ctrl->label='Your name';
        $group->addChildControl($ctrl);

        $ctrl= new jFormsControlInput('prenom');
        $ctrl->defaultValue='robert';
        $ctrl->label='Your firstname';
        $group->addChildControl($ctrl);

        $ctrl= new jFormsControlRadiobuttons('sexe');
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

        $ctrl= new jFormsControlInput('mail');
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
        $expected .= '<td><span class="jforms-radio jforms-ctl-sexe"><input type="radio" name="sexe" id="'.$this->formname.'_sexe_0" class="jforms-ctrl-radiobuttons jforms-required" value="h"/><label for="'.$this->formname.'_sexe_0">un homme</label></span>'." <br/>\n";
        $expected .= '<span class="jforms-radio jforms-ctl-sexe"><input type="radio" name="sexe" id="'.$this->formname.'_sexe_1" class="jforms-ctrl-radiobuttons jforms-required" value="f"/><label for="'.$this->formname.'_sexe_1">une femme</label></span>'." <br/>\n";
        $expected .= '<span class="jforms-radio jforms-ctl-sexe"><input type="radio" name="sexe" id="'.$this->formname.'_sexe_2" class="jforms-ctrl-radiobuttons jforms-required" value="no"/><label for="'.$this->formname.'_sexe_2">je ne sais pas</label></span>'." <br/>\n\n".'</td></tr>'."\n";
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
        $expected .= '<td><span class="jforms-radio jforms-ctl-sexe"><input type="radio" name="sexe" id="'.$this->formname.'_sexe_0" readonly="readonly" class="jforms-ctrl-radiobuttons jforms-readonly" value="h"/><label for="'.$this->formname.'_sexe_0">un homme</label></span>'." <br/>\n";
        $expected .= '<span class="jforms-radio jforms-ctl-sexe"><input type="radio" name="sexe" id="'.$this->formname.'_sexe_1" readonly="readonly" class="jforms-ctrl-radiobuttons jforms-readonly" value="f"/><label for="'.$this->formname.'_sexe_1">une femme</label></span>'." <br/>\n";
        $expected .= '<span class="jforms-radio jforms-ctl-sexe"><input type="radio" name="sexe" id="'.$this->formname.'_sexe_2" readonly="readonly" class="jforms-ctrl-radiobuttons jforms-readonly" value="no"/><label for="'.$this->formname.'_sexe_2">je ne sais pas</label></span>'." <br/>\n\n".'</td></tr>'."\n";
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


    function testOutputChoice(){

        /*
        <choice ref="status">
            <label>Task Status</label
            <item value="new"><label>New</label>

            </item>
            <item value="assigned"><label>Assigned</label>
                <input ref="nom" required="true"><label>Name</label></input>
                <input ref="prenom" required="true"><label>Firstname</label></input>
            </item>
            <item value="closed"><label>Closed</label>
                <menulist ref="reason" required="true">
                    <label>Reason</label>
                    <item value="aa">fixed</item>
                    <item value="bb">won t fixed</item>
                    <item value="cc">later</item>
                    <alert type="required">Hey, specify a reason !</alert>
                </menulist>
            </item>
        </choice>
        */
        $choice= new jFormsControlChoice('status');
        $choice->label='Task Status';
        $choice->createItem('new','New');
        $choice->createItem('assigned','Assigned');
        $choice->createItem('closed','Closed');

        $ctrlnom= new jFormsControlInput('nom');
        $ctrlnom->required=true;
        $ctrlnom->label='Name';
        $choice->addChildControl($ctrlnom,'assigned');

        $ctrlprenom= new jFormsControlInput('prenom');
        $ctrlprenom->defaultValue='robert';
        $ctrlprenom->label='Firstname';
        $choice->addChildControl($ctrlprenom,'assigned');

        $ctrlreason= new jFormsControlMenulist('reason');
        $ctrlreason->required=true;
        $ctrlreason->label='Reason ';
        $ctrlreason->alertRequired='Hey, specify a reason !';
        $ctrlreason->datasource= new jFormsStaticDatasource();
        $ctrlreason->datasource->data = array(
            'aa'=>'fixed',
            'bb'=>'won t fixed',
            'cc'=>'later',
        );
        $choice->addChildControl($ctrlreason,'closed');

        $this->form->addControl($choice);
        $choice->setReadOnly(false);

        // output of the label of <choice>
        ob_start();$this->builder->outputControlLabel($choice);$out = ob_get_clean();
        $this->assertEquals('<span class="jforms-label" id="'.$this->formname.'_status_label">Task Status</span>'."\n", $out);

        // output of the whole <choice>, with original values
        $expected = '<ul class="jforms-choice jforms-ctl-status " id="_status_choice_list" >'."\n";
        $expected .= '<li id="'.$this->formname.'_status_new_item"><input  name="status" id="'.$this->formname.'_status_0" type="radio" value="new" onclick="jFormsJQ.getForm(\'\').getControl(\'status\').activate(\'new\')" /><label for="_status_0">New</label>'."\n".'</li>'."\n";
        $expected .= '<li id="'.$this->formname.'_status_assigned_item"><input  name="status" id="'.$this->formname.'_status_1" type="radio" value="assigned" onclick="jFormsJQ.getForm(\'\').getControl(\'status\').activate(\'assigned\')" /><label for="_status_1">Assigned</label>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label jforms-required" for="'.$this->formname.'_nom" id="'.$this->formname.'_nom_label">Name<span class="jforms-required-star">*</span></label>'."\n".' <input name="nom" id="'.$this->formname.'_nom" class="jforms-ctrl-input jforms-required" value="" type="text"/>'."\n".'</span>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label" for="'.$this->formname.'_prenom" id="'.$this->formname.'_prenom_label">Firstname</label>'."\n".' <input name="prenom" id="'.$this->formname.'_prenom" class="jforms-ctrl-input" value="robert" type="text"/>'."\n".'</span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '<li id="'.$this->formname.'_status_closed_item"><input  name="status" id="'.$this->formname.'_status_2" type="radio" value="closed" onclick="jFormsJQ.getForm(\'\').getControl(\'status\').activate(\'closed\')" /><label for="_status_2">Closed</label>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label jforms-required" for="'.$this->formname.'_reason" id="'.$this->formname.'_reason_label">Reason <span class="jforms-required-star">*</span></label>'."\n";
        $expected .= ' <select name="reason" id="'.$this->formname.'_reason" class="jforms-ctrl-menulist jforms-required" size="1">'."\n".'<option value="aa">fixed</option>'."\n".'<option value="bb">won t fixed</option>'."\n".'<option value="cc">later</option>'."\n".'</select>'."\n".'</span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '</ul>'."\n\n";
        ob_start();$this->builder->outputControl($choice);$out = ob_get_clean();
        $this->assertEquals($expected, $out);
        $this->assertEquals('c = new jFormsJQControlChoice(\'status\', \'Task Status\');
c.errRequired=\'"Task Status" field is required\';
c.errInvalid=\'"Task Status" field is invalid\';
jFormsJQ.tForm.addControl(c);
c2 = c;
c2.items[\'new\']=[];
c = new jFormsJQControlString(\'nom\', \'Name\');
c.required = true;
c.errRequired=\'"Name" field is required\';
c.errInvalid=\'"Name" field is invalid\';
c2.addControl(c, \'assigned\');
c = new jFormsJQControlString(\'prenom\', \'Firstname\');
c.errRequired=\'"Firstname" field is required\';
c.errInvalid=\'"Firstname" field is invalid\';
c2.addControl(c, \'assigned\');
c = new jFormsJQControlString(\'reason\', \'Reason \');
c.required = true;
c.errRequired=\'Hey, specify a reason !\';
c.errInvalid=\'"Reason " field is invalid\';
c2.addControl(c, \'closed\');
c2.activate(\'\');
', $this->builder->getJsContent());

        // ouput of the whole <choice>, with the first item selected
        $this->form->getContainer()->data['status']='assigned';

        $expected = '<ul class="jforms-choice jforms-ctl-status " id="_status_choice_list" >'."\n";
        $expected .= '<li id="'.$this->formname.'_status_new_item"><input  name="status" id="'.$this->formname.'_status_0" type="radio" value="new" onclick="jFormsJQ.getForm(\'\').getControl(\'status\').activate(\'new\')" /><label for="_status_0">New</label>'."\n".'</li>'."\n";
        $expected .= '<li id="'.$this->formname.'_status_assigned_item"><input  name="status" id="'.$this->formname.'_status_1" type="radio" value="assigned" onclick="jFormsJQ.getForm(\'\').getControl(\'status\').activate(\'assigned\')" checked/><label for="_status_1">Assigned</label>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label jforms-required" for="'.$this->formname.'_nom" id="'.$this->formname.'_nom_label">Name<span class="jforms-required-star">*</span></label>'."\n".' <input name="nom" id="'.$this->formname.'_nom" class="jforms-ctrl-input jforms-required" value="" type="text"/>'."\n".'</span>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label" for="'.$this->formname.'_prenom" id="'.$this->formname.'_prenom_label">Firstname</label>'."\n".' <input name="prenom" id="'.$this->formname.'_prenom" class="jforms-ctrl-input" value="robert" type="text"/>'."\n".'</span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '<li id="'.$this->formname.'_status_closed_item"><input  name="status" id="'.$this->formname.'_status_2" type="radio" value="closed" onclick="jFormsJQ.getForm(\'\').getControl(\'status\').activate(\'closed\')" /><label for="_status_2">Closed</label>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label jforms-required" for="'.$this->formname.'_reason" id="'.$this->formname.'_reason_label">Reason <span class="jforms-required-star">*</span></label>'."\n";
        $expected .= ' <select name="reason" id="'.$this->formname.'_reason" class="jforms-ctrl-menulist jforms-required" size="1">'."\n".'<option value="aa">fixed</option>'."\n".'<option value="bb">won t fixed</option>'."\n".'<option value="cc">later</option>'."\n".'</select>'."\n".'</span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '</ul>'."\n\n";
        ob_start();$this->builder->outputControl($choice);$out = ob_get_clean();
        $this->assertEquals($expected, $out);
        $this->assertEquals('c = new jFormsJQControlChoice(\'status\', \'Task Status\');
c.errRequired=\'"Task Status" field is required\';
c.errInvalid=\'"Task Status" field is invalid\';
jFormsJQ.tForm.addControl(c);
c2 = c;
c2.items[\'new\']=[];
c = new jFormsJQControlString(\'nom\', \'Name\');
c.required = true;
c.errRequired=\'"Name" field is required\';
c.errInvalid=\'"Name" field is invalid\';
c2.addControl(c, \'assigned\');
c = new jFormsJQControlString(\'prenom\', \'Firstname\');
c.errRequired=\'"Firstname" field is required\';
c.errInvalid=\'"Firstname" field is invalid\';
c2.addControl(c, \'assigned\');
c = new jFormsJQControlString(\'reason\', \'Reason \');
c.required = true;
c.errRequired=\'Hey, specify a reason !\';
c.errInvalid=\'"Reason " field is invalid\';
c2.addControl(c, \'closed\');
c2.activate(\'assigned\');
', $this->builder->getJsContent());

        // ouput of the whole <choice>, with the second item selected
        $this->form->getContainer()->data['status']='new';
        $expected = '<ul class="jforms-choice jforms-ctl-status " id="_status_choice_list" >'."\n";
        $expected .= '<li id="'.$this->formname.'_status_new_item"><input  name="status" id="'.$this->formname.'_status_0" type="radio" value="new" onclick="jFormsJQ.getForm(\'\').getControl(\'status\').activate(\'new\')" checked/><label for="_status_0">New</label>'."\n".'</li>'."\n";
        $expected .= '<li id="'.$this->formname.'_status_assigned_item"><input  name="status" id="'.$this->formname.'_status_1" type="radio" value="assigned" onclick="jFormsJQ.getForm(\'\').getControl(\'status\').activate(\'assigned\')" /><label for="_status_1">Assigned</label>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label jforms-required" for="'.$this->formname.'_nom" id="'.$this->formname.'_nom_label">Name<span class="jforms-required-star">*</span></label>'."\n".' <input name="nom" id="'.$this->formname.'_nom" class="jforms-ctrl-input jforms-required" value="" type="text"/>'."\n".'</span>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label" for="'.$this->formname.'_prenom" id="'.$this->formname.'_prenom_label">Firstname</label>'."\n".' <input name="prenom" id="'.$this->formname.'_prenom" class="jforms-ctrl-input" value="robert" type="text"/>'."\n".'</span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '<li id="'.$this->formname.'_status_closed_item"><input  name="status" id="'.$this->formname.'_status_2" type="radio" value="closed" onclick="jFormsJQ.getForm(\'\').getControl(\'status\').activate(\'closed\')" /><label for="_status_2">Closed</label>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label jforms-required" for="'.$this->formname.'_reason" id="'.$this->formname.'_reason_label">Reason <span class="jforms-required-star">*</span></label>'."\n";
        $expected .= ' <select name="reason" id="'.$this->formname.'_reason" class="jforms-ctrl-menulist jforms-required" size="1">'."\n".'<option value="aa">fixed</option>'."\n".'<option value="bb">won t fixed</option>'."\n".'<option value="cc">later</option>'."\n".'</select>'."\n".'</span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '</ul>'."\n\n";
        ob_start();$this->builder->outputControl($choice);$out = ob_get_clean();
        $this->assertEquals($expected, $out);
        $this->assertEquals('c = new jFormsJQControlChoice(\'status\', \'Task Status\');
c.errRequired=\'"Task Status" field is required\';
c.errInvalid=\'"Task Status" field is invalid\';
jFormsJQ.tForm.addControl(c);
c2 = c;
c2.items[\'new\']=[];
c = new jFormsJQControlString(\'nom\', \'Name\');
c.required = true;
c.errRequired=\'"Name" field is required\';
c.errInvalid=\'"Name" field is invalid\';
c2.addControl(c, \'assigned\');
c = new jFormsJQControlString(\'prenom\', \'Firstname\');
c.errRequired=\'"Firstname" field is required\';
c.errInvalid=\'"Firstname" field is invalid\';
c2.addControl(c, \'assigned\');
c = new jFormsJQControlString(\'reason\', \'Reason \');
c.required = true;
c.errRequired=\'Hey, specify a reason !\';
c.errInvalid=\'"Reason " field is invalid\';
c2.addControl(c, \'closed\');
c2.activate(\'new\');
', $this->builder->getJsContent());

        // ouput of the whole <choice>, with the third item selected
        $this->form->getContainer()->data['status']='closed';
        $expected = '<ul class="jforms-choice jforms-ctl-status " id="_status_choice_list" >'."\n";
        $expected .= '<li id="'.$this->formname.'_status_new_item"><input  name="status" id="'.$this->formname.'_status_0" type="radio" value="new" onclick="jFormsJQ.getForm(\'\').getControl(\'status\').activate(\'new\')" /><label for="_status_0">New</label>'."\n".'</li>'."\n";
        $expected .= '<li id="'.$this->formname.'_status_assigned_item"><input  name="status" id="'.$this->formname.'_status_1" type="radio" value="assigned" onclick="jFormsJQ.getForm(\'\').getControl(\'status\').activate(\'assigned\')" /><label for="_status_1">Assigned</label>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label jforms-required" for="'.$this->formname.'_nom" id="'.$this->formname.'_nom_label">Name<span class="jforms-required-star">*</span></label>'."\n".' <input name="nom" id="'.$this->formname.'_nom" class="jforms-ctrl-input jforms-required" value="" type="text"/>'."\n".'</span>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label" for="'.$this->formname.'_prenom" id="'.$this->formname.'_prenom_label">Firstname</label>'."\n".' <input name="prenom" id="'.$this->formname.'_prenom" class="jforms-ctrl-input" value="robert" type="text"/>'."\n".'</span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '<li id="'.$this->formname.'_status_closed_item"><input  name="status" id="'.$this->formname.'_status_2" type="radio" value="closed" onclick="jFormsJQ.getForm(\'\').getControl(\'status\').activate(\'closed\')" checked/><label for="_status_2">Closed</label>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label jforms-required" for="'.$this->formname.'_reason" id="'.$this->formname.'_reason_label">Reason <span class="jforms-required-star">*</span></label>'."\n";
        $expected .= ' <select name="reason" id="'.$this->formname.'_reason" class="jforms-ctrl-menulist jforms-required" size="1">'."\n".'<option value="aa">fixed</option>'."\n".'<option value="bb">won t fixed</option>'."\n".'<option value="cc">later</option>'."\n".'</select>'."\n".'</span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '</ul>'."\n\n";
        ob_start();$this->builder->outputControl($choice);$out = ob_get_clean();
        $this->assertEquals($expected, $out);
        $this->assertEquals('c = new jFormsJQControlChoice(\'status\', \'Task Status\');
c.errRequired=\'"Task Status" field is required\';
c.errInvalid=\'"Task Status" field is invalid\';
jFormsJQ.tForm.addControl(c);
c2 = c;
c2.items[\'new\']=[];
c = new jFormsJQControlString(\'nom\', \'Name\');
c.required = true;
c.errRequired=\'"Name" field is required\';
c.errInvalid=\'"Name" field is invalid\';
c2.addControl(c, \'assigned\');
c = new jFormsJQControlString(\'prenom\', \'Firstname\');
c.errRequired=\'"Firstname" field is required\';
c.errInvalid=\'"Firstname" field is invalid\';
c2.addControl(c, \'assigned\');
c = new jFormsJQControlString(\'reason\', \'Reason \');
c.required = true;
c.errRequired=\'Hey, specify a reason !\';
c.errInvalid=\'"Reason " field is invalid\';
c2.addControl(c, \'closed\');
c2.activate(\'closed\');
', $this->builder->getJsContent());


        // ouput of the whole <choice>, in readonly mode, with the third item selected
        $choice->setReadOnly(true);

        $expected = '<ul class="jforms-choice jforms-ctl-status " id="_status_choice_list" >'."\n";
        $expected .= '<li id="'.$this->formname.'_status_new_item"><input  name="status" id="'.$this->formname.'_status_0" readonly="readonly" type="radio" value="new" onclick="jFormsJQ.getForm(\'\').getControl(\'status\').activate(\'new\')" /><label for="_status_0">New</label>'."\n".'</li>'."\n";
        $expected .= '<li id="'.$this->formname.'_status_assigned_item"><input  name="status" id="'.$this->formname.'_status_1" readonly="readonly" type="radio" value="assigned" onclick="jFormsJQ.getForm(\'\').getControl(\'status\').activate(\'assigned\')" /><label for="_status_1">Assigned</label>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label" for="'.$this->formname.'_nom" id="'.$this->formname.'_nom_label">Name</label>'."\n".' <input name="nom" id="'.$this->formname.'_nom" readonly="readonly" class="jforms-ctrl-input jforms-readonly" value="" type="text"/>'."\n".'</span>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label" for="'.$this->formname.'_prenom" id="'.$this->formname.'_prenom_label">Firstname</label>'."\n".' <input name="prenom" id="'.$this->formname.'_prenom" readonly="readonly" class="jforms-ctrl-input jforms-readonly" value="robert" type="text"/>'."\n".'</span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '<li id="'.$this->formname.'_status_closed_item"><input  name="status" id="'.$this->formname.'_status_2" readonly="readonly" type="radio" value="closed" onclick="jFormsJQ.getForm(\'\').getControl(\'status\').activate(\'closed\')" checked/><label for="_status_2">Closed</label>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label" for="'.$this->formname.'_reason" id="'.$this->formname.'_reason_label">Reason </label>'."\n";
        $expected .= ' <select name="reason" id="'.$this->formname.'_reason" class="jforms-ctrl-menulist jforms-readonly" disabled="disabled" size="1">'."\n".'<option value="aa">fixed</option>'."\n".'<option value="bb">won t fixed</option>'."\n".'<option value="cc">later</option>'."\n".'</select>'."\n".'</span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '</ul>'."\n\n";
        ob_start();$this->builder->outputControl($choice);$out = ob_get_clean();
        $this->assertEquals($expected, $out);
        $this->assertEquals('c = new jFormsJQControlChoice(\'status\', \'Task Status\');
c.readOnly = true;
c.errRequired=\'"Task Status" field is required\';
c.errInvalid=\'"Task Status" field is invalid\';
jFormsJQ.tForm.addControl(c);
c2 = c;
c2.items[\'new\']=[];
c = new jFormsJQControlString(\'nom\', \'Name\');
c.readOnly = true;
c.required = true;
c.errRequired=\'"Name" field is required\';
c.errInvalid=\'"Name" field is invalid\';
c2.addControl(c, \'assigned\');
c = new jFormsJQControlString(\'prenom\', \'Firstname\');
c.readOnly = true;
c.errRequired=\'"Firstname" field is required\';
c.errInvalid=\'"Firstname" field is invalid\';
c2.addControl(c, \'assigned\');
c = new jFormsJQControlString(\'reason\', \'Reason \');
c.readOnly = true;
c.required = true;
c.errRequired=\'Hey, specify a reason !\';
c.errInvalid=\'"Reason " field is invalid\';
c2.addControl(c, \'closed\');
c2.activate(\'closed\');
', $this->builder->getJsContent());

        // output of the <choice>, with a deactivated item
        $this->form->getContainer()->data['status']='closed';
        $choice->deactivateItem('assigned');
        $expected = '<ul class="jforms-choice jforms-ctl-status " id="_status_choice_list" >'."\n";
        $expected .= '<li id="'.$this->formname.'_status_new_item"><input  name="status" id="'.$this->formname.'_status_0" readonly="readonly" type="radio" value="new" onclick="jFormsJQ.getForm(\'\').getControl(\'status\').activate(\'new\')" /><label for="_status_0">New</label>'."\n".'</li>'."\n";
        $expected .= '<li id="'.$this->formname.'_status_closed_item"><input  name="status" id="'.$this->formname.'_status_1" readonly="readonly" type="radio" value="closed" onclick="jFormsJQ.getForm(\'\').getControl(\'status\').activate(\'closed\')" checked/><label for="_status_1">Closed</label>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label" for="'.$this->formname.'_reason" id="'.$this->formname.'_reason_label">Reason </label>'."\n";
        $expected .= ' <select name="reason" id="'.$this->formname.'_reason" class="jforms-ctrl-menulist jforms-readonly" disabled="disabled" size="1">'."\n".'<option value="aa">fixed</option>'."\n".'<option value="bb">won t fixed</option>'."\n".'<option value="cc">later</option>'."\n".'</select>'."\n".'</span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '</ul>'."\n\n";
        ob_start();$this->builder->outputControl($choice);$out = ob_get_clean();
        $this->assertEquals($expected, $out);
        $this->assertEquals('c = new jFormsJQControlChoice(\'status\', \'Task Status\');
c.readOnly = true;
c.errRequired=\'"Task Status" field is required\';
c.errInvalid=\'"Task Status" field is invalid\';
jFormsJQ.tForm.addControl(c);
c2 = c;
c2.items[\'new\']=[];
c = new jFormsJQControlString(\'reason\', \'Reason \');
c.readOnly = true;
c.required = true;
c.errRequired=\'Hey, specify a reason !\';
c.errInvalid=\'"Reason " field is invalid\';
c2.addControl(c, \'closed\');
c2.activate(\'closed\');
', $this->builder->getJsContent());

    }

    public function testFormWithExternalUrlAsAction(){
        $this->builder->setAction('http://www.jelix.org/dummy.php',array());
        ob_start();
        $this->builder->setOptions(array('method'=>'post'));
        $this->builder->outputHeader();
        $out = ob_get_clean();

        $result ='<form action="http://www.jelix.org/dummy.php" method="post" id="'.$this->builder->getName().'"><div class="jforms-hiddens"><input type="hidden" name="__JFORMS_TOKEN__" value="'.$this->container->token.'"/>
</div>';

        $this->assertEquals($result, $out);
        $this->assertEquals('jFormsJQ.selectFillUrl=\''.jApp::urlBasePath().'index.php/jelix/forms/getdata\';
jFormsJQ.config = {locale:\''.jApp::config()->locale.'\',basePath:\''.jApp::urlBasePath().'\',jqueryPath:\''.jApp::config()->urlengine['jqueryPath'].'\',jqueryFile:\''.$this->getJQuery().'\',jelixWWWPath:\''.jApp::config()->urlengine['jelixWWWPath'].'\'};
jFormsJQ.tForm = new jFormsJQForm(\'jforms_formtesthtmlbuilder\',\'formtesthtmlbuilder\',\'0\');
jFormsJQ.tForm.setErrorDecorator(new jFormsJQErrorDecoratorHtml());
jFormsJQ.declareForm(jFormsJQ.tForm);
', $this->builder->getJsContent());

        $this->builder->setAction('http://www.jelix.org/dummy.php',array('foo'=>'bar'));
        ob_start();
        $this->builder->setOptions(array('method'=>'post'));
        $this->builder->outputHeader();
        $out = ob_get_clean();

        $result ='<form action="http://www.jelix.org/dummy.php" method="post" id="'.$this->builder->getName().'"><div class="jforms-hiddens"><input type="hidden" name="foo" value="bar"/>
<input type="hidden" name="__JFORMS_TOKEN__" value="'.$this->container->token.'"/>
</div>';

        $this->assertEquals($result, $out);
        $this->assertEquals('jFormsJQ.selectFillUrl=\''.jApp::urlBasePath().'index.php/jelix/forms/getdata\';
jFormsJQ.config = {locale:\''.jApp::config()->locale.'\',basePath:\''.jApp::urlBasePath().'\',jqueryPath:\''.jApp::config()->urlengine['jqueryPath'].'\',jqueryFile:\''.$this->getJQuery().'\',jelixWWWPath:\''.jApp::config()->urlengine['jelixWWWPath'].'\'};
jFormsJQ.tForm = new jFormsJQForm(\'jforms_formtesthtmlbuilder1\',\'formtesthtmlbuilder\',\'0\');
jFormsJQ.tForm.setErrorDecorator(new jFormsJQErrorDecoratorHtml());
jFormsJQ.declareForm(jFormsJQ.tForm);
', $this->builder->getJsContent());

        $this->builder->setAction('https://www.jelix.org/dummy.php',array());
        ob_start();
        $this->builder->setOptions(array('method'=>'get'));
        $this->builder->outputHeader();
        $out = ob_get_clean();

        $result ='<form action="https://www.jelix.org/dummy.php" method="get" id="'.$this->builder->getName().'"><div class="jforms-hiddens"><input type="hidden" name="__JFORMS_TOKEN__" value="'.$this->container->token.'"/>
</div>';

        $this->assertEquals($result, $out);
        $this->assertEquals('jFormsJQ.selectFillUrl=\''.jApp::urlBasePath().'index.php/jelix/forms/getdata\';
jFormsJQ.config = {locale:\''.jApp::config()->locale.'\',basePath:\''.jApp::urlBasePath().'\',jqueryPath:\''.jApp::config()->urlengine['jqueryPath'].'\',jqueryFile:\''.$this->getJQuery().'\',jelixWWWPath:\''.jApp::config()->urlengine['jelixWWWPath'].'\'};
jFormsJQ.tForm = new jFormsJQForm(\'jforms_formtesthtmlbuilder2\',\'formtesthtmlbuilder\',\'0\');
jFormsJQ.tForm.setErrorDecorator(new jFormsJQErrorDecoratorHtml());
jFormsJQ.declareForm(jFormsJQ.tForm);
', $this->builder->getJsContent());

    }

    /**
     *
     */
    function testOutputColor(){
        $ctrl= new jFormsControlColor('inputcol');
        $ctrl->datatype= new jDatatypeColor();
        $ctrl->label='Couleur';
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEquals('<label class="jforms-label" for="'.$this->formname.'_inputcol" id="'.$this->formname.'_inputcol_label">Couleur</label>'."\n", $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEquals('<input name="inputcol" id="'.$this->formname.'_inputcol" class="jforms-ctrl-color" value="" type="color" style="width:5em;height:25px;"/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlColor(\'inputcol\', \'Couleur\');
c.errRequired=\'"Couleur" field is required\';
c.errInvalid=\'"Couleur" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

        $this->form->setData('inputcol','#F0F0F0');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEquals('<input name="inputcol" id="'.$this->formname.'_inputcol" class="jforms-ctrl-color" value="#F0F0F0" type="color" style="width:5em;height:25px;"/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlColor(\'inputcol\', \'Couleur\');
c.errRequired=\'"Couleur" field is required\';
c.errInvalid=\'"Couleur" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

        $this->form->setData('inputcol','#778899');
        ob_start();$this->builder->outputControl($ctrl, array('class'=>'foo', 'onclick'=>"alert('bla')"));$out = ob_get_clean();
        $this->assertEquals('<input class="foo jforms-ctrl-color" onclick="alert(\'bla\')" name="inputcol" id="'.$this->formname.'_inputcol" value="#778899" type="color" style="width:5em;height:25px;"/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlColor(\'inputcol\', \'Couleur\');
c.errRequired=\'"Couleur" field is required\';
c.errInvalid=\'"Couleur" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl->defaultValue='#F0F0F0';
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEquals('<input name="inputcol" id="'.$this->formname.'_inputcol" class="jforms-ctrl-color" value="#778899" type="color" style="width:5em;height:25px;"/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlColor(\'inputcol\', \'Couleur\');
c.errRequired=\'"Couleur" field is required\';
c.errInvalid=\'"Couleur" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

        $this->form->removeControl($ctrl->ref);
        $this->form->addControl($ctrl);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEquals('<input name="inputcol" id="'.$this->formname.'_inputcol" class="jforms-ctrl-color" value="#F0F0F0" type="color" style="width:5em;height:25px;"/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlColor(\'inputcol\', \'Couleur\');
c.errRequired=\'"Couleur" field is required\';
c.errInvalid=\'"Couleur" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl->required=true;
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEquals('<input name="inputcol" id="'.$this->formname.'_inputcol" class="jforms-ctrl-color jforms-required" value="#F0F0F0" type="color" style="width:5em;height:25px;"/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlColor(\'inputcol\', \'Couleur\');
c.required = true;
c.errRequired=\'"Couleur" field is required\';
c.errInvalid=\'"Couleur" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->setReadOnly(true);
        $ctrl->required=false;
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEquals('<input name="inputcol" id="'.$this->formname.'_inputcol" readonly="readonly" class="jforms-ctrl-color jforms-readonly" value="#F0F0F0" type="color" style="width:5em;height:25px;"/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlColor(\'inputcol\', \'Couleur\');
c.readOnly = true;
c.errRequired=\'"Couleur" field is required\';
c.errInvalid=\'"Couleur" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->setReadOnly(false);
        $ctrl->help='some help';
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEquals('<input name="inputcol" id="'.$this->formname.'_inputcol" class="jforms-ctrl-color" value="#F0F0F0" type="color" style="width:5em;height:25px;"/>'."\n".'<span class="jforms-help" id="'.$this->formname.'_inputcol-help">&nbsp;<span>some help</span></span>', $out);
        $this->assertEquals('c = new jFormsJQControlColor(\'inputcol\', \'Couleur\');
c.errRequired=\'"Couleur" field is required\';
c.errInvalid=\'"Couleur" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->help="some \nhelp with ' and\nline break.";
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEquals('<input name="inputcol" id="'.$this->formname.'_inputcol" class="jforms-ctrl-color" value="#F0F0F0" type="color" style="width:5em;height:25px;"/>'."\n".'<span class="jforms-help" id="'.$this->formname.'_inputcol-help">'."&nbsp;<span>some \nhelp with ' and\nline break.</span>".'</span>', $out);
        $this->assertEquals('c = new jFormsJQControlColor(\'inputcol\', \'Couleur\');
c.errRequired=\'"Couleur" field is required\';
c.errInvalid=\'"Couleur" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl->hint='ceci est un tooltip';
        $ctrl->help='some help';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEquals('<label class="jforms-label" for="'.$this->formname.'_inputcol" id="'.$this->formname.'_inputcol_label" title="ceci est un tooltip">Couleur</label>'."\n", $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEquals('<input name="inputcol" id="'.$this->formname.'_inputcol" title="ceci est un tooltip" class="jforms-ctrl-color" value="#F0F0F0" type="color" style="width:5em;height:25px;"/>'."\n".'<span class="jforms-help" id="'.$this->formname.'_inputcol-help">&nbsp;<span>some help</span></span>', $out);
        $this->assertEquals('c = new jFormsJQControlColor(\'inputcol\', \'Couleur\');
c.errRequired=\'"Couleur" field is required\';
c.errInvalid=\'"Couleur" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());



    }
}

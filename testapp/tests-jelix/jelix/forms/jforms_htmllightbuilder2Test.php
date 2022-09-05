<?php
/**
* @package     testapp
* @subpackage  unittest module
* @author      Laurent Jouanneau
* @contributor Julien Issler
* @copyright   2007-2008 Laurent Jouanneau
* @copyright   2008-2010 Julien Issler
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(JELIX_LIB_PATH.'forms/legacy/jFormsBuilderBase.class.php');
include_once(JELIX_LIB_PATH.'forms/legacy/jFormsBuilderHtml.class.php');
require_once(JELIX_LIB_PATH.'plugins/jforms/htmllight/htmllight.jformsbuilder.php');

class testHTMLLightForm2 extends jFormsBase {
}

class testJFormsHtmlLightBuilder2 extends htmllightJformsBuilder {
    function getJsContent() { $js= $this->jsContent; $this->jsContent = '';return $js;}
    function clearJs() { $this->jsContent = ''; }
}


class jforms_HTMLLightBuilder2Test extends \Jelix\UnitTests\UnitTestCaseDb {

    protected $form;
    protected $container;
    protected $builder;
    protected $formname;

    function setUp() : void {
        self::initClassicRequest(TESTAPP_URL.'index.php');
        jApp::pushCurrentModule('jelix_tests');
        if (isset($_SESSION['JFORMS_SESSION'])) {
            unset($_SESSION['JFORMS_SESSION']);
        };
        jFile::removeDir(__DIR__.'/../../../temp/jelixtests/jforms');
        $this->container = new jFormsDataContainer('formtestlightB','');
        $this->form = new testHTMLLightForm2('formtestlightB', $this->container, true );
        $this->builder = new testJFormsHtmlLightBuilder2($this->form);
        $this->formname = $this->builder->getName();
    }

    function tearDown() : void {
        jApp::popCurrentModule();
    }
    function testOutputGroup(){

        $group= new jFormsControlGroup('identity');
        $group->label='Your identity';

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


        $expected = '<fieldset id="'.$this->formname.'_identity" class="jforms-ctrl-group"><legend>Your identity</legend>'."\n";
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
        $this->assertEquals('c = new jFormsControlString(\'nom\', \'Your name\');
c.required = true;
c.errRequired=\'"Your name" field is required\';
c.errInvalid=\'"Your name" field is invalid\';
jForms.tForm.addControl(c);
c = new jFormsControlString(\'prenom\', \'Your firstname\');
c.errRequired=\'"Your firstname" field is required\';
c.errInvalid=\'"Your firstname" field is invalid\';
jForms.tForm.addControl(c);
c = new jFormsControlString(\'sexe\', \'Vous êtes \');
c.required = true;
c.errRequired=\'Vous devez indiquer le sexe, même si vous ne savez pas :-)\';
c.errInvalid=\'"Vous êtes " field is invalid\';
jForms.tForm.addControl(c);
c = new jFormsControlEmail(\'mail\', \'Votre mail\');
c.errRequired=\'"Votre mail" field is required\';
c.errInvalid=\'"Votre mail" field is invalid\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());

        $group->setReadOnly(true);
        $expected = '<fieldset id="'.$this->formname.'_identity" class="jforms-ctrl-group"><legend>Your identity</legend>'."\n";
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
        $this->assertEquals('c = new jFormsControlString(\'nom\', \'Your name\');
c.readOnly = true;
c.required = true;
c.errRequired=\'"Your name" field is required\';
c.errInvalid=\'"Your name" field is invalid\';
jForms.tForm.addControl(c);
c = new jFormsControlString(\'prenom\', \'Your firstname\');
c.readOnly = true;
c.errRequired=\'"Your firstname" field is required\';
c.errInvalid=\'"Your firstname" field is invalid\';
jForms.tForm.addControl(c);
c = new jFormsControlString(\'sexe\', \'Vous êtes \');
c.readOnly = true;
c.required = true;
c.errRequired=\'Vous devez indiquer le sexe, même si vous ne savez pas :-)\';
c.errInvalid=\'"Vous êtes " field is invalid\';
jForms.tForm.addControl(c);
c = new jFormsControlEmail(\'mail\', \'Votre mail\');
c.readOnly = true;
c.errRequired=\'"Votre mail" field is required\';
c.errInvalid=\'"Votre mail" field is invalid\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());

    }


    function testOutputChoice(){

        $choice= new jFormsControlChoice('status');
        $choice->label='Task Status';
        $choice->createItem('new','New');
        $choice->createItem('assigned','Assigned');
        $choice->createItem('closed','Closed');

        $ctrl= new jFormsControlInput('nom');
        $ctrl->required=true;
        $ctrl->label='Name';
        $choice->addChildControl($ctrl,'assigned');


        $ctrl= new jFormsControlInput('prenom');
        $ctrl->defaultValue='robert';
        $ctrl->label='Firstname';
        $choice->addChildControl($ctrl,'assigned');

        $ctrl= new jFormsControlMenulist('reason');
        $ctrl->required=true;
        $ctrl->label='Reason ';
        $ctrl->alertRequired='Hey, specify a reason !';
        $ctrl->datasource= new jFormsStaticDatasource();
        $ctrl->datasource->data = array(
        'aa'=>'fixed',
        'bb'=>'won t fixed',
        'cc'=>'later',
        );
        $choice->addChildControl($ctrl,'closed');

        $this->form->addControl($choice);
        $choice->setReadOnly(false);

        ob_start();$this->builder->outputControlLabel($choice);$out = ob_get_clean();
        $this->assertEquals('<span class="jforms-label" id="'.$this->formname.'_status_label">Task Status</span>'."\n", $out);


        $expected = '<ul class="jforms-choice jforms-ctl-status" >'."\n";
        $expected .= '<li id="'.$this->formname.'_status_new_item">';
        $expected .=    '<input name="status" id="'.$this->formname.'_status_0" type="radio" value="new" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'new\')"/>';
        $expected .=    '<label for="'.$this->formname.'_status_0">New</label>'."\n".'</li>'."\n";
        $expected .= '<li id="'.$this->formname.'_status_assigned_item">';
        $expected .=    '<input name="status" id="'.$this->formname.'_status_1" type="radio" value="assigned" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'assigned\')"/>';
        $expected .=    '<label for="'.$this->formname.'_status_1">Assigned</label>'."\n";
        $expected .=    ' <span class="jforms-item-controls">';
        $expected .=        '<label class="jforms-label jforms-required" for="'.$this->formname.'_nom" id="'.$this->formname.'_nom_label">Name';
        $expected .=            '<span class="jforms-required-star">*</span></label>'."\n";
        $expected .=        ' <input name="nom" id="'.$this->formname.'_nom" class="jforms-ctrl-input jforms-required" value="" type="text"/>'."\n";
        $expected .=    '</span>'."\n";
        $expected .=    ' <span class="jforms-item-controls"><label class="jforms-label" for="'.$this->formname.'_prenom" id="'.$this->formname.'_prenom_label">Firstname</label>'."\n";
        $expected .=        ' <input name="prenom" id="'.$this->formname.'_prenom" class="jforms-ctrl-input" value="robert" type="text"/>'."\n";
        $expected .=    '</span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '<li id="'.$this->formname.'_status_closed_item">';
        $expected .=    '<input name="status" id="'.$this->formname.'_status_2" type="radio" value="closed" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'closed\')"/>';
        $expected .=    '<label for="'.$this->formname.'_status_2">Closed</label>'."\n";
        $expected .=    ' <span class="jforms-item-controls">';
        $expected .=        '<label class="jforms-label jforms-required" for="'.$this->formname.'_reason" id="'.$this->formname.'_reason_label">Reason <span class="jforms-required-star">*</span></label>'."\n";
        $expected .=        ' <select name="reason" id="'.$this->formname.'_reason" class="jforms-ctrl-menulist jforms-required" size="1">'."\n".'<option value="aa">fixed</option>'."\n".'<option value="bb">won t fixed</option>'."\n".'<option value="cc">later</option>'."\n".'</select>'."\n";
        $expected .=    '</span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '</ul>'."\n\n";
        ob_start();$this->builder->outputControl($choice);$out = ob_get_clean();
        $this->assertEquals($expected, $out);
        $this->assertEquals('c = new jFormsControlChoice(\'status\', \'Task Status\');
c.errRequired=\'"Task Status" field is required\';
c.errInvalid=\'"Task Status" field is invalid\';
jForms.tForm.addControl(c);
c2 = c;
c2.items[\'new\']=[];
c = new jFormsControlString(\'nom\', \'Name\');
c.required = true;
c.errRequired=\'"Name" field is required\';
c.errInvalid=\'"Name" field is invalid\';
c2.addControl(c, \'assigned\');
c = new jFormsControlString(\'prenom\', \'Firstname\');
c.errRequired=\'"Firstname" field is required\';
c.errInvalid=\'"Firstname" field is invalid\';
c2.addControl(c, \'assigned\');
c = new jFormsControlString(\'reason\', \'Reason \');
c.required = true;
c.errRequired=\'Hey, specify a reason !\';
c.errInvalid=\'"Reason " field is invalid\';
c2.addControl(c, \'closed\');
c2.activate(\'\');
', $this->builder->getJsContent());

        $this->form->getContainer()->data['status']='assigned';

        $expected = '<ul class="jforms-choice jforms-ctl-status" >'."\n";
        $expected .= '<li id="'.$this->formname.'_status_new_item"><input name="status" id="'.$this->formname.'_status_0" type="radio" value="new" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'new\')"/><label for="'.$this->formname.'_status_0">New</label>'."\n".'</li>'."\n";
        $expected .= '<li id="'.$this->formname.'_status_assigned_item"><input name="status" id="'.$this->formname.'_status_1" type="radio" value="assigned" checked="checked" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'assigned\')"/><label for="'.$this->formname.'_status_1">Assigned</label>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label jforms-required" for="'.$this->formname.'_nom" id="'.$this->formname.'_nom_label">Name<span class="jforms-required-star">*</span></label>'."\n".' <input name="nom" id="'.$this->formname.'_nom" class="jforms-ctrl-input jforms-required" value="" type="text"/>'."\n".'</span>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label" for="'.$this->formname.'_prenom" id="'.$this->formname.'_prenom_label">Firstname</label>'."\n".' <input name="prenom" id="'.$this->formname.'_prenom" class="jforms-ctrl-input" value="robert" type="text"/>'."\n".'</span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '<li id="'.$this->formname.'_status_closed_item"><input name="status" id="'.$this->formname.'_status_2" type="radio" value="closed" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'closed\')"/><label for="'.$this->formname.'_status_2">Closed</label>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label jforms-required" for="'.$this->formname.'_reason" id="'.$this->formname.'_reason_label">Reason <span class="jforms-required-star">*</span></label>'."\n";
        $expected .= ' <select name="reason" id="'.$this->formname.'_reason" class="jforms-ctrl-menulist jforms-required" size="1">'."\n".'<option value="aa">fixed</option>'."\n".'<option value="bb">won t fixed</option>'."\n".'<option value="cc">later</option>'."\n".'</select>'."\n".'</span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '</ul>'."\n\n";
        ob_start();$this->builder->outputControl($choice);$out = ob_get_clean();
        $this->assertEquals($expected, $out);
        $this->assertEquals('c = new jFormsControlChoice(\'status\', \'Task Status\');
c.errRequired=\'"Task Status" field is required\';
c.errInvalid=\'"Task Status" field is invalid\';
jForms.tForm.addControl(c);
c2 = c;
c2.items[\'new\']=[];
c = new jFormsControlString(\'nom\', \'Name\');
c.required = true;
c.errRequired=\'"Name" field is required\';
c.errInvalid=\'"Name" field is invalid\';
c2.addControl(c, \'assigned\');
c = new jFormsControlString(\'prenom\', \'Firstname\');
c.errRequired=\'"Firstname" field is required\';
c.errInvalid=\'"Firstname" field is invalid\';
c2.addControl(c, \'assigned\');
c = new jFormsControlString(\'reason\', \'Reason \');
c.required = true;
c.errRequired=\'Hey, specify a reason !\';
c.errInvalid=\'"Reason " field is invalid\';
c2.addControl(c, \'closed\');
c2.activate(\'assigned\');
', $this->builder->getJsContent());

        $this->form->getContainer()->data['status']='new';
        $expected = '<ul class="jforms-choice jforms-ctl-status" >'."\n";
        $expected .= '<li id="'.$this->formname.'_status_new_item"><input name="status" id="'.$this->formname.'_status_0" type="radio" value="new" checked="checked" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'new\')"/><label for="'.$this->formname.'_status_0">New</label>'."\n".'</li>'."\n";
        $expected .= '<li id="'.$this->formname.'_status_assigned_item"><input name="status" id="'.$this->formname.'_status_1" type="radio" value="assigned" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'assigned\')"/><label for="'.$this->formname.'_status_1">Assigned</label>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label jforms-required" for="'.$this->formname.'_nom" id="'.$this->formname.'_nom_label">Name<span class="jforms-required-star">*</span></label>'."\n".' <input name="nom" id="'.$this->formname.'_nom" class="jforms-ctrl-input jforms-required" value="" type="text"/>'."\n".'</span>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label" for="'.$this->formname.'_prenom" id="'.$this->formname.'_prenom_label">Firstname</label>'."\n".' <input name="prenom" id="'.$this->formname.'_prenom" class="jforms-ctrl-input" value="robert" type="text"/>'."\n".'</span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '<li id="'.$this->formname.'_status_closed_item"><input name="status" id="'.$this->formname.'_status_2" type="radio" value="closed" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'closed\')"/><label for="'.$this->formname.'_status_2">Closed</label>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label jforms-required" for="'.$this->formname.'_reason" id="'.$this->formname.'_reason_label">Reason <span class="jforms-required-star">*</span></label>'."\n";
        $expected .= ' <select name="reason" id="'.$this->formname.'_reason" class="jforms-ctrl-menulist jforms-required" size="1">'."\n".'<option value="aa">fixed</option>'."\n".'<option value="bb">won t fixed</option>'."\n".'<option value="cc">later</option>'."\n".'</select>'."\n".'</span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '</ul>'."\n\n";
        ob_start();$this->builder->outputControl($choice);$out = ob_get_clean();
        $this->assertEquals($expected, $out);
        $this->assertEquals('c = new jFormsControlChoice(\'status\', \'Task Status\');
c.errRequired=\'"Task Status" field is required\';
c.errInvalid=\'"Task Status" field is invalid\';
jForms.tForm.addControl(c);
c2 = c;
c2.items[\'new\']=[];
c = new jFormsControlString(\'nom\', \'Name\');
c.required = true;
c.errRequired=\'"Name" field is required\';
c.errInvalid=\'"Name" field is invalid\';
c2.addControl(c, \'assigned\');
c = new jFormsControlString(\'prenom\', \'Firstname\');
c.errRequired=\'"Firstname" field is required\';
c.errInvalid=\'"Firstname" field is invalid\';
c2.addControl(c, \'assigned\');
c = new jFormsControlString(\'reason\', \'Reason \');
c.required = true;
c.errRequired=\'Hey, specify a reason !\';
c.errInvalid=\'"Reason " field is invalid\';
c2.addControl(c, \'closed\');
c2.activate(\'new\');
', $this->builder->getJsContent());

        $this->form->getContainer()->data['status']='closed';
        $expected = '<ul class="jforms-choice jforms-ctl-status" >'."\n";
        $expected .= '<li id="'.$this->formname.'_status_new_item"><input name="status" id="'.$this->formname.'_status_0" type="radio" value="new" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'new\')"/><label for="'.$this->formname.'_status_0">New</label>'."\n".'</li>'."\n";
        $expected .= '<li id="'.$this->formname.'_status_assigned_item"><input name="status" id="'.$this->formname.'_status_1" type="radio" value="assigned" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'assigned\')"/><label for="'.$this->formname.'_status_1">Assigned</label>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label jforms-required" for="'.$this->formname.'_nom" id="'.$this->formname.'_nom_label">Name<span class="jforms-required-star">*</span></label>'."\n".' <input name="nom" id="'.$this->formname.'_nom" class="jforms-ctrl-input jforms-required" value="" type="text"/>'."\n".'</span>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label" for="'.$this->formname.'_prenom" id="'.$this->formname.'_prenom_label">Firstname</label>'."\n".' <input name="prenom" id="'.$this->formname.'_prenom" class="jforms-ctrl-input" value="robert" type="text"/>'."\n".'</span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '<li id="'.$this->formname.'_status_closed_item"><input name="status" id="'.$this->formname.'_status_2" type="radio" value="closed" checked="checked" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'closed\')"/><label for="'.$this->formname.'_status_2">Closed</label>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label jforms-required" for="'.$this->formname.'_reason" id="'.$this->formname.'_reason_label">Reason <span class="jforms-required-star">*</span></label>'."\n";
        $expected .= ' <select name="reason" id="'.$this->formname.'_reason" class="jforms-ctrl-menulist jforms-required" size="1">'."\n".'<option value="aa">fixed</option>'."\n".'<option value="bb">won t fixed</option>'."\n".'<option value="cc">later</option>'."\n".'</select>'."\n".'</span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '</ul>'."\n\n";
        ob_start();$this->builder->outputControl($choice);$out = ob_get_clean();
        $this->assertEquals($expected, $out);
        $this->assertEquals('c = new jFormsControlChoice(\'status\', \'Task Status\');
c.errRequired=\'"Task Status" field is required\';
c.errInvalid=\'"Task Status" field is invalid\';
jForms.tForm.addControl(c);
c2 = c;
c2.items[\'new\']=[];
c = new jFormsControlString(\'nom\', \'Name\');
c.required = true;
c.errRequired=\'"Name" field is required\';
c.errInvalid=\'"Name" field is invalid\';
c2.addControl(c, \'assigned\');
c = new jFormsControlString(\'prenom\', \'Firstname\');
c.errRequired=\'"Firstname" field is required\';
c.errInvalid=\'"Firstname" field is invalid\';
c2.addControl(c, \'assigned\');
c = new jFormsControlString(\'reason\', \'Reason \');
c.required = true;
c.errRequired=\'Hey, specify a reason !\';
c.errInvalid=\'"Reason " field is invalid\';
c2.addControl(c, \'closed\');
c2.activate(\'closed\');
', $this->builder->getJsContent());

        $choice->setReadOnly(true);

        $expected = '<ul class="jforms-choice jforms-ctl-status" >'."\n";
        $expected .= '<li id="'.$this->formname.'_status_new_item"><input name="status" id="'.$this->formname.'_status_0" readonly="readonly" type="radio" value="new" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'new\')"/><label for="'.$this->formname.'_status_0">New</label>'."\n".'</li>'."\n";
        $expected .= '<li id="'.$this->formname.'_status_assigned_item"><input name="status" id="'.$this->formname.'_status_1" readonly="readonly" type="radio" value="assigned" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'assigned\')"/><label for="'.$this->formname.'_status_1">Assigned</label>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label" for="'.$this->formname.'_nom" id="'.$this->formname.'_nom_label">Name</label>'."\n".' <input name="nom" id="'.$this->formname.'_nom" readonly="readonly" class="jforms-ctrl-input jforms-readonly" value="" type="text"/>'."\n".'</span>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label" for="'.$this->formname.'_prenom" id="'.$this->formname.'_prenom_label">Firstname</label>'."\n".' <input name="prenom" id="'.$this->formname.'_prenom" readonly="readonly" class="jforms-ctrl-input jforms-readonly" value="robert" type="text"/>'."\n".'</span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '<li id="'.$this->formname.'_status_closed_item"><input name="status" id="'.$this->formname.'_status_2" readonly="readonly" type="radio" value="closed" checked="checked" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'closed\')"/><label for="'.$this->formname.'_status_2">Closed</label>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label" for="'.$this->formname.'_reason" id="'.$this->formname.'_reason_label">Reason </label>'."\n";
        $expected .= ' <select name="reason" id="'.$this->formname.'_reason" class="jforms-ctrl-menulist jforms-readonly" disabled="disabled" size="1">'."\n".'<option value="aa">fixed</option>'."\n".'<option value="bb">won t fixed</option>'."\n".'<option value="cc">later</option>'."\n".'</select>'."\n".'</span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '</ul>'."\n\n";
        ob_start();$this->builder->outputControl($choice);$out = ob_get_clean();
        $this->assertEquals($expected, $out);
        $this->assertEquals('c = new jFormsControlChoice(\'status\', \'Task Status\');
c.readOnly = true;
c.errRequired=\'"Task Status" field is required\';
c.errInvalid=\'"Task Status" field is invalid\';
jForms.tForm.addControl(c);
c2 = c;
c2.items[\'new\']=[];
c = new jFormsControlString(\'nom\', \'Name\');
c.readOnly = true;
c.required = true;
c.errRequired=\'"Name" field is required\';
c.errInvalid=\'"Name" field is invalid\';
c2.addControl(c, \'assigned\');
c = new jFormsControlString(\'prenom\', \'Firstname\');
c.readOnly = true;
c.errRequired=\'"Firstname" field is required\';
c.errInvalid=\'"Firstname" field is invalid\';
c2.addControl(c, \'assigned\');
c = new jFormsControlString(\'reason\', \'Reason \');
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

        $result ='<form action="http://www.jelix.org/dummy.php" method="post" id="'.$this->builder->getName().'"><script type="text/javascript">
//<![CDATA[
jForms.tForm = new jFormsForm(\'jforms_formtestlightB\');
jForms.tForm.setErrorDecorator(new jFormsErrorDecoratorHtml());
jForms.declareForm(jForms.tForm);
//]]>
</script><div class="jforms-hiddens"><input type="hidden" name="__JFORMS_TOKEN__" value="'.$this->container->token.'"/>
</div>';

        $this->assertEquals($result, $out);
        $this->assertEquals('', $this->builder->getJsContent());

        $this->builder->setAction('http://www.jelix.org/dummy.php',array('foo'=>'bar'));
        ob_start();
        $this->builder->setOptions(array('method'=>'post'));
        $this->builder->outputHeader();
        $out = ob_get_clean();

        $result ='<form action="http://www.jelix.org/dummy.php" method="post" id="'.$this->builder->getName().'"><script type="text/javascript">
//<![CDATA[
jForms.tForm = new jFormsForm(\'jforms_formtestlightB1\');
jForms.tForm.setErrorDecorator(new jFormsErrorDecoratorHtml());
jForms.declareForm(jForms.tForm);
//]]>
</script><div class="jforms-hiddens"><input type="hidden" name="foo" value="bar"/>
<input type="hidden" name="__JFORMS_TOKEN__" value="'.$this->container->token.'"/>
</div>';

        $this->assertEquals($result, $out);
        $this->assertEquals('', $this->builder->getJsContent());

        $this->builder->setAction('https://www.jelix.org/dummy.php',array());
        ob_start();
        $this->builder->setOptions(array('method'=>'get'));
        $this->builder->outputHeader();
        $out = ob_get_clean();

        $result ='<form action="https://www.jelix.org/dummy.php" method="get" id="'.$this->builder->getName().'"><script type="text/javascript">
//<![CDATA[
jForms.tForm = new jFormsForm(\'jforms_formtestlightB2\');
jForms.tForm.setErrorDecorator(new jFormsErrorDecoratorHtml());
jForms.declareForm(jForms.tForm);
//]]>
</script><div class="jforms-hiddens"><input type="hidden" name="__JFORMS_TOKEN__" value="'.$this->container->token.'"/>
</div>';

        $this->assertEquals($result, $out);
        $this->assertEquals('', $this->builder->getJsContent());

    }
}

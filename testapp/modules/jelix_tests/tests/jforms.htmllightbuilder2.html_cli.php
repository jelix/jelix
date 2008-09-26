<?php
/**
* @package     testapp
* @subpackage  unittest module
* @author      Jouanneau Laurent
* @contributor Julien Issler
* @copyright   2007-2008 Jouanneau laurent
* @copyright   2008 Julien Issler
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(JELIX_LIB_PATH.'forms/jFormsBase.class.php');
require_once(JELIX_LIB_PATH.'forms/jFormsBuilderBase.class.php');
require_once(JELIX_LIB_PATH.'forms/jFormsDataContainer.class.php');
require_once(JELIX_LIB_PATH.'plugins/jforms/htmllight/htmllight.jformsbuilder.php');

class testHTMLLightForm2 extends jFormsBase { 
}

class testJFormsHtmlLightBuilder2 extends htmllightJformsBuilder {
    function getJsContent() { $js= $this->jsContent; $this->jsContent = '';return $js;}
    function clearJs() { $this->jsContent = ''; }
}


class UTjformsHTMLLightBuilder2 extends jUnitTestCaseDb {

    protected $form;
    protected $container;
    protected $builder;
    protected $formname;

    function testStart() {
        $this->container = new jFormsDataContainer('formtestlightB','');
        $this->form = new testHTMLLightForm2('formtestlightB', $this->container, true );
        $this->builder = new testJFormsHtmlLightBuilder2($this->form);
        $this->formname = $this->builder->getName();
    }

    function testOutputGroup(){

        $group= new jFormsControlgroup('identity');
        $group->label='Your identity';

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
        $this->assertEqualOrDiff('', $out);


        $expected = '<fieldset><legend>Your identity</legend>'."\n";
        $expected .= '<table class="jforms-table-group" border="0">'."\n";
        $expected .= '<tr><th scope="row"><label class="jforms-label jforms-required" for="'.$this->formname.'_nom">Your name</label></th>'."\n";
        $expected .= '<td><input type="text" name="nom" id="'.$this->formname.'_nom" class=" jforms-required" value=""/></td></tr>'."\n";
        $expected .= '<tr><th scope="row"><label class="jforms-label" for="'.$this->formname.'_prenom">Your firstname</label></th>'."\n";
        $expected .= '<td><input type="text" name="prenom" id="'.$this->formname.'_prenom" value="robert"/></td></tr>'."\n";
        $expected .= '<tr><th scope="row"><span class="jforms-label jforms-required">Vous êtes </span></th>'."\n";
        $expected .= '<td><span class="jforms-radio jforms-ctl-sexe"><input type="radio" name="sexe" id="'.$this->formname.'_sexe_0" value="h" class=" jforms-required"/><label for="'.$this->formname.'_sexe_0">un homme</label></span>';
        $expected .= '<span class="jforms-radio jforms-ctl-sexe"><input type="radio" name="sexe" id="'.$this->formname.'_sexe_1" value="f" class=" jforms-required"/><label for="'.$this->formname.'_sexe_1">une femme</label></span>';
        $expected .= '<span class="jforms-radio jforms-ctl-sexe"><input type="radio" name="sexe" id="'.$this->formname.'_sexe_2" value="no" class=" jforms-required"/><label for="'.$this->formname.'_sexe_2">je ne sais pas</label></span></td></tr>'."\n";
        $expected .= '<tr><th scope="row"><label class="jforms-label" for="'.$this->formname.'_mail">Votre mail</label></th>'."\n";
        $expected .= '<td><input type="text" name="mail" id="'.$this->formname.'_mail" value=""/></td></tr>'."\n</table></fieldset>";


        ob_start();$this->builder->outputControl($group);$out = ob_get_clean();
        $this->assertEqualOrDiff($expected, $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'nom\', \'Your name\');
c.required = true;
c.errRequired=\'"Your name" field is required\';
c.errInvalid=\'"Your name" field is invalid\';
jForms.tForm.addControl(c);
c = new jFormsControlString(\'prenom\', \'Your firstname\');
c.errInvalid=\'"Your firstname" field is invalid\';
jForms.tForm.addControl(c);
c = new jFormsControlString(\'sexe\', \'Vous êtes \');
c.required = true;
c.errRequired=\'Vous devez indiquer le sexe, même si vous ne savez pas :-)\';
c.errInvalid=\'"Vous êtes " field is invalid\';
jForms.tForm.addControl(c);
c = new jFormsControlEmail(\'mail\', \'Votre mail\');
c.errInvalid=\'"Votre mail" field is invalid\';
jForms.tForm.addControl(c);
', $this->builder->getJsContent());

        $group->setReadOnly(true);
        $expected = '<fieldset><legend>Your identity</legend>'."\n";
        $expected .= '<table class="jforms-table-group" border="0">'."\n";
        $expected .= '<tr><th scope="row"><label class="jforms-label" for="'.$this->formname.'_nom">Your name</label></th>'."\n";
        $expected .= '<td><input type="text" name="nom" id="'.$this->formname.'_nom" readonly="readonly" class=" jforms-readonly" value=""/></td></tr>'."\n";
        $expected .= '<tr><th scope="row"><label class="jforms-label" for="'.$this->formname.'_prenom">Your firstname</label></th>'."\n";
        $expected .= '<td><input type="text" name="prenom" id="'.$this->formname.'_prenom" readonly="readonly" class=" jforms-readonly" value="robert"/></td></tr>'."\n";
        $expected .= '<tr><th scope="row"><span class="jforms-label">Vous êtes </span></th>'."\n";
        $expected .= '<td><span class="jforms-radio jforms-ctl-sexe"><input type="radio" name="sexe" id="'.$this->formname.'_sexe_0" value="h" readonly="readonly" class=" jforms-readonly"/><label for="'.$this->formname.'_sexe_0">un homme</label></span>';
        $expected .= '<span class="jforms-radio jforms-ctl-sexe"><input type="radio" name="sexe" id="'.$this->formname.'_sexe_1" value="f" readonly="readonly" class=" jforms-readonly"/><label for="'.$this->formname.'_sexe_1">une femme</label></span>';
        $expected .= '<span class="jforms-radio jforms-ctl-sexe"><input type="radio" name="sexe" id="'.$this->formname.'_sexe_2" value="no" readonly="readonly" class=" jforms-readonly"/><label for="'.$this->formname.'_sexe_2">je ne sais pas</label></span></td></tr>'."\n";
        $expected .= '<tr><th scope="row"><label class="jforms-label" for="'.$this->formname.'_mail">Votre mail</label></th>'."\n";
        $expected .= '<td><input type="text" name="mail" id="'.$this->formname.'_mail" readonly="readonly" class=" jforms-readonly" value=""/></td></tr>'."\n</table></fieldset>";
        ob_start();$this->builder->outputControl($group);$out = ob_get_clean();
        $this->assertEqualOrDiff($expected, $out);
        $this->assertEqualOrDiff('c = new jFormsControlString(\'nom\', \'Your name\');
c.required = true;
c.errRequired=\'"Your name" field is required\';
c.errInvalid=\'"Your name" field is invalid\';
jForms.tForm.addControl(c);
c = new jFormsControlString(\'prenom\', \'Your firstname\');
c.errInvalid=\'"Your firstname" field is invalid\';
jForms.tForm.addControl(c);
c = new jFormsControlString(\'sexe\', \'Vous êtes \');
c.required = true;
c.errRequired=\'Vous devez indiquer le sexe, même si vous ne savez pas :-)\';
c.errInvalid=\'"Vous êtes " field is invalid\';
jForms.tForm.addControl(c);
c = new jFormsControlEmail(\'mail\', \'Votre mail\');
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

        $ctrl= new jFormsControlinput('nom');
        $ctrl->required=true;
        $ctrl->label='Name';
        $choice->addChildControl($ctrl,'assigned');


        $ctrl= new jFormsControlinput('prenom');
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


        ob_start();$this->builder->outputControlLabel($choice);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_status">Task Status</label>', $out);


        $expected = '<ul class="jforms-choice jforms-ctl-status" >'."\n";
        $expected .= '<li><label><input type="radio" name="status" id="'.$this->formname.'_status_0" value="new" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'new\')"/>New</label> </li>'."\n";
        $expected .= '<li><label><input type="radio" name="status" id="'.$this->formname.'_status_1" value="assigned" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'assigned\')"/>Assigned</label>  <span class="jforms-item-controls"><label class="jforms-label" for="'.$this->formname.'_nom">Name</label> <input type="text" name="nom" id="'.$this->formname.'_nom" readonly="readonly" class=" jforms-readonly" value=""/></span>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label" for="'.$this->formname.'_prenom">Firstname</label> <input type="text" name="prenom" id="'.$this->formname.'_prenom" readonly="readonly" class=" jforms-readonly" value="robert"/></span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '<li><label><input type="radio" name="status" id="'.$this->formname.'_status_2" value="closed" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'closed\')"/>Closed</label>  <span class="jforms-item-controls"><label class="jforms-label jforms-required" for="'.$this->formname.'_reason">Reason </label> <select name="reason" id="'.$this->formname.'_reason" class=" jforms-required" size="1"><option value="aa">fixed</option><option value="bb">won t fixed</option><option value="cc">later</option></select></span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '</ul>'."\n";
        ob_start();$this->builder->outputControl($choice);$out = ob_get_clean();
        $this->assertEqualOrDiff($expected, $out);
        $this->assertEqualOrDiff('c = new jFormsControlChoice(\'status\', \'Task Status\');
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
        $expected .= '<li><label><input type="radio" name="status" id="'.$this->formname.'_status_0" value="new" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'new\')"/>New</label> </li>'."\n";
        $expected .= '<li><label><input type="radio" name="status" id="'.$this->formname.'_status_1" value="assigned" checked="checked" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'assigned\')"/>Assigned</label>  <span class="jforms-item-controls"><label class="jforms-label" for="'.$this->formname.'_nom">Name</label> <input type="text" name="nom" id="'.$this->formname.'_nom" readonly="readonly" class=" jforms-readonly" value=""/></span>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label" for="'.$this->formname.'_prenom">Firstname</label> <input type="text" name="prenom" id="'.$this->formname.'_prenom" readonly="readonly" class=" jforms-readonly" value="robert"/></span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '<li><label><input type="radio" name="status" id="'.$this->formname.'_status_2" value="closed" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'closed\')"/>Closed</label>  <span class="jforms-item-controls"><label class="jforms-label jforms-required" for="'.$this->formname.'_reason">Reason </label> <select name="reason" id="'.$this->formname.'_reason" class=" jforms-required" size="1"><option value="aa">fixed</option><option value="bb">won t fixed</option><option value="cc">later</option></select></span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '</ul>'."\n";
        ob_start();$this->builder->outputControl($choice);$out = ob_get_clean();
        $this->assertEqualOrDiff($expected, $out);
        $this->assertEqualOrDiff('c = new jFormsControlChoice(\'status\', \'Task Status\');
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
        $expected .= '<li><label><input type="radio" name="status" id="'.$this->formname.'_status_0" value="new" checked="checked" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'new\')"/>New</label> </li>'."\n";
        $expected .= '<li><label><input type="radio" name="status" id="'.$this->formname.'_status_1" value="assigned" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'assigned\')"/>Assigned</label>  <span class="jforms-item-controls"><label class="jforms-label" for="'.$this->formname.'_nom">Name</label> <input type="text" name="nom" id="'.$this->formname.'_nom" readonly="readonly" class=" jforms-readonly" value=""/></span>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label" for="'.$this->formname.'_prenom">Firstname</label> <input type="text" name="prenom" id="'.$this->formname.'_prenom" readonly="readonly" class=" jforms-readonly" value="robert"/></span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '<li><label><input type="radio" name="status" id="'.$this->formname.'_status_2" value="closed" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'closed\')"/>Closed</label>  <span class="jforms-item-controls"><label class="jforms-label jforms-required" for="'.$this->formname.'_reason">Reason </label> <select name="reason" id="'.$this->formname.'_reason" class=" jforms-required" size="1"><option value="aa">fixed</option><option value="bb">won t fixed</option><option value="cc">later</option></select></span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '</ul>'."\n";
        ob_start();$this->builder->outputControl($choice);$out = ob_get_clean();
        $this->assertEqualOrDiff($expected, $out);
        $this->assertEqualOrDiff('c = new jFormsControlChoice(\'status\', \'Task Status\');
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
        $expected .= '<li><label><input type="radio" name="status" id="'.$this->formname.'_status_0" value="new" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'new\')"/>New</label> </li>'."\n";
        $expected .= '<li><label><input type="radio" name="status" id="'.$this->formname.'_status_1" value="assigned" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'assigned\')"/>Assigned</label>  <span class="jforms-item-controls"><label class="jforms-label" for="'.$this->formname.'_nom">Name</label> <input type="text" name="nom" id="'.$this->formname.'_nom" readonly="readonly" class=" jforms-readonly" value=""/></span>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label" for="'.$this->formname.'_prenom">Firstname</label> <input type="text" name="prenom" id="'.$this->formname.'_prenom" readonly="readonly" class=" jforms-readonly" value="robert"/></span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '<li><label><input type="radio" name="status" id="'.$this->formname.'_status_2" value="closed" checked="checked" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'closed\')"/>Closed</label>  <span class="jforms-item-controls"><label class="jforms-label jforms-required" for="'.$this->formname.'_reason">Reason </label> <select name="reason" id="'.$this->formname.'_reason" class=" jforms-required" size="1"><option value="aa">fixed</option><option value="bb">won t fixed</option><option value="cc">later</option></select></span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '</ul>'."\n";
        ob_start();$this->builder->outputControl($choice);$out = ob_get_clean();
        $this->assertEqualOrDiff($expected, $out);
        $this->assertEqualOrDiff('c = new jFormsControlChoice(\'status\', \'Task Status\');
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
c.errInvalid=\'"Firstname" field is invalid\';
c2.addControl(c, \'assigned\');
c = new jFormsControlString(\'reason\', \'Reason \');
c.required = true;
c.errRequired=\'Hey, specify a reason !\';
c.errInvalid=\'"Reason " field is invalid\';
c2.addControl(c, \'closed\');
c2.activate(\'closed\');
', $this->builder->getJsContent());
    }
}


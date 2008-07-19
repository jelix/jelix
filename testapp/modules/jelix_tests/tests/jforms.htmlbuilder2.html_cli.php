<?php
/**
* @package     testapp
* @subpackage  unittest module
* @author      Jouanneau Laurent
* @contributor
* @copyright   2007-2008 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(JELIX_LIB_PATH.'forms/jFormsBase.class.php');
require_once(JELIX_LIB_PATH.'forms/jFormsBuilderBase.class.php');
require_once(JELIX_LIB_PATH.'forms/jFormsDataContainer.class.php');
require_once(JELIX_LIB_PATH.'plugins/jforms/html/html.jformsbuilder.php');

class testHMLForm2 extends jFormsBase { 
}

class testJFormsHtmlBuilder2 extends htmlJformsBuilder {
    public function getJavascriptCheck($errDecorator,$helpDecorator){
        return '';
    }
}


class UTjformsHTMLBuilder2 extends jUnitTestCaseDb {

    protected $form;
    protected $container;
    protected $builder;
    protected $formname;

    function testStart() {
        $this->container = new jFormsDataContainer('formtest','');
        $this->form = new testHMLForm2('formtest', $this->container, true );
        $this->builder = new testJFormsHtmlBuilder2($this->form);
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
        $expected .= '<script type="text/javascript">'."\n";
        $expected .= '//<![CDATA['."\n";
        $expected .= 'jForms.getForm("").getControl("status").activate("");'."\n";
        $expected .= '//]]>'."\n";
        $expected .= '</script>';
        ob_start();$this->builder->outputControl($choice);$out = ob_get_clean();
        $this->assertEqualOrDiff($expected, $out);

        $this->form->getContainer()->data['status']='assigned';

        $expected = '<ul class="jforms-choice jforms-ctl-status" >'."\n";
        $expected .= '<li><label><input type="radio" name="status" id="'.$this->formname.'_status_0" value="new" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'new\')"/>New</label> </li>'."\n";
        $expected .= '<li><label><input type="radio" name="status" id="'.$this->formname.'_status_1" value="assigned" checked="checked" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'assigned\')"/>Assigned</label>  <span class="jforms-item-controls"><label class="jforms-label" for="'.$this->formname.'_nom">Name</label> <input type="text" name="nom" id="'.$this->formname.'_nom" readonly="readonly" class=" jforms-readonly" value=""/></span>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label" for="'.$this->formname.'_prenom">Firstname</label> <input type="text" name="prenom" id="'.$this->formname.'_prenom" readonly="readonly" class=" jforms-readonly" value="robert"/></span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '<li><label><input type="radio" name="status" id="'.$this->formname.'_status_2" value="closed" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'closed\')"/>Closed</label>  <span class="jforms-item-controls"><label class="jforms-label jforms-required" for="'.$this->formname.'_reason">Reason </label> <select name="reason" id="'.$this->formname.'_reason" class=" jforms-required" size="1"><option value="aa">fixed</option><option value="bb">won t fixed</option><option value="cc">later</option></select></span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '</ul>'."\n";
        $expected .= '<script type="text/javascript">'."\n";
        $expected .= '//<![CDATA['."\n";
        $expected .= 'jForms.getForm("").getControl("status").activate("assigned");'."\n";
        $expected .= '//]]>'."\n";
        $expected .= '</script>';
        ob_start();$this->builder->outputControl($choice);$out = ob_get_clean();
        $this->assertEqualOrDiff($expected, $out);

        $this->form->getContainer()->data['status']='new';
        $expected = '<ul class="jforms-choice jforms-ctl-status" >'."\n";
        $expected .= '<li><label><input type="radio" name="status" id="'.$this->formname.'_status_0" value="new" checked="checked" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'new\')"/>New</label> </li>'."\n";
        $expected .= '<li><label><input type="radio" name="status" id="'.$this->formname.'_status_1" value="assigned" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'assigned\')"/>Assigned</label>  <span class="jforms-item-controls"><label class="jforms-label" for="'.$this->formname.'_nom">Name</label> <input type="text" name="nom" id="'.$this->formname.'_nom" readonly="readonly" class=" jforms-readonly" value=""/></span>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label" for="'.$this->formname.'_prenom">Firstname</label> <input type="text" name="prenom" id="'.$this->formname.'_prenom" readonly="readonly" class=" jforms-readonly" value="robert"/></span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '<li><label><input type="radio" name="status" id="'.$this->formname.'_status_2" value="closed" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'closed\')"/>Closed</label>  <span class="jforms-item-controls"><label class="jforms-label jforms-required" for="'.$this->formname.'_reason">Reason </label> <select name="reason" id="'.$this->formname.'_reason" class=" jforms-required" size="1"><option value="aa">fixed</option><option value="bb">won t fixed</option><option value="cc">later</option></select></span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '</ul>'."\n";
        $expected .= '<script type="text/javascript">'."\n";
        $expected .= '//<![CDATA['."\n";
        $expected .= 'jForms.getForm("").getControl("status").activate("new");'."\n";
        $expected .= '//]]>'."\n";
        $expected .= '</script>';
        ob_start();$this->builder->outputControl($choice);$out = ob_get_clean();
        $this->assertEqualOrDiff($expected, $out);

        $this->form->getContainer()->data['status']='closed';
        $expected = '<ul class="jforms-choice jforms-ctl-status" >'."\n";
        $expected .= '<li><label><input type="radio" name="status" id="'.$this->formname.'_status_0" value="new" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'new\')"/>New</label> </li>'."\n";
        $expected .= '<li><label><input type="radio" name="status" id="'.$this->formname.'_status_1" value="assigned" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'assigned\')"/>Assigned</label>  <span class="jforms-item-controls"><label class="jforms-label" for="'.$this->formname.'_nom">Name</label> <input type="text" name="nom" id="'.$this->formname.'_nom" readonly="readonly" class=" jforms-readonly" value=""/></span>'."\n";
        $expected .= ' <span class="jforms-item-controls"><label class="jforms-label" for="'.$this->formname.'_prenom">Firstname</label> <input type="text" name="prenom" id="'.$this->formname.'_prenom" readonly="readonly" class=" jforms-readonly" value="robert"/></span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '<li><label><input type="radio" name="status" id="'.$this->formname.'_status_2" value="closed" checked="checked" onclick="jForms.getForm(\'\').getControl(\'status\').activate(\'closed\')"/>Closed</label>  <span class="jforms-item-controls"><label class="jforms-label jforms-required" for="'.$this->formname.'_reason">Reason </label> <select name="reason" id="'.$this->formname.'_reason" class=" jforms-required" size="1"><option value="aa">fixed</option><option value="bb">won t fixed</option><option value="cc">later</option></select></span>'."\n";
        $expected .= '</li>'."\n";
        $expected .= '</ul>'."\n";
        $expected .= '<script type="text/javascript">'."\n";
        $expected .= '//<![CDATA['."\n";
        $expected .= 'jForms.getForm("").getControl("status").activate("closed");'."\n";
        $expected .= '//]]>'."\n";
        $expected .= '</script>';
        ob_start();$this->builder->outputControl($choice);$out = ob_get_clean();
        $this->assertEqualOrDiff($expected, $out);
    }



}


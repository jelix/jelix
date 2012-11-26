<?php
/**
* @package     testapp
* @subpackage  unittest module
* @author      Laurent Jouanneau
* @contributor Dominique Papin, Julien Issler
* @copyright   2007-2008 Laurent Jouanneau
* @copyright   2008 Dominique Papin
* @copyright   2008-2010 Julien Issler
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(JELIX_LIB_PATH.'forms/jFormsBase.class.php');
require_once(JELIX_LIB_PATH.'forms/jFormsBuilderBase.class.php');
include_once(JELIX_LIB_PATH.'forms/jFormsBuilderHtml.class.php');
require_once(JELIX_LIB_PATH.'forms/jFormsDataContainer.class.php');
require_once(JELIX_LIB_PATH.'plugins/jforms/html/html.jformsbuilder.php');

class testHMLForm extends jFormsBase {
}

class testJFormsHtmlBuilder extends htmlJformsBuilder {
    function getJsContent() { $js= $this->jsContent; $this->jsContent = '';return $js;}
    function clearJs() { $this->jsContent = ''; }
    function getLastJsContent() { $js= $this->lastJsContent; $this->lastJsContent = '';return $js;}
}


class UTjformsHTMLBuilder extends jUnitTestCaseDb {

    protected $form;
    protected $container;
    protected $builder;
    function setUpRun() {
        $this->container = new jFormsDataContainer('formtest','0');
        $this->form = new testHMLForm('formtest', $this->container, true );
        $this->form->securityLevel = 0;
        $this->builder = new testJFormsHtmlBuilder($this->form);

    }

    function testOutputHeader(){
        $this->builder->setAction('jelix_tests~urlsig:url1',array());
        ob_start();
        $this->builder->outputHeader(array('method'=>'post', 'attributes'=>array('class'=>'foo')));
        $out = ob_get_clean();
        $result ='<form class="foo" action="'.jApp::config()->urlengine['basePath'].'index.php/jelix_tests/urlsig/url1" method="post" id="'.$this->builder->getName().'"><script type="text/javascript">
//<![CDATA[
jFormsJQ.selectFillUrl=\''.jApp::config()->urlengine['basePath'].'index.php/jelix/jforms/getListData\';
jFormsJQ.config = {locale:\''.jApp::config()->locale.'\',basePath:\''.jApp::config()->urlengine['basePath'].'\',jqueryPath:\''.jApp::config()->urlengine['jqueryPath'].'\',jelixWWWPath:\''.jApp::config()->urlengine['jelixWWWPath'].'\'};
jFormsJQ.tForm = new jFormsJQForm(\'jforms_formtest\',\'formtest\',\'0\');
jFormsJQ.tForm.setErrorDecorator(new jFormsJQErrorDecoratorHtml());
jFormsJQ.declareForm(jFormsJQ.tForm);
//]]>
</script>';
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('', $this->builder->getJsContent());

        $this->form->securityLevel = 1;
        $this->builder->setAction('jelix_tests~urlsig:url1',array('foo'=>'b>ar'));
        ob_start();
        $this->builder->outputHeader(array('method'=>'get'));
        $out = ob_get_clean();
        $result ='<form action="'.jApp::config()->urlengine['basePath'].'index.php/jelix_tests/urlsig/url1" method="get" id="'.$this->builder->getName().'"><script type="text/javascript">
//<![CDATA[
jFormsJQ.selectFillUrl=\''.jApp::config()->urlengine['basePath'].'index.php/jelix/jforms/getListData\';
jFormsJQ.config = {locale:\''.jApp::config()->locale.'\',basePath:\''.jApp::config()->urlengine['basePath'].'\',jqueryPath:\''.jApp::config()->urlengine['jqueryPath'].'\',jelixWWWPath:\''.jApp::config()->urlengine['jelixWWWPath'].'\'};
jFormsJQ.tForm = new jFormsJQForm(\'jforms_formtest1\',\'formtest\',\'0\');
jFormsJQ.tForm.setErrorDecorator(new jFormsJQErrorDecoratorHtml());
jFormsJQ.declareForm(jFormsJQ.tForm);
//]]>
</script><div class="jforms-hiddens"><input type="hidden" name="foo" value="b&gt;ar"/>
<input type="hidden" name="__JFORMS_TOKEN__" value="'.$this->container->token.'"/>
</div>';
        $this->assertEqualOrDiff($result, $out);
        $this->formname = $this->builder->getName();
        $this->assertEqualOrDiff('', $this->builder->getJsContent());
        $this->form->securityLevel = 0;

    }
    function testOutputFooter(){
        ob_start();
        $this->builder->outputFooter();
        $out = ob_get_clean();
        $this->assertEqualOrDiff('<script type="text/javascript">
//<![CDATA[
(function(){var c, c2;

})();
//]]>
</script></form>', $out);
    }
    function testOutputInput(){
        $ctrl= new jFormsControlinput('input1');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre nom';
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_input1" id="'.$this->formname.'_input1_label">Votre nom</label>'."\n", $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="input1" id="'.$this->formname.'_input1" class="jforms-ctrl-input" value="" type="text"/>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'input1\', \'Votre nom\');
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

        $this->form->setData('input1','toto');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="input1" id="'.$this->formname.'_input1" class="jforms-ctrl-input" value="toto" type="text"/>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'input1\', \'Votre nom\');
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

        $this->form->setData('input1','toto');
        ob_start();$this->builder->outputControl($ctrl, array('class'=>'foo', 'onclick'=>"alert('bla')"));$out = ob_get_clean();
        $this->assertEqualOrDiff('<input class="foo jforms-ctrl-input" onclick="alert(\'bla\')" name="input1" id="'.$this->formname.'_input1" value="toto" type="text"/>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'input1\', \'Votre nom\');
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());



        $ctrl->defaultValue='laurent';
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="input1" id="'.$this->formname.'_input1" class="jforms-ctrl-input" value="toto" type="text"/>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'input1\', \'Votre nom\');
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

        $this->form->removeControl($ctrl->ref);
        $this->form->addControl($ctrl);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="input1" id="'.$this->formname.'_input1" class="jforms-ctrl-input" value="laurent" type="text"/>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'input1\', \'Votre nom\');
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl->required=true;
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="input1" id="'.$this->formname.'_input1" class="jforms-ctrl-input jforms-required" value="laurent" type="text"/>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'input1\', \'Votre nom\');
c.required = true;
c.errRequired=\'"Votre nom" field is required\';
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->setReadOnly(true);
        $ctrl->required=false;
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="input1" id="'.$this->formname.'_input1" readonly="readonly" class="jforms-ctrl-input jforms-readonly" value="laurent" type="text"/>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'input1\', \'Votre nom\');
c.readOnly = true;
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->setReadOnly(false);
        $ctrl->help='some help';
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="input1" id="'.$this->formname.'_input1" class="jforms-ctrl-input" value="laurent" type="text"/>'."\n".'<span class="jforms-help" id="jforms_formtest1_input1-help">&nbsp;<span>some help</span></span>', $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'input1\', \'Votre nom\');
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->help="some \nhelp with ' and\nline break.";
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="input1" id="'.$this->formname.'_input1" class="jforms-ctrl-input" value="laurent" type="text"/>'."\n".'<span class="jforms-help" id="jforms_formtest1_input1-help">'."&nbsp;<span>some \nhelp with ' and\nline break.</span>".'</span>', $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'input1\', \'Votre nom\');
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl->hint='ceci est un tooltip';
        $ctrl->help='some help';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_input1" id="'.$this->formname.'_input1_label" title="ceci est un tooltip">Votre nom</label>'."\n", $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="input1" id="'.$this->formname.'_input1" title="ceci est un tooltip" class="jforms-ctrl-input" value="laurent" type="text"/>'."\n".'<span class="jforms-help" id="jforms_formtest1_input1-help">&nbsp;<span>some help</span></span>', $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'input1\', \'Votre nom\');
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->help='';
        $ctrl->hint='';
        $ctrl->datatype->addFacet('maxLength',5);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="input1" id="'.$this->formname.'_input1" class="jforms-ctrl-input" maxlength="5" value="laurent" type="text"/>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'input1\', \'Votre nom\');
c.maxLength = \'5\';
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl->datatype->addFacet('pattern','/^[a-f]{5}$/');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="input1" id="'.$this->formname.'_input1" class="jforms-ctrl-input" maxlength="5" value="laurent" type="text"/>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'input1\', \'Votre nom\');
c.maxLength = \'5\';
c.regexp = /^[a-f]{5}$/;
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

    }
    function testOutputCheckbox(){
        $ctrl= new jFormsControlCheckbox('chk1');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Une option';
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_chk1" id="'.$this->formname.'_chk1_label">Une option</label>'."\n", $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="chk1" id="'.$this->formname.'_chk1" class="jforms-ctrl-checkbox" value="1" type="checkbox"/>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlBoolean(\'chk1\', \'Une option\');
c.errInvalid=\'"Une option" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $this->form->setData('chk1','1');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="chk1" id="'.$this->formname.'_chk1" class="jforms-ctrl-checkbox" checked="checked" value="1" type="checkbox"/>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlBoolean(\'chk1\', \'Une option\');
c.errInvalid=\'"Une option" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl= new jFormsControlCheckbox('chk2');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Une option';
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_chk2" id="'.$this->formname.'_chk2_label">Une option</label>'."\n", $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="chk2" id="'.$this->formname.'_chk2" class="jforms-ctrl-checkbox" value="1" type="checkbox"/>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlBoolean(\'chk2\', \'Une option\');
c.errInvalid=\'"Une option" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->defaultValue='1';
        $this->form->removeControl($ctrl->ref);
        $this->form->addControl($ctrl);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="chk2" id="'.$this->formname.'_chk2" class="jforms-ctrl-checkbox" checked="checked" value="1" type="checkbox"/>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlBoolean(\'chk2\', \'Une option\');
c.errInvalid=\'"Une option" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

        $this->form->setData('chk2', '0');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="chk2" id="'.$this->formname.'_chk2" class="jforms-ctrl-checkbox" value="1" type="checkbox"/>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlBoolean(\'chk2\', \'Une option\');
c.errInvalid=\'"Une option" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->setReadOnly(true);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="chk2" id="'.$this->formname.'_chk2" readonly="readonly" class="jforms-ctrl-checkbox jforms-readonly" value="1" type="checkbox"/>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlBoolean(\'chk2\', \'Une option\');
c.readOnly = true;
c.errInvalid=\'"Une option" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $this->form->setData('chk2', '1');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="chk2" id="'.$this->formname.'_chk2" readonly="readonly" class="jforms-ctrl-checkbox jforms-readonly" checked="checked" value="1" type="checkbox"/>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlBoolean(\'chk2\', \'Une option\');
c.readOnly = true;
c.errInvalid=\'"Une option" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_chk2" id="'.$this->formname.'_chk2_label" title="ceci est un tooltip">Une option</label>'."\n", $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="chk2" id="'.$this->formname.'_chk2" readonly="readonly" title="ceci est un tooltip" class="jforms-ctrl-checkbox jforms-readonly" checked="checked" value="1" type="checkbox"/>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlBoolean(\'chk2\', \'Une option\');
c.readOnly = true;
c.errInvalid=\'"Une option" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

    }

    function testOutputCheckboxes(){
        $ctrl= new jFormsControlcheckboxes('choixsimple');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Vos choix';
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products','findAll','name','id');
        $this->form->addControl($ctrl);

        $records = array(
            array('id'=>'10', 'name'=>'foo', 'price'=>'12'),
            array('id'=>'11', 'name'=>'bar', 'price'=>'54'),
            array('id'=>'23', 'name'=>'baz', 'price'=>'97'),
        );
        $this->insertRecordsIntoTable('product_test', array('id','name','price'), $records, true);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<span class="jforms-label" id="'.$this->formname.'_choixsimple_label">Vos choix</span>'."\n", $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<span class="jforms-chkbox jforms-ctl-choixsimple"><input type="checkbox" name="choixsimple[]" id="'.$this->formname.'_choixsimple_0" class="jforms-ctrl-checkboxes" value="10"/><label for="'.$this->formname.'_choixsimple_0">foo</label></span>'."\n";
        $result.='<span class="jforms-chkbox jforms-ctl-choixsimple"><input type="checkbox" name="choixsimple[]" id="'.$this->formname.'_choixsimple_1" class="jforms-ctrl-checkboxes" value="11"/><label for="'.$this->formname.'_choixsimple_1">bar</label></span>'."\n";
        $result.='<span class="jforms-chkbox jforms-ctl-choixsimple"><input type="checkbox" name="choixsimple[]" id="'.$this->formname.'_choixsimple_2" class="jforms-ctrl-checkboxes" value="23"/><label for="'.$this->formname.'_choixsimple_2">baz</label></span>'."\n\n";
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'choixsimple[]\', \'Vos choix\');
c.errInvalid=\'"Vos choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $this->form->setData('choixsimple',11);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<span class="jforms-chkbox jforms-ctl-choixsimple"><input type="checkbox" name="choixsimple[]" id="'.$this->formname.'_choixsimple_0" class="jforms-ctrl-checkboxes" value="10"/><label for="'.$this->formname.'_choixsimple_0">foo</label></span>'."\n";
        $result.='<span class="jforms-chkbox jforms-ctl-choixsimple"><input type="checkbox" name="choixsimple[]" id="'.$this->formname.'_choixsimple_1" class="jforms-ctrl-checkboxes" value="11" checked="checked"/><label for="'.$this->formname.'_choixsimple_1">bar</label></span>'."\n";
        $result.='<span class="jforms-chkbox jforms-ctl-choixsimple"><input type="checkbox" name="choixsimple[]" id="'.$this->formname.'_choixsimple_2" class="jforms-ctrl-checkboxes" value="23"/><label for="'.$this->formname.'_choixsimple_2">baz</label></span>'."\n\n";
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'choixsimple[]\', \'Vos choix\');
c.errInvalid=\'"Vos choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->datasource= new jFormsStaticDatasource();
        $ctrl->datasource->setGroupBy(true);
        $ctrl->datasource->data = array(
            ''=>array('10'=>'foo'),
            'toto'=>array('11'=>'bar',
            '23'=>'baz',)
        );
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<span class="jforms-chkbox jforms-ctl-choixsimple"><input type="checkbox" name="choixsimple[]" id="'.$this->formname.'_choixsimple_0" class="jforms-ctrl-checkboxes" value="10"/><label for="'.$this->formname.'_choixsimple_0">foo</label></span>'."\n";
        $result.="<fieldset><legend>toto</legend>\n";
        $result.='<span class="jforms-chkbox jforms-ctl-choixsimple"><input type="checkbox" name="choixsimple[]" id="'.$this->formname.'_choixsimple_1" class="jforms-ctrl-checkboxes" value="11" checked="checked"/><label for="'.$this->formname.'_choixsimple_1">bar</label></span>'."\n";
        $result.='<span class="jforms-chkbox jforms-ctl-choixsimple"><input type="checkbox" name="choixsimple[]" id="'.$this->formname.'_choixsimple_2" class="jforms-ctrl-checkboxes" value="23"/><label for="'.$this->formname.'_choixsimple_2">baz</label></span>'."\n";
        $result.="</fieldset>\n\n";
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'choixsimple[]\', \'Vos choix\');
c.errInvalid=\'"Vos choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl= new jFormsControlcheckboxes('choixmultiple');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Vos choix';
        $ctrl->datasource= new jFormsStaticDatasource();
        $ctrl->datasource->data = array(
            '10'=>'foo',
            '11'=>'bar',
            '23'=>'baz',
        );
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<span class="jforms-label" id="'.$this->formname.'_choixmultiple_label">Vos choix</span>'."\n", $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.$this->formname.'_choixmultiple_0" class="jforms-ctrl-checkboxes" value="10"/><label for="'.$this->formname.'_choixmultiple_0">foo</label></span>'."\n";
        $result.='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.$this->formname.'_choixmultiple_1" class="jforms-ctrl-checkboxes" value="11"/><label for="'.$this->formname.'_choixmultiple_1">bar</label></span>'."\n";
        $result.='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.$this->formname.'_choixmultiple_2" class="jforms-ctrl-checkboxes" value="23"/><label for="'.$this->formname.'_choixmultiple_2">baz</label></span>'."\n\n";
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'choixmultiple[]\', \'Vos choix\');
c.errInvalid=\'"Vos choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $this->form->setData('choixmultiple',11);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.$this->formname.'_choixmultiple_0" class="jforms-ctrl-checkboxes" value="10"/><label for="'.$this->formname.'_choixmultiple_0">foo</label></span>'."\n";
        $result.='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.$this->formname.'_choixmultiple_1" class="jforms-ctrl-checkboxes" value="11" checked="checked"/><label for="'.$this->formname.'_choixmultiple_1">bar</label></span>'."\n";
        $result.='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.$this->formname.'_choixmultiple_2" class="jforms-ctrl-checkboxes" value="23"/><label for="'.$this->formname.'_choixmultiple_2">baz</label></span>'."\n\n";
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'choixmultiple[]\', \'Vos choix\');
c.errInvalid=\'"Vos choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $this->form->setData('choixmultiple',array(10,23));
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.$this->formname.'_choixmultiple_0" class="jforms-ctrl-checkboxes" value="10" checked="checked"/><label for="'.$this->formname.'_choixmultiple_0">foo</label></span>'."\n";
        $result.='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.$this->formname.'_choixmultiple_1" class="jforms-ctrl-checkboxes" value="11"/><label for="'.$this->formname.'_choixmultiple_1">bar</label></span>'."\n";
        $result.='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.$this->formname.'_choixmultiple_2" class="jforms-ctrl-checkboxes" value="23" checked="checked"/><label for="'.$this->formname.'_choixmultiple_2">baz</label></span>'."\n\n";
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'choixmultiple[]\', \'Vos choix\');
c.errInvalid=\'"Vos choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->setReadOnly(true);
        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<span class="jforms-label" id="'.$this->formname.'_choixmultiple_label" title="ceci est un tooltip">Vos choix</span>'."\n", $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.$this->formname.'_choixmultiple_0" readonly="readonly" class="jforms-ctrl-checkboxes jforms-readonly" value="10" checked="checked"/><label for="'.$this->formname.'_choixmultiple_0">foo</label></span>'."\n";
        $result.='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.$this->formname.'_choixmultiple_1" readonly="readonly" class="jforms-ctrl-checkboxes jforms-readonly" value="11"/><label for="'.$this->formname.'_choixmultiple_1">bar</label></span>'."\n";
        $result.='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.$this->formname.'_choixmultiple_2" readonly="readonly" class="jforms-ctrl-checkboxes jforms-readonly" value="23" checked="checked"/><label for="'.$this->formname.'_choixmultiple_2">baz</label></span>'."\n\n";
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'choixmultiple[]\', \'Vos choix\');
c.readOnly = true;
c.errInvalid=\'"Vos choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());
    }

    function testOutputRadiobuttons(){
        $ctrl= new jFormsControlradiobuttons('rbchoixsimple');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre choix';
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products','findAll','name','id');
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<span class="jforms-label" id="'.$this->formname.'_rbchoixsimple_label">Votre choix</span>'."\n", $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.$this->formname.'_rbchoixsimple_0" class="jforms-ctrl-radiobuttons" value="10"/><label for="'.$this->formname.'_rbchoixsimple_0">foo</label></span>'."\n";
        $result.='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.$this->formname.'_rbchoixsimple_1" class="jforms-ctrl-radiobuttons" value="11"/><label for="'.$this->formname.'_rbchoixsimple_1">bar</label></span>'."\n";
        $result.='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.$this->formname.'_rbchoixsimple_2" class="jforms-ctrl-radiobuttons" value="23"/><label for="'.$this->formname.'_rbchoixsimple_2">baz</label></span>'."\n\n";
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'rbchoixsimple\', \'Votre choix\');
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $this->form->setData('rbchoixsimple',11);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.$this->formname.'_rbchoixsimple_0" class="jforms-ctrl-radiobuttons" value="10"/><label for="'.$this->formname.'_rbchoixsimple_0">foo</label></span>'."\n";
        $result.='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.$this->formname.'_rbchoixsimple_1" class="jforms-ctrl-radiobuttons" value="11" checked="checked"/><label for="'.$this->formname.'_rbchoixsimple_1">bar</label></span>'."\n";
        $result.='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.$this->formname.'_rbchoixsimple_2" class="jforms-ctrl-radiobuttons" value="23"/><label for="'.$this->formname.'_rbchoixsimple_2">baz</label></span>'."\n\n";
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'rbchoixsimple\', \'Votre choix\');
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->datasource= new jFormsStaticDatasource();
        $ctrl->datasource->data = array(
            '10'=>'foo',
            '11'=>'bar',
            '23'=>'baz',
        );

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.$this->formname.'_rbchoixsimple_0" class="jforms-ctrl-radiobuttons" value="10"/><label for="'.$this->formname.'_rbchoixsimple_0">foo</label></span>'."\n";
        $result.='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.$this->formname.'_rbchoixsimple_1" class="jforms-ctrl-radiobuttons" value="11" checked="checked"/><label for="'.$this->formname.'_rbchoixsimple_1">bar</label></span>'."\n";
        $result.='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.$this->formname.'_rbchoixsimple_2" class="jforms-ctrl-radiobuttons" value="23"/><label for="'.$this->formname.'_rbchoixsimple_2">baz</label></span>'."\n\n";
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'rbchoixsimple\', \'Votre choix\');
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $this->form->setData('rbchoixsimple',23);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.$this->formname.'_rbchoixsimple_0" class="jforms-ctrl-radiobuttons" value="10"/><label for="'.$this->formname.'_rbchoixsimple_0">foo</label></span>'."\n";
        $result.='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.$this->formname.'_rbchoixsimple_1" class="jforms-ctrl-radiobuttons" value="11"/><label for="'.$this->formname.'_rbchoixsimple_1">bar</label></span>'."\n";
        $result.='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.$this->formname.'_rbchoixsimple_2" class="jforms-ctrl-radiobuttons" value="23" checked="checked"/><label for="'.$this->formname.'_rbchoixsimple_2">baz</label></span>'."\n\n";
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'rbchoixsimple\', \'Votre choix\');
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl->setReadOnly(true);
        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<span class="jforms-label" id="'.$this->formname.'_rbchoixsimple_label" title="ceci est un tooltip">Votre choix</span>'."\n", $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.$this->formname.'_rbchoixsimple_0" readonly="readonly" class="jforms-ctrl-radiobuttons jforms-readonly" value="10"/><label for="'.$this->formname.'_rbchoixsimple_0">foo</label></span>'."\n";
        $result.='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.$this->formname.'_rbchoixsimple_1" readonly="readonly" class="jforms-ctrl-radiobuttons jforms-readonly" value="11"/><label for="'.$this->formname.'_rbchoixsimple_1">bar</label></span>'."\n";
        $result.='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.$this->formname.'_rbchoixsimple_2" readonly="readonly" class="jforms-ctrl-radiobuttons jforms-readonly" value="23" checked="checked"/><label for="'.$this->formname.'_rbchoixsimple_2">baz</label></span>'."\n\n";
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'rbchoixsimple\', \'Votre choix\');
c.readOnly = true;
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

        $this->builder->clearJs();

        $ctrl->datasource = new jFormsStaticDatasource();
        $ctrl->datasource->data = array('1'=>'Yes','0'=>'No');
        $this->form->setReadOnly('rbchoixsimple',false);
        $this->form->setData('rbchoixsimple',null);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.$this->formname.'_rbchoixsimple_0" class="jforms-ctrl-radiobuttons" value="1"/><label for="'.$this->formname.'_rbchoixsimple_0">Yes</label></span>'."\n";
        $result.='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.$this->formname.'_rbchoixsimple_1" class="jforms-ctrl-radiobuttons" value="0"/><label for="'.$this->formname.'_rbchoixsimple_1">No</label></span>'."\n\n";
        $this->assertEqualOrDiff($result, $out);

        $this->form->setData('rbchoixsimple',0);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.$this->formname.'_rbchoixsimple_0" class="jforms-ctrl-radiobuttons" value="1"/><label for="'.$this->formname.'_rbchoixsimple_0">Yes</label></span>'."\n";
        $result.='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.$this->formname.'_rbchoixsimple_1" class="jforms-ctrl-radiobuttons" value="0" checked="checked"/><label for="'.$this->formname.'_rbchoixsimple_1">No</label></span>'."\n\n";
        $this->assertEqualOrDiff($result, $out);

        $this->builder->clearJs();
    }

    function testOutputMenulist(){
        $ctrl= new jFormsControlmenulist('menulist1');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre choix';
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products','findAll','name','id');
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_menulist1" id="'.$this->formname.'_menulist1_label">Votre choix</label>'."\n", $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" class="jforms-ctrl-menulist" size="1">'."\n";
        $result.='<option value="" selected="selected"></option>'."\n";
        $result.='<option value="10">foo</option>'."\n";
        $result.='<option value="11">bar</option>'."\n";
        $result.='<option value="23">baz</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'menulist1\', \'Votre choix\');
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $this->form->setData('menulist1',11);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" class="jforms-ctrl-menulist" size="1">'."\n";
        $result.='<option value=""></option>'."\n";
        $result.='<option value="10">foo</option>'."\n";
        $result.='<option value="11" selected="selected">bar</option>'."\n";
        $result.='<option value="23">baz</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'menulist1\', \'Votre choix\');
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->datasource= new jFormsStaticDatasource();
        $ctrl->datasource->data = array(
            '10'=>'foo',
            '11'=>'bar',
            '23'=>'baz',
        );

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'menulist1\', \'Votre choix\');
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());



        $ctrl->datasource->setGroupBy(true);
        $ctrl->datasource->data = array(
            'you'=>array(
                '10'=>'foo',
                '11'=>'bar',),
            ''=>array(
                '23'=>'baz',),
        );

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" class="jforms-ctrl-menulist" size="1">'."\n";
        $result.='<option value=""></option>'."\n";
        $result.='<option value="23">baz</option>'."\n<optgroup label=\"you\">";
        $result.='<option value="10">foo</option>'."\n";
        $result.='<option value="11" selected="selected">bar</option>'."\n</optgroup>";
        $result.='</select>'."\n";

        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'menulist1\', \'Votre choix\');
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl->datasource->setGroupBy(false);
        $ctrl->datasource->data = array(
            '10'=>'foo',
            '11'=>'bar',
            '23'=>'baz',
        );


        $ctrl->setReadOnly(true);
        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_menulist1" id="'.$this->formname.'_menulist1_label" title="ceci est un tooltip">Votre choix</label>'."\n", $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" title="ceci est un tooltip" class="jforms-ctrl-menulist jforms-readonly" disabled="disabled" size="1">'."\n";
        $result.='<option value=""></option>'."\n";
        $result.='<option value="10">foo</option>'."\n";
        $result.='<option value="11" selected="selected">bar</option>'."\n";
        $result.='<option value="23">baz</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'menulist1\', \'Votre choix\');
c.readOnly = true;
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->required = true;
        $this->form->setData('menulist1',"23");
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" title="ceci est un tooltip" class="jforms-ctrl-menulist jforms-readonly" disabled="disabled" size="1">'."\n";
        $result.='<option value=""></option>'."\n";
        $result.='<option value="10">foo</option>'."\n";
        $result.='<option value="11">bar</option>'."\n";
        $result.='<option value="23" selected="selected">baz</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'menulist1\', \'Votre choix\');
c.readOnly = true;
c.required = true;
c.errRequired=\'"Votre choix" field is required\';
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->required = false;
        $this->form->setData('menulist1',"");
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" title="ceci est un tooltip" class="jforms-ctrl-menulist jforms-readonly" disabled="disabled" size="1">'."\n";
        $result.='<option value="" selected="selected"></option>'."\n";
        $result.='<option value="10">foo</option>'."\n";
        $result.='<option value="11">bar</option>'."\n";
        $result.='<option value="23">baz</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'menulist1\', \'Votre choix\');
c.readOnly = true;
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

        $records = array(
            array('id'=>'10', 'name'=>'foo', 'price'=>'15'),
            array('id'=>'11', 'name'=>'bar', 'price'=>'54'),
            array('id'=>'23', 'name'=>'baz', 'price'=>'97'),
            array('id'=>'42', 'name'=>'bidule', 'price'=>'54'),
            array('id'=>'12', 'name'=>'truc', 'price'=>'97'),
            array('id'=>'27', 'name'=>'zoulou', 'price'=>'0'),
        );
        $this->insertRecordsIntoTable('product_test', array('id','name','price'), $records, true);

        $ctrl->setReadOnly(false);
        $ctrl->hint='';
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products','findOrderPrice','name','id');
        $ctrl->datasource->setGroupBy('price');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" class="jforms-ctrl-menulist" size="1">'."\n";
        $result.='<option value="" selected="selected"></option>'."\n";
        $result.='<optgroup label="0"><option value="27">zoulou</option>'."\n";
        $result.='</optgroup><optgroup label="15"><option value="10">foo</option>'."\n";
        $result.='</optgroup><optgroup label="54"><option value="11">bar</option>'."\n";
        $result.='<option value="42">bidule</option>'."\n";
        $result.='</optgroup><optgroup label="97"><option value="23">baz</option>'."\n";
        $result.='<option value="12">truc</option>'."\n";
        $result.='</optgroup></select>'."\n";
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'menulist1\', \'Votre choix\');
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $records = array(
            array('id'=>'10', 'name'=>'foo', 'price'=>'12'),
            array('id'=>'11', 'name'=>'bar', 'price'=>'54'),
            array('id'=>'23', 'name'=>'baz', 'price'=>'97'),
        );
        $this->insertRecordsIntoTable('product_test', array('id','name','price'), $records, true);

        $ctrl->setReadOnly(false);
        $ctrl->hint='';
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products','findByMaxId','name','id','','15');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" class="jforms-ctrl-menulist" size="1">'."\n";
        $result.='<option value="" selected="selected"></option>'."\n";
        $result.='<option value="10">foo</option>'."\n";
        $result.='<option value="11">bar</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'menulist1\', \'Votre choix\');
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products','findByMaxId','name','id','','11');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" class="jforms-ctrl-menulist" size="1">'."\n";
        $result.='<option value="" selected="selected"></option>'."\n";
        $result.='<option value="10">foo</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'menulist1\', \'Votre choix\');
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $this->form->setData('menulist1',"10");
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" class="jforms-ctrl-menulist" size="1">'."\n";
        $result.='<option value=""></option>'."\n";
        $result.='<option value="10" selected="selected">foo</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'menulist1\', \'Votre choix\');
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $this->form->setData('menulist1',"");

        $this->form->addControl(new jFormsControlHidden('hidden1'));
        $this->form->setData('hidden1',"25");
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products','findByMaxId','name','id','',null, 'hidden1');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" class="jforms-ctrl-menulist" size="1">'."\n";
        $result.='<option value="" selected="selected"></option>'."\n";
        $result.='<option value="10">foo</option>'."\n";
        $result.='<option value="11">bar</option>'."\n";
        $result.='<option value="23">baz</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'menulist1\', \'Votre choix\');
c.dependencies = [\'hidden1\'];
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

        $this->assertEqualOrDiff('jFormsJQ.tForm.declareDynamicFill(\'menulist1\');
', $this->builder->getLastJsContent());


        $this->form->setData('hidden1',"15");
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" class="jforms-ctrl-menulist" size="1">'."\n";
        $result.='<option value="" selected="selected"></option>'."\n";
        $result.='<option value="10">foo</option>'."\n";
        $result.='<option value="11">bar</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEqualOrDiff($result, $out);



        $this->form->setData('menulist1',"10");
        $this->form->setData('hidden1',"11");
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" class="jforms-ctrl-menulist" size="1">'."\n";
        $result.='<option value=""></option>'."\n";
        $result.='<option value="10" selected="selected">foo</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEqualOrDiff($result, $out);

        $this->form->setData('menulist1',"");
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products','findByMaxId','name,price','id','','25',null);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" class="jforms-ctrl-menulist" size="1">'."\n";
        $result.='<option value="" selected="selected"></option>'."\n";
        $result.='<option value="10">foo12</option>'."\n";
        $result.='<option value="11">bar54</option>'."\n";
        $result.='<option value="23">baz97</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEqualOrDiff($result, $out);

        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products','findByMaxId','name,price','id','','25',null,' - ');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" class="jforms-ctrl-menulist" size="1">'."\n";
        $result.='<option value="" selected="selected"></option>'."\n";
        $result.='<option value="10">foo - 12</option>'."\n";
        $result.='<option value="11">bar - 54</option>'."\n";
        $result.='<option value="23">baz - 97</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEqualOrDiff($result, $out);

        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products','findBetweenId','name,price','id','','9,25',null,' - ');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" class="jforms-ctrl-menulist" size="1">'."\n";
        $result.='<option value="" selected="selected"></option>'."\n";
        $result.='<option value="10">foo - 12</option>'."\n";
        $result.='<option value="11">bar - 54</option>'."\n";
        $result.='<option value="23">baz - 97</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEqualOrDiff($result, $out);

        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products','findBetweenId','name,price','id','','10,25',null,' - ');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" class="jforms-ctrl-menulist" size="1">'."\n";
        $result.='<option value="" selected="selected"></option>'."\n";
        $result.='<option value="11">bar - 54</option>'."\n";
        $result.='<option value="23">baz - 97</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEqualOrDiff($result, $out);

        $this->form->addControl(new jFormsControlHidden('hidden2'));
        $this->form->setData('hidden1',"9");
        $this->form->setData('hidden2',"25");
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products','findBetweenId','name,price','id','',null,'hidden1,hidden2',' - ');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" class="jforms-ctrl-menulist" size="1">'."\n";
        $result.='<option value="" selected="selected"></option>'."\n";
        $result.='<option value="10">foo - 12</option>'."\n";
        $result.='<option value="11">bar - 54</option>'."\n";
        $result.='<option value="23">baz - 97</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEqualOrDiff($result, $out);

        $this->form->setData('hidden1',"10");
        $this->form->setData('hidden2',"25");
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products','findBetweenId','name,price','id','',null,'hidden1,hidden2',' - ');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="menulist1" id="'.$this->formname.'_menulist1" class="jforms-ctrl-menulist" size="1">'."\n";
        $result.='<option value="" selected="selected"></option>'."\n";
        $result.='<option value="11">bar - 54</option>'."\n";
        $result.='<option value="23">baz - 97</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEqualOrDiff($result, $out);

        $this->form->removeControl('hidden2');
        $this->form->setData('hidden1',"11");
        $this->builder->clearJs();
    }

    function testOutputListbox(){
        $ctrl= new jFormsControllistbox('listbox1');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre choix';
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products','findAll','name','id');
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_listbox1" id="'.$this->formname.'_listbox1_label">Votre choix</label>'."\n", $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="listbox1" id="'.$this->formname.'_listbox1" class="jforms-ctrl-listbox" size="4">'."\n";
        $result.='<option value="10">foo</option>'."\n";
        $result.='<option value="11">bar</option>'."\n";
        $result.='<option value="23">baz</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'listbox1\', \'Votre choix\');
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

        $this->form->setData('listbox1',"23");
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="listbox1" id="'.$this->formname.'_listbox1" class="jforms-ctrl-listbox" size="4">'."\n";
        $result.='<option value="10">foo</option>'."\n";
        $result.='<option value="11">bar</option>'."\n";
        $result.='<option value="23" selected="selected">baz</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'listbox1\', \'Votre choix\');
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->emptyItemLabel = 'no selection';
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="listbox1" id="'.$this->formname.'_listbox1" class="jforms-ctrl-listbox" size="4">'."\n";
        $result.='<option value="">no selection</option>'."\n";
        $result.='<option value="10">foo</option>'."\n";
        $result.='<option value="11">bar</option>'."\n";
        $result.='<option value="23" selected="selected">baz</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEqualOrDiff($result, $out);
        $ctrl->emptyItemLabel = null;
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'listbox1\', \'Votre choix\');
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl->datasource= new jFormsStaticDatasource();
        $ctrl->datasource->data = array(
            '10'=>'foo',
            '11'=>'bar',
            '23'=>'baz',
        );

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="listbox1" id="'.$this->formname.'_listbox1" class="jforms-ctrl-listbox" size="4">'."\n";
        $result.='<option value="10">foo</option>'."\n";
        $result.='<option value="11">bar</option>'."\n";
        $result.='<option value="23" selected="selected">baz</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'listbox1\', \'Votre choix\');
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->setReadOnly(true);
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_listbox1" id="'.$this->formname.'_listbox1_label">Votre choix</label>'."\n", $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="listbox1" id="'.$this->formname.'_listbox1" class="jforms-ctrl-listbox jforms-readonly" disabled="disabled" size="4">'."\n";
        $result.='<option value="10">foo</option>'."\n";
        $result.='<option value="11">bar</option>'."\n";
        $result.='<option value="23" selected="selected">baz</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'listbox1\', \'Votre choix\');
c.readOnly = true;
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());



        $ctrl= new jFormsControllistbox('lbchoixmultiple');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre choix';
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products','findAll','name','id');
        $ctrl->multiple=true;
        $ctrl->hint='ceci est un tooltip';
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_lbchoixmultiple" id="'.$this->formname.'_lbchoixmultiple_label" title="ceci est un tooltip">Votre choix</label>'."\n", $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="lbchoixmultiple[]" id="'.$this->formname.'_lbchoixmultiple" title="ceci est un tooltip" class="jforms-ctrl-listbox" size="4" multiple="multiple">'."\n";
        $result.='<option value="10">foo</option>'."\n";
        $result.='<option value="11">bar</option>'."\n";
        $result.='<option value="23">baz</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'lbchoixmultiple[]\', \'Votre choix\');
c.multiple = true;
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $this->form->setData('lbchoixmultiple',array(10,23));
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="lbchoixmultiple[]" id="'.$this->formname.'_lbchoixmultiple" title="ceci est un tooltip" class="jforms-ctrl-listbox" size="4" multiple="multiple">'."\n";
        $result.='<option value="10" selected="selected">foo</option>'."\n";
        $result.='<option value="11">bar</option>'."\n";
        $result.='<option value="23" selected="selected">baz</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'lbchoixmultiple[]\', \'Votre choix\');
c.multiple = true;
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl= new jFormsControllistbox('listbox2');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre choix';
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products','findAll','name','id');
        $ctrl->defaultValue=array ('10');
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_listbox2" id="'.$this->formname.'_listbox2_label">Votre choix</label>'."\n", $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="listbox2" id="'.$this->formname.'_listbox2" class="jforms-ctrl-listbox" size="4">'."\n";
        $result.='<option value="10" selected="selected">foo</option>'."\n";
        $result.='<option value="11">bar</option>'."\n";
        $result.='<option value="23">baz</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'listbox2\', \'Votre choix\');
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl= new jFormsControllistbox('lbchoixmultiple2');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre choix';
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products','findAll','name','id');
        $ctrl->multiple=true;
        $ctrl->size=8;
        $ctrl->defaultValue=array ('11','23');
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_lbchoixmultiple2" id="'.$this->formname.'_lbchoixmultiple2_label">Votre choix</label>'."\n", $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="lbchoixmultiple2[]" id="'.$this->formname.'_lbchoixmultiple2" class="jforms-ctrl-listbox" size="8" multiple="multiple">'."\n";
        $result.='<option value="10">foo</option>'."\n";
        $result.='<option value="11" selected="selected">bar</option>'."\n";
        $result.='<option value="23" selected="selected">baz</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'lbchoixmultiple2[]\', \'Votre choix\');
c.multiple = true;
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

    }

    function testOutputListboxClassDatasource(){
        $ctrl= new jFormsControllistbox('listboxclass');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre choix';
        jClasses::inc('mydatasource');
        $ctrl->datasource = new mydatasource(0);
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="listboxclass" id="'.$this->formname.'_listboxclass" class="jforms-ctrl-listbox" size="4">'."\n";
        $result.='<option value="aaa">label for aaa</option>'."\n";
        $result.='<option value="bbb">label for bbb</option>'."\n";
        $result.='<option value="ccc">label for ccc</option>'."\n";
        $result.='<option value="ddd">label for ddd</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEqualOrDiff($result, $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'listboxclass\', \'Votre choix\');
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

    }


    function testOutputTextarea(){
        $ctrl= new jFormsControltextarea('textarea1');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre nom';
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_textarea1" id="'.$this->formname.'_textarea1_label">Votre nom</label>'."\n", $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<textarea name="textarea1" id="'.$this->formname.'_textarea1" class="jforms-ctrl-textarea" rows="5" cols="40"></textarea>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'textarea1\', \'Votre nom\');
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $this->form->setData('textarea1','laurent');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<textarea name="textarea1" id="'.$this->formname.'_textarea1" class="jforms-ctrl-textarea" rows="5" cols="40">laurent</textarea>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'textarea1\', \'Votre nom\');
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->setReadOnly(true);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<textarea name="textarea1" id="'.$this->formname.'_textarea1" readonly="readonly" class="jforms-ctrl-textarea jforms-readonly" rows="5" cols="40">laurent</textarea>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'textarea1\', \'Votre nom\');
c.readOnly = true;
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_textarea1" id="'.$this->formname.'_textarea1_label" title="ceci est un tooltip">Votre nom</label>'."\n", $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<textarea name="textarea1" id="'.$this->formname.'_textarea1" readonly="readonly" title="ceci est un tooltip" class="jforms-ctrl-textarea jforms-readonly" rows="5" cols="40">laurent</textarea>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'textarea1\', \'Votre nom\');
c.readOnly = true;
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->rows=20;
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<textarea name="textarea1" id="'.$this->formname.'_textarea1" readonly="readonly" title="ceci est un tooltip" class="jforms-ctrl-textarea jforms-readonly" rows="20" cols="40">laurent</textarea>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'textarea1\', \'Votre nom\');
c.readOnly = true;
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->cols=60;
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<textarea name="textarea1" id="'.$this->formname.'_textarea1" readonly="readonly" title="ceci est un tooltip" class="jforms-ctrl-textarea jforms-readonly" rows="20" cols="60">laurent</textarea>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'textarea1\', \'Votre nom\');
c.readOnly = true;
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


    }
    function testOutputSecret(){
        $ctrl= new jFormsControlSecret('passwd');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='mot de passe';
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_passwd" id="'.$this->formname.'_passwd_label">mot de passe</label>'."\n", $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="passwd" id="'.$this->formname.'_passwd" class="jforms-ctrl-secret" type="password" value=""/>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlSecret(\'passwd\', \'mot de passe\');
c.errInvalid=\'"mot de passe" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

        $this->form->setData('passwd','laurent');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="passwd" id="'.$this->formname.'_passwd" class="jforms-ctrl-secret" type="password" value="laurent"/>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlSecret(\'passwd\', \'mot de passe\');
c.errInvalid=\'"mot de passe" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl->setReadOnly(true);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="passwd" id="'.$this->formname.'_passwd" readonly="readonly" class="jforms-ctrl-secret jforms-readonly" type="password" value="laurent"/>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlSecret(\'passwd\', \'mot de passe\');
c.readOnly = true;
c.errInvalid=\'"mot de passe" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_passwd" id="'.$this->formname.'_passwd_label" title="ceci est un tooltip">mot de passe</label>'."\n", $out);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="passwd" id="'.$this->formname.'_passwd" readonly="readonly" title="ceci est un tooltip" class="jforms-ctrl-secret jforms-readonly" type="password" value="laurent"/>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlSecret(\'passwd\', \'mot de passe\');
c.readOnly = true;
c.errInvalid=\'"mot de passe" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl->datatype->addFacet('minLength',5);
        $ctrl->datatype->addFacet('maxLength',10);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="passwd" id="'.$this->formname.'_passwd" readonly="readonly" title="ceci est un tooltip" class="jforms-ctrl-secret jforms-readonly" maxlength="10" type="password" value="laurent"/>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlSecret(\'passwd\', \'mot de passe\');
c.maxLength = \'10\';
c.minLength = \'5\';
c.readOnly = true;
c.errInvalid=\'"mot de passe" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


    }
    function testOutputSecretConfirm(){
        $ctrl= new jFormsControlSecretConfirm('passwd_confirm');
        $ctrl->label='confirmation mot de passe';
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_passwd_confirm" id="'.$this->formname.'_passwd_confirm_label">confirmation mot de passe</label>'."\n", $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="passwd_confirm" id="'.$this->formname.'_passwd_confirm" class="jforms-ctrl-secretconfirm" type="password" value=""/>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlConfirm(\'passwd_confirm\', \'confirmation mot de passe\');
c.errInvalid=\'"confirmation mot de passe" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl->required = true;
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="passwd_confirm" id="'.$this->formname.'_passwd_confirm" class="jforms-ctrl-secretconfirm jforms-required" type="password" value=""/>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlConfirm(\'passwd_confirm\', \'confirmation mot de passe\');
c.required = true;
c.errRequired=\'"confirmation mot de passe" field is required\';
c.errInvalid=\'"confirmation mot de passe" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());
        $ctrl->required = false;


        $ctrl->setReadOnly(true);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="passwd_confirm" id="'.$this->formname.'_passwd_confirm" readonly="readonly" class="jforms-ctrl-secretconfirm jforms-readonly" type="password" value=""/>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlConfirm(\'passwd_confirm\', \'confirmation mot de passe\');
c.readOnly = true;
c.errInvalid=\'"confirmation mot de passe" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());


        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_passwd_confirm" id="'.$this->formname.'_passwd_confirm_label" title="ceci est un tooltip">confirmation mot de passe</label>'."\n", $out);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="passwd_confirm" id="'.$this->formname.'_passwd_confirm" readonly="readonly" title="ceci est un tooltip" class="jforms-ctrl-secretconfirm jforms-readonly" type="password" value=""/>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlConfirm(\'passwd_confirm\', \'confirmation mot de passe\');
c.readOnly = true;
c.errInvalid=\'"confirmation mot de passe" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

    }

    function testOutputOutput(){
        $ctrl= new jFormsControlOutput('output1');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre nom';
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<span class="jforms-label" id="'.$this->formname.'_output1_label">Votre nom</span>'."\n", $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="output1" id="'.$this->formname.'_output1" type="hidden" value=""/><span class="jforms-value"></span>'."\n", $out);
        $this->assertEqualOrDiff('', $this->builder->getJsContent());


        $this->form->setData('output1','laurent');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="output1" id="'.$this->formname.'_output1" type="hidden" value="laurent"/><span class="jforms-value">laurent</span>'."\n", $out);
        $this->assertEqualOrDiff('', $this->builder->getJsContent());


        $ctrl->setReadOnly(true);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="output1" id="'.$this->formname.'_output1" type="hidden" value="laurent"/><span class="jforms-value">laurent</span>'."\n", $out);
        $this->assertEqualOrDiff('', $this->builder->getJsContent());


        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<span class="jforms-label" id="'.$this->formname.'_output1_label" title="ceci est un tooltip">Votre nom</span>'."\n", $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="output1" id="'.$this->formname.'_output1" type="hidden" value="laurent"/><span class="jforms-value" title="ceci est un tooltip">laurent</span>'."\n", $out);
        $this->assertEqualOrDiff('', $this->builder->getJsContent());

    }

    function testOutputUpload(){
        $ctrl= new jFormsControlUpload('upload1');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre nom';
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_upload1" id="'.$this->formname.'_upload1_label">Votre nom</label>'."\n", $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="upload1" id="'.$this->formname.'_upload1" class="jforms-ctrl-upload" type="file" value=""/>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'upload1\', \'Votre nom\');
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl->setReadOnly(true);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="upload1" id="'.$this->formname.'_upload1" readonly="readonly" class="jforms-ctrl-upload jforms-readonly" type="file" value=""/>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'upload1\', \'Votre nom\');
c.readOnly = true;
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_upload1" id="'.$this->formname.'_upload1_label" title="ceci est un tooltip">Votre nom</label>'."\n", $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="upload1" id="'.$this->formname.'_upload1" readonly="readonly" title="ceci est un tooltip" class="jforms-ctrl-upload jforms-readonly" type="file" value=""/>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'upload1\', \'Votre nom\');
c.readOnly = true;
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

        ob_start();
        $this->builder->outputHeader(array('method'=>'post'));
        $out = ob_get_clean();
        $result ='<form action="'.jApp::config()->urlengine['basePath'].'index.php/jelix_tests/urlsig/url1" method="post" id="'.$this->formname.'" enctype="multipart/form-data"><script type="text/javascript">
//<![CDATA[
jFormsJQ.selectFillUrl=\''.jApp::config()->urlengine['basePath'].'index.php/jelix/jforms/getListData\';
jFormsJQ.config = {locale:\''.jApp::config()->locale.'\',basePath:\''.jApp::config()->urlengine['basePath'].'\',jqueryPath:\''.jApp::config()->urlengine['jqueryPath'].'\',jelixWWWPath:\''.jApp::config()->urlengine['jelixWWWPath'].'\'};
jFormsJQ.tForm = new jFormsJQForm(\'jforms_formtest1\',\'formtest\',\'0\');
jFormsJQ.tForm.setErrorDecorator(new jFormsJQErrorDecoratorHtml());
jFormsJQ.declareForm(jFormsJQ.tForm);
//]]>
</script><div class="jforms-hiddens"><input type="hidden" name="foo" value="b&gt;ar"/>
<input type="hidden" name="hidden1" id="'.$this->formname.'_hidden1" value="11"/>
</div>';
        $this->assertEqualOrDiff($result, $out);

        $this->form->removeControl('upload1');

    }
    function testOutputSubmit(){
        $ctrl= new jFormsControlSubmit('submit1');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Ok';
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="submit1" id="'.$this->formname.'_submit1" class="jforms-submit" type="submit" value="Ok"/>'."\n", $out);
        $this->assertEqualOrDiff('', $this->builder->getJsContent());


        $ctrl->setReadOnly(true);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="submit1" id="'.$this->formname.'_submit1" class="jforms-submit" type="submit" value="Ok"/>'."\n", $out);
        $this->assertEqualOrDiff('', $this->builder->getJsContent());


        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input name="submit1" id="'.$this->formname.'_submit1" title="ceci est un tooltip" class="jforms-submit" type="submit" value="Ok"/>'."\n", $out);
        $this->assertEqualOrDiff('', $this->builder->getJsContent());


        $ctrl->standalone=false;
        $ctrl->datasource= new jFormsStaticDatasource();
        $ctrl->datasource->data = array('svg'=>'Sauvegarde','prev'=>'Preview');

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $output = ' <input name="submit1" id="'.$this->formname.'_submit1_svg" title="ceci est un tooltip" class="jforms-submit" type="submit" value="Sauvegarde"/>';
        $output .= ' <input name="submit1" id="'.$this->formname.'_submit1_prev" title="ceci est un tooltip" class="jforms-submit" type="submit" value="Preview"/>'."\n";
        $this->assertEqualOrDiff($output, $out);
        $this->assertEqualOrDiff('', $this->builder->getJsContent());

    }
    function testOutputReset(){
        $ctrl= new jFormsControlReset('reset1');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Effacer';
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<button name="reset1" id="'.$this->formname.'_reset1" class="jforms-reset" type="reset">Effacer</button>'."\n", $out);
        $this->assertEqualOrDiff('', $this->builder->getJsContent());

        $ctrl->setReadOnly(true);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<button name="reset1" id="'.$this->formname.'_reset1" class="jforms-reset" type="reset">Effacer</button>'."\n", $out);
        $this->assertEqualOrDiff('', $this->builder->getJsContent());


        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<button name="reset1" id="'.$this->formname.'_reset1" title="ceci est un tooltip" class="jforms-reset" type="reset">Effacer</button>'."\n", $out);
        $this->assertEqualOrDiff('', $this->builder->getJsContent());
    }
    function testOutputHidden(){
        $ctrl= new jFormsControlHidden('hidden2');
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('', $out);
        $this->assertEqualOrDiff('', $this->builder->getJsContent());


        ob_start();
        $this->builder->outputHeader(array('method'=>'post'));
        $out = ob_get_clean();
        $result ='<form action="'.jApp::config()->urlengine['basePath'].'index.php/jelix_tests/urlsig/url1" method="post" id="'.$this->formname.'"><script type="text/javascript">
//<![CDATA[
jFormsJQ.selectFillUrl=\''.jApp::config()->urlengine['basePath'].'index.php/jelix/jforms/getListData\';
jFormsJQ.config = {locale:\''.jApp::config()->locale.'\',basePath:\''.jApp::config()->urlengine['basePath'].'\',jqueryPath:\''.jApp::config()->urlengine['jqueryPath'].'\',jelixWWWPath:\''.jApp::config()->urlengine['jelixWWWPath'].'\'};
jFormsJQ.tForm = new jFormsJQForm(\'jforms_formtest1\',\'formtest\',\'0\');
jFormsJQ.tForm.setErrorDecorator(new jFormsJQErrorDecoratorHtml());
jFormsJQ.declareForm(jFormsJQ.tForm);
//]]>
</script><div class="jforms-hiddens"><input type="hidden" name="foo" value="b&gt;ar"/>
<input type="hidden" name="hidden1" id="'.$this->formname.'_hidden1" value="11"/>
<input type="hidden" name="hidden2" id="'.$this->formname.'_hidden2" value=""/>
</div>';
        $this->assertEqualOrDiff($result, $out);

        $ctrl->defaultValue='toto';
        $this->form->removeControl($ctrl->ref);
        $this->form->addControl($ctrl);
        ob_start();
        $this->builder->outputHeader(array('method'=>'post'));
        $out = ob_get_clean();
        $result ='<form action="'.jApp::config()->urlengine['basePath'].'index.php/jelix_tests/urlsig/url1" method="post" id="'.$this->formname.'"><script type="text/javascript">
//<![CDATA[
jFormsJQ.selectFillUrl=\''.jApp::config()->urlengine['basePath'].'index.php/jelix/jforms/getListData\';
jFormsJQ.config = {locale:\''.jApp::config()->locale.'\',basePath:\''.jApp::config()->urlengine['basePath'].'\',jqueryPath:\''.jApp::config()->urlengine['jqueryPath'].'\',jelixWWWPath:\''.jApp::config()->urlengine['jelixWWWPath'].'\'};
jFormsJQ.tForm = new jFormsJQForm(\'jforms_formtest1\',\'formtest\',\'0\');
jFormsJQ.tForm.setErrorDecorator(new jFormsJQErrorDecoratorHtml());
jFormsJQ.declareForm(jFormsJQ.tForm);
//]]>
</script><div class="jforms-hiddens"><input type="hidden" name="foo" value="b&gt;ar"/>
<input type="hidden" name="hidden1" id="'.$this->formname.'_hidden1" value="11"/>
<input type="hidden" name="hidden2" id="'.$this->formname.'_hidden2" value="toto"/>
</div>';
        $this->assertEqualOrDiff($result, $out);
    }

    function testOutputCaptcha(){
        $ctrl= new jFormsControlcaptcha('cap');
        $ctrl->label='captcha for security';
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label jforms-required" for="'.$this->formname.'_cap" id="'.$this->formname.'_cap_label">captcha for security<span class="jforms-required-star">*</span></label>'."\n", $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<span class="jforms-captcha-question">'.htmlspecialchars($ctrl->question).'</span> <input name="cap" id="'.$this->formname.'_cap" class="jforms-ctrl-captcha jforms-required" type="text" value=""/>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'cap\', \'captcha for security\');
c.required = true;
c.errRequired=\'"captcha for security" field is required\';
c.errInvalid=\'"captcha for security" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

        $this->form->setData('cap','toto');
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<span class="jforms-captcha-question">'.htmlspecialchars($ctrl->question).'</span> <input name="cap" id="'.$this->formname.'_cap" class="jforms-ctrl-captcha jforms-required" type="text" value=""/>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'cap\', \'captcha for security\');
c.required = true;
c.errRequired=\'"captcha for security" field is required\';
c.errInvalid=\'"captcha for security" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl->setReadOnly(true);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<span class="jforms-captcha-question">'.htmlspecialchars($ctrl->question).'</span> <input name="cap" id="'.$this->formname.'_cap" class="jforms-ctrl-captcha" type="text" value=""/>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'cap\', \'captcha for security\');
c.readOnly = true;
c.required = true;
c.errRequired=\'"captcha for security" field is required\';
c.errInvalid=\'"captcha for security" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl->setReadOnly(false);
        $ctrl->help='some help';
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<span class="jforms-captcha-question">'.htmlspecialchars($ctrl->question).'</span> <input name="cap" id="'.$this->formname.'_cap" class="jforms-ctrl-captcha jforms-required" type="text" value=""/>'."\n".'<span class="jforms-help" id="jforms_formtest1_cap-help">&nbsp;<span>some help</span></span>', $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'cap\', \'captcha for security\');
c.required = true;
c.errRequired=\'"captcha for security" field is required\';
c.errInvalid=\'"captcha for security" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label jforms-required" for="'.$this->formname.'_cap" id="'.$this->formname.'_cap_label" title="ceci est un tooltip">captcha for security<span class="jforms-required-star">*</span></label>'."\n", $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<span class="jforms-captcha-question">'.htmlspecialchars($ctrl->question).'</span> <input name="cap" id="'.$this->formname.'_cap" title="ceci est un tooltip" class="jforms-ctrl-captcha jforms-required" type="text" value=""/>'."\n".'<span class="jforms-help" id="jforms_formtest1_cap-help">&nbsp;<span>some help</span></span>', $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlString(\'cap\', \'captcha for security\');
c.required = true;
c.errRequired=\'"captcha for security" field is required\';
c.errInvalid=\'"captcha for security" field is invalid\';
jFormsJQ.tForm.addControl(c);
', $this->builder->getJsContent());

    }

    function testOutputHtmleditor(){
        $ctrl= new jFormsControlhtmleditor('contenu');
        $ctrl->label='Texte';
        $this->form->addControl($ctrl);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_contenu" id="'.$this->formname.'_contenu_label">Texte</label>'."\n", $out);

        $this->form->setData('contenu','<p>Ceci est un contenu</p>');

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<textarea name="contenu" id="'.$this->formname.'_contenu" class="jforms-ctrl-htmleditor" rows="5" cols="40">&lt;p&gt;Ceci est un contenu&lt;/p&gt;</textarea>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlHtml(\'contenu\', \'Texte\');
c.errInvalid=\'"Texte" field is invalid\';
jFormsJQ.tForm.addControl(c);
jelix_wymeditor_default("jforms_formtest1_contenu","jforms_formtest1","default",jFormsJQ.config);
', $this->builder->getJsContent());

        $ctrl->setReadOnly(true);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<textarea name="contenu" id="'.$this->formname.'_contenu" readonly="readonly" class="jforms-ctrl-htmleditor jforms-readonly" rows="5" cols="40">&lt;p&gt;Ceci est un contenu&lt;/p&gt;</textarea>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlHtml(\'contenu\', \'Texte\');
c.readOnly = true;
c.errInvalid=\'"Texte" field is invalid\';
jFormsJQ.tForm.addControl(c);
jelix_wymeditor_default("jforms_formtest1_contenu","jforms_formtest1","default",jFormsJQ.config);
', $this->builder->getJsContent());

        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_contenu" id="'.$this->formname.'_contenu_label" title="ceci est un tooltip">Texte</label>'."\n", $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<textarea name="contenu" id="'.$this->formname.'_contenu" readonly="readonly" title="ceci est un tooltip" class="jforms-ctrl-htmleditor jforms-readonly" rows="5" cols="40">&lt;p&gt;Ceci est un contenu&lt;/p&gt;</textarea>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlHtml(\'contenu\', \'Texte\');
c.readOnly = true;
c.errInvalid=\'"Texte" field is invalid\';
jFormsJQ.tForm.addControl(c);
jelix_wymeditor_default("jforms_formtest1_contenu","jforms_formtest1","default",jFormsJQ.config);
', $this->builder->getJsContent());


        $ctrl->rows=20;
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<textarea name="contenu" id="'.$this->formname.'_contenu" readonly="readonly" title="ceci est un tooltip" class="jforms-ctrl-htmleditor jforms-readonly" rows="20" cols="40">&lt;p&gt;Ceci est un contenu&lt;/p&gt;</textarea>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlHtml(\'contenu\', \'Texte\');
c.readOnly = true;
c.errInvalid=\'"Texte" field is invalid\';
jFormsJQ.tForm.addControl(c);
jelix_wymeditor_default("jforms_formtest1_contenu","jforms_formtest1","default",jFormsJQ.config);
', $this->builder->getJsContent());


        $ctrl->cols=60;
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<textarea name="contenu" id="'.$this->formname.'_contenu" readonly="readonly" title="ceci est un tooltip" class="jforms-ctrl-htmleditor jforms-readonly" rows="20" cols="60">&lt;p&gt;Ceci est un contenu&lt;/p&gt;</textarea>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlHtml(\'contenu\', \'Texte\');
c.readOnly = true;
c.errInvalid=\'"Texte" field is invalid\';
jFormsJQ.tForm.addControl(c);
jelix_wymeditor_default("jforms_formtest1_contenu","jforms_formtest1","default",jFormsJQ.config);
', $this->builder->getJsContent());

        $ctrl->required=true;
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<textarea name="contenu" id="'.$this->formname.'_contenu" readonly="readonly" title="ceci est un tooltip" class="jforms-ctrl-htmleditor jforms-readonly" rows="20" cols="60">&lt;p&gt;Ceci est un contenu&lt;/p&gt;</textarea>'."\n", $out);
        $this->assertEqualOrDiff('c = new jFormsJQControlHtml(\'contenu\', \'Texte\');
c.readOnly = true;
c.required = true;
c.errRequired=\'"Texte" field is required\';
c.errInvalid=\'"Texte" field is invalid\';
jFormsJQ.tForm.addControl(c);
jelix_wymeditor_default("jforms_formtest1_contenu","jforms_formtest1","default",jFormsJQ.config);
', $this->builder->getJsContent());
    }
}

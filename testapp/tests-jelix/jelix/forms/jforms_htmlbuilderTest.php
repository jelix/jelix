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
require_once(JELIX_LIB_PATH.'forms/legacy/jFormsBuilderBase.class.php');
include_once(JELIX_LIB_PATH.'forms/legacy/jFormsBuilderHtml.class.php');
require_once(JELIX_LIB_PATH.'forms/jFormsDataContainer.class.php');
require_once(JELIX_LIB_PATH.'plugins/jforms/html/html.jformsbuilder.php');

class testHMLForm extends jFormsBase
{
}

class testJFormsHtmlBuilder extends htmlJformsBuilder
{
    public function getJsContent()
    {
        $js= $this->jsContent;
        $this->jsContent = '';
        return $js;
    }
    public function clearJs()
    {
        $this->jsContent = '';
    }
    public function getLastJsContent()
    {
        $js= $this->lastJsContent;
        $this->lastJsContent = '';
        return $js;
    }
}


class jforms_HTMLBuilderTest extends jUnitTestCaseDb
{
    protected static $form;
    protected static $container;
    protected static $builder;
    protected static $formname = 'jforms_formtest1';

    public function setUp()
    {
        self::initClassicRequest(TESTAPP_URL.'index.php');
        jApp::pushCurrentModule('jelix_tests');
        if (!self::$builder) {
            self::$container = new jFormsDataContainer('formtest', '0');
            self::$form = new testHMLForm('formtest', self::$container, true);
            self::$form->securityLevel = 0;
            self::$builder = new testJFormsHtmlBuilder(self::$form);
        }
    }

    public function tearDown()
    {
        jApp::popCurrentModule();
    }

    public static function tearDownAfterClass()
    {
        self::$container = null;
        self::$form = null;
        self::$form = null;
        self::$builder = null;
    }

    public function testOutputHeader()
    {
        self::$builder->setAction('jelix_tests~urlsig:url1', array());
        ob_start();
        self::$builder->setOptions(array('method'=>'post', 'attributes'=>array('class'=>'foo')));
        self::$builder->outputHeader();
        $out = ob_get_clean();
        $result ='<form class="foo" action="'.jApp::urlBasePath().'index.php/jelix_tests/urlsig/url1" method="post" id="'.self::$builder->getName().'"><script type="text/javascript">
//<![CDATA[
jFormsJQ.selectFillUrl=\''.jApp::urlBasePath().'index.php/jelix/jforms/getListData\';
jFormsJQ.config = {locale:\''.jApp::config()->locale.'\',basePath:\''.jApp::urlBasePath().'\',jqueryPath:\''.jApp::config()->urlengine['jqueryPath'].'\',jqueryFile:\''.jApp::config()->jquery['jquery'].'\',jelixWWWPath:\''.jApp::config()->urlengine['jelixWWWPath'].'\'};
jFormsJQ.tForm = new jFormsJQForm(\'jforms_formtest\',\'formtest\',\'0\');
jFormsJQ.tForm.setErrorDecorator(new jFormsJQErrorDecoratorHtml());
jFormsJQ.declareForm(jFormsJQ.tForm);
//]]>
</script>';
        $this->assertEquals($result, $out);
        $this->assertEquals('', self::$builder->getJsContent());

        self::$form->securityLevel = 1;
        self::$builder->setAction('jelix_tests~urlsig:url1', array('foo'=>'b>ar'));
        ob_start();
        self::$builder->setOptions(array('method'=>'get'));
        self::$builder->outputHeader();
        $out = ob_get_clean();
        $result ='<form action="'.jApp::urlBasePath().'index.php/jelix_tests/urlsig/url1" method="get" id="'.self::$builder->getName().'"><script type="text/javascript">
//<![CDATA[
jFormsJQ.selectFillUrl=\''.jApp::urlBasePath().'index.php/jelix/jforms/getListData\';
jFormsJQ.config = {locale:\''.jApp::config()->locale.'\',basePath:\''.jApp::urlBasePath().'\',jqueryPath:\''.jApp::config()->urlengine['jqueryPath'].'\',jqueryFile:\''.jApp::config()->jquery['jquery'].'\',jelixWWWPath:\''.jApp::config()->urlengine['jelixWWWPath'].'\'};
jFormsJQ.tForm = new jFormsJQForm(\'jforms_formtest1\',\'formtest\',\'0\');
jFormsJQ.tForm.setErrorDecorator(new jFormsJQErrorDecoratorHtml());
jFormsJQ.declareForm(jFormsJQ.tForm);
//]]>
</script><div class="jforms-hiddens"><input type="hidden" name="foo" value="b&gt;ar"/>
<input type="hidden" name="__JFORMS_TOKEN__" value="'.self::$container->token.'"/>
</div>';
        $this->assertEquals($result, $out);
        $this->assertEquals('', self::$builder->getJsContent());
        self::$form->securityLevel = 0;
    }
    public function testOutputFooter()
    {
        ob_start();
        self::$builder->outputFooter();
        $out = ob_get_clean();
        $this->assertEquals('<script type="text/javascript">
//<![CDATA[
(function(){var c, c2;

})();
//]]>
</script></form>', $out);
    }
    public function testOutputInput()
    {
        $ctrl= new jFormsControlinput('input1');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre nom';
        self::$form->addControl($ctrl);

        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<label class="jforms-label" for="'.self::$formname.'_input1" id="'.self::$formname.'_input1_label">Votre nom</label>'."\n", $out);

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="input1" id="'.self::$formname.'_input1" class="jforms-ctrl-input" value="" type="text"/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlString(\'input1\', \'Votre nom\');
c.errRequired=\'"Votre nom" field is required\';
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());

        self::$form->setData('input1', 'toto');
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="input1" id="'.self::$formname.'_input1" class="jforms-ctrl-input" value="toto" type="text"/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlString(\'input1\', \'Votre nom\');
c.errRequired=\'"Votre nom" field is required\';
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());

        self::$form->setData('input1', 'toto');
        ob_start();
        self::$builder->outputControl($ctrl, array('class'=>'foo', 'onclick'=>"alert('bla')"));
        $out = ob_get_clean();
        $this->assertEquals('<input class="foo jforms-ctrl-input" onclick="alert(\'bla\')" name="input1" id="'.self::$formname.'_input1" value="toto" type="text"/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlString(\'input1\', \'Votre nom\');
c.errRequired=\'"Votre nom" field is required\';
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());



        $ctrl->defaultValue='laurent';
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="input1" id="'.self::$formname.'_input1" class="jforms-ctrl-input" value="toto" type="text"/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlString(\'input1\', \'Votre nom\');
c.errRequired=\'"Votre nom" field is required\';
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());

        self::$form->removeControl($ctrl->ref);
        self::$form->addControl($ctrl);
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="input1" id="'.self::$formname.'_input1" class="jforms-ctrl-input" value="laurent" type="text"/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlString(\'input1\', \'Votre nom\');
c.errRequired=\'"Votre nom" field is required\';
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());

        $ctrl->required=true;
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="input1" id="'.self::$formname.'_input1" class="jforms-ctrl-input jforms-required" value="laurent" type="text"/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlString(\'input1\', \'Votre nom\');
c.required = true;
c.errRequired=\'"Votre nom" field is required\';
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());


        $ctrl->setReadOnly(true);
        $ctrl->required=false;
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="input1" id="'.self::$formname.'_input1" readonly="readonly" class="jforms-ctrl-input jforms-readonly" value="laurent" type="text"/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlString(\'input1\', \'Votre nom\');
c.readOnly = true;
c.errRequired=\'"Votre nom" field is required\';
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());


        $ctrl->setReadOnly(false);
        $ctrl->help='some help';
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="input1" id="'.self::$formname.'_input1" class="jforms-ctrl-input" value="laurent" type="text"/>'."\n".'<span class="jforms-help" id="jforms_formtest1_input1-help">&nbsp;<span>some help</span></span>', $out);
        $this->assertEquals('c = new jFormsJQControlString(\'input1\', \'Votre nom\');
c.errRequired=\'"Votre nom" field is required\';
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());


        $ctrl->help="some \nhelp with ' and\nline break.";
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="input1" id="'.self::$formname.'_input1" class="jforms-ctrl-input" value="laurent" type="text"/>'."\n".'<span class="jforms-help" id="jforms_formtest1_input1-help">'."&nbsp;<span>some \nhelp with ' and\nline break.</span>".'</span>', $out);
        $this->assertEquals('c = new jFormsJQControlString(\'input1\', \'Votre nom\');
c.errRequired=\'"Votre nom" field is required\';
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());

        $ctrl->hint='ceci est un tooltip';
        $ctrl->help='some help';
        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<label class="jforms-label" for="'.self::$formname.'_input1" id="'.self::$formname.'_input1_label" title="ceci est un tooltip">Votre nom</label>'."\n", $out);

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="input1" id="'.self::$formname.'_input1" title="ceci est un tooltip" class="jforms-ctrl-input" value="laurent" type="text"/>'."\n".'<span class="jforms-help" id="jforms_formtest1_input1-help">&nbsp;<span>some help</span></span>', $out);
        $this->assertEquals('c = new jFormsJQControlString(\'input1\', \'Votre nom\');
c.errRequired=\'"Votre nom" field is required\';
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());


        $ctrl->help='';
        $ctrl->hint='';
        $ctrl->datatype->addFacet('maxLength', 5);
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="input1" id="'.self::$formname.'_input1" class="jforms-ctrl-input" maxlength="5" value="laurent" type="text"/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlString(\'input1\', \'Votre nom\');
c.maxLength = \'5\';
c.errRequired=\'"Votre nom" field is required\';
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());

        $ctrl->datatype->addFacet('pattern', '/^[a-f]{5}$/');
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="input1" id="'.self::$formname.'_input1" class="jforms-ctrl-input" maxlength="5" value="laurent" type="text"/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlString(\'input1\', \'Votre nom\');
c.maxLength = \'5\';
c.regexp = /^[a-f]{5}$/;
c.errRequired=\'"Votre nom" field is required\';
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());
    }
    public function testOutputCheckbox()
    {
        $ctrl= new jFormsControlCheckbox('chk1');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Une option';
        self::$form->addControl($ctrl);

        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<label class="jforms-label" for="'.self::$formname.'_chk1" id="'.self::$formname.'_chk1_label">Une option</label>'."\n", $out);

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="chk1" id="'.self::$formname.'_chk1" class="jforms-ctrl-checkbox" value="1" type="checkbox"/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlBoolean(\'chk1\', \'Une option\');
c.errRequired=\'"Une option" field is required\';
c.errInvalid=\'"Une option" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());


        self::$form->setData('chk1', '1');
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="chk1" id="'.self::$formname.'_chk1" class="jforms-ctrl-checkbox" checked="checked" value="1" type="checkbox"/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlBoolean(\'chk1\', \'Une option\');
c.errRequired=\'"Une option" field is required\';
c.errInvalid=\'"Une option" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());

        $ctrl= new jFormsControlCheckbox('chk2');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Une option';
        self::$form->addControl($ctrl);

        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<label class="jforms-label" for="'.self::$formname.'_chk2" id="'.self::$formname.'_chk2_label">Une option</label>'."\n", $out);

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="chk2" id="'.self::$formname.'_chk2" class="jforms-ctrl-checkbox" value="1" type="checkbox"/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlBoolean(\'chk2\', \'Une option\');
c.errRequired=\'"Une option" field is required\';
c.errInvalid=\'"Une option" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());


        $ctrl->defaultValue='1';
        self::$form->removeControl($ctrl->ref);
        self::$form->addControl($ctrl);
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="chk2" id="'.self::$formname.'_chk2" class="jforms-ctrl-checkbox" checked="checked" value="1" type="checkbox"/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlBoolean(\'chk2\', \'Une option\');
c.errRequired=\'"Une option" field is required\';
c.errInvalid=\'"Une option" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());

        self::$form->setData('chk2', '0');
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="chk2" id="'.self::$formname.'_chk2" class="jforms-ctrl-checkbox" value="1" type="checkbox"/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlBoolean(\'chk2\', \'Une option\');
c.errRequired=\'"Une option" field is required\';
c.errInvalid=\'"Une option" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());


        $ctrl->setReadOnly(true);
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="chk2" id="'.self::$formname.'_chk2" readonly="readonly" class="jforms-ctrl-checkbox jforms-readonly" value="1" type="checkbox"/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlBoolean(\'chk2\', \'Une option\');
c.readOnly = true;
c.errRequired=\'"Une option" field is required\';
c.errInvalid=\'"Une option" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());


        self::$form->setData('chk2', '1');
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="chk2" id="'.self::$formname.'_chk2" readonly="readonly" class="jforms-ctrl-checkbox jforms-readonly" checked="checked" value="1" type="checkbox"/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlBoolean(\'chk2\', \'Une option\');
c.readOnly = true;
c.errRequired=\'"Une option" field is required\';
c.errInvalid=\'"Une option" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());

        $ctrl->hint='ceci est un tooltip';
        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<label class="jforms-label" for="'.self::$formname.'_chk2" id="'.self::$formname.'_chk2_label" title="ceci est un tooltip">Une option</label>'."\n", $out);

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="chk2" id="'.self::$formname.'_chk2" readonly="readonly" title="ceci est un tooltip" class="jforms-ctrl-checkbox jforms-readonly" checked="checked" value="1" type="checkbox"/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlBoolean(\'chk2\', \'Une option\');
c.readOnly = true;
c.errRequired=\'"Une option" field is required\';
c.errInvalid=\'"Une option" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());
    }

    public function testOutputCheckboxes()
    {
        $ctrl= new jFormsControlcheckboxes('choixsimple');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Vos choix';
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products', 'findAll', 'name', 'id');
        self::$form->addControl($ctrl);

        $records = array(
            array('id'=>'10', 'name'=>'foo', 'price'=>'12'),
            array('id'=>'11', 'name'=>'bar', 'price'=>'54'),
            array('id'=>'23', 'name'=>'baz', 'price'=>'97'),
        );
        $this->insertRecordsIntoTable('product_test', array('id','name','price'), $records, true);

        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<span class="jforms-label" id="'.self::$formname.'_choixsimple_label">Vos choix</span>'."\n", $out);

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<span class="jforms-chkbox jforms-ctl-choixsimple"><input type="checkbox" name="choixsimple[]" id="'.self::$formname.'_choixsimple_0" class="jforms-ctrl-checkboxes" value="10"/><label for="'.self::$formname.'_choixsimple_0">foo</label></span> <br/>'."\n";
        $result.='<span class="jforms-chkbox jforms-ctl-choixsimple"><input type="checkbox" name="choixsimple[]" id="'.self::$formname.'_choixsimple_1" class="jforms-ctrl-checkboxes" value="11"/><label for="'.self::$formname.'_choixsimple_1">bar</label></span> <br/>'."\n";
        $result.='<span class="jforms-chkbox jforms-ctl-choixsimple"><input type="checkbox" name="choixsimple[]" id="'.self::$formname.'_choixsimple_2" class="jforms-ctrl-checkboxes" value="23"/><label for="'.self::$formname.'_choixsimple_2">baz</label></span> <br/>'."\n\n";
        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'choixsimple[]\', \'Vos choix\');
c.errRequired=\'"Vos choix" field is required\';
c.errInvalid=\'"Vos choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());


        self::$form->setData('choixsimple', 11);
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<span class="jforms-chkbox jforms-ctl-choixsimple"><input type="checkbox" name="choixsimple[]" id="'.self::$formname.'_choixsimple_0" class="jforms-ctrl-checkboxes" value="10"/><label for="'.self::$formname.'_choixsimple_0">foo</label></span> <br/>'."\n";
        $result.='<span class="jforms-chkbox jforms-ctl-choixsimple"><input type="checkbox" name="choixsimple[]" id="'.self::$formname.'_choixsimple_1" class="jforms-ctrl-checkboxes" value="11" checked="checked"/><label for="'.self::$formname.'_choixsimple_1">bar</label></span> <br/>'."\n";
        $result.='<span class="jforms-chkbox jforms-ctl-choixsimple"><input type="checkbox" name="choixsimple[]" id="'.self::$formname.'_choixsimple_2" class="jforms-ctrl-checkboxes" value="23"/><label for="'.self::$formname.'_choixsimple_2">baz</label></span> <br/>'."\n\n";
        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'choixsimple[]\', \'Vos choix\');
c.errRequired=\'"Vos choix" field is required\';
c.errInvalid=\'"Vos choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());


        $ctrl->datasource= new jFormsStaticDatasource();
        $ctrl->datasource->setGroupBy(true);
        $ctrl->datasource->data = array(
            ''=>array('10'=>'foo'),
            'toto'=>array('11'=>'bar',
            '23'=>'baz',)
        );
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<span class="jforms-chkbox jforms-ctl-choixsimple"><input type="checkbox" name="choixsimple[]" id="'.self::$formname.'_choixsimple_0" class="jforms-ctrl-checkboxes" value="10"/><label for="'.self::$formname.'_choixsimple_0">foo</label></span> <br/>'."\n";
        $result.="<fieldset><legend>toto</legend>\n";
        $result.='<span class="jforms-chkbox jforms-ctl-choixsimple"><input type="checkbox" name="choixsimple[]" id="'.self::$formname.'_choixsimple_1" class="jforms-ctrl-checkboxes" value="11" checked="checked"/><label for="'.self::$formname.'_choixsimple_1">bar</label></span> <br/>'."\n";
        $result.='<span class="jforms-chkbox jforms-ctl-choixsimple"><input type="checkbox" name="choixsimple[]" id="'.self::$formname.'_choixsimple_2" class="jforms-ctrl-checkboxes" value="23"/><label for="'.self::$formname.'_choixsimple_2">baz</label></span> <br/>'."\n";
        $result.="</fieldset>\n\n";
        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'choixsimple[]\', \'Vos choix\');
c.errRequired=\'"Vos choix" field is required\';
c.errInvalid=\'"Vos choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());


        $ctrl= new jFormsControlcheckboxes('choixmultiple');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Vos choix';
        $ctrl->datasource= new jFormsStaticDatasource();
        $ctrl->datasource->data = array(
            '10'=>'foo',
            '11'=>'bar',
            '23'=>'baz',
        );
        self::$form->addControl($ctrl);

        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<span class="jforms-label" id="'.self::$formname.'_choixmultiple_label">Vos choix</span>'."\n", $out);

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.self::$formname.'_choixmultiple_0" class="jforms-ctrl-checkboxes" value="10"/><label for="'.self::$formname.'_choixmultiple_0">foo</label></span> <br/>'."\n";
        $result.='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.self::$formname.'_choixmultiple_1" class="jforms-ctrl-checkboxes" value="11"/><label for="'.self::$formname.'_choixmultiple_1">bar</label></span> <br/>'."\n";
        $result.='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.self::$formname.'_choixmultiple_2" class="jforms-ctrl-checkboxes" value="23"/><label for="'.self::$formname.'_choixmultiple_2">baz</label></span> <br/>'."\n\n";
        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'choixmultiple[]\', \'Vos choix\');
c.errRequired=\'"Vos choix" field is required\';
c.errInvalid=\'"Vos choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());


        self::$form->setData('choixmultiple', 11);
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.self::$formname.'_choixmultiple_0" class="jforms-ctrl-checkboxes" value="10"/><label for="'.self::$formname.'_choixmultiple_0">foo</label></span> <br/>'."\n";
        $result.='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.self::$formname.'_choixmultiple_1" class="jforms-ctrl-checkboxes" value="11" checked="checked"/><label for="'.self::$formname.'_choixmultiple_1">bar</label></span> <br/>'."\n";
        $result.='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.self::$formname.'_choixmultiple_2" class="jforms-ctrl-checkboxes" value="23"/><label for="'.self::$formname.'_choixmultiple_2">baz</label></span> <br/>'."\n\n";
        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'choixmultiple[]\', \'Vos choix\');
c.errRequired=\'"Vos choix" field is required\';
c.errInvalid=\'"Vos choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());


        self::$form->setData('choixmultiple', array(10,23));
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.self::$formname.'_choixmultiple_0" class="jforms-ctrl-checkboxes" value="10" checked="checked"/><label for="'.self::$formname.'_choixmultiple_0">foo</label></span> <br/>'."\n";
        $result.='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.self::$formname.'_choixmultiple_1" class="jforms-ctrl-checkboxes" value="11"/><label for="'.self::$formname.'_choixmultiple_1">bar</label></span> <br/>'."\n";
        $result.='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.self::$formname.'_choixmultiple_2" class="jforms-ctrl-checkboxes" value="23" checked="checked"/><label for="'.self::$formname.'_choixmultiple_2">baz</label></span> <br/>'."\n\n";
        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'choixmultiple[]\', \'Vos choix\');
c.errRequired=\'"Vos choix" field is required\';
c.errInvalid=\'"Vos choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());


        $ctrl->setReadOnly(true);
        $ctrl->hint='ceci est un tooltip';
        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<span class="jforms-label" id="'.self::$formname.'_choixmultiple_label" title="ceci est un tooltip">Vos choix</span>'."\n", $out);

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.self::$formname.'_choixmultiple_0" readonly="readonly" class="jforms-ctrl-checkboxes jforms-readonly" value="10" checked="checked"/><label for="'.self::$formname.'_choixmultiple_0">foo</label></span> <br/>'."\n";
        $result.='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.self::$formname.'_choixmultiple_1" readonly="readonly" class="jforms-ctrl-checkboxes jforms-readonly" value="11"/><label for="'.self::$formname.'_choixmultiple_1">bar</label></span> <br/>'."\n";
        $result.='<span class="jforms-chkbox jforms-ctl-choixmultiple"><input type="checkbox" name="choixmultiple[]" id="'.self::$formname.'_choixmultiple_2" readonly="readonly" class="jforms-ctrl-checkboxes jforms-readonly" value="23" checked="checked"/><label for="'.self::$formname.'_choixmultiple_2">baz</label></span> <br/>'."\n\n";
        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'choixmultiple[]\', \'Vos choix\');
c.readOnly = true;
c.errRequired=\'"Vos choix" field is required\';
c.errInvalid=\'"Vos choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());
    }

    public function testOutputRadiobuttons()
    {
        $ctrl= new jFormsControlradiobuttons('rbchoixsimple');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre choix';
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products', 'findAll', 'name', 'id');
        self::$form->addControl($ctrl);

        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<span class="jforms-label" id="'.self::$formname.'_rbchoixsimple_label">Votre choix</span>'."\n", $out);

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.self::$formname.'_rbchoixsimple_0" class="jforms-ctrl-radiobuttons" value="10"/><label for="'.self::$formname.'_rbchoixsimple_0">foo</label></span> <br/>'."\n";
        $result.='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.self::$formname.'_rbchoixsimple_1" class="jforms-ctrl-radiobuttons" value="11"/><label for="'.self::$formname.'_rbchoixsimple_1">bar</label></span> <br/>'."\n";
        $result.='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.self::$formname.'_rbchoixsimple_2" class="jforms-ctrl-radiobuttons" value="23"/><label for="'.self::$formname.'_rbchoixsimple_2">baz</label></span> <br/>'."\n\n";
        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'rbchoixsimple\', \'Votre choix\');
c.errRequired=\'"Votre choix" field is required\';
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());


        self::$form->setData('rbchoixsimple', 11);

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.self::$formname.'_rbchoixsimple_0" class="jforms-ctrl-radiobuttons" value="10"/><label for="'.self::$formname.'_rbchoixsimple_0">foo</label></span> <br/>'."\n";
        $result.='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.self::$formname.'_rbchoixsimple_1" class="jforms-ctrl-radiobuttons" value="11" checked="checked"/><label for="'.self::$formname.'_rbchoixsimple_1">bar</label></span> <br/>'."\n";
        $result.='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.self::$formname.'_rbchoixsimple_2" class="jforms-ctrl-radiobuttons" value="23"/><label for="'.self::$formname.'_rbchoixsimple_2">baz</label></span> <br/>'."\n\n";
        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'rbchoixsimple\', \'Votre choix\');
c.errRequired=\'"Votre choix" field is required\';
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());


        $ctrl->datasource= new jFormsStaticDatasource();
        $ctrl->datasource->data = array(
            '10'=>'foo',
            '11'=>'bar',
            '23'=>'baz',
        );

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.self::$formname.'_rbchoixsimple_0" class="jforms-ctrl-radiobuttons" value="10"/><label for="'.self::$formname.'_rbchoixsimple_0">foo</label></span> <br/>'."\n";
        $result.='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.self::$formname.'_rbchoixsimple_1" class="jforms-ctrl-radiobuttons" value="11" checked="checked"/><label for="'.self::$formname.'_rbchoixsimple_1">bar</label></span> <br/>'."\n";
        $result.='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.self::$formname.'_rbchoixsimple_2" class="jforms-ctrl-radiobuttons" value="23"/><label for="'.self::$formname.'_rbchoixsimple_2">baz</label></span> <br/>'."\n\n";
        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'rbchoixsimple\', \'Votre choix\');
c.errRequired=\'"Votre choix" field is required\';
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());


        self::$form->setData('rbchoixsimple', 23);
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.self::$formname.'_rbchoixsimple_0" class="jforms-ctrl-radiobuttons" value="10"/><label for="'.self::$formname.'_rbchoixsimple_0">foo</label></span> <br/>'."\n";
        $result.='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.self::$formname.'_rbchoixsimple_1" class="jforms-ctrl-radiobuttons" value="11"/><label for="'.self::$formname.'_rbchoixsimple_1">bar</label></span> <br/>'."\n";
        $result.='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.self::$formname.'_rbchoixsimple_2" class="jforms-ctrl-radiobuttons" value="23" checked="checked"/><label for="'.self::$formname.'_rbchoixsimple_2">baz</label></span> <br/>'."\n\n";
        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'rbchoixsimple\', \'Votre choix\');
c.errRequired=\'"Votre choix" field is required\';
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());

        $ctrl->setReadOnly(true);
        $ctrl->hint='ceci est un tooltip';
        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<span class="jforms-label" id="'.self::$formname.'_rbchoixsimple_label" title="ceci est un tooltip">Votre choix</span>'."\n", $out);

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.self::$formname.'_rbchoixsimple_0" readonly="readonly" class="jforms-ctrl-radiobuttons jforms-readonly" value="10"/><label for="'.self::$formname.'_rbchoixsimple_0">foo</label></span> <br/>'."\n";
        $result.='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.self::$formname.'_rbchoixsimple_1" readonly="readonly" class="jforms-ctrl-radiobuttons jforms-readonly" value="11"/><label for="'.self::$formname.'_rbchoixsimple_1">bar</label></span> <br/>'."\n";
        $result.='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.self::$formname.'_rbchoixsimple_2" readonly="readonly" class="jforms-ctrl-radiobuttons jforms-readonly" value="23" checked="checked"/><label for="'.self::$formname.'_rbchoixsimple_2">baz</label></span> <br/>'."\n\n";
        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'rbchoixsimple\', \'Votre choix\');
c.readOnly = true;
c.errRequired=\'"Votre choix" field is required\';
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());

        self::$builder->clearJs();

        $ctrl->datasource = new jFormsStaticDatasource();
        $ctrl->datasource->data = array('1'=>'Yes','0'=>'No');
        self::$form->setReadOnly('rbchoixsimple', false);
        self::$form->setData('rbchoixsimple', null);
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.self::$formname.'_rbchoixsimple_0" class="jforms-ctrl-radiobuttons" value="1"/><label for="'.self::$formname.'_rbchoixsimple_0">Yes</label></span> <br/>'."\n";
        $result.='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.self::$formname.'_rbchoixsimple_1" class="jforms-ctrl-radiobuttons" value="0"/><label for="'.self::$formname.'_rbchoixsimple_1">No</label></span> <br/>'."\n\n";
        $this->assertEquals($result, $out);

        self::$form->setData('rbchoixsimple', 0);
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.self::$formname.'_rbchoixsimple_0" class="jforms-ctrl-radiobuttons" value="1"/><label for="'.self::$formname.'_rbchoixsimple_0">Yes</label></span> <br/>'."\n";
        $result.='<span class="jforms-radio jforms-ctl-rbchoixsimple"><input type="radio" name="rbchoixsimple" id="'.self::$formname.'_rbchoixsimple_1" class="jforms-ctrl-radiobuttons" value="0" checked="checked"/><label for="'.self::$formname.'_rbchoixsimple_1">No</label></span> <br/>'."\n\n";
        $this->assertEquals($result, $out);

        self::$builder->clearJs();
    }

    public function testOutputMenulist()
    {
        $ctrl= new jFormsControlmenulist('menulist1');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre choix';
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products', 'findAll', 'name', 'id');
        self::$form->addControl($ctrl);

        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<label class="jforms-label" for="'.self::$formname.'_menulist1" id="'.self::$formname.'_menulist1_label">Votre choix</label>'."\n", $out);

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<select name="menulist1" id="'.self::$formname.'_menulist1" class="jforms-ctrl-menulist" size="1">'."\n";
        $result.='<option value="" selected="selected"></option>'."\n";
        $result.='<option value="10">foo</option>'."\n";
        $result.='<option value="11">bar</option>'."\n";
        $result.='<option value="23">baz</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'menulist1\', \'Votre choix\');
c.errRequired=\'"Votre choix" field is required\';
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());

        $ctrl->emptyItemLabel = '-- select --';
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<select name="menulist1" id="'.self::$formname.'_menulist1" class="jforms-ctrl-menulist" size="1">'."\n";
        $result.='<option value="" selected="selected">-- select --</option>'."\n";
        $result.='<option value="10">foo</option>'."\n";
        $result.='<option value="11">bar</option>'."\n";
        $result.='<option value="23">baz</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'menulist1\', \'Votre choix\');
c.errRequired=\'"Votre choix" field is required\';
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());

        $ctrl->emptyItemLabel = '';
        self::$form->setData('menulist1', 11);
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<select name="menulist1" id="'.self::$formname.'_menulist1" class="jforms-ctrl-menulist" size="1">'."\n";
        $result.='<option value=""></option>'."\n";
        $result.='<option value="10">foo</option>'."\n";
        $result.='<option value="11" selected="selected">bar</option>'."\n";
        $result.='<option value="23">baz</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'menulist1\', \'Votre choix\');
c.errRequired=\'"Votre choix" field is required\';
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());

        $ctrl->emptyItemLabel = null;
        $ctrl->datasource= new jFormsStaticDatasource();
        $ctrl->datasource->data = array(
            '10'=>'foo',
            '11'=>'bar',
            '23'=>'baz',
        );

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'menulist1\', \'Votre choix\');
c.errRequired=\'"Votre choix" field is required\';
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());



        $ctrl->datasource->setGroupBy(true);
        $ctrl->datasource->data = array(
            'you'=>array(
                '10'=>'foo',
                '11'=>'bar',),
            ''=>array(
                '23'=>'baz',),
        );

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<select name="menulist1" id="'.self::$formname.'_menulist1" class="jforms-ctrl-menulist" size="1">'."\n";
        $result.='<option value=""></option>'."\n";
        $result.='<option value="23">baz</option>'."\n<optgroup label=\"you\">";
        $result.='<option value="10">foo</option>'."\n";
        $result.='<option value="11" selected="selected">bar</option>'."\n</optgroup>";
        $result.='</select>'."\n";

        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'menulist1\', \'Votre choix\');
c.errRequired=\'"Votre choix" field is required\';
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());

        $ctrl->datasource->setGroupBy(false);
        $ctrl->datasource->data = array(
            '10'=>'foo',
            '11'=>'bar',
            '23'=>'baz',
        );


        $ctrl->setReadOnly(true);
        $ctrl->hint='ceci est un tooltip';
        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<label class="jforms-label" for="'.self::$formname.'_menulist1" id="'.self::$formname.'_menulist1_label" title="ceci est un tooltip">Votre choix</label>'."\n", $out);

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<select name="menulist1" id="'.self::$formname.'_menulist1" title="ceci est un tooltip" class="jforms-ctrl-menulist jforms-readonly" disabled="disabled" size="1">'."\n";
        $result.='<option value=""></option>'."\n";
        $result.='<option value="10">foo</option>'."\n";
        $result.='<option value="11" selected="selected">bar</option>'."\n";
        $result.='<option value="23">baz</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'menulist1\', \'Votre choix\');
c.readOnly = true;
c.errRequired=\'"Votre choix" field is required\';
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());


        $ctrl->required = true;
        self::$form->setData('menulist1', "23");
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<select name="menulist1" id="'.self::$formname.'_menulist1" title="ceci est un tooltip" class="jforms-ctrl-menulist jforms-readonly" disabled="disabled" size="1">'."\n";
        $result.='<option value="10">foo</option>'."\n";
        $result.='<option value="11">bar</option>'."\n";
        $result.='<option value="23" selected="selected">baz</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'menulist1\', \'Votre choix\');
c.readOnly = true;
c.required = true;
c.errRequired=\'"Votre choix" field is required\';
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());


        $ctrl->required = false;
        self::$form->setData('menulist1', "");
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<select name="menulist1" id="'.self::$formname.'_menulist1" title="ceci est un tooltip" class="jforms-ctrl-menulist jforms-readonly" disabled="disabled" size="1">'."\n";
        $result.='<option value="" selected="selected"></option>'."\n";
        $result.='<option value="10">foo</option>'."\n";
        $result.='<option value="11">bar</option>'."\n";
        $result.='<option value="23">baz</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'menulist1\', \'Votre choix\');
c.readOnly = true;
c.errRequired=\'"Votre choix" field is required\';
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());

        $ctrl->required = true;
        $ctrl->emptyItemLabel = ' -- select -- ';
        self::$form->setData('menulist1', "");
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<select name="menulist1" id="'.self::$formname.'_menulist1" title="ceci est un tooltip" class="jforms-ctrl-menulist jforms-readonly" disabled="disabled" size="1">'."\n";
        $result.='<option value="" selected="selected"> -- select -- </option>'."\n";
        $result.='<option value="10">foo</option>'."\n";
        $result.='<option value="11">bar</option>'."\n";
        $result.='<option value="23">baz</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'menulist1\', \'Votre choix\');
c.readOnly = true;
c.required = true;
c.errRequired=\'"Votre choix" field is required\';
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());
        $ctrl->required = false;
        $ctrl->emptyItemLabel = null;

        
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
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products', 'findOrderPrice', 'name', 'id');
        $ctrl->datasource->setGroupBy('price');
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<select name="menulist1" id="'.self::$formname.'_menulist1" class="jforms-ctrl-menulist" size="1">'."\n";
        $result.='<option value="" selected="selected"></option>'."\n";
        $result.='<optgroup label="0"><option value="27">zoulou</option>'."\n";
        $result.='</optgroup><optgroup label="15"><option value="10">foo</option>'."\n";
        $result.='</optgroup><optgroup label="54"><option value="11">bar</option>'."\n";
        $result.='<option value="42">bidule</option>'."\n";
        $result.='</optgroup><optgroup label="97"><option value="23">baz</option>'."\n";
        $result.='<option value="12">truc</option>'."\n";
        $result.='</optgroup></select>'."\n";
        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'menulist1\', \'Votre choix\');
c.errRequired=\'"Votre choix" field is required\';
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());


        $records = array(
            array('id'=>'10', 'name'=>'foo', 'price'=>'12'),
            array('id'=>'11', 'name'=>'bar', 'price'=>'54'),
            array('id'=>'23', 'name'=>'baz', 'price'=>'97'),
        );
        $this->insertRecordsIntoTable('product_test', array('id','name','price'), $records, true);

        $ctrl->setReadOnly(false);
        $ctrl->hint='';
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products', 'findByMaxId', 'name', 'id', '', '15');
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<select name="menulist1" id="'.self::$formname.'_menulist1" class="jforms-ctrl-menulist" size="1">'."\n";
        $result.='<option value="" selected="selected"></option>'."\n";
        $result.='<option value="10">foo</option>'."\n";
        $result.='<option value="11">bar</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'menulist1\', \'Votre choix\');
c.errRequired=\'"Votre choix" field is required\';
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());


        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products', 'findByMaxId', 'name', 'id', '', '11');
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<select name="menulist1" id="'.self::$formname.'_menulist1" class="jforms-ctrl-menulist" size="1">'."\n";
        $result.='<option value="" selected="selected"></option>'."\n";
        $result.='<option value="10">foo</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'menulist1\', \'Votre choix\');
c.errRequired=\'"Votre choix" field is required\';
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());


        self::$form->setData('menulist1', "10");
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<select name="menulist1" id="'.self::$formname.'_menulist1" class="jforms-ctrl-menulist" size="1">'."\n";
        $result.='<option value=""></option>'."\n";
        $result.='<option value="10" selected="selected">foo</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'menulist1\', \'Votre choix\');
c.errRequired=\'"Votre choix" field is required\';
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());


        self::$form->setData('menulist1', "");

        self::$form->addControl(new jFormsControlHidden('hidden1'));
        self::$form->setData('hidden1', "25");
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products', 'findByMaxId', 'name', 'id', '', null, 'hidden1');
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<select name="menulist1" id="'.self::$formname.'_menulist1" class="jforms-ctrl-menulist" size="1">'."\n";
        $result.='<option value="" selected="selected"></option>'."\n";
        $result.='<option value="10">foo</option>'."\n";
        $result.='<option value="11">bar</option>'."\n";
        $result.='<option value="23">baz</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'menulist1\', \'Votre choix\');
c.dependencies = [\'hidden1\'];
c.errRequired=\'"Votre choix" field is required\';
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());

        $this->assertEquals('jFormsJQ.tForm.declareDynamicFill(\'menulist1\');
', self::$builder->getLastJsContent());


        self::$form->setData('hidden1', "15");
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<select name="menulist1" id="'.self::$formname.'_menulist1" class="jforms-ctrl-menulist" size="1">'."\n";
        $result.='<option value="" selected="selected"></option>'."\n";
        $result.='<option value="10">foo</option>'."\n";
        $result.='<option value="11">bar</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEquals($result, $out);



        self::$form->setData('menulist1', "10");
        self::$form->setData('hidden1', "11");
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<select name="menulist1" id="'.self::$formname.'_menulist1" class="jforms-ctrl-menulist" size="1">'."\n";
        $result.='<option value=""></option>'."\n";
        $result.='<option value="10" selected="selected">foo</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEquals($result, $out);

        self::$form->setData('menulist1', "");
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products', 'findByMaxId', 'name,price', 'id', '', '25', null);
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<select name="menulist1" id="'.self::$formname.'_menulist1" class="jforms-ctrl-menulist" size="1">'."\n";
        $result.='<option value="" selected="selected"></option>'."\n";
        $result.='<option value="10">foo12</option>'."\n";
        $result.='<option value="11">bar54</option>'."\n";
        $result.='<option value="23">baz97</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEquals($result, $out);

        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products', 'findByMaxId', 'name,price', 'id', '', '25', null, ' - ');
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<select name="menulist1" id="'.self::$formname.'_menulist1" class="jforms-ctrl-menulist" size="1">'."\n";
        $result.='<option value="" selected="selected"></option>'."\n";
        $result.='<option value="10">foo - 12</option>'."\n";
        $result.='<option value="11">bar - 54</option>'."\n";
        $result.='<option value="23">baz - 97</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEquals($result, $out);

        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products', 'findBetweenId', 'name,price', 'id', '', '9,25', null, ' - ');
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<select name="menulist1" id="'.self::$formname.'_menulist1" class="jforms-ctrl-menulist" size="1">'."\n";
        $result.='<option value="" selected="selected"></option>'."\n";
        $result.='<option value="10">foo - 12</option>'."\n";
        $result.='<option value="11">bar - 54</option>'."\n";
        $result.='<option value="23">baz - 97</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEquals($result, $out);

        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products', 'findBetweenId', 'name,price', 'id', '', '10,25', null, ' - ');
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<select name="menulist1" id="'.self::$formname.'_menulist1" class="jforms-ctrl-menulist" size="1">'."\n";
        $result.='<option value="" selected="selected"></option>'."\n";
        $result.='<option value="11">bar - 54</option>'."\n";
        $result.='<option value="23">baz - 97</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEquals($result, $out);

        self::$form->addControl(new jFormsControlHidden('hidden2'));
        self::$form->setData('hidden1', "9");
        self::$form->setData('hidden2', "25");
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products', 'findBetweenId', 'name,price', 'id', '', null, 'hidden1,hidden2', ' - ');
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<select name="menulist1" id="'.self::$formname.'_menulist1" class="jforms-ctrl-menulist" size="1">'."\n";
        $result.='<option value="" selected="selected"></option>'."\n";
        $result.='<option value="10">foo - 12</option>'."\n";
        $result.='<option value="11">bar - 54</option>'."\n";
        $result.='<option value="23">baz - 97</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEquals($result, $out);

        self::$form->setData('hidden1', "10");
        self::$form->setData('hidden2', "25");
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products', 'findBetweenId', 'name,price', 'id', '', null, 'hidden1,hidden2', ' - ');
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<select name="menulist1" id="'.self::$formname.'_menulist1" class="jforms-ctrl-menulist" size="1">'."\n";
        $result.='<option value="" selected="selected"></option>'."\n";
        $result.='<option value="11">bar - 54</option>'."\n";
        $result.='<option value="23">baz - 97</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEquals($result, $out);

        self::$form->removeControl('hidden2');
        self::$form->setData('hidden1', "11");
        self::$builder->clearJs();
    }

    public function testOutputListbox()
    {
        $ctrl= new jFormsControllistbox('listbox1');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre choix';
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products', 'findAll', 'name', 'id');
        self::$form->addControl($ctrl);

        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<label class="jforms-label" for="'.self::$formname.'_listbox1" id="'.self::$formname.'_listbox1_label">Votre choix</label>'."\n", $out);

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<select name="listbox1" id="'.self::$formname.'_listbox1" class="jforms-ctrl-listbox" size="4">'."\n";
        $result.='<option value="10">foo</option>'."\n";
        $result.='<option value="11">bar</option>'."\n";
        $result.='<option value="23">baz</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'listbox1\', \'Votre choix\');
c.errRequired=\'"Votre choix" field is required\';
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());

        self::$form->setData('listbox1', "23");
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<select name="listbox1" id="'.self::$formname.'_listbox1" class="jforms-ctrl-listbox" size="4">'."\n";
        $result.='<option value="10">foo</option>'."\n";
        $result.='<option value="11">bar</option>'."\n";
        $result.='<option value="23" selected="selected">baz</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'listbox1\', \'Votre choix\');
c.errRequired=\'"Votre choix" field is required\';
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());


        $ctrl->emptyItemLabel = 'no selection';
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<select name="listbox1" id="'.self::$formname.'_listbox1" class="jforms-ctrl-listbox" size="4">'."\n";
        $result.='<option value="">no selection</option>'."\n";
        $result.='<option value="10">foo</option>'."\n";
        $result.='<option value="11">bar</option>'."\n";
        $result.='<option value="23" selected="selected">baz</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEquals($result, $out);
        $ctrl->emptyItemLabel = null;
        $this->assertEquals('c = new jFormsJQControlString(\'listbox1\', \'Votre choix\');
c.errRequired=\'"Votre choix" field is required\';
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());

        $ctrl->datasource= new jFormsStaticDatasource();
        $ctrl->datasource->data = array(
            '10'=>'foo',
            '11'=>'bar',
            '23'=>'baz',
        );

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<select name="listbox1" id="'.self::$formname.'_listbox1" class="jforms-ctrl-listbox" size="4">'."\n";
        $result.='<option value="10">foo</option>'."\n";
        $result.='<option value="11">bar</option>'."\n";
        $result.='<option value="23" selected="selected">baz</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'listbox1\', \'Votre choix\');
c.errRequired=\'"Votre choix" field is required\';
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());


        $ctrl->setReadOnly(true);
        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<label class="jforms-label" for="'.self::$formname.'_listbox1" id="'.self::$formname.'_listbox1_label">Votre choix</label>'."\n", $out);

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<select name="listbox1" id="'.self::$formname.'_listbox1" class="jforms-ctrl-listbox jforms-readonly" disabled="disabled" size="4">'."\n";
        $result.='<option value="10">foo</option>'."\n";
        $result.='<option value="11">bar</option>'."\n";
        $result.='<option value="23" selected="selected">baz</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'listbox1\', \'Votre choix\');
c.readOnly = true;
c.errRequired=\'"Votre choix" field is required\';
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());



        $ctrl= new jFormsControllistbox('lbchoixmultiple');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre choix';
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products', 'findAll', 'name', 'id');
        $ctrl->multiple=true;
        $ctrl->hint='ceci est un tooltip';
        self::$form->addControl($ctrl);

        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<label class="jforms-label" for="'.self::$formname.'_lbchoixmultiple" id="'.self::$formname.'_lbchoixmultiple_label" title="ceci est un tooltip">Votre choix</label>'."\n", $out);

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<select name="lbchoixmultiple[]" id="'.self::$formname.'_lbchoixmultiple" title="ceci est un tooltip" class="jforms-ctrl-listbox" size="4" multiple="multiple">'."\n";
        $result.='<option value="10">foo</option>'."\n";
        $result.='<option value="11">bar</option>'."\n";
        $result.='<option value="23">baz</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'lbchoixmultiple[]\', \'Votre choix\');
c.multiple = true;
c.errRequired=\'"Votre choix" field is required\';
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());


        self::$form->setData('lbchoixmultiple', array(10,23));
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<select name="lbchoixmultiple[]" id="'.self::$formname.'_lbchoixmultiple" title="ceci est un tooltip" class="jforms-ctrl-listbox" size="4" multiple="multiple">'."\n";
        $result.='<option value="10" selected="selected">foo</option>'."\n";
        $result.='<option value="11">bar</option>'."\n";
        $result.='<option value="23" selected="selected">baz</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'lbchoixmultiple[]\', \'Votre choix\');
c.multiple = true;
c.errRequired=\'"Votre choix" field is required\';
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());


        $ctrl= new jFormsControllistbox('listbox2');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre choix';
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products', 'findAll', 'name', 'id');
        $ctrl->defaultValue=array('10');
        self::$form->addControl($ctrl);

        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<label class="jforms-label" for="'.self::$formname.'_listbox2" id="'.self::$formname.'_listbox2_label">Votre choix</label>'."\n", $out);

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<select name="listbox2" id="'.self::$formname.'_listbox2" class="jforms-ctrl-listbox" size="4">'."\n";
        $result.='<option value="10" selected="selected">foo</option>'."\n";
        $result.='<option value="11">bar</option>'."\n";
        $result.='<option value="23">baz</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'listbox2\', \'Votre choix\');
c.errRequired=\'"Votre choix" field is required\';
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());


        $ctrl= new jFormsControllistbox('lbchoixmultiple2');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre choix';
        $ctrl->datasource = new jFormsDaoDatasource('jelix_tests~products', 'findAll', 'name', 'id');
        $ctrl->multiple=true;
        $ctrl->size=8;
        $ctrl->defaultValue=array('11','23');
        self::$form->addControl($ctrl);

        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<label class="jforms-label" for="'.self::$formname.'_lbchoixmultiple2" id="'.self::$formname.'_lbchoixmultiple2_label">Votre choix</label>'."\n", $out);

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<select name="lbchoixmultiple2[]" id="'.self::$formname.'_lbchoixmultiple2" class="jforms-ctrl-listbox" size="8" multiple="multiple">'."\n";
        $result.='<option value="10">foo</option>'."\n";
        $result.='<option value="11" selected="selected">bar</option>'."\n";
        $result.='<option value="23" selected="selected">baz</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'lbchoixmultiple2[]\', \'Votre choix\');
c.multiple = true;
c.errRequired=\'"Votre choix" field is required\';
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());
    }

    public function testOutputListboxClassDatasource()
    {
        $ctrl= new jFormsControllistbox('listboxclass');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre choix';
        jClasses::inc('mydatasource');
        $ctrl->datasource = new mydatasource(0);
        self::$form->addControl($ctrl);

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $result='<select name="listboxclass" id="'.self::$formname.'_listboxclass" class="jforms-ctrl-listbox" size="4">'."\n";
        $result.='<option value="aaa">label for aaa</option>'."\n";
        $result.='<option value="bbb">label for bbb</option>'."\n";
        $result.='<option value="ccc">label for ccc</option>'."\n";
        $result.='<option value="ddd">label for ddd</option>'."\n";
        $result.='</select>'."\n";
        $this->assertEquals($result, $out);
        $this->assertEquals('c = new jFormsJQControlString(\'listboxclass\', \'Votre choix\');
c.errRequired=\'"Votre choix" field is required\';
c.errInvalid=\'"Votre choix" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());
    }


    public function testOutputTextarea()
    {
        $ctrl= new jFormsControltextarea('textarea1');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre nom';
        self::$form->addControl($ctrl);

        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<label class="jforms-label" for="'.self::$formname.'_textarea1" id="'.self::$formname.'_textarea1_label">Votre nom</label>'."\n", $out);

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<textarea name="textarea1" id="'.self::$formname.'_textarea1" class="jforms-ctrl-textarea" rows="5" cols="40"></textarea>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlString(\'textarea1\', \'Votre nom\');
c.errRequired=\'"Votre nom" field is required\';
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());


        self::$form->setData('textarea1', 'laurent');
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<textarea name="textarea1" id="'.self::$formname.'_textarea1" class="jforms-ctrl-textarea" rows="5" cols="40">laurent</textarea>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlString(\'textarea1\', \'Votre nom\');
c.errRequired=\'"Votre nom" field is required\';
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());


        $ctrl->setReadOnly(true);
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<textarea name="textarea1" id="'.self::$formname.'_textarea1" readonly="readonly" class="jforms-ctrl-textarea jforms-readonly" rows="5" cols="40">laurent</textarea>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlString(\'textarea1\', \'Votre nom\');
c.readOnly = true;
c.errRequired=\'"Votre nom" field is required\';
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());


        $ctrl->hint='ceci est un tooltip';
        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<label class="jforms-label" for="'.self::$formname.'_textarea1" id="'.self::$formname.'_textarea1_label" title="ceci est un tooltip">Votre nom</label>'."\n", $out);

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<textarea name="textarea1" id="'.self::$formname.'_textarea1" readonly="readonly" title="ceci est un tooltip" class="jforms-ctrl-textarea jforms-readonly" rows="5" cols="40">laurent</textarea>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlString(\'textarea1\', \'Votre nom\');
c.readOnly = true;
c.errRequired=\'"Votre nom" field is required\';
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());


        $ctrl->rows=20;
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<textarea name="textarea1" id="'.self::$formname.'_textarea1" readonly="readonly" title="ceci est un tooltip" class="jforms-ctrl-textarea jforms-readonly" rows="20" cols="40">laurent</textarea>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlString(\'textarea1\', \'Votre nom\');
c.readOnly = true;
c.errRequired=\'"Votre nom" field is required\';
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());


        $ctrl->cols=60;
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<textarea name="textarea1" id="'.self::$formname.'_textarea1" readonly="readonly" title="ceci est un tooltip" class="jforms-ctrl-textarea jforms-readonly" rows="20" cols="60">laurent</textarea>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlString(\'textarea1\', \'Votre nom\');
c.readOnly = true;
c.errRequired=\'"Votre nom" field is required\';
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());
    }
    public function testOutputSecret()
    {
        $ctrl= new jFormsControlSecret('passwd');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='mot de passe';
        self::$form->addControl($ctrl);

        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<label class="jforms-label" for="'.self::$formname.'_passwd" id="'.self::$formname.'_passwd_label">mot de passe</label>'."\n", $out);

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="passwd" id="'.self::$formname.'_passwd" class="jforms-ctrl-secret" type="password" value=""/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlSecret(\'passwd\', \'mot de passe\');
c.errRequired=\'"mot de passe" field is required\';
c.errInvalid=\'"mot de passe" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());

        self::$form->setData('passwd', 'laurent');
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="passwd" id="'.self::$formname.'_passwd" class="jforms-ctrl-secret" type="password" value="laurent"/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlSecret(\'passwd\', \'mot de passe\');
c.errRequired=\'"mot de passe" field is required\';
c.errInvalid=\'"mot de passe" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());

        $ctrl->setReadOnly(true);
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="passwd" id="'.self::$formname.'_passwd" readonly="readonly" class="jforms-ctrl-secret jforms-readonly" type="password" value="laurent"/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlSecret(\'passwd\', \'mot de passe\');
c.readOnly = true;
c.errRequired=\'"mot de passe" field is required\';
c.errInvalid=\'"mot de passe" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());

        $ctrl->hint='ceci est un tooltip';
        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<label class="jforms-label" for="'.self::$formname.'_passwd" id="'.self::$formname.'_passwd_label" title="ceci est un tooltip">mot de passe</label>'."\n", $out);
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="passwd" id="'.self::$formname.'_passwd" readonly="readonly" title="ceci est un tooltip" class="jforms-ctrl-secret jforms-readonly" type="password" value="laurent"/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlSecret(\'passwd\', \'mot de passe\');
c.readOnly = true;
c.errRequired=\'"mot de passe" field is required\';
c.errInvalid=\'"mot de passe" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());

        $ctrl->datatype->addFacet('minLength', 5);
        $ctrl->datatype->addFacet('maxLength', 10);
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="passwd" id="'.self::$formname.'_passwd" readonly="readonly" title="ceci est un tooltip" class="jforms-ctrl-secret jforms-readonly" maxlength="10" type="password" value="laurent"/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlSecret(\'passwd\', \'mot de passe\');
c.maxLength = \'10\';
c.minLength = \'5\';
c.readOnly = true;
c.errRequired=\'"mot de passe" field is required\';
c.errInvalid=\'"mot de passe" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());
    }
    public function testOutputSecretConfirm()
    {
        $ctrl= new jFormsControlSecretConfirm('passwd_confirm');
        $ctrl->label='confirmation mot de passe';
        self::$form->addControl($ctrl);

        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<label class="jforms-label" for="'.self::$formname.'_passwd_confirm" id="'.self::$formname.'_passwd_confirm_label">confirmation mot de passe</label>'."\n", $out);

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="passwd_confirm" id="'.self::$formname.'_passwd_confirm" class="jforms-ctrl-secretconfirm" type="password" value=""/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlConfirm(\'passwd_confirm\', \'confirmation mot de passe\');
c.errRequired=\'"confirmation mot de passe" field is required\';
c.errInvalid=\'"confirmation mot de passe" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());

        $ctrl->required = true;
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="passwd_confirm" id="'.self::$formname.'_passwd_confirm" class="jforms-ctrl-secretconfirm jforms-required" type="password" value=""/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlConfirm(\'passwd_confirm\', \'confirmation mot de passe\');
c.required = true;
c.errRequired=\'"confirmation mot de passe" field is required\';
c.errInvalid=\'"confirmation mot de passe" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());
        $ctrl->required = false;


        $ctrl->setReadOnly(true);
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="passwd_confirm" id="'.self::$formname.'_passwd_confirm" readonly="readonly" class="jforms-ctrl-secretconfirm jforms-readonly" type="password" value=""/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlConfirm(\'passwd_confirm\', \'confirmation mot de passe\');
c.readOnly = true;
c.errRequired=\'"confirmation mot de passe" field is required\';
c.errInvalid=\'"confirmation mot de passe" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());


        $ctrl->hint='ceci est un tooltip';
        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<label class="jforms-label" for="'.self::$formname.'_passwd_confirm" id="'.self::$formname.'_passwd_confirm_label" title="ceci est un tooltip">confirmation mot de passe</label>'."\n", $out);
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="passwd_confirm" id="'.self::$formname.'_passwd_confirm" readonly="readonly" title="ceci est un tooltip" class="jforms-ctrl-secretconfirm jforms-readonly" type="password" value=""/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlConfirm(\'passwd_confirm\', \'confirmation mot de passe\');
c.readOnly = true;
c.errRequired=\'"confirmation mot de passe" field is required\';
c.errInvalid=\'"confirmation mot de passe" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());
    }

    public function testOutputOutput()
    {
        $ctrl= new jFormsControlOutput('output1');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre nom';
        self::$form->addControl($ctrl);

        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<span class="jforms-label" id="'.self::$formname.'_output1_label">Votre nom</span>'."\n", $out);

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="output1" id="'.self::$formname.'_output1" type="hidden" value=""/><span class="jforms-value"></span>'."\n", $out);
        $this->assertEquals('c=null;', self::$builder->getJsContent());


        self::$form->setData('output1', 'laurent');
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="output1" id="'.self::$formname.'_output1" type="hidden" value="laurent"/><span class="jforms-value">laurent</span>'."\n", $out);
        $this->assertEquals('c=null;', self::$builder->getJsContent());


        $ctrl->setReadOnly(true);
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="output1" id="'.self::$formname.'_output1" type="hidden" value="laurent"/><span class="jforms-value">laurent</span>'."\n", $out);
        $this->assertEquals('c=null;', self::$builder->getJsContent());


        $ctrl->hint='ceci est un tooltip';
        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<span class="jforms-label" id="'.self::$formname.'_output1_label" title="ceci est un tooltip">Votre nom</span>'."\n", $out);

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="output1" id="'.self::$formname.'_output1" type="hidden" value="laurent"/><span class="jforms-value" title="ceci est un tooltip">laurent</span>'."\n", $out);
        $this->assertEquals('c=null;', self::$builder->getJsContent());
    }

    public function testOutputUpload()
    {
        $ctrl= new jFormsControlUpload('upload1');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre nom';
        self::$form->addControl($ctrl);

        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<label class="jforms-label" for="'.self::$formname.'_upload1" id="'.self::$formname.'_upload1_label">Votre nom</label>'."\n", $out);

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="upload1" id="'.self::$formname.'_upload1" class="jforms-ctrl-upload" type="file" value=""/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlString(\'upload1\', \'Votre nom\');
c.errRequired=\'"Votre nom" field is required\';
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());

        $ctrl->setReadOnly(true);
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="upload1" id="'.self::$formname.'_upload1" readonly="readonly" class="jforms-ctrl-upload jforms-readonly" type="file" value=""/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlString(\'upload1\', \'Votre nom\');
c.readOnly = true;
c.errRequired=\'"Votre nom" field is required\';
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());

        $ctrl->hint='ceci est un tooltip';
        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<label class="jforms-label" for="'.self::$formname.'_upload1" id="'.self::$formname.'_upload1_label" title="ceci est un tooltip">Votre nom</label>'."\n", $out);

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="upload1" id="'.self::$formname.'_upload1" readonly="readonly" title="ceci est un tooltip" class="jforms-ctrl-upload jforms-readonly" type="file" value=""/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlString(\'upload1\', \'Votre nom\');
c.readOnly = true;
c.errRequired=\'"Votre nom" field is required\';
c.errInvalid=\'"Votre nom" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());

        ob_start();
        self::$builder->setOptions(array('method'=>'post'));
        self::$builder->outputHeader();
        $out = ob_get_clean();
        $result ='<form action="'.jApp::urlBasePath().'index.php/jelix_tests/urlsig/url1" method="post" id="'.self::$formname.'" enctype="multipart/form-data"><script type="text/javascript">
//<![CDATA[
jFormsJQ.selectFillUrl=\''.jApp::urlBasePath().'index.php/jelix/jforms/getListData\';
jFormsJQ.config = {locale:\''.jApp::config()->locale.'\',basePath:\''.jApp::urlBasePath().'\',jqueryPath:\''.jApp::config()->urlengine['jqueryPath'].'\',jqueryFile:\''.jApp::config()->jquery['jquery'].'\',jelixWWWPath:\''.jApp::config()->urlengine['jelixWWWPath'].'\'};
jFormsJQ.tForm = new jFormsJQForm(\'jforms_formtest1\',\'formtest\',\'0\');
jFormsJQ.tForm.setErrorDecorator(new jFormsJQErrorDecoratorHtml());
jFormsJQ.declareForm(jFormsJQ.tForm);
//]]>
</script><div class="jforms-hiddens"><input type="hidden" name="foo" value="b&gt;ar"/>
<input type="hidden" name="hidden1" id="'.self::$formname.'_hidden1" value="11"/>
</div>';
        $this->assertEquals($result, $out);

        self::$form->removeControl('upload1');
    }
    public function testOutputSubmit()
    {
        $ctrl= new jFormsControlSubmit('submit1');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Ok';
        self::$form->addControl($ctrl);

        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('', $out);

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="submit1" id="'.self::$formname.'_submit1" class="jforms-submit" type="submit" value="Ok"/>'."\n", $out);
        $this->assertEquals('', self::$builder->getJsContent());


        $ctrl->setReadOnly(true);
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="submit1" id="'.self::$formname.'_submit1" class="jforms-submit" type="submit" value="Ok"/>'."\n", $out);
        $this->assertEquals('', self::$builder->getJsContent());


        $ctrl->hint='ceci est un tooltip';
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<input name="submit1" id="'.self::$formname.'_submit1" title="ceci est un tooltip" class="jforms-submit" type="submit" value="Ok"/>'."\n", $out);
        $this->assertEquals('', self::$builder->getJsContent());


        $ctrl->standalone=false;
        $ctrl->datasource= new jFormsStaticDatasource();
        $ctrl->datasource->data = array('svg'=>'Sauvegarde','prev'=>'Preview');

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $output = ' <input name="submit1" id="'.self::$formname.'_submit1_svg" title="ceci est un tooltip" class="jforms-submit" type="submit" value="Sauvegarde"/>';
        $output .= ' <input name="submit1" id="'.self::$formname.'_submit1_prev" title="ceci est un tooltip" class="jforms-submit" type="submit" value="Preview"/>'."\n";
        $this->assertEquals($output, $out);
        $this->assertEquals('', self::$builder->getJsContent());
    }
    public function testOutputReset()
    {
        $ctrl= new jFormsControlReset('reset1');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Effacer';
        self::$form->addControl($ctrl);

        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('', $out);

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<button name="reset1" id="'.self::$formname.'_reset1" class="jforms-reset" type="reset">Effacer</button>'."\n", $out);
        $this->assertEquals('', self::$builder->getJsContent());

        $ctrl->setReadOnly(true);
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<button name="reset1" id="'.self::$formname.'_reset1" class="jforms-reset" type="reset">Effacer</button>'."\n", $out);
        $this->assertEquals('', self::$builder->getJsContent());


        $ctrl->hint='ceci est un tooltip';
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<button name="reset1" id="'.self::$formname.'_reset1" title="ceci est un tooltip" class="jforms-reset" type="reset">Effacer</button>'."\n", $out);
        $this->assertEquals('', self::$builder->getJsContent());
    }
    public function testOutputHidden()
    {
        $ctrl= new jFormsControlHidden('hidden2');
        self::$form->addControl($ctrl);

        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('', $out);

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('', $out);
        $this->assertEquals('', self::$builder->getJsContent());


        ob_start();
        self::$builder->setOptions(array('method'=>'post'));
        self::$builder->outputHeader();
        $out = ob_get_clean();
        $result ='<form action="'.jApp::urlBasePath().'index.php/jelix_tests/urlsig/url1" method="post" id="'.self::$formname.'"><script type="text/javascript">
//<![CDATA[
jFormsJQ.selectFillUrl=\''.jApp::urlBasePath().'index.php/jelix/jforms/getListData\';
jFormsJQ.config = {locale:\''.jApp::config()->locale.'\',basePath:\''.jApp::urlBasePath().'\',jqueryPath:\''.jApp::config()->urlengine['jqueryPath'].'\',jqueryFile:\''.jApp::config()->jquery['jquery'].'\',jelixWWWPath:\''.jApp::config()->urlengine['jelixWWWPath'].'\'};
jFormsJQ.tForm = new jFormsJQForm(\'jforms_formtest1\',\'formtest\',\'0\');
jFormsJQ.tForm.setErrorDecorator(new jFormsJQErrorDecoratorHtml());
jFormsJQ.declareForm(jFormsJQ.tForm);
//]]>
</script><div class="jforms-hiddens"><input type="hidden" name="foo" value="b&gt;ar"/>
<input type="hidden" name="hidden1" id="'.self::$formname.'_hidden1" value="11"/>
<input type="hidden" name="hidden2" id="'.self::$formname.'_hidden2" value=""/>
</div>';
        $this->assertEquals($result, $out);

        $ctrl->defaultValue='toto';
        self::$form->removeControl($ctrl->ref);
        self::$form->addControl($ctrl);
        ob_start();
        self::$builder->setOptions(array('method'=>'post'));
        self::$builder->outputHeader();
        $out = ob_get_clean();
        $result ='<form action="'.jApp::urlBasePath().'index.php/jelix_tests/urlsig/url1" method="post" id="'.self::$formname.'"><script type="text/javascript">
//<![CDATA[
jFormsJQ.selectFillUrl=\''.jApp::urlBasePath().'index.php/jelix/jforms/getListData\';
jFormsJQ.config = {locale:\''.jApp::config()->locale.'\',basePath:\''.jApp::urlBasePath().'\',jqueryPath:\''.jApp::config()->urlengine['jqueryPath'].'\',jqueryFile:\''.jApp::config()->jquery['jquery'].'\',jelixWWWPath:\''.jApp::config()->urlengine['jelixWWWPath'].'\'};
jFormsJQ.tForm = new jFormsJQForm(\'jforms_formtest1\',\'formtest\',\'0\');
jFormsJQ.tForm.setErrorDecorator(new jFormsJQErrorDecoratorHtml());
jFormsJQ.declareForm(jFormsJQ.tForm);
//]]>
</script><div class="jforms-hiddens"><input type="hidden" name="foo" value="b&gt;ar"/>
<input type="hidden" name="hidden1" id="'.self::$formname.'_hidden1" value="11"/>
<input type="hidden" name="hidden2" id="'.self::$formname.'_hidden2" value="toto"/>
</div>';
        $this->assertEquals($result, $out);
    }

    public function testOutputCaptcha()
    {
        $ctrl= new jFormsControlcaptcha('cap');
        $ctrl->label='captcha for security';
        self::$form->addControl($ctrl);

        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<label class="jforms-label jforms-required" for="'.self::$formname.'_cap" id="'.self::$formname.'_cap_label">captcha for security<span class="jforms-required-star">*</span></label>'."\n", $out);

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<span class="jforms-captcha-question">'.htmlspecialchars($ctrl->question).'</span> <input name="cap" id="'.self::$formname.'_cap" class="jforms-ctrl-captcha jforms-required" type="text" value=""/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlString(\'cap\', \'captcha for security\');
c.required = true;
c.errRequired=\'"captcha for security" field is required\';
c.errInvalid=\'"captcha for security" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());

        self::$form->setData('cap', 'toto');
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<span class="jforms-captcha-question">'.htmlspecialchars($ctrl->question).'</span> <input name="cap" id="'.self::$formname.'_cap" class="jforms-ctrl-captcha jforms-required" type="text" value=""/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlString(\'cap\', \'captcha for security\');
c.required = true;
c.errRequired=\'"captcha for security" field is required\';
c.errInvalid=\'"captcha for security" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());

        $ctrl->setReadOnly(true);
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<span class="jforms-captcha-question">'.htmlspecialchars($ctrl->question).'</span> <input name="cap" id="'.self::$formname.'_cap" class="jforms-ctrl-captcha" type="text" value=""/>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlString(\'cap\', \'captcha for security\');
c.readOnly = true;
c.required = true;
c.errRequired=\'"captcha for security" field is required\';
c.errInvalid=\'"captcha for security" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());

        $ctrl->setReadOnly(false);
        $ctrl->help='some help';
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<span class="jforms-captcha-question">'.htmlspecialchars($ctrl->question).'</span> <input name="cap" id="'.self::$formname.'_cap" class="jforms-ctrl-captcha jforms-required" type="text" value=""/>'."\n".'<span class="jforms-help" id="jforms_formtest1_cap-help">&nbsp;<span>some help</span></span>', $out);
        $this->assertEquals('c = new jFormsJQControlString(\'cap\', \'captcha for security\');
c.required = true;
c.errRequired=\'"captcha for security" field is required\';
c.errInvalid=\'"captcha for security" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());

        $ctrl->hint='ceci est un tooltip';
        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<label class="jforms-label jforms-required" for="'.self::$formname.'_cap" id="'.self::$formname.'_cap_label" title="ceci est un tooltip">captcha for security<span class="jforms-required-star">*</span></label>'."\n", $out);

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<span class="jforms-captcha-question">'.htmlspecialchars($ctrl->question).'</span> <input name="cap" id="'.self::$formname.'_cap" title="ceci est un tooltip" class="jforms-ctrl-captcha jforms-required" type="text" value=""/>'."\n".'<span class="jforms-help" id="jforms_formtest1_cap-help">&nbsp;<span>some help</span></span>', $out);
        $this->assertEquals('c = new jFormsJQControlString(\'cap\', \'captcha for security\');
c.required = true;
c.errRequired=\'"captcha for security" field is required\';
c.errInvalid=\'"captcha for security" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());
    }

    public function testOutputHtmleditor()
    {
        $ctrl= new jFormsControlhtmleditor('contenu');
        $ctrl->label='Texte';
        self::$form->addControl($ctrl);

        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<label class="jforms-label" for="'.self::$formname.'_contenu" id="'.self::$formname.'_contenu_label">Texte</label>'."\n", $out);

        self::$form->setData('contenu', '<p>Ceci est un contenu</p>');

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<textarea name="contenu" id="'.self::$formname.'_contenu" class="jforms-ctrl-htmleditor" rows="5" cols="40">&lt;p&gt;Ceci est un contenu&lt;/p&gt;</textarea>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlHtml(\'contenu\', \'Texte\');
c.errRequired=\'"Texte" field is required\';
c.errInvalid=\'"Texte" field is invalid\';
jFormsJQ.tForm.addControl(c);
jelix_wymeditor_default("jforms_formtest1_contenu","jforms_formtest1","default",jFormsJQ.config);
', self::$builder->getJsContent());

        $ctrl->setReadOnly(true);
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<textarea name="contenu" id="'.self::$formname.'_contenu" readonly="readonly" class="jforms-ctrl-htmleditor jforms-readonly" rows="5" cols="40">&lt;p&gt;Ceci est un contenu&lt;/p&gt;</textarea>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlHtml(\'contenu\', \'Texte\');
c.readOnly = true;
c.errRequired=\'"Texte" field is required\';
c.errInvalid=\'"Texte" field is invalid\';
jFormsJQ.tForm.addControl(c);
jelix_wymeditor_default("jforms_formtest1_contenu","jforms_formtest1","default",jFormsJQ.config);
', self::$builder->getJsContent());

        $ctrl->hint='ceci est un tooltip';
        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<label class="jforms-label" for="'.self::$formname.'_contenu" id="'.self::$formname.'_contenu_label" title="ceci est un tooltip">Texte</label>'."\n", $out);

        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<textarea name="contenu" id="'.self::$formname.'_contenu" readonly="readonly" title="ceci est un tooltip" class="jforms-ctrl-htmleditor jforms-readonly" rows="5" cols="40">&lt;p&gt;Ceci est un contenu&lt;/p&gt;</textarea>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlHtml(\'contenu\', \'Texte\');
c.readOnly = true;
c.errRequired=\'"Texte" field is required\';
c.errInvalid=\'"Texte" field is invalid\';
jFormsJQ.tForm.addControl(c);
jelix_wymeditor_default("jforms_formtest1_contenu","jforms_formtest1","default",jFormsJQ.config);
', self::$builder->getJsContent());


        $ctrl->rows=20;
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<textarea name="contenu" id="'.self::$formname.'_contenu" readonly="readonly" title="ceci est un tooltip" class="jforms-ctrl-htmleditor jforms-readonly" rows="20" cols="40">&lt;p&gt;Ceci est un contenu&lt;/p&gt;</textarea>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlHtml(\'contenu\', \'Texte\');
c.readOnly = true;
c.errRequired=\'"Texte" field is required\';
c.errInvalid=\'"Texte" field is invalid\';
jFormsJQ.tForm.addControl(c);
jelix_wymeditor_default("jforms_formtest1_contenu","jforms_formtest1","default",jFormsJQ.config);
', self::$builder->getJsContent());


        $ctrl->cols=60;
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<textarea name="contenu" id="'.self::$formname.'_contenu" readonly="readonly" title="ceci est un tooltip" class="jforms-ctrl-htmleditor jforms-readonly" rows="20" cols="60">&lt;p&gt;Ceci est un contenu&lt;/p&gt;</textarea>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlHtml(\'contenu\', \'Texte\');
c.readOnly = true;
c.errRequired=\'"Texte" field is required\';
c.errInvalid=\'"Texte" field is invalid\';
jFormsJQ.tForm.addControl(c);
jelix_wymeditor_default("jforms_formtest1_contenu","jforms_formtest1","default",jFormsJQ.config);
', self::$builder->getJsContent());

        $ctrl->required=true;
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<textarea name="contenu" id="'.self::$formname.'_contenu" readonly="readonly" title="ceci est un tooltip" class="jforms-ctrl-htmleditor jforms-readonly" rows="20" cols="60">&lt;p&gt;Ceci est un contenu&lt;/p&gt;</textarea>'."\n", $out);
        $this->assertEquals('c = new jFormsJQControlHtml(\'contenu\', \'Texte\');
c.readOnly = true;
c.required = true;
c.errRequired=\'"Texte" field is required\';
c.errInvalid=\'"Texte" field is invalid\';
jFormsJQ.tForm.addControl(c);
jelix_wymeditor_default("jforms_formtest1_contenu","jforms_formtest1","default",jFormsJQ.config);
', self::$builder->getJsContent());
    }

    public function testOutputDate()
    {
        $ctrl = new jFormsControlDate('date1');
        $ctrl->datatype = new jDatatypeDate();
        $ctrl->label = 'mydate';
        self::$form->addControl($ctrl);

        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<span class="jforms-label" id="' . self::$formname . '_date1_label">mydate</span>' . "\n", $out);

        // empty value
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<select name="date1[month]" id="' . self::$formname . '_date1_month" class="jforms-ctrl-date">'.
                '<option value="">Month</option>'.
            '<option value="01">January</option><option value="02">February</option>'.
            '<option value="03">March</option><option value="04">April</option>'.
            '<option value="05">May</option><option value="06">June</option>'.
            '<option value="07">July</option><option value="08">August</option>'.
            '<option value="09">September</option><option value="10">October</option>'.
            '<option value="11">November</option><option value="12">December</option>'.
            '</select> '.
            '<select name="date1[day]" id="' . self::$formname . '_date1_day" class="jforms-ctrl-date">'.
            '<option value="">Day</option><option value="01">01</option>'.
            '<option value="02">02</option><option value="03">03</option>'.
            '<option value="04">04</option><option value="05">05</option>'.
            '<option value="06">06</option><option value="07">07</option>'.
            '<option value="08">08</option><option value="09">09</option>'.
            '<option value="10">10</option><option value="11">11</option>'.
            '<option value="12">12</option><option value="13">13</option>'.
            '<option value="14">14</option><option value="15">15</option>'.
            '<option value="16">16</option><option value="17">17</option>'.
            '<option value="18">18</option><option value="19">19</option>'.
            '<option value="20">20</option><option value="21">21</option>'.
            '<option value="22">22</option><option value="23">23</option>'.
            '<option value="24">24</option><option value="25">25</option>'.
            '<option value="26">26</option><option value="27">27</option>'.
            '<option value="28">28</option><option value="29">29</option>'.
            '<option value="30">30</option><option value="31">31</option>'.
            '</select> '.
            '<input type="text" size="4" maxlength="4" name="date1[year]" '.
            'id="' . self::$formname . '_date1_year" class="jforms-ctrl-date" value=""/>'
            . "\n", $out);
        $this->assertEquals('c = new jFormsJQControlDate(\'date1\', \'mydate\');
c.multiFields = true;
c.errRequired=\'"mydate" field is required\';
c.errInvalid=\'"mydate" field is invalid\';
jFormsJQ.tForm.addControl(c);
jelix_datepicker_default(c, jFormsJQ.config);
', self::$builder->getJsContent());

        // simple date
        self::$form->setData('date1', '2019-07-24');
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<select name="date1[month]" id="' . self::$formname . '_date1_month" class="jforms-ctrl-date">'.
            '<option value="">Month</option>'.
            '<option value="01">January</option><option value="02">February</option>'.
            '<option value="03">March</option><option value="04">April</option>'.
            '<option value="05">May</option><option value="06">June</option>'.
            '<option value="07" selected="selected">July</option><option value="08">August</option>'.
            '<option value="09">September</option><option value="10">October</option>'.
            '<option value="11">November</option><option value="12">December</option>'.
            '</select> '.
            '<select name="date1[day]" id="' . self::$formname . '_date1_day" class="jforms-ctrl-date">'.
            '<option value="">Day</option><option value="01">01</option>'.
            '<option value="02">02</option><option value="03">03</option>'.
            '<option value="04">04</option><option value="05">05</option>'.
            '<option value="06">06</option><option value="07">07</option>'.
            '<option value="08">08</option><option value="09">09</option>'.
            '<option value="10">10</option><option value="11">11</option>'.
            '<option value="12">12</option><option value="13">13</option>'.
            '<option value="14">14</option><option value="15">15</option>'.
            '<option value="16">16</option><option value="17">17</option>'.
            '<option value="18">18</option><option value="19">19</option>'.
            '<option value="20">20</option><option value="21">21</option>'.
            '<option value="22">22</option><option value="23">23</option>'.
            '<option value="24" selected="selected">24</option><option value="25">25</option>'.
            '<option value="26">26</option><option value="27">27</option>'.
            '<option value="28">28</option><option value="29">29</option>'.
            '<option value="30">30</option><option value="31">31</option>'.
            '</select> '.
            '<input type="text" size="4" maxlength="4" name="date1[year]" '.
            'id="' . self::$formname . '_date1_year" class="jforms-ctrl-date" value="2019"/>'
            . "\n", $out);
        $this->assertEquals('c = new jFormsJQControlDate(\'date1\', \'mydate\');
c.multiFields = true;
c.errRequired=\'"mydate" field is required\';
c.errInvalid=\'"mydate" field is invalid\';
jFormsJQ.tForm.addControl(c);
jelix_datepicker_default(c, jFormsJQ.config);
', self::$builder->getJsContent());

        // full date time
        self::$form->setData('date1', '2019-07-24 15:03:27');
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<select name="date1[month]" id="' . self::$formname . '_date1_month" class="jforms-ctrl-date">'.
            '<option value="">Month</option>'.
            '<option value="01">January</option><option value="02">February</option>'.
            '<option value="03">March</option><option value="04">April</option>'.
            '<option value="05">May</option><option value="06">June</option>'.
            '<option value="07" selected="selected">July</option><option value="08">August</option>'.
            '<option value="09">September</option><option value="10">October</option>'.
            '<option value="11">November</option><option value="12">December</option>'.
            '</select> '.
            '<select name="date1[day]" id="' . self::$formname . '_date1_day" class="jforms-ctrl-date">'.
            '<option value="">Day</option><option value="01">01</option>'.
            '<option value="02">02</option><option value="03">03</option>'.
            '<option value="04">04</option><option value="05">05</option>'.
            '<option value="06">06</option><option value="07">07</option>'.
            '<option value="08">08</option><option value="09">09</option>'.
            '<option value="10">10</option><option value="11">11</option>'.
            '<option value="12">12</option><option value="13">13</option>'.
            '<option value="14">14</option><option value="15">15</option>'.
            '<option value="16">16</option><option value="17">17</option>'.
            '<option value="18">18</option><option value="19">19</option>'.
            '<option value="20">20</option><option value="21">21</option>'.
            '<option value="22">22</option><option value="23">23</option>'.
            '<option value="24" selected="selected">24</option><option value="25">25</option>'.
            '<option value="26">26</option><option value="27">27</option>'.
            '<option value="28">28</option><option value="29">29</option>'.
            '<option value="30">30</option><option value="31">31</option>'.
            '</select> '.
            '<input type="text" size="4" maxlength="4" name="date1[year]" '.
            'id="' . self::$formname . '_date1_year" class="jforms-ctrl-date" value="2019"/>'
            . "\n", $out);
        $this->assertEquals('c = new jFormsJQControlDate(\'date1\', \'mydate\');
c.multiFields = true;
c.errRequired=\'"mydate" field is required\';
c.errInvalid=\'"mydate" field is invalid\';
jFormsJQ.tForm.addControl(c);
jelix_datepicker_default(c, jFormsJQ.config);
', self::$builder->getJsContent());

        // full date time
        self::$form->setData('date1', '2019-07-24T15:03:27.123465');
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<select name="date1[month]" id="' . self::$formname . '_date1_month" class="jforms-ctrl-date">'.
            '<option value="">Month</option>'.
            '<option value="01">January</option><option value="02">February</option>'.
            '<option value="03">March</option><option value="04">April</option>'.
            '<option value="05">May</option><option value="06">June</option>'.
            '<option value="07" selected="selected">July</option><option value="08">August</option>'.
            '<option value="09">September</option><option value="10">October</option>'.
            '<option value="11">November</option><option value="12">December</option>'.
            '</select> '.
            '<select name="date1[day]" id="' . self::$formname . '_date1_day" class="jforms-ctrl-date">'.
            '<option value="">Day</option><option value="01">01</option>'.
            '<option value="02">02</option><option value="03">03</option>'.
            '<option value="04">04</option><option value="05">05</option>'.
            '<option value="06">06</option><option value="07">07</option>'.
            '<option value="08">08</option><option value="09">09</option>'.
            '<option value="10">10</option><option value="11">11</option>'.
            '<option value="12">12</option><option value="13">13</option>'.
            '<option value="14">14</option><option value="15">15</option>'.
            '<option value="16">16</option><option value="17">17</option>'.
            '<option value="18">18</option><option value="19">19</option>'.
            '<option value="20">20</option><option value="21">21</option>'.
            '<option value="22">22</option><option value="23">23</option>'.
            '<option value="24" selected="selected">24</option><option value="25">25</option>'.
            '<option value="26">26</option><option value="27">27</option>'.
            '<option value="28">28</option><option value="29">29</option>'.
            '<option value="30">30</option><option value="31">31</option>'.
            '</select> '.
            '<input type="text" size="4" maxlength="4" name="date1[year]" '.
            'id="' . self::$formname . '_date1_year" class="jforms-ctrl-date" value="2019"/>'
            . "\n", $out);
        $this->assertEquals('c = new jFormsJQControlDate(\'date1\', \'mydate\');
c.multiFields = true;
c.errRequired=\'"mydate" field is required\';
c.errInvalid=\'"mydate" field is invalid\';
jFormsJQ.tForm.addControl(c);
jelix_datepicker_default(c, jFormsJQ.config);
', self::$builder->getJsContent());
    }

    public function testOutputDateTime()
    {
        $ctrl = new jFormsControlDatetime('date2');
        $ctrl->datatype = new jDatatypeDateTime();
        $ctrl->label = 'mydate';
        self::$form->addControl($ctrl);

        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<span class="jforms-label" id="' . self::$formname . '_date2_label">mydate</span>' . "\n", $out);

        // empty value
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<select name="date2[month]" id="' . self::$formname . '_date2_month" class="jforms-ctrl-datetime">'.
                '<option value="">Month</option>'.
            '<option value="01">January</option><option value="02">February</option>'.
            '<option value="03">March</option><option value="04">April</option>'.
            '<option value="05">May</option><option value="06">June</option>'.
            '<option value="07">July</option><option value="08">August</option>'.
            '<option value="09">September</option><option value="10">October</option>'.
            '<option value="11">November</option><option value="12">December</option>'.
            '</select> '.
            '<select name="date2[day]" id="' . self::$formname . '_date2_day" class="jforms-ctrl-datetime">'.
            '<option value="">Day</option><option value="01">01</option>'.
            '<option value="02">02</option><option value="03">03</option>'.
            '<option value="04">04</option><option value="05">05</option>'.
            '<option value="06">06</option><option value="07">07</option>'.
            '<option value="08">08</option><option value="09">09</option>'.
            '<option value="10">10</option><option value="11">11</option>'.
            '<option value="12">12</option><option value="13">13</option>'.
            '<option value="14">14</option><option value="15">15</option>'.
            '<option value="16">16</option><option value="17">17</option>'.
            '<option value="18">18</option><option value="19">19</option>'.
            '<option value="20">20</option><option value="21">21</option>'.
            '<option value="22">22</option><option value="23">23</option>'.
            '<option value="24">24</option><option value="25">25</option>'.
            '<option value="26">26</option><option value="27">27</option>'.
            '<option value="28">28</option><option value="29">29</option>'.
            '<option value="30">30</option><option value="31">31</option>'.
            '</select> '.
            '<input type="text" size="4" maxlength="4" name="date2[year]" '.
            'id="' . self::$formname . '_date2_year" class="jforms-ctrl-datetime" value=""/> '.
            '<select name="date2[hour]" id="' . self::$formname . '_date2_hour" class="jforms-ctrl-datetime">'.
            '<option value="">Hour</option><option value="00">00</option>'.
            '<option value="01">01</option><option value="02">02</option>'.
            '<option value="03">03</option><option value="04">04</option>'.
            '<option value="05">05</option><option value="06">06</option>'.
            '<option value="07">07</option><option value="08">08</option>'.
            '<option value="09">09</option><option value="10">10</option>'.
            '<option value="11">11</option><option value="12">12</option>'.
            '<option value="13">13</option><option value="14">14</option>'.
            '<option value="15">15</option><option value="16">16</option>'.
            '<option value="17">17</option><option value="18">18</option>'.
            '<option value="19">19</option><option value="20">20</option>'.
            '<option value="21">21</option><option value="22">22</option>'.
            '<option value="23">23</option></select> '.
            '<select name="date2[minutes]" id="' . self::$formname . '_date2_minutes" class="jforms-ctrl-datetime">'.
            '<option value="">Minutes</option><option value="00">00</option>'.
            '<option value="01">01</option><option value="02">02</option>'.
            '<option value="03">03</option><option value="04">04</option>'.
            '<option value="05">05</option><option value="06">06</option>'.
            '<option value="07">07</option><option value="08">08</option>'.
            '<option value="09">09</option><option value="10">10</option>'.
            '<option value="11">11</option><option value="12">12</option>'.
            '<option value="13">13</option><option value="14">14</option>'.
            '<option value="15">15</option><option value="16">16</option>'.
            '<option value="17">17</option><option value="18">18</option>'.
            '<option value="19">19</option><option value="20">20</option>'.
            '<option value="21">21</option><option value="22">22</option>'.
            '<option value="23">23</option><option value="24">24</option>'.
            '<option value="25">25</option><option value="26">26</option>'.
            '<option value="27">27</option><option value="28">28</option>'.
            '<option value="29">29</option><option value="30">30</option>'.
            '<option value="31">31</option><option value="32">32</option>'.
            '<option value="33">33</option><option value="34">34</option>'.
            '<option value="35">35</option><option value="36">36</option>'.
            '<option value="37">37</option><option value="38">38</option>'.
            '<option value="39">39</option><option value="40">40</option>'.
            '<option value="41">41</option><option value="42">42</option>'.
            '<option value="43">43</option><option value="44">44</option>'.
            '<option value="45">45</option><option value="46">46</option>'.
            '<option value="47">47</option><option value="48">48</option>'.
            '<option value="49">49</option><option value="50">50</option>'.
            '<option value="51">51</option><option value="52">52</option>'.
            '<option value="53">53</option><option value="54">54</option>'.
            '<option value="55">55</option><option value="56">56</option>'.
            '<option value="57">57</option><option value="58">58</option>'.
            '<option value="59">59</option></select> '.
            '<input type="hidden" id="' . self::$formname . '_date2_seconds" name="date2[seconds]" value=""/>'
            . "\n", $out);
        $this->assertEquals('c = new jFormsJQControlDatetime(\'date2\', \'mydate\');
c.multiFields = true;
c.errRequired=\'"mydate" field is required\';
c.errInvalid=\'"mydate" field is invalid\';
jFormsJQ.tForm.addControl(c);
jelix_datepicker_default(c, jFormsJQ.config);
', self::$builder->getJsContent());

        // simple date
        self::$form->setData('date2', '2019-07-24');
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<select name="date2[month]" id="' . self::$formname . '_date2_month" class="jforms-ctrl-datetime">'.
            '<option value="">Month</option>'.
            '<option value="01">January</option><option value="02">February</option>'.
            '<option value="03">March</option><option value="04">April</option>'.
            '<option value="05">May</option><option value="06">June</option>'.
            '<option value="07" selected="selected">July</option><option value="08">August</option>'.
            '<option value="09">September</option><option value="10">October</option>'.
            '<option value="11">November</option><option value="12">December</option>'.
            '</select> '.
            '<select name="date2[day]" id="' . self::$formname . '_date2_day" class="jforms-ctrl-datetime">'.
            '<option value="">Day</option><option value="01">01</option>'.
            '<option value="02">02</option><option value="03">03</option>'.
            '<option value="04">04</option><option value="05">05</option>'.
            '<option value="06">06</option><option value="07">07</option>'.
            '<option value="08">08</option><option value="09">09</option>'.
            '<option value="10">10</option><option value="11">11</option>'.
            '<option value="12">12</option><option value="13">13</option>'.
            '<option value="14">14</option><option value="15">15</option>'.
            '<option value="16">16</option><option value="17">17</option>'.
            '<option value="18">18</option><option value="19">19</option>'.
            '<option value="20">20</option><option value="21">21</option>'.
            '<option value="22">22</option><option value="23">23</option>'.
            '<option value="24" selected="selected">24</option><option value="25">25</option>'.
            '<option value="26">26</option><option value="27">27</option>'.
            '<option value="28">28</option><option value="29">29</option>'.
            '<option value="30">30</option><option value="31">31</option>'.
            '</select> '.
            '<input type="text" size="4" maxlength="4" name="date2[year]" '.
            'id="' . self::$formname . '_date2_year" class="jforms-ctrl-datetime" value="2019"/> '.
            '<select name="date2[hour]" id="' . self::$formname . '_date2_hour" class="jforms-ctrl-datetime">'.
            '<option value="">Hour</option><option value="00" selected="selected">00</option>'.
            '<option value="01">01</option><option value="02">02</option>'.
            '<option value="03">03</option><option value="04">04</option>'.
            '<option value="05">05</option><option value="06">06</option>'.
            '<option value="07">07</option><option value="08">08</option>'.
            '<option value="09">09</option><option value="10">10</option>'.
            '<option value="11">11</option><option value="12">12</option>'.
            '<option value="13">13</option><option value="14">14</option>'.
            '<option value="15">15</option><option value="16">16</option>'.
            '<option value="17">17</option><option value="18">18</option>'.
            '<option value="19">19</option><option value="20">20</option>'.
            '<option value="21">21</option><option value="22">22</option>'.
            '<option value="23">23</option></select> '.
            '<select name="date2[minutes]" id="' . self::$formname . '_date2_minutes" class="jforms-ctrl-datetime">'.
            '<option value="">Minutes</option><option value="00" selected="selected">00</option>'.
            '<option value="01">01</option><option value="02">02</option>'.
            '<option value="03">03</option><option value="04">04</option>'.
            '<option value="05">05</option><option value="06">06</option>'.
            '<option value="07">07</option><option value="08">08</option>'.
            '<option value="09">09</option><option value="10">10</option>'.
            '<option value="11">11</option><option value="12">12</option>'.
            '<option value="13">13</option><option value="14">14</option>'.
            '<option value="15">15</option><option value="16">16</option>'.
            '<option value="17">17</option><option value="18">18</option>'.
            '<option value="19">19</option><option value="20">20</option>'.
            '<option value="21">21</option><option value="22">22</option>'.
            '<option value="23">23</option><option value="24">24</option>'.
            '<option value="25">25</option><option value="26">26</option>'.
            '<option value="27">27</option><option value="28">28</option>'.
            '<option value="29">29</option><option value="30">30</option>'.
            '<option value="31">31</option><option value="32">32</option>'.
            '<option value="33">33</option><option value="34">34</option>'.
            '<option value="35">35</option><option value="36">36</option>'.
            '<option value="37">37</option><option value="38">38</option>'.
            '<option value="39">39</option><option value="40">40</option>'.
            '<option value="41">41</option><option value="42">42</option>'.
            '<option value="43">43</option><option value="44">44</option>'.
            '<option value="45">45</option><option value="46">46</option>'.
            '<option value="47">47</option><option value="48">48</option>'.
            '<option value="49">49</option><option value="50">50</option>'.
            '<option value="51">51</option><option value="52">52</option>'.
            '<option value="53">53</option><option value="54">54</option>'.
            '<option value="55">55</option><option value="56">56</option>'.
            '<option value="57">57</option><option value="58">58</option>'.
            '<option value="59">59</option></select> '.
            '<input type="hidden" id="' . self::$formname . '_date2_seconds" name="date2[seconds]" value="00"/>'
            . "\n", $out);
        $this->assertEquals('c = new jFormsJQControlDatetime(\'date2\', \'mydate\');
c.multiFields = true;
c.errRequired=\'"mydate" field is required\';
c.errInvalid=\'"mydate" field is invalid\';
jFormsJQ.tForm.addControl(c);
jelix_datepicker_default(c, jFormsJQ.config);
', self::$builder->getJsContent());

        // full date time
        self::$form->setData('date2', '2019-07-24 15:03:27');
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<select name="date2[month]" id="' . self::$formname . '_date2_month" class="jforms-ctrl-datetime">'.
            '<option value="">Month</option>'.
            '<option value="01">January</option><option value="02">February</option>'.
            '<option value="03">March</option><option value="04">April</option>'.
            '<option value="05">May</option><option value="06">June</option>'.
            '<option value="07" selected="selected">July</option><option value="08">August</option>'.
            '<option value="09">September</option><option value="10">October</option>'.
            '<option value="11">November</option><option value="12">December</option>'.
            '</select> '.
            '<select name="date2[day]" id="' . self::$formname . '_date2_day" class="jforms-ctrl-datetime">'.
            '<option value="">Day</option><option value="01">01</option>'.
            '<option value="02">02</option><option value="03">03</option>'.
            '<option value="04">04</option><option value="05">05</option>'.
            '<option value="06">06</option><option value="07">07</option>'.
            '<option value="08">08</option><option value="09">09</option>'.
            '<option value="10">10</option><option value="11">11</option>'.
            '<option value="12">12</option><option value="13">13</option>'.
            '<option value="14">14</option><option value="15">15</option>'.
            '<option value="16">16</option><option value="17">17</option>'.
            '<option value="18">18</option><option value="19">19</option>'.
            '<option value="20">20</option><option value="21">21</option>'.
            '<option value="22">22</option><option value="23">23</option>'.
            '<option value="24" selected="selected">24</option><option value="25">25</option>'.
            '<option value="26">26</option><option value="27">27</option>'.
            '<option value="28">28</option><option value="29">29</option>'.
            '<option value="30">30</option><option value="31">31</option>'.
            '</select> '.
            '<input type="text" size="4" maxlength="4" name="date2[year]" '.
            'id="' . self::$formname . '_date2_year" class="jforms-ctrl-datetime" value="2019"/> '.
            '<select name="date2[hour]" id="' . self::$formname . '_date2_hour" class="jforms-ctrl-datetime">'.
            '<option value="">Hour</option><option value="00">00</option>'.
            '<option value="01">01</option><option value="02">02</option>'.
            '<option value="03">03</option><option value="04">04</option>'.
            '<option value="05">05</option><option value="06">06</option>'.
            '<option value="07">07</option><option value="08">08</option>'.
            '<option value="09">09</option><option value="10">10</option>'.
            '<option value="11">11</option><option value="12">12</option>'.
            '<option value="13">13</option><option value="14">14</option>'.
            '<option value="15" selected="selected">15</option><option value="16">16</option>'.
            '<option value="17">17</option><option value="18">18</option>'.
            '<option value="19">19</option><option value="20">20</option>'.
            '<option value="21">21</option><option value="22">22</option>'.
            '<option value="23">23</option></select> '.
            '<select name="date2[minutes]" id="' . self::$formname . '_date2_minutes" class="jforms-ctrl-datetime">'.
            '<option value="">Minutes</option><option value="00">00</option>'.
            '<option value="01">01</option><option value="02">02</option>'.
            '<option value="03" selected="selected">03</option><option value="04">04</option>'.
            '<option value="05">05</option><option value="06">06</option>'.
            '<option value="07">07</option><option value="08">08</option>'.
            '<option value="09">09</option><option value="10">10</option>'.
            '<option value="11">11</option><option value="12">12</option>'.
            '<option value="13">13</option><option value="14">14</option>'.
            '<option value="15">15</option><option value="16">16</option>'.
            '<option value="17">17</option><option value="18">18</option>'.
            '<option value="19">19</option><option value="20">20</option>'.
            '<option value="21">21</option><option value="22">22</option>'.
            '<option value="23">23</option><option value="24">24</option>'.
            '<option value="25">25</option><option value="26">26</option>'.
            '<option value="27">27</option><option value="28">28</option>'.
            '<option value="29">29</option><option value="30">30</option>'.
            '<option value="31">31</option><option value="32">32</option>'.
            '<option value="33">33</option><option value="34">34</option>'.
            '<option value="35">35</option><option value="36">36</option>'.
            '<option value="37">37</option><option value="38">38</option>'.
            '<option value="39">39</option><option value="40">40</option>'.
            '<option value="41">41</option><option value="42">42</option>'.
            '<option value="43">43</option><option value="44">44</option>'.
            '<option value="45">45</option><option value="46">46</option>'.
            '<option value="47">47</option><option value="48">48</option>'.
            '<option value="49">49</option><option value="50">50</option>'.
            '<option value="51">51</option><option value="52">52</option>'.
            '<option value="53">53</option><option value="54">54</option>'.
            '<option value="55">55</option><option value="56">56</option>'.
            '<option value="57">57</option><option value="58">58</option>'.
            '<option value="59">59</option></select> '.
            '<input type="hidden" id="' . self::$formname . '_date2_seconds" name="date2[seconds]" value="27"/>'
            . "\n", $out);
        $this->assertEquals('c = new jFormsJQControlDatetime(\'date2\', \'mydate\');
c.multiFields = true;
c.errRequired=\'"mydate" field is required\';
c.errInvalid=\'"mydate" field is invalid\';
jFormsJQ.tForm.addControl(c);
jelix_datepicker_default(c, jFormsJQ.config);
', self::$builder->getJsContent());

        // full date time
        self::$form->setData('date2', '2019-07-24T15:03:27.123465');
        $ctrl->enableSeconds = true;
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<select name="date2[month]" id="' . self::$formname . '_date2_month" class="jforms-ctrl-datetime">'.
            '<option value="">Month</option>'.
            '<option value="01">January</option><option value="02">February</option>'.
            '<option value="03">March</option><option value="04">April</option>'.
            '<option value="05">May</option><option value="06">June</option>'.
            '<option value="07" selected="selected">July</option><option value="08">August</option>'.
            '<option value="09">September</option><option value="10">October</option>'.
            '<option value="11">November</option><option value="12">December</option>'.
            '</select> '.
            '<select name="date2[day]" id="' . self::$formname . '_date2_day" class="jforms-ctrl-datetime">'.
            '<option value="">Day</option><option value="01">01</option>'.
            '<option value="02">02</option><option value="03">03</option>'.
            '<option value="04">04</option><option value="05">05</option>'.
            '<option value="06">06</option><option value="07">07</option>'.
            '<option value="08">08</option><option value="09">09</option>'.
            '<option value="10">10</option><option value="11">11</option>'.
            '<option value="12">12</option><option value="13">13</option>'.
            '<option value="14">14</option><option value="15">15</option>'.
            '<option value="16">16</option><option value="17">17</option>'.
            '<option value="18">18</option><option value="19">19</option>'.
            '<option value="20">20</option><option value="21">21</option>'.
            '<option value="22">22</option><option value="23">23</option>'.
            '<option value="24" selected="selected">24</option><option value="25">25</option>'.
            '<option value="26">26</option><option value="27">27</option>'.
            '<option value="28">28</option><option value="29">29</option>'.
            '<option value="30">30</option><option value="31">31</option>'.
            '</select> '.
            '<input type="text" size="4" maxlength="4" name="date2[year]" '.
            'id="' . self::$formname . '_date2_year" class="jforms-ctrl-datetime" value="2019"/> '.
            '<select name="date2[hour]" id="' . self::$formname . '_date2_hour" class="jforms-ctrl-datetime">'.
            '<option value="">Hour</option><option value="00">00</option>'.
            '<option value="01">01</option><option value="02">02</option>'.
            '<option value="03">03</option><option value="04">04</option>'.
            '<option value="05">05</option><option value="06">06</option>'.
            '<option value="07">07</option><option value="08">08</option>'.
            '<option value="09">09</option><option value="10">10</option>'.
            '<option value="11">11</option><option value="12">12</option>'.
            '<option value="13">13</option><option value="14">14</option>'.
            '<option value="15" selected="selected">15</option><option value="16">16</option>'.
            '<option value="17">17</option><option value="18">18</option>'.
            '<option value="19">19</option><option value="20">20</option>'.
            '<option value="21">21</option><option value="22">22</option>'.
            '<option value="23">23</option></select> '.
            '<select name="date2[minutes]" id="' . self::$formname . '_date2_minutes" class="jforms-ctrl-datetime">'.
            '<option value="">Minutes</option><option value="00">00</option>'.
            '<option value="01">01</option><option value="02">02</option>'.
            '<option value="03" selected="selected">03</option><option value="04">04</option>'.
            '<option value="05">05</option><option value="06">06</option>'.
            '<option value="07">07</option><option value="08">08</option>'.
            '<option value="09">09</option><option value="10">10</option>'.
            '<option value="11">11</option><option value="12">12</option>'.
            '<option value="13">13</option><option value="14">14</option>'.
            '<option value="15">15</option><option value="16">16</option>'.
            '<option value="17">17</option><option value="18">18</option>'.
            '<option value="19">19</option><option value="20">20</option>'.
            '<option value="21">21</option><option value="22">22</option>'.
            '<option value="23">23</option><option value="24">24</option>'.
            '<option value="25">25</option><option value="26">26</option>'.
            '<option value="27">27</option><option value="28">28</option>'.
            '<option value="29">29</option><option value="30">30</option>'.
            '<option value="31">31</option><option value="32">32</option>'.
            '<option value="33">33</option><option value="34">34</option>'.
            '<option value="35">35</option><option value="36">36</option>'.
            '<option value="37">37</option><option value="38">38</option>'.
            '<option value="39">39</option><option value="40">40</option>'.
            '<option value="41">41</option><option value="42">42</option>'.
            '<option value="43">43</option><option value="44">44</option>'.
            '<option value="45">45</option><option value="46">46</option>'.
            '<option value="47">47</option><option value="48">48</option>'.
            '<option value="49">49</option><option value="50">50</option>'.
            '<option value="51">51</option><option value="52">52</option>'.
            '<option value="53">53</option><option value="54">54</option>'.
            '<option value="55">55</option><option value="56">56</option>'.
            '<option value="57">57</option><option value="58">58</option>'.
            '<option value="59">59</option></select> '.
            '<select name="date2[seconds]" id="' . self::$formname . '_date2_seconds" class="jforms-ctrl-datetime">'.
            '<option value="">Seconds</option>'.
            '<option value="00">00</option><option value="01">01</option>'.
            '<option value="02">02</option><option value="03">03</option>'.
            '<option value="04">04</option><option value="05">05</option>'.
            '<option value="06">06</option><option value="07">07</option>'.
            '<option value="08">08</option><option value="09">09</option>'.
            '<option value="10">10</option><option value="11">11</option>'.
            '<option value="12">12</option><option value="13">13</option>'.
            '<option value="14">14</option><option value="15">15</option>'.
            '<option value="16">16</option><option value="17">17</option>'.
            '<option value="18">18</option><option value="19">19</option>'.
            '<option value="20">20</option><option value="21">21</option>'.
            '<option value="22">22</option><option value="23">23</option>'.
            '<option value="24">24</option><option value="25">25</option>'.
            '<option value="26">26</option><option value="27" selected="selected">27</option>'.
            '<option value="28">28</option><option value="29">29</option>'.
            '<option value="30">30</option><option value="31">31</option>'.
            '<option value="32">32</option><option value="33">33</option>'.
            '<option value="34">34</option><option value="35">35</option>'.
            '<option value="36">36</option><option value="37">37</option>'.
            '<option value="38">38</option><option value="39">39</option>'.
            '<option value="40">40</option><option value="41">41</option>'.
            '<option value="42">42</option><option value="43">43</option>'.
            '<option value="44">44</option><option value="45">45</option>'.
            '<option value="46">46</option><option value="47">47</option>'.
            '<option value="48">48</option><option value="49">49</option>'.
            '<option value="50">50</option><option value="51">51</option>'.
            '<option value="52">52</option><option value="53">53</option>'.
            '<option value="54">54</option><option value="55">55</option>'.
            '<option value="56">56</option><option value="57">57</option>'.
            '<option value="58">58</option><option value="59">59</option>'.
            '</select>'.
            PHP_EOL, $out);

        $this->assertEquals('c = new jFormsJQControlDatetime(\'date2\', \'mydate\');
c.multiFields = true;
c.errRequired=\'"mydate" field is required\';
c.errInvalid=\'"mydate" field is invalid\';
jFormsJQ.tForm.addControl(c);
jelix_datepicker_default(c, jFormsJQ.config);
', self::$builder->getJsContent());
    }

    public function testOutputTime()
    {
        $ctrl = new jFormsControlTime('time1');
        $ctrl->datatype = new jDatatypeTime();
        $ctrl->label = 'mytime';
        self::$form->addControl($ctrl);

        ob_start();
        self::$builder->outputControlLabel($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<span class="jforms-label" id="' . self::$formname . '_time1_label">mytime</span>' . "\n", $out);

        // empty value
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<select name="time1[hour]" id="' . self::$formname . '_time1_hour" class="jforms-ctrl-time">'.
                '<option value="">Hour</option>'.
                '<option value="00">00</option><option value="01">01</option>'.
                '<option value="02">02</option><option value="03">03</option>'.
                '<option value="04">04</option><option value="05">05</option>'.
                '<option value="06">06</option><option value="07">07</option>'.
                '<option value="08">08</option><option value="09">09</option>'.
                '<option value="10">10</option><option value="11">11</option>'.
                '<option value="12">12</option><option value="13">13</option>'.
                '<option value="14">14</option><option value="15">15</option>'.
                '<option value="16">16</option><option value="17">17</option>'.
                '<option value="18">18</option><option value="19">19</option>'.
                '<option value="20">20</option><option value="21">21</option>'.
                '<option value="22">22</option><option value="23">23</option>'.
            '</select> '.
            '<select name="time1[minutes]" id="' . self::$formname . '_time1_minutes" class="jforms-ctrl-time">'.
                '<option value="">Minutes</option>'.
                '<option value="00">00</option><option value="01">01</option>'.
                '<option value="02">02</option><option value="03">03</option>'.
                '<option value="04">04</option><option value="05">05</option>'.
                '<option value="06">06</option><option value="07">07</option>'.
                '<option value="08">08</option><option value="09">09</option>'.
                '<option value="10">10</option><option value="11">11</option>'.
                '<option value="12">12</option><option value="13">13</option>'.
                '<option value="14">14</option><option value="15">15</option>'.
                '<option value="16">16</option><option value="17">17</option>'.
                '<option value="18">18</option><option value="19">19</option>'.
                '<option value="20">20</option><option value="21">21</option>'.
                '<option value="22">22</option><option value="23">23</option>'.
                '<option value="24">24</option><option value="25">25</option>'.
                '<option value="26">26</option><option value="27">27</option>'.
                '<option value="28">28</option><option value="29">29</option>'.
                '<option value="30">30</option><option value="31">31</option>'.
                '<option value="32">32</option><option value="33">33</option>'.
                '<option value="34">34</option><option value="35">35</option>'.
                '<option value="36">36</option><option value="37">37</option>'.
                '<option value="38">38</option><option value="39">39</option>'.
                '<option value="40">40</option><option value="41">41</option>'.
                '<option value="42">42</option><option value="43">43</option>'.
                '<option value="44">44</option><option value="45">45</option>'.
                '<option value="46">46</option><option value="47">47</option>'.
                '<option value="48">48</option><option value="49">49</option>'.
                '<option value="50">50</option><option value="51">51</option>'.
                '<option value="52">52</option><option value="53">53</option>'.
                '<option value="54">54</option><option value="55">55</option>'.
                '<option value="56">56</option><option value="57">57</option>'.
                '<option value="58">58</option><option value="59">59</option>'.
            '</select> '.
            '<input type="hidden" id="' . self::$formname . '_time1_seconds" name="time1[seconds]" value=""/>'
            . PHP_EOL, $out);

        $this->assertEquals('c = new jFormsJQControlTime2(\'time1\', \'mytime\');
c.multiFields = true;
c.errRequired=\'"mytime" field is required\';
c.errInvalid=\'"mytime" field is invalid\';
jFormsJQ.tForm.addControl(c);
', self::$builder->getJsContent());

        // simple time
        self::$form->setData('time1', '13:41:00');
        $ctrl->enableSeconds = true;
        $ctrl->timepickerConfig = 'foo';
        ob_start();
        self::$builder->outputControl($ctrl);
        $out = ob_get_clean();
        $this->assertEquals('<select name="time1[hour]" id="' . self::$formname . '_time1_hour" class="jforms-ctrl-time">'.
        '<option value="">Hour</option>'.
        '<option value="00">00</option><option value="01">01</option>'.
        '<option value="02">02</option><option value="03">03</option>'.
        '<option value="04">04</option><option value="05">05</option>'.
        '<option value="06">06</option><option value="07">07</option>'.
        '<option value="08">08</option><option value="09">09</option>'.
        '<option value="10">10</option><option value="11">11</option>'.
        '<option value="12">12</option><option value="13" selected="selected">13</option>'.
        '<option value="14">14</option><option value="15">15</option>'.
        '<option value="16">16</option><option value="17">17</option>'.
        '<option value="18">18</option><option value="19">19</option>'.
        '<option value="20">20</option><option value="21">21</option>'.
        '<option value="22">22</option><option value="23">23</option>'.
    '</select> '.
    '<select name="time1[minutes]" id="' . self::$formname . '_time1_minutes" class="jforms-ctrl-time">'.
        '<option value="">Minutes</option>'.
        '<option value="00">00</option><option value="01">01</option>'.
        '<option value="02">02</option><option value="03">03</option>'.
        '<option value="04">04</option><option value="05">05</option>'.
        '<option value="06">06</option><option value="07">07</option>'.
        '<option value="08">08</option><option value="09">09</option>'.
        '<option value="10">10</option><option value="11">11</option>'.
        '<option value="12">12</option><option value="13">13</option>'.
        '<option value="14">14</option><option value="15">15</option>'.
        '<option value="16">16</option><option value="17">17</option>'.
        '<option value="18">18</option><option value="19">19</option>'.
        '<option value="20">20</option><option value="21">21</option>'.
        '<option value="22">22</option><option value="23">23</option>'.
        '<option value="24">24</option><option value="25">25</option>'.
        '<option value="26">26</option><option value="27">27</option>'.
        '<option value="28">28</option><option value="29">29</option>'.
        '<option value="30">30</option><option value="31">31</option>'.
        '<option value="32">32</option><option value="33">33</option>'.
        '<option value="34">34</option><option value="35">35</option>'.
        '<option value="36">36</option><option value="37">37</option>'.
        '<option value="38">38</option><option value="39">39</option>'.
        '<option value="40">40</option><option value="41" selected="selected">41</option>'.
        '<option value="42">42</option><option value="43">43</option>'.
        '<option value="44">44</option><option value="45">45</option>'.
        '<option value="46">46</option><option value="47">47</option>'.
        '<option value="48">48</option><option value="49">49</option>'.
        '<option value="50">50</option><option value="51">51</option>'.
        '<option value="52">52</option><option value="53">53</option>'.
        '<option value="54">54</option><option value="55">55</option>'.
        '<option value="56">56</option><option value="57">57</option>'.
        '<option value="58">58</option><option value="59">59</option>'.
    '</select> '.
    '<select name="time1[seconds]" id="' . self::$formname . '_time1_seconds" class="jforms-ctrl-time">'.
    '<option value="">Seconds</option>'.
        '<option value="00" selected="selected">00</option><option value="01">01</option>'.
        '<option value="02">02</option><option value="03">03</option>'.
        '<option value="04">04</option><option value="05">05</option>'.
        '<option value="06">06</option><option value="07">07</option>'.
        '<option value="08">08</option><option value="09">09</option>'.
        '<option value="10">10</option><option value="11">11</option>'.
        '<option value="12">12</option><option value="13">13</option>'.
        '<option value="14">14</option><option value="15">15</option>'.
        '<option value="16">16</option><option value="17">17</option>'.
        '<option value="18">18</option><option value="19">19</option>'.
        '<option value="20">20</option><option value="21">21</option>'.
        '<option value="22">22</option><option value="23">23</option>'.
        '<option value="24">24</option><option value="25">25</option>'.
        '<option value="26">26</option><option value="27">27</option>'.
        '<option value="28">28</option><option value="29">29</option>'.
        '<option value="30">30</option><option value="31">31</option>'.
        '<option value="32">32</option><option value="33">33</option>'.
        '<option value="34">34</option><option value="35">35</option>'.
        '<option value="36">36</option><option value="37">37</option>'.
        '<option value="38">38</option><option value="39">39</option>'.
        '<option value="40">40</option><option value="41">41</option>'.
        '<option value="42">42</option><option value="43">43</option>'.
        '<option value="44">44</option><option value="45">45</option>'.
        '<option value="46">46</option><option value="47">47</option>'.
        '<option value="48">48</option><option value="49">49</option>'.
        '<option value="50">50</option><option value="51">51</option>'.
        '<option value="52">52</option><option value="53">53</option>'.
        '<option value="54">54</option><option value="55">55</option>'.
        '<option value="56">56</option><option value="57">57</option>'.
        '<option value="58">58</option><option value="59">59</option>'.
    '</select>'.PHP_EOL, $out);
        $this->assertEquals('c = new jFormsJQControlTime2(\'time1\', \'mytime\');
c.multiFields = true;
c.errRequired=\'"mytime" field is required\';
c.errInvalid=\'"mytime" field is invalid\';
jFormsJQ.tForm.addControl(c);
jelix_timepicker_foo(c, jFormsJQ.config);
', self::$builder->getJsContent());
    }
}

<?php
/**
* @package     testapp
* @subpackage  unittest module
* @author      Jouanneau Laurent
* @contributor
* @copyright   2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(JELIX_LIB_FORMS_PATH.'jFormsBuilderBase.class.php');
require_once(JELIX_LIB_FORMS_PATH.'jFormsControl.class.php');
require_once(JELIX_LIB_UTILS_PATH.'jDatatype.class.php');
require_once(JELIX_LIB_FORMS_PATH.'jFormsDatasource.class.php');
require_once(JELIX_LIB_FORMS_PATH.'jFormsDataContainer.class.php');

class testHMLForm { // simulate a jFormBase object
    public $controls= array();
    public $container;

    function __construct(){
        $this->container = new jFormsDataContainer('','');
    }

    function getData($name) {
        $a = array('nom'=>'laurent', 'chk'=>'1', 'chk2'=>'', 'chk3'=>'0', 'choixsimple'=>'11', 'choixmultiple'=>array('10','23'));
        if(isset($a[$name]))
            return $a[$name];
        else
            return null;
    }
    function getControls() {
        return $this->controls;
    }
    function getContainer() {
        return $this->container;
    }
    function hasUpload(){
       return false;
    }
}

class testJFormsHtmlBuilder extends jFormsHtmlBuilderBase {
    public function getJavascriptCheck($errDecorator,$helpDecorator){
        return '';
    }
}


class UTjformsHTMLBuilder extends jUnitTestCaseDb {

    protected $builder;
    function testStart() {
        $form = new testHMLForm();
        $this->builder = new testJFormsHtmlBuilder($form, 'jelix_tests~urlsig_url1',array());
        $this->formname = $this->builder->getName();
    }


    function testOutputHeader(){
        $builder = new testJFormsHtmlBuilder(new testHMLForm(), 'jelix_tests~urlsig_url1',array());
        $formname = $builder->getName();
        ob_start();
        $builder->outputHeader(array('',''));
        $out = ob_get_clean();
        $result ='<form action="/index.php" method="POST" name="'.$formname.'" onsubmit="return jForms.verifyForm(this)"><div><input type="hidden" name="module" value="jelix_tests"/>
<input type="hidden" name="action" value="urlsig_url1"/>
</div><script type="text/javascript"> 
//<[CDATA[

//]]>
</script>';
        $this->assertEqualOrDiff($result, $out);

        $builder = new testJFormsHtmlBuilder(new testHMLForm(), 'jelix_tests~urlsig_url1',array('foo'=>'b>ar'));
        $formname = $builder->getName();
        ob_start();
        $builder->outputHeader(array('',''));
        $out = ob_get_clean();
        $result ='<form action="/index.php" method="POST" name="'.$formname.'" onsubmit="return jForms.verifyForm(this)"><div><input type="hidden" name="foo" value="b&gt;ar"/>
<input type="hidden" name="module" value="jelix_tests"/>
<input type="hidden" name="action" value="urlsig_url1"/>
</div><script type="text/javascript"> 
//<[CDATA[

//]]>
</script>';
        $this->assertEqualOrDiff($result, $out);

    }
    function testOutputFooter(){
        ob_start();
        $this->builder->outputFooter();
        $out = ob_get_clean();
        $this->assertEqualOrDiff('</form>', $out);
    }
    function testOutputInput(){
        $ctrl= new jFormsControlinput('nom');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre nom';

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_nom">Votre nom</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="text" name="nom" id="'.$this->formname.'_nom" value="laurent"/>', $out);

        $ctrl->readonly=true;
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="text" name="nom" id="'.$this->formname.'_nom" readonly="readonly" value="laurent"/>', $out);

        $ctrl= new jFormsControlinput('nominconnu');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre nom';
        $ctrl->defaultValue='toto';
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="text" name="nominconnu" id="'.$this->formname.'_nominconnu" value="toto"/>', $out);

        $ctrl->hasHelp=true;
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="text" name="nominconnu" id="'.$this->formname.'_nominconnu" value="toto"/><span class="jforms-help"><a href="javascript:jForms.showHelp(\''. $this->formname.'\',\'nominconnu\')">?</a></span>', $out);

        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_nominconnu" title="ceci est un tooltip">Votre nom</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="text" name="nominconnu" id="'.$this->formname.'_nominconnu" title="ceci est un tooltip" value="toto"/><span class="jforms-help"><a href="javascript:jForms.showHelp(\''. $this->formname.'\',\'nominconnu\')">?</a></span>', $out);

    }
    function testOutputCheckbox(){
        $ctrl= new jFormsControlCheckbox('chk');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Une option';

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_chk">Une option</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="checkbox" name="chk" id="'.$this->formname.'_chk" checked="checked" value="1"/>', $out);

        $ctrl= new jFormsControlCheckbox('chk2');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Une option';

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_chk2">Une option</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="checkbox" name="chk2" id="'.$this->formname.'_chk2" value="1"/>', $out);

        $ctrl->defaultValue='1';
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="checkbox" name="chk2" id="'.$this->formname.'_chk2" checked="checked" value="1"/>', $out);


        $ctrl= new jFormsControlCheckbox('chk3');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Une option';

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_chk3">Une option</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="checkbox" name="chk3" id="'.$this->formname.'_chk3" value="1"/>', $out);

        $ctrl->defaultValue='1';
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="checkbox" name="chk3" id="'.$this->formname.'_chk3" value="1"/>', $out);


        $ctrl= new jFormsControlCheckbox('chkinconnu');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Une option';

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="checkbox" name="chkinconnu" id="'.$this->formname.'_chkinconnu" value="1"/>', $out);

        $ctrl->readonly=true;
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="checkbox" name="chkinconnu" id="'.$this->formname.'_chkinconnu" readonly="readonly" value="1"/>', $out);

        $ctrl->defaultValue='1';
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="checkbox" name="chkinconnu" id="'.$this->formname.'_chkinconnu" readonly="readonly" checked="checked" value="1"/>', $out);


        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_chkinconnu" title="ceci est un tooltip">Une option</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="checkbox" name="chkinconnu" id="'.$this->formname.'_chkinconnu" readonly="readonly" title="ceci est un tooltip" checked="checked" value="1"/>', $out);


    }
    function testOutputCheckboxes(){
        $ctrl= new jFormsControlcheckboxes('choixsimple');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Vos choix';
        $ctrl->datasource = new jFormDaoDatasource('jelix_tests~products','findAll','name','id');

        $records = array(
            array('id'=>'10', 'name'=>'foo', 'price'=>'12'),
            array('id'=>'11', 'name'=>'bar', 'price'=>'54'),
            array('id'=>'23', 'name'=>'baz', 'price'=>'97'),
        );
        $this->insertRecordsIntoTable('product_test', array('id','name','price'), $records, true);

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<span class="jforms-label">Vos choix</span>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<input type="checkbox" name="choixsimple[]" id="'.$this->formname.'_choixsimple_0" value="10"/><label for="'.$this->formname.'_choixsimple_0">foo</label>';
        $result.='<input type="checkbox" name="choixsimple[]" id="'.$this->formname.'_choixsimple_1" value="11" checked="checked"/><label for="'.$this->formname.'_choixsimple_1">bar</label>';
        $result.='<input type="checkbox" name="choixsimple[]" id="'.$this->formname.'_choixsimple_2" value="23"/><label for="'.$this->formname.'_choixsimple_2">baz</label>';
        $this->assertEqualOrDiff($result, $out);

        $ctrl= new jFormsControlcheckboxes('choixmultiple');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Vos choix';
        $ctrl->datasource= new jFormStaticDatasource();
        $ctrl->datasource->datas = array(
            '10'=>'foo',
            '11'=>'bar',
            '23'=>'baz',
        );
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<span class="jforms-label">Vos choix</span>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<input type="checkbox" name="choixmultiple[]" id="'.$this->formname.'_choixmultiple_0" value="10" checked="checked"/><label for="'.$this->formname.'_choixmultiple_0">foo</label>';
        $result.='<input type="checkbox" name="choixmultiple[]" id="'.$this->formname.'_choixmultiple_1" value="11"/><label for="'.$this->formname.'_choixmultiple_1">bar</label>';
        $result.='<input type="checkbox" name="choixmultiple[]" id="'.$this->formname.'_choixmultiple_2" value="23" checked="checked"/><label for="'.$this->formname.'_choixmultiple_2">baz</label>';
        $this->assertEqualOrDiff($result, $out);

        $ctrl->readonly = true;
        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<span class="jforms-label" title="ceci est un tooltip">Vos choix</span>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<input type="checkbox" name="choixmultiple[]" id="'.$this->formname.'_choixmultiple_0" value="10" checked="checked" readonly="readonly"/><label for="'.$this->formname.'_choixmultiple_0">foo</label>';
        $result.='<input type="checkbox" name="choixmultiple[]" id="'.$this->formname.'_choixmultiple_1" value="11" readonly="readonly"/><label for="'.$this->formname.'_choixmultiple_1">bar</label>';
        $result.='<input type="checkbox" name="choixmultiple[]" id="'.$this->formname.'_choixmultiple_2" value="23" checked="checked" readonly="readonly"/><label for="'.$this->formname.'_choixmultiple_2">baz</label>';
        $this->assertEqualOrDiff($result, $out);

    }

    function testOutputRadiobuttons(){
        $ctrl= new jFormsControlradiobuttons('choixsimple');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre choix';
        $ctrl->datasource = new jFormDaoDatasource('jelix_tests~products','findAll','name','id');

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<span class="jforms-label">Votre choix</span>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<input type="radio" name="choixsimple" id="'.$this->formname.'_choixsimple_0" value="10"/><label for="'.$this->formname.'_choixsimple_0">foo</label>';
        $result.='<input type="radio" name="choixsimple" id="'.$this->formname.'_choixsimple_1" value="11" checked="checked"/><label for="'.$this->formname.'_choixsimple_1">bar</label>';
        $result.='<input type="radio" name="choixsimple" id="'.$this->formname.'_choixsimple_2" value="23"/><label for="'.$this->formname.'_choixsimple_2">baz</label>';
        $this->assertEqualOrDiff($result, $out);

        $ctrl->datasource= new jFormStaticDatasource();
        $ctrl->datasource->datas = array(
            '10'=>'foo',
            '11'=>'bar',
            '23'=>'baz',
        );

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<input type="radio" name="choixsimple" id="'.$this->formname.'_choixsimple_0" value="10"/><label for="'.$this->formname.'_choixsimple_0">foo</label>';
        $result.='<input type="radio" name="choixsimple" id="'.$this->formname.'_choixsimple_1" value="11" checked="checked"/><label for="'.$this->formname.'_choixsimple_1">bar</label>';
        $result.='<input type="radio" name="choixsimple" id="'.$this->formname.'_choixsimple_2" value="23"/><label for="'.$this->formname.'_choixsimple_2">baz</label>';
        $this->assertEqualOrDiff($result, $out);

        $ctrl->readonly = true;
        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<span class="jforms-label" title="ceci est un tooltip">Votre choix</span>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<input type="radio" name="choixsimple" id="'.$this->formname.'_choixsimple_0" value="10" readonly="readonly"/><label for="'.$this->formname.'_choixsimple_0">foo</label>';
        $result.='<input type="radio" name="choixsimple" id="'.$this->formname.'_choixsimple_1" value="11" checked="checked" readonly="readonly"/><label for="'.$this->formname.'_choixsimple_1">bar</label>';
        $result.='<input type="radio" name="choixsimple" id="'.$this->formname.'_choixsimple_2" value="23" readonly="readonly"/><label for="'.$this->formname.'_choixsimple_2">baz</label>';
        $this->assertEqualOrDiff($result, $out);
    }
    function testOutputMenulist(){
        $ctrl= new jFormsControlmenulist('choixsimple');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre choix';
        $ctrl->datasource = new jFormDaoDatasource('jelix_tests~products','findAll','name','id');

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_choixsimple">Votre choix</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="choixsimple" id="'.$this->formname.'_choixsimple" size="1">';
        $result.='<option value="10">foo</option>';
        $result.='<option value="11" selected="selected">bar</option>';
        $result.='<option value="23">baz</option>';
        $result.='</select>';
        $this->assertEqualOrDiff($result, $out);

        $ctrl->datasource= new jFormStaticDatasource();
        $ctrl->datasource->datas = array(
            '10'=>'foo',
            '11'=>'bar',
            '23'=>'baz',
        );

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff($result, $out);

        $ctrl->readonly = true;
        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_choixsimple" title="ceci est un tooltip">Votre choix</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="choixsimple" id="'.$this->formname.'_choixsimple" readonly="readonly" title="ceci est un tooltip" size="1">';
        $result.='<option value="10">foo</option>';
        $result.='<option value="11" selected="selected">bar</option>';
        $result.='<option value="23">baz</option>';
        $result.='</select>';
        $this->assertEqualOrDiff($result, $out);
    }
    function testOutputListbox(){
        $ctrl= new jFormsControllistbox('choixsimple');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre choix';
        $ctrl->datasource = new jFormDaoDatasource('jelix_tests~products','findAll','name','id');

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_choixsimple">Votre choix</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="choixsimple" id="'.$this->formname.'_choixsimple" size="4">';
        $result.='<option value="10">foo</option>';
        $result.='<option value="11" selected="selected">bar</option>';
        $result.='<option value="23">baz</option>';
        $result.='</select>';
        $this->assertEqualOrDiff($result, $out);

        $ctrl->datasource= new jFormStaticDatasource();
        $ctrl->datasource->datas = array(
            '10'=>'foo',
            '11'=>'bar',
            '23'=>'baz',
        );

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff($result, $out);

        $ctrl->readonly = true;
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_choixsimple">Votre choix</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="choixsimple" id="'.$this->formname.'_choixsimple" readonly="readonly" size="4">';
        $result.='<option value="10">foo</option>';
        $result.='<option value="11" selected="selected">bar</option>';
        $result.='<option value="23">baz</option>';
        $result.='</select>';
        $this->assertEqualOrDiff($result, $out);


        $ctrl= new jFormsControllistbox('choixmultiple');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre choix';
        $ctrl->datasource = new jFormDaoDatasource('jelix_tests~products','findAll','name','id');
        $ctrl->multiple=true;
        $ctrl->hint='ceci est un tooltip';

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_choixmultiple" title="ceci est un tooltip">Votre choix</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="choixmultiple[]" id="'.$this->formname.'_choixmultiple" title="ceci est un tooltip" size="4" multiple="multiple">';
        $result.='<option value="10" selected="selected">foo</option>';
        $result.='<option value="11">bar</option>';
        $result.='<option value="23" selected="selected">baz</option>';
        $result.='</select>';
        $this->assertEqualOrDiff($result, $out);


        $ctrl= new jFormsControllistbox('choixsimpleinconnu');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre choix';
        $ctrl->datasource = new jFormDaoDatasource('jelix_tests~products','findAll','name','id');
        $ctrl->selectedValues=array ('10');

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_choixsimpleinconnu">Votre choix</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="choixsimpleinconnu" id="'.$this->formname.'_choixsimpleinconnu" size="4">';
        $result.='<option value="10" selected="selected">foo</option>';
        $result.='<option value="11">bar</option>';
        $result.='<option value="23">baz</option>';
        $result.='</select>';
        $this->assertEqualOrDiff($result, $out);



        $ctrl= new jFormsControllistbox('choixmultipleinconnu');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre choix';
        $ctrl->datasource = new jFormDaoDatasource('jelix_tests~products','findAll','name','id');
        $ctrl->multiple=true;
        $ctrl->size=8;
        $ctrl->selectedValues=array ('11','23');
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_choixmultipleinconnu">Votre choix</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $result='<select name="choixmultipleinconnu[]" id="'.$this->formname.'_choixmultipleinconnu" size="8" multiple="multiple">';
        $result.='<option value="10">foo</option>';
        $result.='<option value="11" selected="selected">bar</option>';
        $result.='<option value="23" selected="selected">baz</option>';
        $result.='</select>';
        $this->assertEqualOrDiff($result, $out);
    }
    function testOutputTextarea(){
        $ctrl= new jFormsControltextarea('nom');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre nom';

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_nom">Votre nom</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<textarea name="nom" id="'.$this->formname.'_nom">laurent</textarea>', $out);

        $ctrl->readonly=true;
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<textarea name="nom" id="'.$this->formname.'_nom" readonly="readonly">laurent</textarea>', $out);

        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_nom" title="ceci est un tooltip">Votre nom</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<textarea name="nom" id="'.$this->formname.'_nom" readonly="readonly" title="ceci est un tooltip">laurent</textarea>', $out);

    }
    function testOutputSecret(){
        $ctrl= new jFormsControlSecret('nom');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre nom';

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_nom">Votre nom</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="password" name="nom" id="'.$this->formname.'_nom" value="laurent"/>', $out);

        $ctrl->readonly = true;
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="password" name="nom" id="'.$this->formname.'_nom" readonly="readonly" value="laurent"/>', $out);

        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_nom" title="ceci est un tooltip">Votre nom</label>', $out);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="password" name="nom" id="'.$this->formname.'_nom" readonly="readonly" title="ceci est un tooltip" value="laurent"/>', $out);
    }
    function testOutputSecretConfirm(){
        $ctrl= new jFormsControlSecretConfirm('nom_confirm');
        $ctrl->label='Votre nom';

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_nom_confirm">Votre nom</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="password" name="nom_confirm" id="'.$this->formname.'_nom_confirm" value=""/>', $out);

        $ctrl->readonly = true;
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="password" name="nom_confirm" id="'.$this->formname.'_nom_confirm" readonly="readonly" value=""/>', $out);

        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_nom_confirm" title="ceci est un tooltip">Votre nom</label>', $out);
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="password" name="nom_confirm" id="'.$this->formname.'_nom_confirm" readonly="readonly" title="ceci est un tooltip" value=""/>', $out);
    }

    function testOutputOutput(){
        $ctrl= new jFormsControlOutput('nom');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre nom';

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<span class="jforms-label">Votre nom</span>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="hidden" name="nom" id="'.$this->formname.'_nom" value="laurent"/><span class="jforms-value">laurent</span>', $out);
        $ctrl->readonly=true;
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="hidden" name="nom" id="'.$this->formname.'_nom" value="laurent"/><span class="jforms-value">laurent</span>', $out);

        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<span class="jforms-label" title="ceci est un tooltip">Votre nom</span>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="hidden" name="nom" id="'.$this->formname.'_nom" value="laurent"/><span class="jforms-value" title="ceci est un tooltip">laurent</span>', $out);

    }
    function testOutputUpload(){
        $ctrl= new jFormsControlUpload('nom');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre nom';

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_nom">Votre nom</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="file" name="nom" id="'.$this->formname.'_nom" value=""/>', $out);

        $ctrl->readonly = true;
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="file" name="nom" id="'.$this->formname.'_nom" readonly="readonly" value=""/>', $out);

        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<label class="jforms-label" for="'.$this->formname.'_nom" title="ceci est un tooltip">Votre nom</label>', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<input type="file" name="nom" id="'.$this->formname.'_nom" readonly="readonly" title="ceci est un tooltip" value=""/>', $out);

    }
    function testOutputSubmit(){
        $ctrl= new jFormsControlSubmit('nom');
        $ctrl->datatype= new jDatatypeString();
        $ctrl->label='Votre nom';

        ob_start();$this->builder->outputControlLabel($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('', $out);

        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<button type="submit" name="nom" id="'.$this->formname.'_nom">Votre nom</button>', $out);

        $ctrl->readonly = true;
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<button type="submit" name="nom" id="'.$this->formname.'_nom">Votre nom</button>', $out);

        $ctrl->hint='ceci est un tooltip';
        ob_start();$this->builder->outputControl($ctrl);$out = ob_get_clean();
        $this->assertEqualOrDiff('<button type="submit" name="nom" id="'.$this->formname.'_nom" title="ceci est un tooltip">Votre nom</button>', $out);
    }
}

?>
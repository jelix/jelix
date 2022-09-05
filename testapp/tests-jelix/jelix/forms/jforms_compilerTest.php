<?php
/**
* @package     testapp
* @subpackage  unittest module
* @author      Laurent Jouanneau
* @contributor Loic Mathaud, Julien Issler
* @copyright   2007-2008 Laurent Jouanneau
* @copyright   2007 Loic Mathaud
* @copyright   2008 Julien Issler
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class testJFormsCompiler10 extends jFormsCompiler_jf_1_0 {

    public function __construct() {
        parent::__construct('myfile');
    }

    public function testPhpForm($doc){
        $dummysrc = $dummyBuilders = array();
        return $this->compile($doc, $dummysrc, $dummyBuilders);
    }

    public function testPhpControl($controltype, $control){
        return $this->generatePHPControl($controltype, $control);
    }
}

class jforms_compilerTest extends \Jelix\UnitTests\UnitTestCase {

    protected $_XmlControls = array(
0=>'<input ref="nom" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</input>',
1=>'<input ref="nom" readonly="true" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</input>',
2=>'<input ref="nom" required="true" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</input>',
3=>'<input ref="nom" xmlns="http://jelix.org/ns/forms/1.0">
    <label locale="foo~bar"/>
</input>',
4=>'<textarea ref="nom" xmlns="http://jelix.org/ns/forms/1.0" required="false">
    <label>Votre nom</label>
</textarea>',
5=>'<secret ref="nom" xmlns="http://jelix.org/ns/forms/1.0" readonly="false">
    <label>Votre nom</label>
</secret>',
6=>'<output ref="nom" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</output>',
7=>'<upload ref="nom" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</upload>',
10=>'<submit ref="nom" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</submit>',
11=>'<input ref="nom" type="string" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</input>',
12=>'<input ref="nom" type="boolean" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</input>',
13=>'<input ref="nom" type="decimal" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</input>',
14=>'<input ref="nom" type="integer" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</input>',
15=>'<input ref="nom" type="hexadecimal" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</input>',
16=>'<input ref="nom" type="datetime" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</input>',
17=>'<input ref="nom" type="date" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</input>',
18=>'<input ref="nom" type="time" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</input>',
19=>'<input ref="nom" type="localedatetime" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</input>',
20=>'<input ref="nom" type="localedate" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</input>',
21=>'<input ref="nom" type="localetime" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</input>',
22=>'<input ref="nom" type="url" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</input>',
23=>'<input ref="nom" type="email" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</input>',
24=>'<input ref="nom" type="ipv4" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</input>',
25=>'<input ref="nom" type="ipv6" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</input>',
26=>'<checkbox ref="nom" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Avez-vous un nom ?</label>
</checkbox>',
27=>'<checkboxes ref="nom" xmlns="http://jelix.org/ns/forms/1.0"
    dao="foo" daomethod="bar" daolabelproperty="baz" daovalueproperty="plop">
    <label>Votre nom</label>
</checkboxes>',
28=>'<checkboxes ref="nom" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
    <item value="aaa">1aa</item>
    <item locale="locb" value="bbb" />
    <item value="ccc"/>
</checkboxes>',
29=>'<radiobuttons ref="nom" xmlns="http://jelix.org/ns/forms/1.0"
    dao="foo" daomethod="bar" daolabelproperty="baz" daovalueproperty="plop">
    <label>Votre nom</label>
</radiobuttons>',
30=>'<radiobuttons ref="nom" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
    <item value="aaa">1aa</item>
    <item locale="locb" value="bbb" />
    <item value="ccc"/>
</radiobuttons>',
31=>'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.0"
    dao="foo" daomethod="bar" daolabelproperty="baz" daovalueproperty="plop">
    <label>Votre nom</label>
</listbox>',
32=>'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.0" size="8">
    <label>Votre nom</label>
    <item value="aaa">1aa</item>
    <item locale="locb" value="bbb" />
    <item value="ccc"/>
</listbox>',
33=>'<menulist ref="nom" xmlns="http://jelix.org/ns/forms/1.0"
    dao="foo" daomethod="bar" daolabelproperty="baz" daovalueproperty="plop">
    <label>Votre nom</label>
</menulist>',
34=>'<menulist ref="nom" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
    <item value="aaa">1aa</item>
    <item locale="locb" value="bbb" />
    <item value="ccc"/>
</menulist>',
35=>'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.0" multiple="true"
    dao="foo" daomethod="bar" daolabelproperty="baz" daovalueproperty="plop">
    <label>Votre nom</label>
</listbox>',
36=>'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.0" multiple="false">
    <label>Votre nom</label>
    <item value="aaa">1aa</item>
    <item locale="locb" value="bbb" />
    <item value="ccc"/>
</listbox>',
37=>'<input ref="nom" defaultvalue="toto" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</input>',
38=>'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.0" multiple="false">
    <label>Votre nom</label>
    <item selected="true" value="aaa">1aa</item>
    <item locale="locb" value="bbb" />
    <item value="ccc"/>
</listbox>',
39=>'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.0" multiple="true">
    <label>Votre nom</label>
    <item selected="true" value="aaa">1aa</item>
    <item locale="locb" value="bbb" />
    <item selected="true" value="ccc"/>
</listbox>',
40=>'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.0" multiple="true" selectedvalue="aaa">
    <label>Votre nom</label>
    <item value="aaa">1aa</item>
    <item locale="locb" value="bbb" />
    <item value="ccc"/>
</listbox>',
41=>'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.0" multiple="true">
    <label>Votre nom</label>
    <item value="aaa">1aa</item>
    <item locale="locb" value="bbb" />
    <item value="ccc"/>
    <selectedvalues> <value>bbb</value><value>aaa</value></selectedvalues>
</listbox>',
42=>'<input ref="nom" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
    <help>vous devez indiquer votre nom</help>
</input>',
43=>'<input ref="nom" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
    <hint>vous devez indiquer votre nom</hint>
</input>',
44=>'<input ref="nom" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
    <alert>Le nom est invalide</alert>
</input>',
45=>'<input ref="nom" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
    <alert type="invalid">Le nom est invalide</alert>
</input>',
46=>'<input ref="nom" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
    <alert type="required">vous avez oublié le nom</alert>
</input>',
47=>'<input ref="nom" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
    <alert locale="error.alert.invalid.nom"/>
</input>',
48=>'<input ref="nom" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
    <alert type="invalid">Le nom est invalide</alert>
    <alert type="required" locale="error.alert.invalid.nom"/>
</input>',
49=>'<checkbox ref="nom" xmlns="http://jelix.org/ns/forms/1.0" valueoncheck="oui" valueonuncheck="non">
    <label>Avez-vous un nom ?</label>
</checkbox>',
50=>'<secret ref="pwd" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre mot de passe</label>
    <confirm>confirmez</confirm>
</secret>',
51=>'<secret ref="pwd" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre mot de passe</label>
    <confirm locale="password.confirm" />
</secret>',
52=>'<submit ref="validation" xmlns="http://jelix.org/ns/forms/1.0"
    dao="foo" daomethod="bar" daolabelproperty="baz" daovalueproperty="plop">
    <label>Type de validation</label>
</submit>',
53=>'<submit ref="validation" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Type de validation</label>
    <item value="aaa">1aa</item>
    <item locale="locb" value="bbb" />
    <item value="ccc"/>
</submit>',
54=>'<upload ref="nom" xmlns="http://jelix.org/ns/forms/1.0" maxsize="22356">
    <label>Votre nom</label>
</upload>',
55=>'<upload ref="nom" xmlns="http://jelix.org/ns/forms/1.0" maxsize="22356" mimetype="image/gif">
    <label>Votre nom</label>
</upload>',
56=>'<upload ref="nom" xmlns="http://jelix.org/ns/forms/1.0" maxsize="22356" mimetype="image/gif;">
    <label>Votre nom</label>
</upload>',
57=>'<upload ref="nom" xmlns="http://jelix.org/ns/forms/1.0" maxsize="22356" mimetype="image/gif;image/png">
    <label>Votre nom</label>
</upload>',
58=>'<upload ref="nom" xmlns="http://jelix.org/ns/forms/1.0" mimetype="image/gif;;image/png;">
    <label>Votre nom</label>
</upload>',
59=>'<input ref="nom" size="20" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</input>',
60=>'<secret ref="pwd" size="10" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre mot de passe</label>
</secret>',
61=>'<secret ref="pwd" size="10" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre mot de passe</label>
    <confirm>confirmez</confirm>
</secret>',
62=>'<textarea ref="nom" cols="15" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</textarea>',
63=>'<textarea ref="nom" rows="15" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</textarea>',
64=>'<textarea ref="nom" rows="15" cols="20" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</textarea>',
65=>'<input ref="nom" maxlength="3" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</input>',
66=>'<input ref="nom" minlength="3" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</input>',
67=>'<reset ref="annulation" xmlns="http://jelix.org/ns/forms/1.0">
    <label>type annulation</label>
</reset>',

    );

    protected $_PhpControls = array(
0=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
1=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->initialReadOnly=true;
$this->addControl($ctrl);',
2=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->required=true;
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
3=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->label=jLocale::get(\'foo~bar\');
$this->addControl($ctrl);',
4=>'$ctrl= new jFormsControlTextarea(\'nom\');
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
5=>'$ctrl= new jFormsControlSecret(\'nom\');
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
6=>'$ctrl= new jFormsControlOutput(\'nom\');
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
7=>'$ctrl= new jFormsControlUpload(\'nom\');
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
10=>'$ctrl= new jFormsControlSubmit(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource= new jFormsStaticDatasource();
$this->addControl($ctrl);',
11=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
12=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype= new jDatatypeboolean();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
13=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype= new jDatatypedecimal();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
14=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype= new jDatatypeinteger();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
15=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype= new jDatatypehexadecimal();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
16=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype= new jDatatypedatetime();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
17=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype= new jDatatypedate();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
18=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype= new jDatatypetime();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
19=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype= new jDatatypelocaledatetime();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
20=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype= new jDatatypelocaledate();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
21=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype= new jDatatypelocaletime();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
22=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype= new jDatatypeurl();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
23=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype= new jDatatypeemail();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
24=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype= new jDatatypeipv4();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
25=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype= new jDatatypeipv6();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
26=>'$ctrl= new jFormsControlCheckbox(\'nom\');
$ctrl->label=\'Avez-vous un nom ?\';
$this->addControl($ctrl);',
27=>'$ctrl= new jFormsControlCheckboxes(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource = new jFormsDaoDatasource(\'foo\',\'bar\',\'baz\',\'plop\');
$this->addControl($ctrl);',
28=>'$ctrl= new jFormsControlCheckboxes(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource= new jFormsStaticDatasource();
$ctrl->datasource->data = array(
\'aaa\'=>\'1aa\',
\'bbb\'=>jLocale::get(\'locb\'),
\'ccc\'=>\'ccc\',
);
$this->addControl($ctrl);',
29=>'$ctrl= new jFormsControlRadiobuttons(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource = new jFormsDaoDatasource(\'foo\',\'bar\',\'baz\',\'plop\');
$this->addControl($ctrl);',
30=>'$ctrl= new jFormsControlRadiobuttons(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource= new jFormsStaticDatasource();
$ctrl->datasource->data = array(
\'aaa\'=>\'1aa\',
\'bbb\'=>jLocale::get(\'locb\'),
\'ccc\'=>\'ccc\',
);
$this->addControl($ctrl);',
31=>'$ctrl= new jFormsControlListbox(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource = new jFormsDaoDatasource(\'foo\',\'bar\',\'baz\',\'plop\');
$this->addControl($ctrl);',
32=>'$ctrl= new jFormsControlListbox(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->size=8;
$ctrl->datasource= new jFormsStaticDatasource();
$ctrl->datasource->data = array(
\'aaa\'=>\'1aa\',
\'bbb\'=>jLocale::get(\'locb\'),
\'ccc\'=>\'ccc\',
);
$this->addControl($ctrl);',
33=>'$ctrl= new jFormsControlMenulist(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource = new jFormsDaoDatasource(\'foo\',\'bar\',\'baz\',\'plop\');
$this->addControl($ctrl);',
34=>'$ctrl= new jFormsControlMenulist(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource= new jFormsStaticDatasource();
$ctrl->datasource->data = array(
\'aaa\'=>\'1aa\',
\'bbb\'=>jLocale::get(\'locb\'),
\'ccc\'=>\'ccc\',
);
$this->addControl($ctrl);',
35=>'$ctrl= new jFormsControlListbox(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource = new jFormsDaoDatasource(\'foo\',\'bar\',\'baz\',\'plop\');
$ctrl->multiple=true;
$this->addControl($ctrl);',
36=>'$ctrl= new jFormsControlListbox(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource= new jFormsStaticDatasource();
$ctrl->datasource->data = array(
\'aaa\'=>\'1aa\',
\'bbb\'=>jLocale::get(\'locb\'),
\'ccc\'=>\'ccc\',
);
$this->addControl($ctrl);',
37=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->defaultValue=\'toto\';
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
38=>'$ctrl= new jFormsControlListbox(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource= new jFormsStaticDatasource();
$ctrl->datasource->data = array(
\'aaa\'=>\'1aa\',
\'bbb\'=>jLocale::get(\'locb\'),
\'ccc\'=>\'ccc\',
);
$ctrl->defaultValue=array (
  0 => \'aaa\',
);
$this->addControl($ctrl);',
39=>'$ctrl= new jFormsControlListbox(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource= new jFormsStaticDatasource();
$ctrl->datasource->data = array(
\'aaa\'=>\'1aa\',
\'bbb\'=>jLocale::get(\'locb\'),
\'ccc\'=>\'ccc\',
);
$ctrl->defaultValue=array (
  0 => \'aaa\',
  1 => \'ccc\',
);
$ctrl->multiple=true;
$this->addControl($ctrl);',
40=>'$ctrl= new jFormsControlListbox(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->defaultValue=array(\'aaa\');
$ctrl->datasource= new jFormsStaticDatasource();
$ctrl->datasource->data = array(
\'aaa\'=>\'1aa\',
\'bbb\'=>jLocale::get(\'locb\'),
\'ccc\'=>\'ccc\',
);
$ctrl->multiple=true;
$this->addControl($ctrl);',
41=>'$ctrl= new jFormsControlListbox(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->defaultValue= array(\'bbb\',\'aaa\',);
$ctrl->datasource= new jFormsStaticDatasource();
$ctrl->datasource->data = array(
\'aaa\'=>\'1aa\',
\'bbb\'=>jLocale::get(\'locb\'),
\'ccc\'=>\'ccc\',
);
$ctrl->multiple=true;
$this->addControl($ctrl);',
42=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->help=\'vous devez indiquer votre nom\';
$this->addControl($ctrl);',
43=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->hint=\'vous devez indiquer votre nom\';
$this->addControl($ctrl);',
44=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->alertInvalid=\'Le nom est invalide\';
$this->addControl($ctrl);',
45=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->alertInvalid=\'Le nom est invalide\';
$this->addControl($ctrl);',
46=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->alertRequired=\'vous avez oublié le nom\';
$this->addControl($ctrl);',
47=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->alertInvalid=jLocale::get(\'error.alert.invalid.nom\');
$this->addControl($ctrl);',
48=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->alertRequired=jLocale::get(\'error.alert.invalid.nom\');
$ctrl->alertInvalid=\'Le nom est invalide\';
$this->addControl($ctrl);',
49=>'$ctrl= new jFormsControlCheckbox(\'nom\');
$ctrl->label=\'Avez-vous un nom ?\';
$ctrl->valueOnCheck=\'oui\';
$ctrl->valueOnUncheck=\'non\';
$this->addControl($ctrl);',
50=>'$ctrl= new jFormsControlSecret(\'pwd\');
$ctrl->label=\'Votre mot de passe\';
$ctrl2 = new jFormsControlSecretConfirm(\'pwd_confirm\');
$ctrl2->primarySecret = \'pwd\';
$ctrl2->label=\'confirmez\';
$ctrl2->required = $ctrl->required;
$this->addControl($ctrl);
$this->addControl($ctrl2);',
51=>'$ctrl= new jFormsControlSecret(\'pwd\');
$ctrl->label=\'Votre mot de passe\';
$ctrl2 = new jFormsControlSecretConfirm(\'pwd_confirm\');
$ctrl2->primarySecret = \'pwd\';
$ctrl2->label=jLocale::get(\'password.confirm\');
$ctrl2->required = $ctrl->required;
$this->addControl($ctrl);
$this->addControl($ctrl2);',
52=>'$ctrl= new jFormsControlSubmit(\'validation\');
$ctrl->label=\'Type de validation\';
$ctrl->datasource = new jFormsDaoDatasource(\'foo\',\'bar\',\'baz\',\'plop\');
$ctrl->standalone=false;
$this->addControl($ctrl);',
53=>'$ctrl= new jFormsControlSubmit(\'validation\');
$ctrl->label=\'Type de validation\';
$ctrl->standalone=false;
$ctrl->datasource= new jFormsStaticDatasource();
$ctrl->datasource->data = array(
\'aaa\'=>\'1aa\',
\'bbb\'=>jLocale::get(\'locb\'),
\'ccc\'=>\'ccc\',
);
$this->addControl($ctrl);',
54=>'$ctrl= new jFormsControlUpload(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->maxsize=22356;
$this->addControl($ctrl);',
55=>'$ctrl= new jFormsControlUpload(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->maxsize=22356;
$ctrl->mimetype=array (
  0 => \'image/gif\',
);
$this->addControl($ctrl);',
56=>'$ctrl= new jFormsControlUpload(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->maxsize=22356;
$ctrl->mimetype=array (
  0 => \'image/gif\',
);
$this->addControl($ctrl);',
57=>'$ctrl= new jFormsControlUpload(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->maxsize=22356;
$ctrl->mimetype=array (
  0 => \'image/gif\',
  1 => \'image/png\',
);
$this->addControl($ctrl);',
58=>'$ctrl= new jFormsControlUpload(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->mimetype=array (
  0 => \'image/gif\',
  2 => \'image/png\',
);
$this->addControl($ctrl);',
59=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->size=20;
$this->addControl($ctrl);',
60=>'$ctrl= new jFormsControlSecret(\'pwd\');
$ctrl->label=\'Votre mot de passe\';
$ctrl->size=10;
$this->addControl($ctrl);',
61=>'$ctrl= new jFormsControlSecret(\'pwd\');
$ctrl->label=\'Votre mot de passe\';
$ctrl->size=10;
$ctrl2 = new jFormsControlSecretConfirm(\'pwd_confirm\');
$ctrl2->primarySecret = \'pwd\';
$ctrl2->label=\'confirmez\';
$ctrl2->required = $ctrl->required;
$ctrl2->size=$ctrl->size;
$this->addControl($ctrl);
$this->addControl($ctrl2);',
62=>'$ctrl= new jFormsControlTextarea(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->cols=15;
$this->addControl($ctrl);',
63=>'$ctrl= new jFormsControlTextarea(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->rows=15;
$this->addControl($ctrl);',
64=>'$ctrl= new jFormsControlTextarea(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->rows=15;
$ctrl->cols=20;
$this->addControl($ctrl);',
65=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype->addFacet(\'maxLength\',3);
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
66=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype->addFacet(\'minLength\',3);
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
67=>'$ctrl= new jFormsControlReset(\'annulation\');
$ctrl->label=\'type annulation\';
$this->addControl($ctrl);',


);

    function testPhpControl(){
        $jfc = new testJFormsCompiler10();

        foreach($this->_XmlControls as $k=>$control){
            $sxml = simplexml_load_string("<?xml version='1.0'?>\n".$control);
            if(!$sxml){
                $this->fail("Can't load xml test content ($k)");
            }
            else{
                $ct = $jfc->testPhpControl($sxml->getName(), $sxml);
                $this->assertEquals($this->_PhpControls[$k],$ct, "test $k failed");
            }
        }
    }

    protected $_BadXmlControls = array(
array(
'<foo ref="nom" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</foo>',
'jelix~formserr.unknown.tag',
array('foo','myfile')
),
array(
'<input ref="foo" controlclass="jFormsControlFoo" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</input>',
'jelix~formserr.unknown.control.class',
array('jFormsControlFoo','input','myfile')
),
array(
'<input xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</input>',
'jelix~formserr.attribute.missing',
array('ref','input','myfile')
),
array(
'<textarea ref="nom" type="boolean" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</textarea>',
'jelix~formserr.attribute.not.allowed',
array('type','textarea','myfile')
),
array(
'<input ref="nom" type="foo" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</input>',
'jelix~formserr.datatype.unknown',
array('foo','input','myfile')
),
array(
'<input ref="nom" xmlns="http://jelix.org/ns/forms/1.0">
</input>',
'jelix~formserr.tag.missing',
array('label','input','myfile')
),
array(
'<checkbox ref="nom" xmlns="http://jelix.org/ns/forms/1.0" type="string">
    <label>Votre nom</label>
</checkbox>',
'jelix~formserr.attribute.not.allowed',
array('type','checkbox','myfile')
),
array(
'<secret ref="pwd" defaultvalue="toto"  xmlns="http://jelix.org/ns/forms/1.0">
<label>Votre mot de passe</label>
</secret>',
'jelix~formserr.attribute.not.allowed',
array('defaultvalue','secret','myfile')
),
array(
'<secret ref="pwd"  xmlns="http://jelix.org/ns/forms/1.0">
<label>Votre mot de passe</label>
<confirm />
</secret>',
'jelix~formserr.content.missing',
array('confirm','myfile')
),
array(
'<secret ref="pwd"  xmlns="http://jelix.org/ns/forms/1.0">
<label>Votre mot de passe</label>
<confirm></confirm>
</secret>',
'jelix~formserr.content.missing',
array('confirm','myfile')
),
array(
'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.0" multiple="false">
    <label>Votre nom</label>
    <item selected="true" value="aaa">1aa</item>
    <item locale="locb" value="bbb" />
    <item selected="true" value="ccc"/>
</listbox>',
'jelix~formserr.multiple.selected.not.allowed',
'myfile'
),
array(
'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.0" multiple="false">
    <label>Votre nom</label>
    <item value="aaa">1aa</item>
    <item locale="locb" value="bbb" />
    <item value="ccc"/>
     <selectedvalues> <value>bbb</value><value>aaa</value></selectedvalues>
</listbox>',
'jelix~formserr.defaultvalues.not.allowed',
'myfile'
),
array(
'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.0" multiple="true">
    <label>Votre nom</label>
    <item selected="true" value="aaa">1aa</item>
    <item locale="locb" value="bbb" />
    <item value="ccc"/>
    <selectedvalues> <value>bbb</value><value>aaa</value></selectedvalues>
</listbox>',
'jelix~formserr.selected.attribute.not.allowed',
'myfile'
),
array(
'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.0" selectedvalue="aaa" multiple="true">
    <label>Votre nom</label>
    <item selected="true" value="aaa">1aa</item>
    <item locale="locb" value="bbb" />
    <item value="ccc"/>
</listbox>',
'jelix~formserr.selected.attribute.not.allowed',
'myfile'
),
array(
'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.0" selectedvalue="aaa" multiple="true">
    <label>Votre nom</label>
    <item value="aaa">1aa</item>
    <item locale="locb" value="bbb" />
    <item value="ccc"/>
     <selectedvalues> <value>bbb</value><value>aaa</value></selectedvalues>
</listbox>',
'jelix~formserr.attribute.not.allowed',
array('selectedvalue','listbox','myfile')
),

/*array(
'<input ref="nom" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</input>',
'',
array('','','myfile')
),*/
    );


    function testBadControl(){
        $jfc = new testJFormsCompiler10();
        foreach($this->_BadXmlControls as $k=>$control){
            $dom = new DOMDocument;
            if(!$dom->loadXML($control[0])){
                $this->fail("Can't load bad xml test content ($k)");
            }else{
                try {
                    // getName() in simplexml doesn't exists in prior version of php 5.1.3, so we use a DOM
                    $ct = $jfc->testPhpControl($dom->documentElement->localName, simplexml_import_dom($dom));

                    $this->fail("no exception during bad xml test content $k");
                }catch(jException $e){
                    $this->assertEquals($control[1], $e->getLocaleKey(),"%s ($k)");
                    $this->assertEquals($control[2], $e->getLocaleParameters(),"%s ($k)");
                }catch(Exception $e){
                    $this->fail("Unexpected exception for bad xml test content $k :". $e->getMessage());
                }
            }
        }
    }

    protected $_BadXmlForms = array(
array(
'<form xmlns="http://jelix.org/ns/forms/1.0">
  <reset ref="reset1">
    <label>annulation 1</label>
  </reset>
  <reset ref="reset2">
    <label>annulation 2</label>
  </reset>
</form>',
'jelix~formserr.notunique.tag',
array( 'reset','myfile')
),
    );

    function testBadForm() {
        $jfc = new testJFormsCompiler10();

        foreach($this->_BadXmlForms as $k=>$form){
            $dom = new DOMDocument;
            if(!$dom->loadXML($form[0])){
                $this->fail("Can't load bad xml test content ($k)");
            }else{
                try {
                    // getName() in simplexml doesn't exists in prior version of php 5.1.3, so we use a DOM
                    $ct = $jfc->testPhpForm($dom);
                    $this->fail("no exception during bad xml test content $k");
                }catch(jException $e){
                    $this->assertEquals($form[1], $e->getLocaleKey());
                    $this->assertEquals($form[2], $e->getLocaleParameters());
                }catch(Exception $e){
                    $this->fail("Unexpected exception for bad xml test content $k :". $e->getMessage());
                }
            }
        }
    }
}


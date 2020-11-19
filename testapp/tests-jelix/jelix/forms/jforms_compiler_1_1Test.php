<?php
/**
* @package     testapp
* @subpackage  unittest module
* @author      Laurent Jouanneau
* @contributor Loic Mathaud, Dominique Papin, Julien Issler
* @copyright   2007-2009 Laurent Jouanneau
* @copyright   2007 Loic Mathaud, 2008 Dominique Papin
* @copyright   2008-2015 Julien Issler
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(JELIX_LIB_PATH.'forms/jFormsCompiler.class.php');
require_once(JELIX_LIB_PATH.'forms/jFormsCompiler_jf_1_1.class.php');
require_once(JELIX_LIB_PATH.'forms/jFormsControl.class.php');
require_once(JELIX_LIB_PATH.'forms/jFormsDatasource.class.php');
require_once(JELIX_LIB_UTILS_PATH.'jDatatype.class.php');


class testJFormsCompiler11 extends jFormsCompiler_jf_1_1 {

    public function __construct() {
        parent::__construct('myfile');
    }

    public function testPhpForm($doc){
        $dummysrc = array();
        $dummyBuilders = array('html'=>null);
        return $this->compile($doc, $dummysrc, $dummyBuilders);
    }

    public function testPhpControl($controltype, $control){
        $this->srcBuilders = array();

        return $this->generatePHPControl($controltype, $control);
    }
}


class jforms_compiler_1_1Test extends jUnitTestCase {

    function setUp() {
        self::initJelixConfig();
    }


    protected $_XmlControls = array(
0=>'<input ref="nom" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</input>',
1=>'<input ref="nom" readonly="true" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</input>',
2=>'<input ref="nom" required="true" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</input>',
3=>'<input ref="nom" xmlns="http://jelix.org/ns/forms/1.1">
    <label locale="foo~bar"/>
</input>',
4=>'<textarea ref="nom" xmlns="http://jelix.org/ns/forms/1.1" required="false">
    <label>Votre nom</label>
</textarea>',
5=>'<secret ref="nom" xmlns="http://jelix.org/ns/forms/1.1" readonly="false">
    <label>Votre nom</label>
</secret>',
6=>'<output ref="nom" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</output>',
7=>'<upload ref="nom" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</upload>',
8=>'<submit ref="nom" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</submit>',
9=>'<input ref="nom" type="string" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</input>',
10=>'<input ref="nom" type="boolean" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</input>',
11=>'<input ref="nom" type="decimal" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</input>',
12=>'<input ref="nom" type="integer" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</input>',
13=>'<input ref="nom" type="hexadecimal" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</input>',
14=>'<input ref="nom" type="datetime" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</input>',
15=>'<input ref="nom" type="date" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</input>',
16=>'<input ref="nom" type="time" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</input>',
17=>'<input ref="nom" type="localedatetime" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</input>',
18=>'<input ref="nom" type="localedate" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</input>',
19=>'<input ref="nom" type="localetime" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</input>',
20=>'<input ref="nom" type="url" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</input>',
21=>'<input ref="nom" type="email" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</input>',
22=>'<input ref="nom" type="ipv4" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</input>',
23=>'<input ref="nom" type="ipv6" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</input>',
24=>'<checkbox ref="nom" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Avez-vous un nom ?</label>
</checkbox>',
25=>'<checkboxes ref="nom" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
    <item value="aaa">1aa</item>
    <item locale="locb" value="bbb" />
    <item value="ccc"/>
</checkboxes>',
26=>'<radiobuttons ref="nom" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
    <item value="aaa">1aa</item>
    <item locale="locb" value="bbb" />
    <item value="ccc"/>
</radiobuttons>',
27=>'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.1" size="8">
    <label>Votre nom</label>
    <item value="aaa">1aa</item>
    <item locale="locb" value="bbb" />
    <item value="ccc"/>
</listbox>',
28=>'<menulist ref="nom" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
    <item value="aaa">1aa</item>
    <item locale="locb" value="bbb" />
    <item value="ccc"/>
</menulist>',
29=>'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.1" multiple="false">
    <label>Votre nom</label>
    <item value="aaa">1aa</item>
    <item locale="locb" value="bbb" />
    <item value="ccc"/>
</listbox>',
30=>'<input ref="nom" defaultvalue="toto" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</input>',
31=>'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.1" multiple="false">
    <label>Votre nom</label>
    <item selected="true" value="aaa">1aa</item>
    <item locale="locb" value="bbb" />
    <item value="ccc"/>
</listbox>',
32=>'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.1" multiple="true">
    <label>Votre nom</label>
    <item selected="true" value="aaa">1aa</item>
    <item locale="locb" value="bbb" />
    <item selected="true" value="ccc"/>
</listbox>',
33=>'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.1" multiple="true" selectedvalue="aaa">
    <label>Votre nom</label>
    <item value="aaa">1aa</item>
    <item locale="locb" value="bbb" />
    <item value="ccc"/>
</listbox>',
34=>'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.1" multiple="true">
    <label>Votre nom</label>
    <item value="aaa">1aa</item>
    <item locale="locb" value="bbb" />
    <item value="ccc"/>
    <selectedvalues> <value>bbb</value><value>aaa</value></selectedvalues>
</listbox>',
35=>'<input ref="nom" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
    <help>vous devez indiquer votre nom</help>
</input>',
36=>'<input ref="nom" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
    <hint>vous devez indiquer votre nom</hint>
</input>',
37=>'<input ref="nom" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
    <alert>Le nom est invalide</alert>
</input>',
38=>'<input ref="nom" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
    <alert type="invalid">Le nom est invalide</alert>
</input>',
39=>'<input ref="nom" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
    <alert type="required">vous avez oublié le nom</alert>
</input>',
40=>'<input ref="nom" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
    <alert locale="error.alert.invalid.nom"/>
</input>',
41=>'<input ref="nom" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
    <alert type="invalid">Le nom est invalide</alert>
    <alert type="required" locale="error.alert.invalid.nom"/>
</input>',
42=>'<checkbox ref="nom" xmlns="http://jelix.org/ns/forms/1.1" valueoncheck="oui" valueonuncheck="non">
    <label>Avez-vous un nom ?</label>
</checkbox>',
43=>'<secret ref="pwd" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre mot de passe</label>
    <confirm>confirmez</confirm>
</secret>',
44=>'<secret ref="pwd" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre mot de passe</label>
    <confirm locale="password.confirm" />
</secret>',
45=>'<submit ref="validation" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Type de validation</label>
    <datasource dao="foo" method="bar" labelproperty="baz" valueproperty="plop"/>
</submit>',
46=>'<submit ref="validation" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Type de validation</label>
    <item value="aaa">1aa</item>
    <item locale="locb" value="bbb" />
    <item value="ccc"/>
</submit>',
47=>'<upload ref="nom" xmlns="http://jelix.org/ns/forms/1.1" maxsize="22356">
    <label>Votre nom</label>
</upload>',
48=>'<upload ref="nom" xmlns="http://jelix.org/ns/forms/1.1" maxsize="22356" mimetype="image/gif">
    <label>Votre nom</label>
</upload>',
49=>'<upload ref="nom" xmlns="http://jelix.org/ns/forms/1.1" maxsize="22356" mimetype="image/gif;">
    <label>Votre nom</label>
</upload>',
50=>'<upload ref="nom" xmlns="http://jelix.org/ns/forms/1.1" maxsize="22356" mimetype="image/gif;image/png">
    <label>Votre nom</label>
</upload>',
51=>'<upload ref="nom" xmlns="http://jelix.org/ns/forms/1.1" mimetype="image/gif;;image/png;">
    <label>Votre nom</label>
</upload>',
52=>'<input ref="nom" size="20" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</input>',
53=>'<secret ref="pwd" size="10" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre mot de passe</label>
</secret>',
54=>'<secret ref="pwd" size="10" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre mot de passe</label>
    <confirm>confirmez</confirm>
</secret>',
55=>'<textarea ref="nom" cols="15" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</textarea>',
56=>'<textarea ref="nom" rows="15" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</textarea>',
57=>'<textarea ref="nom" rows="15" cols="20" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</textarea>',
58=>'<input ref="nom" maxlength="3" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</input>',
59=>'<input ref="nom" minlength="3" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</input>',
60=>'<reset ref="annulation" xmlns="http://jelix.org/ns/forms/1.1">
    <label>type annulation</label>
</reset>',
61=>'<hidden ref="nom" xmlns="http://jelix.org/ns/forms/1.1">
</hidden>',
62=>'<input ref="nom" type="html" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</input>',
63=>'<textarea ref="nom" type="html" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</textarea>',
64=>'<captcha ref="cap" xmlns="http://jelix.org/ns/forms/1.1">
    <label>captcha</label>
</captcha>',
65=>'<htmleditor ref="contenu" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Texte</label>
</htmleditor>',
66=>'<htmleditor ref="contenu" config="simple" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Texte</label>
</htmleditor>',
67=>'<checkboxes ref="nom" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
    <datasource dao="foo" method="bar" labelproperty="baz" valueproperty="plop"/>
</checkboxes>',
68=>'<radiobuttons ref="nom" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
    <datasource dao="foo" method="bar" labelproperty="baz" valueproperty="plop"/>
</radiobuttons>',
69=>'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
    <datasource dao="foo" method="bar" labelproperty="baz" valueproperty="plop"/>
</listbox>',
70=>'<menulist ref="nom" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
    <datasource dao="foo" method="bar" labelproperty="baz" valueproperty="plop"/>
</menulist>',
71=>'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.1" multiple="true">
    <label>Votre nom</label>
    <datasource dao="foo" method="bar" labelproperty="baz" valueproperty="plop"/>
</listbox>',
72=>'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
    <datasource dao="foo" method="bar" labelproperty="baz" valueproperty="plop" criteria="toto"/>
</listbox>',
73=>'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.1">
     <label>Votre nom</label>
    <datasource dao="foo" method="bar" labelproperty="baz" valueproperty="plop" profile="youp" criteria="toto"/>
</listbox>',
74=>'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.1" multiple="true">
    <label>Votre nom</label>
     <datasource dao="foo" method="bar" labelproperty="baz" valueproperty="plop" criteriafrom="prenom"/>
 </listbox>',
75=>'<menulist ref="nom" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
    <datasource class="jelix_tests~mydatasource"/>
</menulist>',
76=>'<group ref="agroup" xmlns="http://jelix.org/ns/forms/1.1">
    <label>the group</label>
    <input ref="nom">
        <label>Votre nom</label>
     </input>
    <listbox ref="list">
       <label>a list</label>
       <datasource dao="foo" method="bar" labelproperty="baz" valueproperty="plop"/>
     </listbox>
</group>',
77=>'<choice ref="achoice" xmlns="http://jelix.org/ns/forms/1.1">
    <label>the choice</label>
<item value="choix1">
   <label>Choix 1</label>
</item>
<item value="choix2">
   <label>Choix 2</label>
</item>
</choice>',
78=>'<choice ref="achoice" xmlns="http://jelix.org/ns/forms/1.1">
    <label>the choice</label>
<item value="choix1">
   <label>Choix 1</label>
    <input ref="nom" xmlns="http://jelix.org/ns/forms/1.1">
        <label>Votre nom</label>
        <alert>Le nom est invalide</alert>
    </input>
</item>
<item value="choix2">
   <label>Choix 2</label>
     <listbox ref="list">
       <label>a list</label>
       <datasource dao="foo" method="bar" labelproperty="baz" valueproperty="plop"/>
     </listbox>
    <secret ref="pwd" size="10" xmlns="http://jelix.org/ns/forms/1.1">
        <label>Votre mot de passe</label>
    </secret>
</item>
</choice>',
79=>'<menulist ref="nom" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
    <datasource dao="foo" method="bar" labelproperty="baz" valueproperty="plop" criteriafrom="prenom" profile="youp"/>
</menulist>',
80=>'<menulist ref="nom" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
    <datasource dao="foo" method="bar" labelproperty="baz,biz" valueproperty="plop" criteria="joe,dumb"/>
</menulist>',
81=>'<menulist ref="nom" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
    <datasource dao="foo" method="bar" labelproperty="baz,biz" valueproperty="plop" criteriafrom="prenom,nom"/>
</menulist>',
82=>'<menulist ref="nom" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
    <datasource dao="foo" method="bar" labelproperty="baz,biz" labelseparator=" - " valueproperty="plop" criteriafrom="prenom,nom"/>
</menulist>',
83=>'<secret ref="pwd" minlength="5" maxlength="10" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre mot de passe</label>
    <confirm>confirmez</confirm>
</secret>',
84=>'<input ref="nom" type="xhtml" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</input>',
85=>'<textarea ref="nom" type="xhtml" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</textarea>',
86=>'<htmleditor ref="contenu" xhtml="true" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Texte</label>
</htmleditor>',
87=>'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.1" size="8">
    <label>Votre nom</label>
    <item value="aaa">1aa</item>
    <itemsgroup label="group">
        <item locale="locb" value="bbb" />
        <item value="ccc"/>
    </itemsgroup>
</listbox>',
88=>'<menulist ref="nom" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
    <item value="aaa">1aa</item>
    <itemsgroup label="group">
        <item locale="locb" value="bbb" />
        <item value="ccc"/>
    </itemsgroup>
</menulist>',
89=>'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.1" multiple="false">
    <label>Votre nom</label>
    <item value="aaa">1aa</item>
    <itemsgroup label="group">
        <item selected="true" locale="locb" value="bbb" />
        <item value="ccc"/>
    </itemsgroup>
</listbox>',
90=>'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.1" multiple="true">
    <label>Votre nom</label>
    <item selected="true" value="aaa">1aa</item>
    <itemsgroup label="group">
        <item locale="locb" value="bbb" />
        <item selected="true" value="ccc"/>
    </itemsgroup>
</listbox>',
91=>'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.1" multiple="true">
    <label>Votre nom</label>
    <item value="aaa">1aa</item>
    <itemsgroup label="group">
        <item locale="locb" value="bbb" />
        <item value="ccc"/>
    </itemsgroup>
    <selectedvalues> <value>bbb</value><value>aaa</value></selectedvalues>
</listbox>',
92=>'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.1" multiple="true">
    <label>Votre nom</label>
     <datasource dao="foo" method="bar" labelproperty="baz" valueproperty="plop" criteriafrom="prenom" groupby="category"/>
 </listbox>',
93=>'<input ref="nom" pattern="/^[0-9]+$/" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</input>',
94=>'<color ref="couleur" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Couleur</label>
</color>',
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
8=>'$ctrl= new jFormsControlSubmit(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource= new jFormsStaticDatasource();
$this->addControl($ctrl);',
9=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
10=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype= new jDatatypeboolean();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
11=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype= new jDatatypedecimal();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
12=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype= new jDatatypeinteger();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
13=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype= new jDatatypehexadecimal();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
14=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype= new jDatatypedatetime();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
15=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype= new jDatatypedate();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
16=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype= new jDatatypetime();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
17=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype= new jDatatypelocaledatetime();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
18=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype= new jDatatypelocaledate();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
19=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype= new jDatatypelocaletime();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
20=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype= new jDatatypeurl();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
21=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype= new jDatatypeemail();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
22=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype= new jDatatypeipv4();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
23=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype= new jDatatypeipv6();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
24=>'$ctrl= new jFormsControlCheckbox(\'nom\');
$ctrl->label=\'Avez-vous un nom ?\';
$this->addControl($ctrl);',
25=>'$ctrl= new jFormsControlCheckboxes(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource= new jFormsStaticDatasource();
$ctrl->datasource->data = array(\'aaa\'=>\'1aa\',\'bbb\'=>jLocale::get(\'locb\'),\'ccc\'=>\'ccc\',);
$this->addControl($ctrl);',
26=>'$ctrl= new jFormsControlRadiobuttons(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource= new jFormsStaticDatasource();
$ctrl->datasource->data = array(\'aaa\'=>\'1aa\',\'bbb\'=>jLocale::get(\'locb\'),\'ccc\'=>\'ccc\',);
$this->addControl($ctrl);',
27=>'$ctrl= new jFormsControlListbox(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->size=8;
$ctrl->datasource= new jFormsStaticDatasource();
$ctrl->datasource->data = array(\'aaa\'=>\'1aa\',\'bbb\'=>jLocale::get(\'locb\'),\'ccc\'=>\'ccc\',);
$this->addControl($ctrl);',
28=>'$ctrl= new jFormsControlMenulist(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource= new jFormsStaticDatasource();
$ctrl->datasource->data = array(\'aaa\'=>\'1aa\',\'bbb\'=>jLocale::get(\'locb\'),\'ccc\'=>\'ccc\',);
$this->addControl($ctrl);',
29=>'$ctrl= new jFormsControlListbox(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource= new jFormsStaticDatasource();
$ctrl->datasource->data = array(\'aaa\'=>\'1aa\',\'bbb\'=>jLocale::get(\'locb\'),\'ccc\'=>\'ccc\',);
$this->addControl($ctrl);',
30=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->defaultValue=\'toto\';
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
31=>'$ctrl= new jFormsControlListbox(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource= new jFormsStaticDatasource();
$ctrl->datasource->data = array(\'aaa\'=>\'1aa\',\'bbb\'=>jLocale::get(\'locb\'),\'ccc\'=>\'ccc\',);
$ctrl->defaultValue=array (
  0 => \'aaa\',
);
$this->addControl($ctrl);',
32=>'$ctrl= new jFormsControlListbox(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource= new jFormsStaticDatasource();
$ctrl->datasource->data = array(\'aaa\'=>\'1aa\',\'bbb\'=>jLocale::get(\'locb\'),\'ccc\'=>\'ccc\',);
$ctrl->defaultValue=array (
  0 => \'aaa\',
  1 => \'ccc\',
);
$ctrl->multiple=true;
$this->addControl($ctrl);',
33=>'$ctrl= new jFormsControlListbox(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->defaultValue=array(\'aaa\');
$ctrl->datasource= new jFormsStaticDatasource();
$ctrl->datasource->data = array(\'aaa\'=>\'1aa\',\'bbb\'=>jLocale::get(\'locb\'),\'ccc\'=>\'ccc\',);
$ctrl->multiple=true;
$this->addControl($ctrl);',
34=>'$ctrl= new jFormsControlListbox(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->defaultValue= array(\'bbb\',\'aaa\',);
$ctrl->datasource= new jFormsStaticDatasource();
$ctrl->datasource->data = array(\'aaa\'=>\'1aa\',\'bbb\'=>jLocale::get(\'locb\'),\'ccc\'=>\'ccc\',);
$ctrl->multiple=true;
$this->addControl($ctrl);',
35=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->help=\'vous devez indiquer votre nom\';
$this->addControl($ctrl);',
36=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->hint=\'vous devez indiquer votre nom\';
$this->addControl($ctrl);',
37=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->alertInvalid=\'Le nom est invalide\';
$this->addControl($ctrl);',
38=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->alertInvalid=\'Le nom est invalide\';
$this->addControl($ctrl);',
39=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->alertRequired=\'vous avez oublié le nom\';
$this->addControl($ctrl);',
40=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->alertInvalid=jLocale::get(\'error.alert.invalid.nom\');
$this->addControl($ctrl);',
41=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->alertRequired=jLocale::get(\'error.alert.invalid.nom\');
$ctrl->alertInvalid=\'Le nom est invalide\';
$this->addControl($ctrl);',
42=>'$ctrl= new jFormsControlCheckbox(\'nom\');
$ctrl->label=\'Avez-vous un nom ?\';
$ctrl->valueOnCheck=\'oui\';
$ctrl->valueOnUncheck=\'non\';
$this->addControl($ctrl);',
43=>'$ctrl= new jFormsControlSecret(\'pwd\');
$ctrl->label=\'Votre mot de passe\';
$ctrl2 = new jFormsControlSecretConfirm(\'pwd_confirm\');
$ctrl2->primarySecret = \'pwd\';
$ctrl2->label=\'confirmez\';
$ctrl2->required = $ctrl->required;
$this->addControl($ctrl);
$this->addControl($ctrl2);',
44=>'$ctrl= new jFormsControlSecret(\'pwd\');
$ctrl->label=\'Votre mot de passe\';
$ctrl2 = new jFormsControlSecretConfirm(\'pwd_confirm\');
$ctrl2->primarySecret = \'pwd\';
$ctrl2->label=jLocale::get(\'password.confirm\');
$ctrl2->required = $ctrl->required;
$this->addControl($ctrl);
$this->addControl($ctrl2);',
45=>'$ctrl= new jFormsControlSubmit(\'validation\');
$ctrl->label=\'Type de validation\';
$ctrl->datasource = new jFormsDaoDatasource(\'foo\',\'bar\',\'baz\',\'plop\',\'\',null,null);
$ctrl->standalone=false;
$this->addControl($ctrl);',
46=>'$ctrl= new jFormsControlSubmit(\'validation\');
$ctrl->label=\'Type de validation\';
$ctrl->datasource= new jFormsStaticDatasource();
$ctrl->datasource->data = array(\'aaa\'=>\'1aa\',\'bbb\'=>jLocale::get(\'locb\'),\'ccc\'=>\'ccc\',);
$ctrl->standalone=false;
$this->addControl($ctrl);',
47=>'$ctrl= new jFormsControlUpload(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->maxsize=22356;
$this->addControl($ctrl);',
48=>'$ctrl= new jFormsControlUpload(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->maxsize=22356;
$ctrl->mimetype=array (
  0 => \'image/gif\',
);
$this->addControl($ctrl);',
49=>'$ctrl= new jFormsControlUpload(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->maxsize=22356;
$ctrl->mimetype=array (
  0 => \'image/gif\',
);
$this->addControl($ctrl);',
50=>'$ctrl= new jFormsControlUpload(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->maxsize=22356;
$ctrl->mimetype=array (
  0 => \'image/gif\',
  1 => \'image/png\',
);
$this->addControl($ctrl);',
51=>'$ctrl= new jFormsControlUpload(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->mimetype=array (
  0 => \'image/gif\',
  2 => \'image/png\',
);
$this->addControl($ctrl);',
52=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->size=20;
$this->addControl($ctrl);',
53=>'$ctrl= new jFormsControlSecret(\'pwd\');
$ctrl->label=\'Votre mot de passe\';
$ctrl->size=10;
$this->addControl($ctrl);',
54=>'$ctrl= new jFormsControlSecret(\'pwd\');
$ctrl->label=\'Votre mot de passe\';
$ctrl->size=10;
$ctrl2 = new jFormsControlSecretConfirm(\'pwd_confirm\');
$ctrl2->primarySecret = \'pwd\';
$ctrl2->label=\'confirmez\';
$ctrl2->required = $ctrl->required;
$ctrl2->size=$ctrl->size;
$this->addControl($ctrl);
$this->addControl($ctrl2);',
55=>'$ctrl= new jFormsControlTextarea(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->cols=15;
$this->addControl($ctrl);',
56=>'$ctrl= new jFormsControlTextarea(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->rows=15;
$this->addControl($ctrl);',
57=>'$ctrl= new jFormsControlTextarea(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->rows=15;
$ctrl->cols=20;
$this->addControl($ctrl);',
58=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype->addFacet(\'maxLength\',3);
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
59=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype->addFacet(\'minLength\',3);
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
60=>'$ctrl= new jFormsControlReset(\'annulation\');
$ctrl->label=\'type annulation\';
$this->addControl($ctrl);',
61=>'$ctrl= new jFormsControlHidden(\'nom\');
$this->addControl($ctrl);',
62=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype= new jDatatypehtml();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
63=>'$ctrl= new jFormsControlTextarea(\'nom\');
$ctrl->datatype= new jDatatypeHtml();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
64=>'$ctrl= new jFormsControlCaptcha(\'cap\');
$ctrl->label=\'captcha\';
$this->addControl($ctrl);',
65=>'$ctrl= new jFormsControlHtmlEditor(\'contenu\');
$ctrl->label=\'Texte\';
$this->addControl($ctrl);',
66=>'$ctrl= new jFormsControlHtmlEditor(\'contenu\');
$ctrl->label=\'Texte\';
$ctrl->config=\'simple\';
$this->addControl($ctrl);',
67=>'$ctrl= new jFormsControlCheckboxes(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource = new jFormsDaoDatasource(\'foo\',\'bar\',\'baz\',\'plop\',\'\',null,null);
$this->addControl($ctrl);',
68=>'$ctrl= new jFormsControlRadiobuttons(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource = new jFormsDaoDatasource(\'foo\',\'bar\',\'baz\',\'plop\',\'\',null,null);
$this->addControl($ctrl);',
69=>'$ctrl= new jFormsControlListbox(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource = new jFormsDaoDatasource(\'foo\',\'bar\',\'baz\',\'plop\',\'\',null,null);
$this->addControl($ctrl);',
70=>'$ctrl= new jFormsControlMenulist(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource = new jFormsDaoDatasource(\'foo\',\'bar\',\'baz\',\'plop\',\'\',null,null);
$this->addControl($ctrl);',
71=>'$ctrl= new jFormsControlListbox(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource = new jFormsDaoDatasource(\'foo\',\'bar\',\'baz\',\'plop\',\'\',null,null);
$ctrl->multiple=true;
$this->addControl($ctrl);',
72=>'$ctrl= new jFormsControlListbox(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource = new jFormsDaoDatasource(\'foo\',\'bar\',\'baz\',\'plop\',\'\',\'toto\',null);
$this->addControl($ctrl);',
73=>'$ctrl= new jFormsControlListbox(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource = new jFormsDaoDatasource(\'foo\',\'bar\',\'baz\',\'plop\',\'youp\',\'toto\',null);
$this->addControl($ctrl);',
74=>'$ctrl= new jFormsControlListbox(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource = new jFormsDaoDatasource(\'foo\',\'bar\',\'baz\',\'plop\',\'\',null,\'prenom\');
$ctrl->multiple=true;
$this->addControl($ctrl);',
75=>'$ctrl= new jFormsControlMenulist(\'nom\');
$ctrl->label=\'Votre nom\';
jClasses::inc(\'jelix_tests~mydatasource\');
$datasource = new mydatasource($this->id());
if ($datasource instanceof jIFormsDatasource){$ctrl->datasource=$datasource;
}
else{$ctrl->datasource=new jFormsStaticDatasource();}
$this->addControl($ctrl);',
76=>'$ctrl= new jFormsControlGroup(\'agroup\');
$ctrl->label=\'the group\';
$topctrl = $ctrl;
$ctrl= new jFormsControlInput(\'nom\');
$ctrl->label=\'Votre nom\';
$topctrl->addChildControl($ctrl);
$ctrl= new jFormsControlListbox(\'list\');
$ctrl->label=\'a list\';
$ctrl->datasource = new jFormsDaoDatasource(\'foo\',\'bar\',\'baz\',\'plop\',\'\',null,null);
$topctrl->addChildControl($ctrl);
$ctrl = $topctrl;
$this->addControl($ctrl);',
77=>'$ctrl= new jFormsControlChoice(\'achoice\');
$ctrl->label=\'the choice\';
$choicectrl = $ctrl;
$choicectrl->createItem(\'choix1\', \'Choix 1\');
$choicectrl->createItem(\'choix2\', \'Choix 2\');
$choicectrl->defaultValue=\'\';
$ctrl = $choicectrl;
$this->addControl($ctrl);',
78=>'$ctrl= new jFormsControlChoice(\'achoice\');
$ctrl->label=\'the choice\';
$choicectrl = $ctrl;
$choicectrl->createItem(\'choix1\', \'Choix 1\');
$ctrl= new jFormsControlInput(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->alertInvalid=\'Le nom est invalide\';
$choicectrl->addChildControl($ctrl,\'choix1\');
$choicectrl->createItem(\'choix2\', \'Choix 2\');
$ctrl= new jFormsControlListbox(\'list\');
$ctrl->label=\'a list\';
$ctrl->datasource = new jFormsDaoDatasource(\'foo\',\'bar\',\'baz\',\'plop\',\'\',null,null);
$choicectrl->addChildControl($ctrl,\'choix2\');
$ctrl= new jFormsControlSecret(\'pwd\');
$ctrl->label=\'Votre mot de passe\';
$ctrl->size=10;
$choicectrl->addChildControl($ctrl,\'choix2\');
$choicectrl->defaultValue=\'\';
$ctrl = $choicectrl;
$this->addControl($ctrl);',
79=>'$ctrl= new jFormsControlMenulist(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource = new jFormsDaoDatasource(\'foo\',\'bar\',\'baz\',\'plop\',\'youp\',null,\'prenom\');
$this->addControl($ctrl);',
80=>'$ctrl= new jFormsControlMenulist(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource = new jFormsDaoDatasource(\'foo\',\'bar\',\'baz,biz\',\'plop\',\'\',\'joe,dumb\',null);
$this->addControl($ctrl);',
81=>'$ctrl= new jFormsControlMenulist(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource = new jFormsDaoDatasource(\'foo\',\'bar\',\'baz,biz\',\'plop\',\'\',null,\'prenom,nom\');
$this->addControl($ctrl);',
82=>'$ctrl= new jFormsControlMenulist(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource = new jFormsDaoDatasource(\'foo\',\'bar\',\'baz,biz\',\'plop\',\'\',null,\'prenom,nom\',\' - \');
$this->addControl($ctrl);',
83=>'$ctrl= new jFormsControlSecret(\'pwd\');
$ctrl->datatype->addFacet(\'minLength\',5);
$ctrl->datatype->addFacet(\'maxLength\',10);
$ctrl->label=\'Votre mot de passe\';
$ctrl2 = new jFormsControlSecretConfirm(\'pwd_confirm\');
$ctrl2->primarySecret = \'pwd\';
$ctrl2->label=\'confirmez\';
$ctrl2->required = $ctrl->required;
$this->addControl($ctrl);
$this->addControl($ctrl2);',
84=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype= new jDatatypeHtml(true);
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
85=>'$ctrl= new jFormsControlTextarea(\'nom\');
$ctrl->datatype= new jDatatypeHtml(true);
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
86=>'$ctrl= new jFormsControlHtmlEditor(\'contenu\');
$ctrl->datatype= new jDatatypeHtml(true, true);
$ctrl->label=\'Texte\';
$this->addControl($ctrl);',
87=>'$ctrl= new jFormsControlListbox(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->size=8;
$ctrl->datasource= new jFormsStaticDatasource();
$ctrl->datasource->data[\'\'] = array(\'aaa\'=>\'1aa\',);
$ctrl->datasource->data[\'group\']=array(\'bbb\'=>jLocale::get(\'locb\'),\'ccc\'=>\'ccc\',);
$ctrl->datasource->setGroupBy(true);
$this->addControl($ctrl);',
88=>'$ctrl= new jFormsControlMenulist(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource= new jFormsStaticDatasource();
$ctrl->datasource->data[\'\'] = array(\'aaa\'=>\'1aa\',);
$ctrl->datasource->data[\'group\']=array(\'bbb\'=>jLocale::get(\'locb\'),\'ccc\'=>\'ccc\',);
$ctrl->datasource->setGroupBy(true);
$this->addControl($ctrl);',
89=>'$ctrl= new jFormsControlListbox(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource= new jFormsStaticDatasource();
$ctrl->datasource->data[\'\'] = array(\'aaa\'=>\'1aa\',);
$ctrl->datasource->data[\'group\']=array(\'bbb\'=>jLocale::get(\'locb\'),\'ccc\'=>\'ccc\',);
$ctrl->datasource->setGroupBy(true);
$ctrl->defaultValue=array (
  0 => \'bbb\',
);
$this->addControl($ctrl);',
90=>'$ctrl= new jFormsControlListbox(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource= new jFormsStaticDatasource();
$ctrl->datasource->data[\'\'] = array(\'aaa\'=>\'1aa\',);
$ctrl->datasource->data[\'group\']=array(\'bbb\'=>jLocale::get(\'locb\'),\'ccc\'=>\'ccc\',);
$ctrl->datasource->setGroupBy(true);
$ctrl->defaultValue=array (
  0 => \'aaa\',
  1 => \'ccc\',
);
$ctrl->multiple=true;
$this->addControl($ctrl);',
91=>'$ctrl= new jFormsControlListbox(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->defaultValue= array(\'bbb\',\'aaa\',);
$ctrl->datasource= new jFormsStaticDatasource();
$ctrl->datasource->data[\'\'] = array(\'aaa\'=>\'1aa\',);
$ctrl->datasource->data[\'group\']=array(\'bbb\'=>jLocale::get(\'locb\'),\'ccc\'=>\'ccc\',);
$ctrl->datasource->setGroupBy(true);
$ctrl->multiple=true;
$this->addControl($ctrl);',
92=>'$ctrl= new jFormsControlListbox(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource = new jFormsDaoDatasource(\'foo\',\'bar\',\'baz\',\'plop\',\'\',null,\'prenom\');
$ctrl->datasource->setGroupBy(\'category\');
$ctrl->multiple=true;
$this->addControl($ctrl);',
93=>'$ctrl= new jFormsControlInput(\'nom\');
$ctrl->datatype->addFacet(\'pattern\',\'/^[0-9]+$/\');
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
94=>'$ctrl= new jFormsControlColor(\'couleur\');
$ctrl->datatype= new jDatatypeColor();
$ctrl->label=\'Couleur\';
$this->addControl($ctrl);',
);

    function testPhpControl11(){
        $jfc = new testJFormsCompiler11();

        foreach($this->_XmlControls as $k=>$control){
            $dom = new DOMDocument;
            if(!$dom->loadXML($control)){
                $this->fail("Can't load xml test content ($k)");
            }else{
                // getName() in simplexml doesn't exists in prior version of php 5.1.3, so we use a DOM
                $ct = $jfc->testPhpControl($dom->documentElement->localName, simplexml_import_dom($dom));

                $this->assertEquals($this->_PhpControls[$k],$ct, "test $k failed" );
            }
        }
    }

    protected $_BadXmlControls = array(
array(
'<foo ref="nom" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</foo>',
'jelix~formserr.unknown.tag',
array('foo','myfile')
),
array(
'<input xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</input>',
'jelix~formserr.attribute.missing',
array('ref','input','myfile')
),
array(
'<textarea ref="nom" type="boolean" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</textarea>',
'jelix~formserr.datatype.unknown',
array('boolean','textarea','myfile')
),
array(
'<input ref="nom" type="foo" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</input>',
'jelix~formserr.datatype.unknown',
array('foo','input','myfile')
),
array(
'<input ref="nom" xmlns="http://jelix.org/ns/forms/1.1">
</input>',
'jelix~formserr.tag.missing',
array('label','input','myfile')
),
array(
'<checkbox ref="nom" xmlns="http://jelix.org/ns/forms/1.1" type="string">
    <label>Votre nom</label>
</checkbox>',
'jelix~formserr.attribute.not.allowed',
array('type','checkbox','myfile')
),
array(
'<secret ref="pwd" defaultvalue="toto"  xmlns="http://jelix.org/ns/forms/1.1">
<label>Votre mot de passe</label>
</secret>',
'jelix~formserr.attribute.not.allowed',
array('defaultvalue','secret','myfile')
),
array(
'<secret ref="pwd"  xmlns="http://jelix.org/ns/forms/1.1">
<label>Votre mot de passe</label>
<confirm />
</secret>',
'jelix~formserr.content.missing',
array('confirm','myfile')
),
array(
'<secret ref="pwd"  xmlns="http://jelix.org/ns/forms/1.1">
<label>Votre mot de passe</label>
<confirm></confirm>
</secret>',
'jelix~formserr.content.missing',
array('confirm','myfile')
),
array(
'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.1" multiple="false">
    <label>Votre nom</label>
    <item selected="true" value="aaa">1aa</item>
    <item locale="locb" value="bbb" />
    <item selected="true" value="ccc"/>
</listbox>',
'jelix~formserr.multiple.selected.not.allowed',
'myfile'
),
array(
'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.1" multiple="false">
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
'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.1" multiple="true">
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
'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.1" selectedvalue="aaa" multiple="true">
    <label>Votre nom</label>
    <item selected="true" value="aaa">1aa</item>
    <item locale="locb" value="bbb" />
    <item value="ccc"/>
</listbox>',
'jelix~formserr.selected.attribute.not.allowed',
'myfile'
),
array(
'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.1" selectedvalue="aaa" multiple="true">
    <label>Votre nom</label>
    <item value="aaa">1aa</item>
    <item locale="locb" value="bbb" />
    <item value="ccc"/>
     <selectedvalues> <value>bbb</value><value>aaa</value></selectedvalues>
</listbox>',
'jelix~formserr.attribute.not.allowed',
array('selectedvalue','listbox','myfile')
),
array(
'<checkboxes ref="nom" xmlns="http://jelix.org/ns/forms/1.1"
    dao="foo" daomethod="bar" daolabelproperty="baz" daovalueproperty="plop">
    <label>Votre nom</label>
</checkboxes>',
'jelix~formserr.attribute.not.allowed',
array('dao','checkboxes','myfile')
),
array(
'<radiobuttons ref="nom" xmlns="http://jelix.org/ns/forms/1.1"
    dao="foo" daomethod="bar" daolabelproperty="baz" daovalueproperty="plop">
    <label>Votre nom</label>
</radiobuttons>',
'jelix~formserr.attribute.not.allowed',
array('dao','radiobuttons','myfile')
),
array(
'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.1"
    dao="foo" daomethod="bar" daolabelproperty="baz" daovalueproperty="plop">
    <label>Votre nom</label>
</listbox>',
'jelix~formserr.attribute.not.allowed',
array('dao','listbox','myfile')
),
array(
'<menulist ref="nom" xmlns="http://jelix.org/ns/forms/1.1"
    dao="foo" daomethod="bar" daolabelproperty="baz" daovalueproperty="plop">
    <label>Votre nom</label>
</menulist>',
'jelix~formserr.attribute.not.allowed',
array('dao','menulist','myfile')
),
array(
'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.1" multiple="true"
    dao="foo" daomethod="bar" daolabelproperty="baz" daovalueproperty="plop">
    <label>Votre nom</label>
</listbox>',
'jelix~formserr.attribute.not.allowed',
array('dao','listbox','myfile')
),
array(
'<submit ref="validation" xmlns="http://jelix.org/ns/forms/1.1"
    dao="foo" daomethod="bar" daolabelproperty="baz" daovalueproperty="plop">
    <label>Type de validation</label>
</submit>',
'jelix~formserr.attribute.not.allowed',
array('dao','submit','myfile')
),



/*array(
'<input ref="nom" xmlns="http://jelix.org/ns/forms/1.1">
    <label>Votre nom</label>
</input>',
'',
array('','','myfile')
),*/
    );


    function testBadControl(){
        $jfc = new testJFormsCompiler11();

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
'<form xmlns="http://jelix.org/ns/forms/1.1">
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
        $jfc = new testJFormsCompiler11();

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

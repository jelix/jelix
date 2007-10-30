<?php
/**
* @package     testapp
* @subpackage  unittest module
* @author      Jouanneau Laurent
* @contributor Loic Mathaud
* @copyright   2007 Jouanneau laurent
* @copyright   2007 Loic Mathaud
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(JELIX_LIB_FORMS_PATH.'jFormsCompiler.class.php');

class testJFormsCompiler extends jFormsCompiler {

   protected $sourceFile = 'myfile';

   public function testPhpControl($controltype, $control){
        return $this->generatePHPControl($controltype, $control);
   }

   public function testJsControl($controltype, $control){
        return $this->generateJsControl($controltype, $control);
   }
}


class UTjformsCompiler extends jUnitTestCase {

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
</textarea>'
    );

    protected $_PhpControls = array(
0=>'$ctrl= new jFormsControlinput(\'nom\');
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
1=>'$ctrl= new jFormsControlinput(\'nom\');
$ctrl->readonly=true;
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
2=>'$ctrl= new jFormsControlinput(\'nom\');
$ctrl->required=true;
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
3=>'$ctrl= new jFormsControlinput(\'nom\');
$ctrl->label=jLocale::get(\'foo~bar\');
$this->addControl($ctrl);',
4=>'$ctrl= new jFormsControltextarea(\'nom\');
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
5=>'$ctrl= new jFormsControlsecret(\'nom\');
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
6=>'$ctrl= new jFormsControloutput(\'nom\');
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
7=>'$ctrl= new jFormsControlupload(\'nom\');
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
10=>'$ctrl= new jFormsControlsubmit(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource= new jFormStaticDatasource();
$this->addControl($ctrl);',
11=>'$ctrl= new jFormsControlinput(\'nom\');
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
12=>'$ctrl= new jFormsControlinput(\'nom\');
$ctrl->datatype= new jDatatypeboolean();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
13=>'$ctrl= new jFormsControlinput(\'nom\');
$ctrl->datatype= new jDatatypedecimal();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
14=>'$ctrl= new jFormsControlinput(\'nom\');
$ctrl->datatype= new jDatatypeinteger();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
15=>'$ctrl= new jFormsControlinput(\'nom\');
$ctrl->datatype= new jDatatypehexadecimal();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
16=>'$ctrl= new jFormsControlinput(\'nom\');
$ctrl->datatype= new jDatatypedatetime();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
17=>'$ctrl= new jFormsControlinput(\'nom\');
$ctrl->datatype= new jDatatypedate();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
18=>'$ctrl= new jFormsControlinput(\'nom\');
$ctrl->datatype= new jDatatypetime();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
19=>'$ctrl= new jFormsControlinput(\'nom\');
$ctrl->datatype= new jDatatypelocaledatetime();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
20=>'$ctrl= new jFormsControlinput(\'nom\');
$ctrl->datatype= new jDatatypelocaledate();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
21=>'$ctrl= new jFormsControlinput(\'nom\');
$ctrl->datatype= new jDatatypelocaletime();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
22=>'$ctrl= new jFormsControlinput(\'nom\');
$ctrl->datatype= new jDatatypeurl();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
23=>'$ctrl= new jFormsControlinput(\'nom\');
$ctrl->datatype= new jDatatypeemail();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
24=>'$ctrl= new jFormsControlinput(\'nom\');
$ctrl->datatype= new jDatatypeipv4();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
25=>'$ctrl= new jFormsControlinput(\'nom\');
$ctrl->datatype= new jDatatypeipv6();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
26=>'$ctrl= new jFormsControlcheckbox(\'nom\');
$ctrl->datatype= new jDatatypeBoolean();
$ctrl->label=\'Avez-vous un nom ?\';
$this->addControl($ctrl);',
27=>'$ctrl= new jFormsControlcheckboxes(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource = new jFormDaoDatasource(\'foo\',\'bar\',\'baz\',\'plop\');
$this->addControl($ctrl);',
28=>'$ctrl= new jFormsControlcheckboxes(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource= new jFormStaticDatasource();
$ctrl->datasource->datas = array(
\'aaa\'=>\'1aa\',
\'bbb\'=>jLocale::get(\'locb\'),
\'ccc\'=>\'ccc\',
);
$this->addControl($ctrl);',
29=>'$ctrl= new jFormsControlradiobuttons(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource = new jFormDaoDatasource(\'foo\',\'bar\',\'baz\',\'plop\');
$this->addControl($ctrl);',
30=>'$ctrl= new jFormsControlradiobuttons(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource= new jFormStaticDatasource();
$ctrl->datasource->datas = array(
\'aaa\'=>\'1aa\',
\'bbb\'=>jLocale::get(\'locb\'),
\'ccc\'=>\'ccc\',
);
$this->addControl($ctrl);',
31=>'$ctrl= new jFormsControllistbox(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource = new jFormDaoDatasource(\'foo\',\'bar\',\'baz\',\'plop\');
$this->addControl($ctrl);',
32=>'$ctrl= new jFormsControllistbox(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->size=8;
$ctrl->datasource= new jFormStaticDatasource();
$ctrl->datasource->datas = array(
\'aaa\'=>\'1aa\',
\'bbb\'=>jLocale::get(\'locb\'),
\'ccc\'=>\'ccc\',
);
$this->addControl($ctrl);',
33=>'$ctrl= new jFormsControlmenulist(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource = new jFormDaoDatasource(\'foo\',\'bar\',\'baz\',\'plop\');
$this->addControl($ctrl);',
34=>'$ctrl= new jFormsControlmenulist(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource= new jFormStaticDatasource();
$ctrl->datasource->datas = array(
\'aaa\'=>\'1aa\',
\'bbb\'=>jLocale::get(\'locb\'),
\'ccc\'=>\'ccc\',
);
$this->addControl($ctrl);',
35=>'$ctrl= new jFormsControllistbox(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource = new jFormDaoDatasource(\'foo\',\'bar\',\'baz\',\'plop\');
$ctrl->multiple=true;
$this->addControl($ctrl);',
36=>'$ctrl= new jFormsControllistbox(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource= new jFormStaticDatasource();
$ctrl->datasource->datas = array(
\'aaa\'=>\'1aa\',
\'bbb\'=>jLocale::get(\'locb\'),
\'ccc\'=>\'ccc\',
);
$this->addControl($ctrl);',
37=>'$ctrl= new jFormsControlinput(\'nom\');
$ctrl->defaultValue=\'toto\';
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
38=>'$ctrl= new jFormsControllistbox(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource= new jFormStaticDatasource();
$ctrl->datasource->datas = array(
\'aaa\'=>\'1aa\',
\'bbb\'=>jLocale::get(\'locb\'),
\'ccc\'=>\'ccc\',
);
$ctrl->defaultValue=array (
  0 => \'aaa\',
);
$this->addControl($ctrl);',
39=>'$ctrl= new jFormsControllistbox(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->datasource= new jFormStaticDatasource();
$ctrl->datasource->datas = array(
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
40=>'$ctrl= new jFormsControllistbox(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->defaultValue=array(\'aaa\');
$ctrl->datasource= new jFormStaticDatasource();
$ctrl->datasource->datas = array(
\'aaa\'=>\'1aa\',
\'bbb\'=>jLocale::get(\'locb\'),
\'ccc\'=>\'ccc\',
);
$ctrl->multiple=true;
$this->addControl($ctrl);',
41=>'$ctrl= new jFormsControllistbox(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->defaultValue= array(\'bbb\',\'aaa\',);
$ctrl->datasource= new jFormStaticDatasource();
$ctrl->datasource->datas = array(
\'aaa\'=>\'1aa\',
\'bbb\'=>jLocale::get(\'locb\'),
\'ccc\'=>\'ccc\',
);
$ctrl->multiple=true;
$this->addControl($ctrl);',
42=>'$ctrl= new jFormsControlinput(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->hasHelp=true;
$this->addControl($ctrl);',
43=>'$ctrl= new jFormsControlinput(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->hint=\'vous devez indiquer votre nom\';
$this->addControl($ctrl);',
44=>'$ctrl= new jFormsControlinput(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->alertInvalid=\'Le nom est invalide\';
$this->addControl($ctrl);',
45=>'$ctrl= new jFormsControlinput(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->alertInvalid=\'Le nom est invalide\';
$this->addControl($ctrl);',
46=>'$ctrl= new jFormsControlinput(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->alertRequired=\'vous avez oublié le nom\';
$this->addControl($ctrl);',
47=>'$ctrl= new jFormsControlinput(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->alertInvalid=jLocale::get(\'error.alert.invalid.nom\');
$this->addControl($ctrl);',
48=>'$ctrl= new jFormsControlinput(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->alertRequired=jLocale::get(\'error.alert.invalid.nom\');
$ctrl->alertInvalid=\'Le nom est invalide\';
$this->addControl($ctrl);',
49=>'$ctrl= new jFormsControlcheckbox(\'nom\');
$ctrl->datatype= new jDatatypeBoolean();
$ctrl->label=\'Avez-vous un nom ?\';
$ctrl->valueOnCheck=\'oui\';
$ctrl->valueOnUncheck=\'non\';
$this->addControl($ctrl);',
50=>'$ctrl= new jFormsControlsecret(\'pwd\');
$ctrl->label=\'Votre mot de passe\';
$ctrl2 = new jFormsControlSecretConfirm(\'pwd_confirm\');
$ctrl2->primarySecret = \'pwd\';
$ctrl2->label=\'confirmez\';
$ctrl2->required = $ctrl->required;
$ctrl2->readonly = $ctrl->readonly;
$this->addControl($ctrl);
$this->addControl($ctrl2);',
51=>'$ctrl= new jFormsControlsecret(\'pwd\');
$ctrl->label=\'Votre mot de passe\';
$ctrl2 = new jFormsControlSecretConfirm(\'pwd_confirm\');
$ctrl2->primarySecret = \'pwd\';
$ctrl2->label=jLocale::get(\'password.confirm\');
$ctrl2->required = $ctrl->required;
$ctrl2->readonly = $ctrl->readonly;
$this->addControl($ctrl);
$this->addControl($ctrl2);',
52=>'$ctrl= new jFormsControlsubmit(\'validation\');
$ctrl->label=\'Type de validation\';
$ctrl->datasource = new jFormDaoDatasource(\'foo\',\'bar\',\'baz\',\'plop\');
$ctrl->standalone=false;
$this->addControl($ctrl);',
53=>'$ctrl= new jFormsControlsubmit(\'validation\');
$ctrl->label=\'Type de validation\';
$ctrl->standalone=false;
$ctrl->datasource= new jFormStaticDatasource();
$ctrl->datasource->datas = array(
\'aaa\'=>\'1aa\',
\'bbb\'=>jLocale::get(\'locb\'),
\'ccc\'=>\'ccc\',
);
$this->addControl($ctrl);',
54=>'$ctrl= new jFormsControlupload(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->maxsize=22356;
$this->addControl($ctrl);',
55=>'$ctrl= new jFormsControlupload(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->maxsize=22356;
$ctrl->mimetype=array (
  0 => \'image/gif\',
);
$this->addControl($ctrl);',
56=>'$ctrl= new jFormsControlupload(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->maxsize=22356;
$ctrl->mimetype=array (
  0 => \'image/gif\',
);
$this->addControl($ctrl);',
57=>'$ctrl= new jFormsControlupload(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->maxsize=22356;
$ctrl->mimetype=array (
  0 => \'image/gif\',
  1 => \'image/png\',
);
$this->addControl($ctrl);',
58=>'$ctrl= new jFormsControlupload(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->mimetype=array (
  0 => \'image/gif\',
  2 => \'image/png\',
);
$this->addControl($ctrl);',
59=>'$ctrl= new jFormsControlinput(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->size=20;
$this->addControl($ctrl);',
60=>'$ctrl= new jFormsControlsecret(\'pwd\');
$ctrl->label=\'Votre mot de passe\';
$ctrl->size=10;
$this->addControl($ctrl);',
61=>'$ctrl= new jFormsControlsecret(\'pwd\');
$ctrl->label=\'Votre mot de passe\';
$ctrl->size=10;
$ctrl2 = new jFormsControlSecretConfirm(\'pwd_confirm\');
$ctrl2->primarySecret = \'pwd\';
$ctrl2->label=\'confirmez\';
$ctrl2->required = $ctrl->required;
$ctrl2->readonly = $ctrl->readonly;
$ctrl2->size=$ctrl->size;
$this->addControl($ctrl);
$this->addControl($ctrl2);',
62=>'$ctrl= new jFormsControltextarea(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->cols=15;
$this->addControl($ctrl);',
63=>'$ctrl= new jFormsControltextarea(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->rows=15;
$this->addControl($ctrl);',
64=>'$ctrl= new jFormsControltextarea(\'nom\');
$ctrl->label=\'Votre nom\';
$ctrl->rows=15;
$ctrl->cols=20;
$this->addControl($ctrl);'
);


    protected $_JsControls = array(
0=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
1=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.readonly = true;\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
2=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.required = true;\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
3=>'$label = str_replace("\'","\\\'",jLocale::get(\'foo~bar\'));
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
4=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
5=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
6=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
7=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
/*8=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
9=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',*/
10=>'',
11=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
12=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'boolean\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
13=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'decimal\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
14=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'integer\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
15=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'hexadecimal\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
16=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'datetime\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
17=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'date\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
18=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'time\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
19=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'localedatetime\');\n";
$js.="jForms.tControl.lang=\'".$GLOBALS[\'gJConfig\']->locale."\';\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
20=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'localedate\');\n";
$js.="jForms.tControl.lang=\'".$GLOBALS[\'gJConfig\']->locale."\';\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
21=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'localetime\');\n";
$js.="jForms.tControl.lang=\'".$GLOBALS[\'gJConfig\']->locale."\';\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
22=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'url\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
23=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'email\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
24=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'ipv4\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
25=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'ipv6\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
26=>'$label = str_replace("\'","\\\'",\'Avez-vous un nom ?\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'boolean\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
27=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom[]\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
28=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom[]\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
29=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
30=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
31=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
32=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
33=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
34=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
35=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom[]\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tControl.multiple = true;\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
36=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
37=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
38=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
39=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom[]\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tControl.multiple = true;\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
40=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom[]\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tControl.multiple = true;\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
41=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom[]\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tControl.multiple = true;\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
42=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.help=\'".str_replace("\'","\\\'",\'vous devez indiquer votre nom\')."\';\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
43=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
44=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",\'Le nom est invalide\')."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
45=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",\'Le nom est invalide\')."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
46=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",\'vous avez oublié le nom\')."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
47=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'error.alert.invalid.nom\'))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
48=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'error.alert.invalid.nom\'))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",\'Le nom est invalide\')."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
49=>'$label = str_replace("\'","\\\'",\'Avez-vous un nom ?\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'boolean\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
50=>'$label = str_replace("\'","\\\'",\'Votre mot de passe\');
$js.="jForms.tControl = new jFormsControl(\'pwd\', \'".$label."\', \'string\');\n";
$label2 = str_replace("\'","\\\'",\'confirmez\');
$js.="jForms.tControl2 = new jFormsControl(\'pwd_confirm\', \'".$label2."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tControl2.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label2))."\';\n";
$js.="jForms.tControl2.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label2))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";
$js.="jForms.tControl2.isConfirmField=true;\njForms.tControl2.confirmFieldOf=\'pwd\';\n";
$js.="jForms.tForm.addControl( jForms.tControl2);\n";',
51=>'$label = str_replace("\'","\\\'",\'Votre mot de passe\');
$js.="jForms.tControl = new jFormsControl(\'pwd\', \'".$label."\', \'string\');\n";
$label2 = str_replace("\'","\\\'",jLocale::get(\'password.confirm\'));
$js.="jForms.tControl2 = new jFormsControl(\'pwd_confirm\', \'".$label2."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tControl2.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label2))."\';\n";
$js.="jForms.tControl2.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label2))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";
$js.="jForms.tControl2.isConfirmField=true;\njForms.tControl2.confirmFieldOf=\'pwd\';\n";
$js.="jForms.tForm.addControl( jForms.tControl2);\n";',
52=>'',
53=>'',
54=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
55=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
56=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
57=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
58=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
59=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
60=>'$label = str_replace("\'","\\\'",\'Votre mot de passe\');
$js.="jForms.tControl = new jFormsControl(\'pwd\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
61=>'$label = str_replace("\'","\\\'",\'Votre mot de passe\');
$js.="jForms.tControl = new jFormsControl(\'pwd\', \'".$label."\', \'string\');\n";
$label2 = str_replace("\'","\\\'",\'confirmez\');
$js.="jForms.tControl2 = new jFormsControl(\'pwd_confirm\', \'".$label2."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tControl2.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label2))."\';\n";
$js.="jForms.tControl2.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label2))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";
$js.="jForms.tControl2.isConfirmField=true;\njForms.tControl2.confirmFieldOf=\'pwd\';\n";
$js.="jForms.tForm.addControl( jForms.tControl2);\n";',
62=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
63=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";',
64=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="jForms.tControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="jForms.tControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="jForms.tControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="jForms.tForm.addControl( jForms.tControl);\n";'
    );

    function testPhpControl(){
        $jfc = new testJFormsCompiler();

        foreach($this->_XmlControls as $k=>$control){
            $dom = new DOMDocument;
            if(!$dom->loadXML($control)){
                $this->fail("Can't load xml test content ($k)");
            }else{
                // getName() in simplexml doesn't exists in prior version of php 5.1.3, so we use a DOM
                $ct = $jfc->testPhpControl($dom->documentElement->localName, simplexml_import_dom($dom));
                $this->assertEqualOrDiff($this->_PhpControls[$k],$ct, "test $k failed" );
            }
        }
    }


    function testJsControl(){
        $jfc = new testJFormsCompiler();

        foreach($this->_XmlControls as $k=>$control){
            $dom = new DOMDocument;
            if(!$dom->loadXML($control)){
                $this->fail("Can't load xml test content ($k)");
            }else{
                // getName() in simplexml doesn't exists in prior version of php 5.1.3, so we use a DOM
                $ct = $jfc->testJsControl($dom->documentElement->localName, simplexml_import_dom($dom));
                $this->assertEqualOrDiff($this->_JsControls[$k],$ct, "test $k failed" );
            }
        }
    }



    protected $_BadXmlControls = array(
array(
'<foo ref="nom" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
</foo>',
'jelix~formserr.unknow.tag',
array('foo','myfile')
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
'jelix~formserr.datatype.unknow',
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
'<checkbox ref="nom" xmlns="http://jelix.org/ns/forms/1.0" required="true">
    <label>Votre nom</label>
</checkbox>',
'jelix~formserr.attribute.not.allowed',
array('required','checkbox','myfile')
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
        $jfc = new testJFormsCompiler();

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
                    $this->assertEqualOrDiff($control[1], $e->getLocaleKey());
                    $this->assertEqual($control[2], $e->getLocaleParameters());
                }catch(Exception $e){
                    $this->fail("Unexpected exception for bad xml test content $k :". $e->getMessage());
                }
            }
        }
    }
}

?>

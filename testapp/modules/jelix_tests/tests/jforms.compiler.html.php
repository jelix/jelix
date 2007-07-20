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
/*8=>'<select1 ref="nom" xmlns="http://jelix.org/ns/forms/1.0"
    dao="foo" daomethod="bar" daolabelproperty="baz" daovalueproperty="plop">
    <label>Votre nom</label>
</select1>',
9=>'<select ref="nom" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
    <item label="1aa" value="aaa" />
    <item labellocale="locb" value="bbb" />
    <item value="ccc"/>
</select>',*/
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
    <item label="1aa" value="aaa" />
    <item labellocale="locb" value="bbb" />
    <item value="ccc"/>
</checkboxes>',
29=>'<radiobuttons ref="nom" xmlns="http://jelix.org/ns/forms/1.0"
    dao="foo" daomethod="bar" daolabelproperty="baz" daovalueproperty="plop">
    <label>Votre nom</label>
</radiobuttons>',
30=>'<radiobuttons ref="nom" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
    <item label="1aa" value="aaa" />
    <item labellocale="locb" value="bbb" />
    <item value="ccc"/>
</radiobuttons>',
31=>'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.0"
    dao="foo" daomethod="bar" daolabelproperty="baz" daovalueproperty="plop">
    <label>Votre nom</label>
</listbox>',
32=>'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
    <item label="1aa" value="aaa" />
    <item labellocale="locb" value="bbb" />
    <item value="ccc"/>
</listbox>',
33=>'<menulist ref="nom" xmlns="http://jelix.org/ns/forms/1.0"
    dao="foo" daomethod="bar" daolabelproperty="baz" daovalueproperty="plop">
    <label>Votre nom</label>
</menulist>',
34=>'<menulist ref="nom" xmlns="http://jelix.org/ns/forms/1.0">
    <label>Votre nom</label>
    <item label="1aa" value="aaa" />
    <item labellocale="locb" value="bbb" />
    <item value="ccc"/>
</menulist>',
35=>'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.0" multiple="true"
    dao="foo" daomethod="bar" daolabelproperty="baz" daovalueproperty="plop">
    <label>Votre nom</label>
</listbox>',
36=>'<listbox ref="nom" xmlns="http://jelix.org/ns/forms/1.0" multiple="false">
    <label>Votre nom</label>
    <item label="1aa" value="aaa" />
    <item labellocale="locb" value="bbb" />
    <item value="ccc"/>
</listbox>',
    );

    protected $_PhpControls = array(
0=>'$ctrl= new jFormsControlinput(\'nom\');
$ctrl->datatype= new jDatatypeString();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
1=>'$ctrl= new jFormsControlinput(\'nom\');
$ctrl->datatype= new jDatatypeString();
$ctrl->readonly=true;
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
2=>'$ctrl= new jFormsControlinput(\'nom\');
$ctrl->datatype= new jDatatypeString();
$ctrl->required=true;
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
3=>'$ctrl= new jFormsControlinput(\'nom\');
$ctrl->datatype= new jDatatypeString();
$ctrl->label=jLocale::get(\'foo~bar\');
$this->addControl($ctrl);',
4=>'$ctrl= new jFormsControltextarea(\'nom\');
$ctrl->datatype= new jDatatypeString();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
5=>'$ctrl= new jFormsControlsecret(\'nom\');
$ctrl->datatype= new jDatatypeString();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
6=>'$ctrl= new jFormsControloutput(\'nom\');
$ctrl->datatype= new jDatatypeString();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
7=>'$ctrl= new jFormsControlupload(\'nom\');
$ctrl->datatype= new jDatatypeString();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
/*8=>'$ctrl= new jFormsControlselect1(\'nom\');
$ctrl->datatype= new jDatatypeString();
$ctrl->label=\'Votre nom\';
$ctrl->datasource = new jFormDaoDatasource(\'foo\',\'bar\',\'baz\',\'plop\');
$this->addControl($ctrl);',
9=>'$ctrl= new jFormsControlselect(\'nom\');
$ctrl->datatype= new jDatatypeString();
$ctrl->label=\'Votre nom\';
$ctrl->datasource= new jFormStaticDatasource();
$ctrl->datasource->datas = array(
\'aaa\'=>\'1aa\',
\'bbb\'=>jLocale::get(\'locb\'),
\'ccc\'=>\'ccc\',
);
$this->addControl($ctrl);',*/
10=>'$ctrl= new jFormsControlsubmit(\'nom\');
$ctrl->datatype= new jDatatypeString();
$ctrl->label=\'Votre nom\';
$this->addControl($ctrl);',
11=>'$ctrl= new jFormsControlinput(\'nom\');
$ctrl->datatype= new jDatatypestring();
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
$ctrl->datatype= new jDatatypeString();
$ctrl->label=\'Votre nom\';
$ctrl->datasource = new jFormDaoDatasource(\'foo\',\'bar\',\'baz\',\'plop\');
$this->addControl($ctrl);',
28=>'$ctrl= new jFormsControlcheckboxes(\'nom\');
$ctrl->datatype= new jDatatypeString();
$ctrl->label=\'Votre nom\';
$ctrl->datasource= new jFormStaticDatasource();
$ctrl->datasource->datas = array(
\'aaa\'=>\'1aa\',
\'bbb\'=>jLocale::get(\'locb\'),
\'ccc\'=>\'ccc\',
);
$this->addControl($ctrl);',
29=>'$ctrl= new jFormsControlradiobuttons(\'nom\');
$ctrl->datatype= new jDatatypeString();
$ctrl->label=\'Votre nom\';
$ctrl->datasource = new jFormDaoDatasource(\'foo\',\'bar\',\'baz\',\'plop\');
$this->addControl($ctrl);',
30=>'$ctrl= new jFormsControlradiobuttons(\'nom\');
$ctrl->datatype= new jDatatypeString();
$ctrl->label=\'Votre nom\';
$ctrl->datasource= new jFormStaticDatasource();
$ctrl->datasource->datas = array(
\'aaa\'=>\'1aa\',
\'bbb\'=>jLocale::get(\'locb\'),
\'ccc\'=>\'ccc\',
);
$this->addControl($ctrl);',
31=>'$ctrl= new jFormsControllistbox(\'nom\');
$ctrl->datatype= new jDatatypeString();
$ctrl->label=\'Votre nom\';
$ctrl->datasource = new jFormDaoDatasource(\'foo\',\'bar\',\'baz\',\'plop\');
$this->addControl($ctrl);',
32=>'$ctrl= new jFormsControllistbox(\'nom\');
$ctrl->datatype= new jDatatypeString();
$ctrl->label=\'Votre nom\';
$ctrl->datasource= new jFormStaticDatasource();
$ctrl->datasource->datas = array(
\'aaa\'=>\'1aa\',
\'bbb\'=>jLocale::get(\'locb\'),
\'ccc\'=>\'ccc\',
);
$this->addControl($ctrl);',
33=>'$ctrl= new jFormsControlmenulist(\'nom\');
$ctrl->datatype= new jDatatypeString();
$ctrl->label=\'Votre nom\';
$ctrl->datasource = new jFormDaoDatasource(\'foo\',\'bar\',\'baz\',\'plop\');
$this->addControl($ctrl);',
34=>'$ctrl= new jFormsControlmenulist(\'nom\');
$ctrl->datatype= new jDatatypeString();
$ctrl->label=\'Votre nom\';
$ctrl->datasource= new jFormStaticDatasource();
$ctrl->datasource->datas = array(
\'aaa\'=>\'1aa\',
\'bbb\'=>jLocale::get(\'locb\'),
\'ccc\'=>\'ccc\',
);
$this->addControl($ctrl);',
35=>'$ctrl= new jFormsControllistbox(\'nom\');
$ctrl->datatype= new jDatatypeString();
$ctrl->label=\'Votre nom\';
$ctrl->datasource = new jFormDaoDatasource(\'foo\',\'bar\',\'baz\',\'plop\');
$ctrl->multiple=true;
$this->addControl($ctrl);',
36=>'$ctrl= new jFormsControllistbox(\'nom\');
$ctrl->datatype= new jDatatypeString();
$ctrl->label=\'Votre nom\';
$ctrl->datasource= new jFormStaticDatasource();
$ctrl->datasource->datas = array(
\'aaa\'=>\'1aa\',
\'bbb\'=>jLocale::get(\'locb\'),
\'ccc\'=>\'ccc\',
);
$this->addControl($ctrl);',
);


    protected $_JsControls = array(
0=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',
1=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="gControl.readonly = true;\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',
2=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="gControl.required = true;\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',
3=>'$label = str_replace("\'","\\\'",jLocale::get(\'foo~bar\'));
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',
4=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',
5=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',
6=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',
7=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',
/*8=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',
9=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',*/
10=>'',
11=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',
12=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'boolean\');\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',
13=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'decimal\');\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',
14=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'integer\');\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',
15=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'hexadecimal\');\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',
16=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'datetime\');\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',
17=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'date\');\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',
18=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'time\');\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',
19=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'localedatetime\');\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',
20=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'localedate\');\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',
21=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'localetime\');\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',
22=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'url\');\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',
23=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'email\');\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',
24=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'ipv4\');\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',
25=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'ipv6\');\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',
26=>'$label = str_replace("\'","\\\'",\'Avez-vous un nom ?\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'boolean\');\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',
27=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',
28=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',
29=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',
30=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',
31=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',
32=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',
33=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',
34=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',
35=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gControl.multiple = true;\n";
$js.="gForm.addControl( gControl);\n";',
36=>'$label = str_replace("\'","\\\'",\'Votre nom\');
$js.="gControl = new jFormsControl(\'nom\', \'".$label."\', \'string\');\n";
$js.="gControl.errRequired=\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.required\',$label))."\';\n";
$js.="gControl.errInvalid =\'".str_replace("\'","\\\'",jLocale::get(\'jelix~formserr.js.err.invalid\', $label))."\';\n";
$js.="gForm.addControl( gControl);\n";',

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
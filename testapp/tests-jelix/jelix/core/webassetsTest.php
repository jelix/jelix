<?php

require_once(JELIX_LIB_PATH.'plugins/configcompiler/webassets/webassets.configcompiler.php');

require_once(JELIX_LIB_PATH.'core/response/jResponseHtml.class.php');


class htmlRespAssetsTest extends jResponseHtml {
    function __construct (){
        $this->body = new jTpl();
    }
    protected function sendHttpHeaders(){ $this->_httpHeadersSent=true; }
}


class webassetsTest extends jUnitTestCase
{

    function testParseEmptyWebAssets() {
        $compiler = new webassetsConfigCompilerPlugin();
        $config = (object)parse_ini_string('
[webassets]
useSet=foo

[webassets_foo]
',      true);
        $compiler->atStart($config);
        $this->assertEquals(array(), $config->webassets_foo);
    }

    function testParseSimpleWebAssets() {
        $compiler = new webassetsConfigCompilerPlugin();
        $config = (object)parse_ini_string('
[webassets]
useSet=dev

[webassets_foo]
example.css= "/absolute/path.css, related/to/basepath, module~ctrl:meth, module:path/to/file.css"
example.js[]= "/absolute/path.js"
example.js[]= related/to/basepath
example.js[]= "module:path/to/file.js, module~ctrl:meth"
example.js[]= "$theme/path/to/file.js, path/$lang/machin.js, /$locale/truc.js"

',      true);
        $compiler->atStart($config);
        $this->assertEquals(array(
            "example.css" => array(
                'u>/absolute/path.css',
                'b>related/to/basepath',
                'a>module~ctrl:meth',
                'm>module:path/to/file.css'
            ),
            "example.js" => array(
                'u>/absolute/path.js',
                'b>related/to/basepath',
                'm>module:path/to/file.js',
                'a>module~ctrl:meth',
                't>path/to/file.js',
                'k>path/$lang/machin.js',
                'l>/$locale/truc.js'
            ),
            "example.include" => array(),
            "example.require" => array(),

        ), $config->webassets_foo);
    }


    function testParseCommonWebAssets() {
        $compiler = new webassetsConfigCompilerPlugin();
        $config = (object)parse_ini_string('
[webassets]
useSet=foo

[webassets_common]
example2.css = my.css

example3.js = foo.js

[webassets_foo]

example.css= "/absolute/path.css, related/to/basepath, module~ctrl:meth, module:path/to/file.css"
example.js[]= /absolute/path.js
example.js[]= related/to/basepath
example.js[]= "module:path/to/file.js, module~ctrl:meth"
example.js[]= "$theme/path/to/file.js"

example3.css = hello.css

',      true);
        $compiler->atStart($config);
        $this->assertEquals(array(
            "example2.css" => array(
                'b>my.css',
            ),
            "example2.js" => array(),
            "example2.include" => array(),
            "example2.require" => array(),
            "example3.css" => array(),
            "example3.js" => array('b>foo.js',),
            "example3.include" => array(),
            "example3.require" => array(),
        ), $config->webassets_common);
        $this->assertEquals(array(
            "example.css" => array(
                'u>/absolute/path.css',
                'b>related/to/basepath',
                'a>module~ctrl:meth',
                'm>module:path/to/file.css'
            ),
            "example.js" => array(
                'u>/absolute/path.js',
                'b>related/to/basepath',
                'm>module:path/to/file.js',
                'a>module~ctrl:meth',
                't>path/to/file.js'
            ),
            "example.include" => array(),
            "example.require" => array(),
            "example3.css" => array('b>hello.css'),
            "example3.js" => array(),
            "example3.include" => array(),
            "example3.require" => array(),
            "example2.css" => array(
                'b>my.css',
            ),
            "example2.js" => array(),
            "example2.include" => array(),
            "example2.require" => array(),

        ), $config->webassets_foo);
    }

    function testExample1() {
        self::initJelixConfig();
        $compiler = new webassetsConfigCompilerPlugin();
        $config = (object)parse_ini_string('
[urlengine]
jelixWWWPath=/mypath/jelix/

[webassets]
useSet=common

[webassets_common]
jquery.js = "$jelix/jquery/jquery.js"

jquery_ui.js = "$jelix/jquery/ui/jquery-ui-core-widg-mous-posi.custom.min.js"
jquery_ui.css = "$jelix/jquery/themes/base/jquery.ui.all.css"
jquery_ui.require = jquery

jforms_datepicker.css=
jforms_datepicker.js[]="$jelix/jquery/ui/jquery.ui.datepicker.min.js"
jforms_datepicker.js[]="$jelix/jquery/ui/i18n/jquery.ui.datepicker-$lang.js"
jforms_datepicker.require=jquery_ui
',      true);
        $compiler->atStart($config);
        $this->assertEquals(array(
            'jquery.js' => Array (
                'u>/mypath/jelix/jquery/jquery.js'
            ),
            'jquery_ui.js' => Array (
                'u>/mypath/jelix/jquery/ui/jquery-ui-core-widg-mous-posi.custom.min.js'
            ),
            'jquery_ui.css' => Array (
                'u>/mypath/jelix/jquery/themes/base/jquery.ui.all.css'
            ),
            'jquery_ui.require' => Array ('jquery'),
            'jforms_datepicker.css' => Array (),
            'jforms_datepicker.js' => Array (
                'u>/mypath/jelix/jquery/ui/jquery.ui.datepicker.min.js',
                'l>/mypath/jelix/jquery/ui/i18n/jquery.ui.datepicker-$lang.js'
            ),
            'jforms_datepicker.require' => Array ('jquery_ui'),
            'jquery.css' => Array (
            ),
            'jquery.include' => Array (),
            'jquery.require' => Array (),
            'jquery_ui.include' => Array (),
            'jforms_datepicker.include' => Array (),
        ), $config->webassets_common);

    }


    function testOutputNoInclude() {
        self::initJelixConfig();
        jApp::config()->webassets_common = array(
            "example.css" => array(
                'u>/absolute/path.css',
                'b>related/to/basepath.css'
            ),
            "example.js" => array(
                'u>/absolute/path.js',
                'b>related/to/basepath.js'
            ),
            "example.include" => array(),
            "example.require" => array(),
        );

        $resp = new htmlRespAssetsTest();
        $resp->addAssets('example');
        ob_start();
        $resp->output();
        $output = ob_get_clean();
        $this->assertEquals('<!DOCTYPE HTML>
<html lang="">
<head>
<meta content="text/html; charset=" http-equiv="content-type"/>
<title></title>
<link type="text/css" href="/absolute/path.css" rel="stylesheet" />

<link type="text/css" href="related/to/basepath.css" rel="stylesheet" />

<script type="text/javascript" src="/absolute/path.js" ></script>
<script type="text/javascript" src="related/to/basepath.js" ></script>
</head><body >
</body></html>', $output);
    }

    function testOutputWithInclude() {
        self::initJelixConfig();
        jApp::config()->webassets_common = array(
            "example.css" => array(
                'u>/absolute/path.css',
                'b>related/to/basepath.css'
            ),
            "example.js" => array(
                'u>/absolute/path.js',
                'b>related/to/basepath.js'
            ),
            "example.include" => array(
                'example2'
            ),
            "example.require" => array(),
            "example2.css" => array(

            ),
            "example2.js" => array(
                'b>ex2/to/basepath.js'
            ),
            "example2.include" => array(
            ),
            "example2.require" => array('example3'),
            "example3.css" => array(
                'u>/example3/path.css',
                'b>related/to/basepath.css' // same css as in example
            ),
            "example3.js" => array(
                'b>ex3/basepath.js'
            ),
            "example3.include" => array(
                'example'
            ),
            "example3.require" => array(),
        );

        $resp = new htmlRespAssetsTest();
        $resp->addAssets('example');
        ob_start();
        $resp->output();
        $output = ob_get_clean();
        $this->assertEquals('<!DOCTYPE HTML>
<html lang="">
<head>
<meta content="text/html; charset=" http-equiv="content-type"/>
<title></title>
<link type="text/css" href="/absolute/path.css" rel="stylesheet" />

<link type="text/css" href="related/to/basepath.css" rel="stylesheet" />

<link type="text/css" href="/example3/path.css" rel="stylesheet" />

<script type="text/javascript" src="/absolute/path.js" ></script>
<script type="text/javascript" src="related/to/basepath.js" ></script>
<script type="text/javascript" src="ex3/basepath.js" ></script>
<script type="text/javascript" src="ex2/to/basepath.js" ></script>
</head><body >
</body></html>', $output);
    }
}
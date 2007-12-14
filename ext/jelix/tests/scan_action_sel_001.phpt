--TEST--
Test jelix_scan_action_sel with some action selectors
--SKIPIF--
<?php if (!extension_loaded("jelix")) print "skip"; ?>
--FILE--
<?php 
class obj {
    public $module;
    public $resource;
    public $request;
    public $controller;
    public $method;
}

$tests = array( 0=>"toto", 1=>"aaa~toto", 2=>"aa_AZ123aR~toPO:etto", 3=>"foo.bar~trucmuche", 4=>"#",
                5=>"#~foo", 6=>"foo~#", 7=>"#~#", 8=>"@classic", 9=>"foo.bar~trucmuche@classic",
                10=>"foo.bar~truc:muche@classic", 11=>"#@classic",  12=>"#~foo@classic", 
                13=>"foo~#@classic", 14=>"#~#@classic",
                15=>"testapp~ctrl:meth@truc", 16=>"testapp~:meth@truc", 17=>"testapp~meth@truc",
                18=>"testapp~ctrl:@truc", 19=>"testapp~@truc", 20=>"testapp~#@truc",
                21=>"testapp~ctrl:meth", 22=>"testapp~:meth", 23=>"testapp~meth",
                24=>"testapp~ctrl:", 25=>"testapp~", 26=>"testapp~#",
                27=>"#~ctrl:meth@truc", 28=>"#~:meth@truc", 29=>"#~meth@truc",
                30=>"#~ctrl:@truc", 31=>"#~@truc", 32=>"#~#@truc",
                33=>"#~ctrl:meth", 34=>"#~:meth", 35=>"#~meth",
                36=>"#~ctrl:", 37=>"#~", 38=>"#~#", 39=>"ctrl:meth@truc",
                40=>":meth@truc", 41=>"ctrl:@truc", 42=>"@truc",
                43=>"#@truc", 44=>"ctrl:meth", 45=>":meth",
                46=>"meth", 47=>"ctrl:", 48=>"",
                49=>"aa_AZ123aR~to_PO:etto",
                50=>"aa_AZ123aR~toPO:et_to",
                51=>"aa_AZ123aR~t_oPO:et_t_o",
 );

foreach($tests as $k=>$t){

    $obj = new obj();
    $ret = jelix_scan_action_sel($t, $obj, "machin:bidule");
    echo $k,":";
    if($ret === true){
        echo "ok\n";
    }else{
        var_export($ret);
        echo "\n";
    }

    echo 'm=',$obj->module,"\n";
    echo 'r=',$obj->resource,"\n";
    echo 'q=',$obj->request,"\n";
    echo 'c=',$obj->controller,"\n";
    echo 'm=',$obj->method,"\n";
}
?>
--EXPECT--
0:ok
m=
r=default:toto
q=
c=default
m=toto
1:ok
m=aaa
r=default:toto
q=
c=default
m=toto
2:ok
m=aa_AZ123aR
r=toPO:etto
q=
c=toPO
m=etto
3:ok
m=foo.bar
r=default:trucmuche
q=
c=default
m=trucmuche
4:ok
m=
r=machin:bidule
q=
c=machin
m=bidule
5:ok
m=#
r=default:foo
q=
c=default
m=foo
6:ok
m=foo
r=machin:bidule
q=
c=machin
m=bidule
7:ok
m=#
r=machin:bidule
q=
c=machin
m=bidule
8:ok
m=
r=default:index
q=classic
c=default
m=index
9:ok
m=foo.bar
r=default:trucmuche
q=classic
c=default
m=trucmuche
10:ok
m=foo.bar
r=truc:muche
q=classic
c=truc
m=muche
11:ok
m=
r=machin:bidule
q=classic
c=machin
m=bidule
12:ok
m=#
r=default:foo
q=classic
c=default
m=foo
13:ok
m=foo
r=machin:bidule
q=classic
c=machin
m=bidule
14:ok
m=#
r=machin:bidule
q=classic
c=machin
m=bidule
15:ok
m=testapp
r=ctrl:meth
q=truc
c=ctrl
m=meth
16:ok
m=testapp
r=default:meth
q=truc
c=default
m=meth
17:ok
m=testapp
r=default:meth
q=truc
c=default
m=meth
18:ok
m=testapp
r=ctrl:index
q=truc
c=ctrl
m=index
19:ok
m=testapp
r=default:index
q=truc
c=default
m=index
20:ok
m=testapp
r=machin:bidule
q=truc
c=machin
m=bidule
21:ok
m=testapp
r=ctrl:meth
q=
c=ctrl
m=meth
22:ok
m=testapp
r=default:meth
q=
c=default
m=meth
23:ok
m=testapp
r=default:meth
q=
c=default
m=meth
24:ok
m=testapp
r=ctrl:index
q=
c=ctrl
m=index
25:ok
m=testapp
r=default:index
q=
c=default
m=index
26:ok
m=testapp
r=machin:bidule
q=
c=machin
m=bidule
27:ok
m=#
r=ctrl:meth
q=truc
c=ctrl
m=meth
28:ok
m=#
r=default:meth
q=truc
c=default
m=meth
29:ok
m=#
r=default:meth
q=truc
c=default
m=meth
30:ok
m=#
r=ctrl:index
q=truc
c=ctrl
m=index
31:ok
m=#
r=default:index
q=truc
c=default
m=index
32:ok
m=#
r=machin:bidule
q=truc
c=machin
m=bidule
33:ok
m=#
r=ctrl:meth
q=
c=ctrl
m=meth
34:ok
m=#
r=default:meth
q=
c=default
m=meth
35:ok
m=#
r=default:meth
q=
c=default
m=meth
36:ok
m=#
r=ctrl:index
q=
c=ctrl
m=index
37:ok
m=#
r=default:index
q=
c=default
m=index
38:ok
m=#
r=machin:bidule
q=
c=machin
m=bidule
39:ok
m=
r=ctrl:meth
q=truc
c=ctrl
m=meth
40:ok
m=
r=default:meth
q=truc
c=default
m=meth
41:ok
m=
r=ctrl:index
q=truc
c=ctrl
m=index
42:ok
m=
r=default:index
q=truc
c=default
m=index
43:ok
m=
r=machin:bidule
q=truc
c=machin
m=bidule
44:ok
m=
r=ctrl:meth
q=
c=ctrl
m=meth
45:ok
m=
r=default:meth
q=
c=default
m=meth
46:ok
m=
r=default:meth
q=
c=default
m=meth
47:ok
m=
r=ctrl:index
q=
c=ctrl
m=index
48:ok
m=
r=default:index
q=
c=default
m=index
49:ok
m=aa_AZ123aR
r=to_PO:etto
q=
c=to_PO
m=etto
50:ok
m=aa_AZ123aR
r=toPO:et_to
q=
c=toPO
m=et_to
51:ok
m=aa_AZ123aR
r=t_oPO:et_t_o
q=
c=t_oPO
m=et_t_o

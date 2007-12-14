--TEST--
Test jelix_scan_old_action_sel with some action selectors
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

$tests = array( 0=>"toto", 1=>"aaa~toto", 2=>"aa_AZ123aR~toPO_etto", 3=>"foo.bar~trucmuche", 4=>"#",
                5=>"#~foo", 6=>"foo~#", 7=>"#~#", 8=>"@classic", 9=>"foo.bar~trucmuche@classic",
                10=>"foo.bar~truc_muche@classic", 11=>"#@classic",  12=>"#~foo@classic", 
                13=>"foo~#@classic", 14=>"#~#@classic",
                15=>"testapp~ctrl_meth@truc", 16=>"testapp~_meth@truc", 17=>"testapp~meth@truc",
                18=>"testapp~ctrl_@truc", 19=>"testapp~@truc", 20=>"testapp~#@truc",
                21=>"testapp~ctrl_meth", 22=>"testapp~_meth", 23=>"testapp~meth",
                24=>"testapp~ctrl_", 25=>"testapp~", 26=>"testapp~#",
                27=>"#~ctrl_meth@truc", 28=>"#~_meth@truc", 29=>"#~meth@truc",
                30=>"#~ctrl_@truc", 31=>"#~@truc", 32=>"#~#@truc",
                33=>"#~ctrl_meth", 34=>"#~_meth", 35=>"#~meth",
                36=>"#~ctrl_", 37=>"#~", 38=>"#~#", 39=>"ctrl_meth@truc",
                40=>"_meth@truc", 41=>"ctrl_@truc", 42=>"@truc",
                43=>"#@truc", 44=>"ctrl_meth", 45=>"_meth",
                46=>"meth", 47=>"ctrl_", 48=>"",
 );

foreach($tests as $k=>$t){

    $obj = new obj();
    $ret = jelix_scan_old_action_sel($t, $obj, "machin_bidule");
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

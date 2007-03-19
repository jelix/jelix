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

$tests = array( 0=>"toto", 1=>"aaa~toto", 2=>"aa_AZ123aR~toPO_etto", 3=>"foo.bar~truc.muche", 4=>"#",
                5=>"#~foo", 6=>"foo~#", 7=>"#~#", 8=>"@classic", 9=>"foo.bar~truc.muche@classic",
                10=>"foo.bar~truc_muche@classic", 11=>"#@classic",  12=>"#~foo@classic", 
                13=>"foo~#@classic", 14=>"#~#@classic"

 );

foreach($tests as $k=>$t){

    $obj = new obj();
    $ret = jelix_scan_action_sel($t, $obj);
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
}
?>
--EXPECT--
0:ok
m=
r=toto
q=
1:ok
m=aaa
r=toto
q=
2:ok
m=aa_AZ123aR
r=toPO_etto
q=
3:ok
m=foo.bar
r=truc.muche
q=
4:ok
m=
r=#
q=
5:ok
m=#
r=foo
q=
6:ok
m=foo
r=#
q=
7:ok
m=#
r=#
q=
8:ok
m=
r=
q=classic
9:ok
m=foo.bar
r=truc.muche
q=classic
10:ok
m=foo.bar
r=truc_muche
q=classic
11:ok
m=
r=#
q=classic
12:ok
m=#
r=foo
q=classic
13:ok
m=foo
r=#
q=classic
14:ok
m=#
r=#
q=classic

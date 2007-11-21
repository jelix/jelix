--TEST--
Test jelix_scan_class_sel with some simple selectors
--SKIPIF--
<?php if (!extension_loaded("jelix")) print "skip"; ?>
--FILE--
<?php 
class obj {
    public $module;
    public $resource;
    public $className;
    public $subpath;
}

$tests = array("toto", "foo/bar", "/foo", "foo/bar/baz/truc", "foo/", "~foo", "foo/bar~baz",
 "mod~toto", "mod~foo/bar", "mod~/foo", "mod~foo/bar/baz/truc", "mod~foo/");

//"aaa~toto", "aa_AZ123aR~toPO__etto", "foo.bar~truc.muche");

foreach($tests as $k=>$t){

    $obj = new obj();
    $ret = jelix_scan_class_sel($t, $obj);
    echo $k,":";
    if($ret === true){
        echo "ok\n";
    }else{
        var_export($ret);
        echo "\n";
    }
    echo $obj->module,"\n";
    echo $obj->resource,"\n";
    echo $obj->subpath,"\n";
    echo $obj->className,"\n";
}
?>
--EXPECT--
0:ok

toto

toto
1:ok

foo/bar
foo/
bar
2:ok

/foo

foo
3:ok

foo/bar/baz/truc
foo/bar/baz/
truc
4:false




5:false




6:false




7:ok
mod
toto

toto
8:ok
mod
foo/bar
foo/
bar
9:ok
mod
/foo

foo
10:ok
mod
foo/bar/baz/truc
foo/bar/baz/
truc
11:false





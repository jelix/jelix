--TEST--
Test jelix_scan_selector with some action selectors
--SKIPIF--
<?php if (!extension_loaded("jelix")) print "skip"; ?>
--FILE--
<?php 
class obj {
    public $module;
    public $resource;
}

$tests = array("toto", "aaa~toto", "aa_AZ123aR~toPO__etto", "foo.bar~truc.muche",
                "#",  "#~foo");

foreach($tests as $k=>$t){

    $obj = new obj();
    $ret = jelix_scan_selector($t, $obj, JELIX_SEL_ACTION);
    echo $k,":";
    if($ret === true){
        echo "ok\n";
    }else{
        var_export($ret);
        echo "\n";
    }

    echo $obj->module,"\n";
    echo $obj->resource,"\n";
}
?>
--EXPECT--
0:ok

toto
1:ok
aaa
toto
2:ok
aa_AZ123aR
toPO__etto
3:ok
foo.bar
truc.muche
4:ok

#
5:ok
#
foo

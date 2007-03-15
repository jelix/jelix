--TEST--
Test jelix_scan_selector with some simple selectors
--SKIPIF--
<?php if (!extension_loaded("jelix")) print "skip"; ?>
--FILE--
<?php 
class obj {
    public $module;
    public $resource;
}



$tests = array("toto", "aaa~toto", "aa_AZ123aR~toPO__etto", "foo.bar~truc.muche");

foreach($tests as $k=>$t){

    $obj = new obj();
    $ret = jelix_scan_selector($t, $obj);
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

--TEST--
Test jelix_scan_selector with some bad simple selectors
--SKIPIF--
<?php if (!extension_loaded("jelix")) print "skip"; ?>
--FILE--
<?php 
class obj {
    public $module;
    public $resource;
}

$tests = array("a-b~toto", "ab~ro-ro", "~toPO__etto");

foreach($tests as $k=>$t){

    $obj = new obj();
    $ret = jelix_scan_selector($t, $obj);
    echo $k,":";
    if($ret === false){
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


1:ok


2:ok



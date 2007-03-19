--TEST--
Test jelix_scan_action_sel with some bad action selectors
--SKIPIF--
<?php if (!extension_loaded("jelix")) print "skip"; ?>
--FILE--
<?php 
class obj {
    public $module;
    public $resource;
    public $request;
}

$tests = array("a-b~toto", "ab~ro-ro", "~toPO__etto", 
   "#aaa", "##" , "aa#aa", "aaa#",
   "foo~#aaa", "foo~aa#aa", "foo~aaa#", "~@classic", "@", "#@");

foreach($tests as $k=>$t){

    $obj = new obj();
    $ret = jelix_scan_action_sel($t, $obj);
    echo $k,":";
    if($ret === false){
        echo "ok\n";
    }else{
        var_export($ret);
        echo "\n";
    }

    echo $obj->module,"!", $obj->resource,"!",$obj->resource,"\n";
}
?>
--EXPECT--
0:ok
!!
1:ok
!!
2:ok
!!
3:ok
!!
4:ok
!!
5:ok
!!
6:ok
!!
7:ok
!!
8:ok
!!
9:ok
!!
10:ok
!!
11:ok
!!
12:ok
!!

<?php

function selector($foo, $obj){
    if(preg_match("/^(([\w\.]+)~)?([\w\.]+)$/", $foo, $m)){
        if($m[1]!='' && $m[2]!=''){
            $obj->module = $m[2];
        }else{
            $obj->module = '';
        }
        $obj->resource = $m[3];
        return true;
    }else{
        return false;
    }
}


class obj {
    public $module;
    public $resource;
}

$t1 = microtime(true);

for($i=0; $i < 1000; $i++){
    $o = new obj();
    selector("aaa~bbb",$o);
}
$t2 = microtime(true);

for($i=0; $i < 1000; $i++){
    $o = new obj();
    jelix_scan_selector("aaa~bbb",$o);
}
$t3 = microtime(true);

echo "selector = ".($t2-$t1)."\n";
echo "jelix_scan_selector = ".($t3-$t2)."\n";
?>
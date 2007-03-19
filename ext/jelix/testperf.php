<?php

function module_selector($foo, $obj){
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

function action_selector($foo, $obj){
    if(preg_match("/^(?:([\w\.]+|\#)~)?([\w\.]+|\#)?(?:@([\w\.]+))?$/", $foo, $m)){
        $m=array_pad($m,4,'');
        if($m[1]!=''){
            if($m[1] == '#')
                $obj->module = '';
            else
                $obj->module = $m[1];
        }else{
            $obj->module = '';
        }
        if($m[2] == '#')
            $obj->resource = 'index';
        else
            $obj->resource = $m[2];

        $r = explode('_',$obj->resource);

        if(count($r) == 1){
            $obj->controller = 'default';
            $obj->method = $r[0]==''?'index':$r[0];
        }else{
            $obj->controller = $r[0]=='' ? 'default':$r[0];
            $obj->method = $r[1]==''?'index':$r[1];
        }
        $obj->resource = $obj->controller.'_'.$obj->method;
        if($m[3] != '' && $enableRequestPart)
            $obj->request = $m[3];
        else
            $obj->request = 'classic';

        return true;
    }else{
        return false;
    }
}



class obj {
    public $module;
    public $resource;
    public $request;
}

$t1 = microtime(true);

for($i=0; $i < 1000; $i++){
    $o = new obj();
    module_selector("aaa~bbb",$o);
}
$t2 = microtime(true);

for($i=0; $i < 1000; $i++){
    $o = new obj();
    jelix_scan_module_sel("aaa~bbb",$o);
}
$t3 = microtime(true);

echo "module_selector = ".($t2-$t1)."\n";
echo "jelix_scan_selector = ".($t3-$t2)."\n";


$t1 = microtime(true);

for($i=0; $i < 1000; $i++){
    $o = new obj();
    action_selector("aaa~bbb@classic",$o);
}
$t2 = microtime(true);

for($i=0; $i < 1000; $i++){
    $o = new obj();
    jelix_scan_action_sel("aaa~bbb@classic",$o);
}
$t3 = microtime(true);

echo "action_selector = ".($t2-$t1)."\n";
echo "jelix_scan_selector = ".($t3-$t2)."\n";





?>
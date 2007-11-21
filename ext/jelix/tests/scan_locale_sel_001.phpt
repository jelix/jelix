--TEST--
Test jelix_scan_class_sel with some simple selectors
--SKIPIF--
<?php if (!extension_loaded("jelix")) print "skip"; ?>
--FILE--
<?php 
class obj {
    public $module;
    public $resource;
    public $fileKey;
    public $messageKey;
}

$tests = array("toto", "foo.bar", ".foo", "foo.bar.baz.truc", "foo.", "~foo", "foo.bar~baz",
 "mod~toto", "mod~foo.bar", "mod~.foo", "mod~foo.bar.baz.truc", "mod~foo.", "jelix~errors.selector.invalid.target", "mod~");


foreach($tests as $k=>$t){

    $obj = new obj();

    $ret = jelix_scan_locale_sel($t, $obj);
    echo $k,":";
    if($ret === true){
        echo "ok\n";
    }else{
        var_export($ret);
        echo "\n";
    }

    echo $obj->module,"\n";
    echo $obj->resource,"\n";
    echo $obj->fileKey,"\n";
    echo $obj->messageKey,"\n";
}
?>
--EXPECT--
0:false




1:ok

foo
foo
bar
2:false




3:ok

foo
foo
bar.baz.truc
4:false




5:false




6:false




7:false




8:ok
mod
foo
foo
bar
9:false




10:ok
mod
foo
foo
bar.baz.truc
11:false




12:ok
jelix
errors
errors
selector.invalid.target
13:false





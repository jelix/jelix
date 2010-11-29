--TEST--
Use of jICoordPlugin interface
--SKIPIF--
<?php if (!extension_loaded("jelix")) print "skip"; ?>
--FILE--
<?php 

class myClass implements jICoordPlugin {

    function __construct($config) {
        
    }

	function beforeAction($param){
		echo "beforeAction\n";
	}
	function beforeOutput(){
		echo "beforeOutput\n";
	}
	function afterProcess(){
		echo "afterProcess\n";
	}

}

$c = new myClass(null);
$c->beforeAction('');
$c->beforeOutput();
$c->afterProcess();


?>
--EXPECT--
beforeAction
beforeOutput
afterProcess

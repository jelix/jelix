--TEST--
Use of jIPlugin interface
--SKIPIF--
<?php if (!extension_loaded("jelix")) print "skip"; ?>
--FILE--
<?php 

class myClass implements jIPlugin {

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

$c = new myClass();
$c->beforeAction('');
$c->beforeOutput();
$c->afterProcess();


?>
--EXPECT--
beforeAction
beforeOutput
afterProcess

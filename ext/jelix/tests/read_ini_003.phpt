--TEST--
Test jelix_read_ini on two ini file, with a predefined object
--SKIPIF--
<?php if (!extension_loaded("jelix")) print "skip"; ?>
--FILE--
<?php 
class myConf {
	public $toto;
	public $name;
}

$conf = new myConf();
$conf->name = "a good name";
$conf->toto = "a default value for toto";

jelix_read_ini("jelix.ini", $conf);
jelix_read_ini("jelix2.ini",$conf);

var_export($conf);
?>
--EXPECT--
myConf::__set_state(array(
   'toto' => 'titi',
   'name' => 'a good name',
   'foo' => 'bartitude',
   'uneSection' => 
  array (
    'plop' => 'thcouk',
    'ahah' => 'pffff',
    'troc' => 'situveux',
  ),
   'uneAutreSection' => 
  array (
    'look' => 'this',
    'machin' => 'bidule',
  ),
))
--TEST--
Test jelix_read_ini on two ini file
--SKIPIF--
<?php if (!extension_loaded("jelix")) print "skip"; ?>
--FILE--
<?php 
$conf = jelix_read_ini("jelix.ini");

jelix_read_ini("jelix2.ini",$conf);

var_export($conf);
?>
--EXPECT--
stdClass::__set_state(array(
   'foo' => 'bartitude',
   'toto' => 'titi',
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
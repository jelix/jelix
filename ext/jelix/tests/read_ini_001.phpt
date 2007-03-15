--TEST--
Test jelix_read_ini on a single ini file
--SKIPIF--
<?php if (!extension_loaded("jelix")) print "skip"; ?>
--FILE--
<?php 
$conf = jelix_read_ini("jelix.ini");
var_export($conf);
?>
--EXPECT--
stdClass::__set_state(array(
   'foo' => 'bar',
   'toto' => 'titi',
   'uneSection' => 
  array (
    'plop' => 'thcouk',
    'ahah' => 'pffff',
  ),
   'uneAutreSection' => 
  array (
    'look' => 'that',
  ),
))
--TEST--
Test jelix constants
--SKIPIF--
<?php if (!extension_loaded("jelix")) print "skip"; ?>
--FILE--
<?php 
echo JELIX_SEL_MODULE ,"\n";
echo JELIX_SEL_ACTION ,"\n";
echo JELIX_SEL_LOCALE ,"\n";
echo JELIX_SEL_SIMPLEFILE ,"\n";
?>
--EXPECT--
1
2
3
4

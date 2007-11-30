--TEST--
Test jelix constants
--SKIPIF--
<?php if (!extension_loaded("jelix")) print "skip"; ?>
--FILE--
<?php 
echo  JELIX_NAMESPACE_BASE,"\n";
?>
--EXPECT--
http://jelix.org/ns/

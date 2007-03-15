--TEST--
Test jelix_version()
--SKIPIF--
<?php if (!extension_loaded("jelix")) print "skip"; ?>
--FILE--
<?php 
echo 'Version='.jelix_version();
?>
--EXPECT--
Version=0.1
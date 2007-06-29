--TEST--
Check for jICoordPlugin interface
--SKIPIF--
<?php if (!extension_loaded("jelix")) print "skip"; ?>
--FILE--
<?php 
if(interface_exists('jICoordPlugin', false)) echo "YES"; else echo "NO";
?>
--EXPECT--
YES
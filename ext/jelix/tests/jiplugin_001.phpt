--TEST--
Check for jIPlugin interface
--SKIPIF--
<?php if (!extension_loaded("jelix")) print "skip"; ?>
--FILE--
<?php 
if(interface_exists('jIPlugin', false)) echo "YES"; else echo "NO";
?>
--EXPECT--
YES
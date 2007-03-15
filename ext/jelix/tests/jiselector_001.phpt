--TEST--
Check for jISelector interface
--SKIPIF--
<?php if (!extension_loaded("jelix")) print "skip"; ?>
--FILE--
<?php 
if(interface_exists('jISelector', false)) echo "YES"; else echo "NO";
?>
--EXPECT--
YES
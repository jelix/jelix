--TEST--
Check for jIAuthDriver interface
--SKIPIF--
<?php if (!extension_loaded("jelix")) print "skip"; ?>
--FILE--
<?php 
if(interface_exists('jIAuthDriver', false)) echo "YES"; else echo "NO";
?>
--EXPECT--
YES
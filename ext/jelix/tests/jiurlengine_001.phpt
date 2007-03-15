--TEST--
Check for jIUrlEngine interface
--SKIPIF--
<?php if (!extension_loaded("jelix")) print "skip"; ?>
--FILE--
<?php 
if(interface_exists('jIUrlEngine', false)) echo "YES"; else echo "NO";
?>
--EXPECT--
YES
--TEST--
Check for jIUrlSignificantHandler interface
--SKIPIF--
<?php if (!extension_loaded("jelix")) print "skip"; ?>
--FILE--
<?php 
if(interface_exists('jIUrlSignificantHandler', false)) echo "YES"; else echo "NO";
?>
--EXPECT--
YES
--TEST--
Check for jIKVPersistent interface
--SKIPIF--
<?php if (!extension_loaded("jelix")) print "skip"; ?>
--FILE--
<?php 
if(interface_exists('jIKVPersistent', false)) echo "YES"; else echo "NO";
?>
--EXPECT--
YES
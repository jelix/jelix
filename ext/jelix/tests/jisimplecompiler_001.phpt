--TEST--
Check for jISimpleCompiler interface
--SKIPIF--
<?php if (!extension_loaded("jelix")) print "skip"; ?>
--FILE--
<?php 
if(interface_exists('jISimpleCompiler', false)) echo "YES"; else echo "NO";
?>
--EXPECT--
YES
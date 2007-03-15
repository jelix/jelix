--TEST--
Check for jIMultiFileCompiler interface
--SKIPIF--
<?php if (!extension_loaded("jelix")) print "skip"; ?>
--FILE--
<?php 
if(interface_exists('jIMultiFileCompiler', false)) echo "YES"; else echo "NO";
?>
--EXPECT--
YES
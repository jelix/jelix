--TEST--
Test jelix constants
--SKIPIF--
<?php if (!extension_loaded("jelix")) print "skip"; ?>
--FILE--
<?php 
echo  JELIX_NAMESPACE_BASE,"\n";
echo  JPDO_FETCH_OBJ,"\n";
echo  JPDO_FETCH_ORI_NEXT,"\n";
echo  JPDO_FETCH_ORI_FIRST,"\n";
echo  JPDO_FETCH_COLUMN,"\n";
echo  JPDO_FETCH_CLASS,"\n";
echo  JPDO_ATTR_STATEMENT_CLASS,"\n";
echo  JPDO_ATTR_AUTOCOMMIT,"\n";
echo  JPDO_ATTR_CURSOR,"\n";
echo  JPDO_CURSOR_SCROLL,"\n";
echo  JPDO_ATTR_ERRMODE,"\n";
echo  JPDO_ERRMODE_EXCEPTION,"\n";
echo  JPDO_MYSQL_ATTR_USE_BUFFERED_QUERY,"\n";

?>
--EXPECT--
http://jelix.org/ns/
5
0
2
7
8
13
0
10
1
3
2
1000

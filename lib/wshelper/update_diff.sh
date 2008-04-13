#!/bin/bash

diff -U 8 unpatched/IPPhpDoc.class.php IPPhpDoc.class.php > jelix.diff
diff -U 8 unpatched/IPReflectionClass.class.php IPReflectionClass.class.php >> jelix.diff
diff -U 8 unpatched/IPReflectionCommentParser.class.php IPReflectionCommentParser.class.php >> jelix.diff
diff -U 8 unpatched/IPReflectionMethod.class.php IPReflectionMethod.class.php >> jelix.diff
diff -U 8 unpatched/IPReflectionProperty.class.php IPReflectionProperty.class.php >> jelix.diff
diff -U 8 unpatched/IPXMLSchema.class.php IPXMLSchema.class.php >> jelix.diff
diff -U 8 unpatched/WSDLException.class.php WSDLException.class.php >> jelix.diff
diff -U 8 unpatched/WSDLStruct.class.php WSDLStruct.class.php >> jelix.diff
diff -U 8 unpatched/WSException.class.php WSException.class.php >> jelix.diff
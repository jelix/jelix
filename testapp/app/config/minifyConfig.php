<?php

// See Minify documentation/configuration to know this options
// values here are default values. You can configure only these options.
$min_allowDebugFlag = true;
$min_errorLogger = new debugMinify();
//$min_cacheFileLocking = true;
//$min_serveOptions['bubbleCssImports'] = false;
//$min_serveOptions['maxAge'] = 1800;
$min_serveOptions['minApp']['allowDirs'] = array('//testminify');
//$min_serveOptions['minApp']['maxFiles'] = 10;
$min_symlinks = array(
  '//jelix' => LIB_PATH.'jelix-www'
);
//$min_uploaderHoursBehind = 0;

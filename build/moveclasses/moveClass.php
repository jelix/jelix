<?php
require(__DIR__.'/../lib/jCmdUtils.class.php');
require(__DIR__.'/../lib/ManifestParser.php');

$sws = array();
$params = array('sourcedir'=>true, 'sourcefile'=>true, 'targetdir'=>true, 'targetfile'=>true);

list($switches, $p) = jCmdUtils::getOptionsAndParams($_SERVER['argv'], $sws, $params);

$p['targetdir'] = trim($p['targetdir'], '/');
$p['sourcedir'] = trim($p['sourcedir'], '/');

// ------------------ update manifests


$jelixLib = new ManifestParser(__DIR__.'/../manifests/jelix-lib.mn');
$jelixLib->parse();

$deprecated = new ManifestParser(__DIR__.'/../manifests/jelix-deprecated.mn');
$deprecated->parse();

$jelixLib->removeFile('lib/jelix-legacy/'.$p['sourcedir'], $p['sourcefile']);
$deprecated->addFile('lib/jelix-legacy/'.$p['sourcedir'], $p['sourcefile']);
$jelixLib->addFile('lib/Jelix/'.$p['targetdir'], $p['targetfile']);

$jelixLib->save();
$deprecated->save();

//-------------------  create dummy class

$namespace = str_replace('/', '\\', $p['targetdir']).'\\';

$newclass = $namespace.substr($p['targetfile'],0, strpos($p['targetfile'], '.'));
$oldclass = substr($p['sourcefile'],0, strpos($p['sourcefile'], '.'));

$template = file_get_contents(__DIR__.'/classtemplate.txt');
$template = str_replace('%%OLDCLASS%%', $oldclass, $template);
$template = str_replace('%%NEWCLASS%%', $newclass, $template);

$legacyDir = 'lib/Jelix/Legacy/'.$p['sourcedir'];

if (!file_exists($legacyDir))
    mkdir ($legacyDir, 775, true);

file_put_contents($legacyDir.'/'.$oldclass.'.php', $template);


// ----------------- update mapping.json

$mapping = json_decode(file_get_contents('lib/Jelix/Legacy/mapping.json'), true);
$mapping[$oldclass] = str_replace('lib/Jelix/Legacy/', '', $legacyDir).'/'.$oldclass.'.php';
file_put_contents('lib/Jelix/Legacy/mapping.json', json_encode($mapping, JSON_PRETTY_PRINT| JSON_UNESCAPED_SLASHES));


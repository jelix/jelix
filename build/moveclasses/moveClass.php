<?php
/**
* @package     jelix
* @author      Laurent Jouanneau
* @copyright   2014-2023 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

use Jelix\BuildTools\Cli\Params as CliParams;
use Jelix\BuildTools\Manifest as Manifest;

// we don't use the composer autoloader, as it load legacy init.php
require(__DIR__.'/../vendor/jelix/buildtools/lib/autoloader.php');

$sws = array();
$params = array('sourcedir'=>true, 'sourcefile'=>true, 'targetdir'=>true, 'targetfile'=>true);

list($switches, $p) = CliParams::getOptionsAndParams($_SERVER['argv'], $sws, $params);

$p['targetdir'] = trim($p['targetdir'], '/');
$p['sourcedir'] = trim($p['sourcedir'], '/');

$namespace = '\\Jelix\\'.str_replace('/', '\\', $p['targetdir']).'\\';

$newclass = $namespace.substr($p['targetfile'],0, strpos($p['targetfile'], '.'));
$oldclass = substr($p['sourcefile'],0, strpos($p['sourcefile'], '.'));

$legacyDir = 'lib/Jelix/Legacy/'.$p['sourcedir'];


// ------------------ update manifests

$jelixLib = new Manifest\Modifier(__DIR__.'/../manifests/jelix-lib.mn');
$jelixLib->parse();

$jelixLib->removeFile('lib/jelix-legacy/'.$p['sourcedir'], $p['sourcefile']);
$jelixLib->addFile('lib/Jelix/'.$p['targetdir'], $p['targetfile']);
$jelixLib->addFile($legacyDir, $oldclass.'.php');

$jelixLib->save();

//-------------------  create dummy class

if (!file_exists($legacyDir))
    mkdir ($legacyDir, 0775, true);

$template = file_get_contents(__DIR__.'/classtemplate.txt');
$template = str_replace('%%OLDCLASS%%', $oldclass, $template);
$template = str_replace('%%NEWCLASS%%', $newclass, $template);

file_put_contents($legacyDir.'/'.$oldclass.'.php', $template);


// ----------------- update mapping.json

$mapping = json_decode(file_get_contents('lib/Jelix/Legacy/mapping.json'), true);
$mapping[$oldclass] = str_replace('lib/Jelix/Legacy/', '', $legacyDir).'/'.$oldclass.'.php';
ksort($mapping);
file_put_contents('lib/Jelix/Legacy/mapping.json', json_encode($mapping, JSON_PRETTY_PRINT| JSON_UNESCAPED_SLASHES));

// ----------------- update newclassname.json

$mapping = json_decode(file_get_contents('lib/Jelix/Legacy/newclassname.json'), true);
$mapping[$oldclass] = $newclass;
ksort($mapping);
file_put_contents('lib/Jelix/Legacy/newclassname.json', json_encode($mapping, JSON_PRETTY_PRINT| JSON_UNESCAPED_SLASHES));

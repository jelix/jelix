<?php

use Jelix\IniFile\IniModifier;

require(__DIR__.'/../testapp/vendor/autoload.php');

$names = array();
$englishName = array();
$frenchNames = array();
$f = fopen(__DIR__.'/lang_names.txt', 'r');
while (!feof($f)) {

    $line = fgets($f);
    if (trim($line) == '') {
        continue;
    }
    $l = preg_split("/\t/u", $line);
    $code = trim($l[0]);
    $names[$code] = $l[4];
    $englishName[$code] = mb_substr($l[5], 0, -1);
    $frenchNames[$code] =$l[3];
}

file_put_contents(__DIR__.'/../lib/jelix/core/lang_names__.ini', "; ISO 639-1\n");
$ini = new IniModifier(__DIR__.'/../lib/jelix/core/lang_names__.ini');
$ini->setValue('names', $names);
$ini->save();

file_put_contents(__DIR__.'/../lib/jelix/core/lang_names_en.ini', "; ISO 639-1 - english\n");
$ini = new IniModifier(__DIR__.'/../lib/jelix/core/lang_names_en.ini');
$ini->setValue('names', $englishName);
$ini->save();

file_put_contents(__DIR__.'/../lib/jelix/core/lang_names_fr.ini', "; ISO 639-1 - french\n");
$ini = new IniModifier(__DIR__.'/../lib/jelix/core/lang_names_fr.ini');
$ini->setValue('names', $frenchNames);
$ini->save();
/*
Jelix\IniFile\Util::write(
    array(
        'names'=> array(
            'names'=>$names,
        )
    ), __DIR__.'/../lib/jelix/core/lang_names_.ini', '; ISO 639-1');

Jelix\IniFile\Util::write(
    array(
        'names'=> array(
            'names_en'=>$englishName,
        )
    ), __DIR__.'/../lib/jelix/core/lang_names_en.ini', '; ISO 639-1 - english');

Jelix\IniFile\Util::write(
    array(
        'names'=> array(
            'names_fr'=>$frenchNames
        )
    ), __DIR__.'/../lib/jelix/core/lang_names_fr.ini', '; ISO 639-1 - french');

*/


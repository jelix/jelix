<?php 
error_reporting(E_ALL);
include '../jtpl_standalone_prepend.php';

jTplConfig::$templatePath = dirname(__FILE__) . '/';

$tpl = new jTpl();

$countries = array('France', 'Italie', 'Espagne', 'Belgique');
$tpl->assign('countries', $countries);
$tpl->assign('titre', 'This is a test !');
$tpl->display('test.tpl');

$tpl = new jTpl();
$tpl->assign('titre', 'This is an other test !');
$tpl->display('foo/test.tpl');




<?php 


include 'jtpl/prepend.php';

$tpl = new jTpl();

$countries = array('France', 'Italie', 'Espagne', 'Belgique');
$tpl->assign('countries', $countries);
$tpl->assign('titre', 'Ceci est un test !!!!');
$tpl->display('test.tpl');



?>

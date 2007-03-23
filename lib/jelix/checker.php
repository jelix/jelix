<?php

/**
* check a jelix installation
*
* @package  jelix
* @subpackage core
* @author   Jouanneau Laurent
* @copyright 2007 Jouanneau laurent
* @link     http://www.jelix.org
* @licence  GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

include dirname(__FILE__).'/core/jInstallChecker.class.php';

class jHtmlInstallChecker implements jIInstallCheckReporter {
    function start(){
        echo '<ul class="checkresults">';
    }
    function showError($message){
        echo '<li class="checkerror">'.htmlspecialchars($message).'</li>';
    }
    function showWarning($message){
        echo '<li class="checkwarning">'.htmlspecialchars($message).'</li>';

    }
    function showOk($message){
        echo '<li class="checkok">'.htmlspecialchars($message).'</li>';

    }
    function showNotice($message){
        echo '<li class="checknotice">'.htmlspecialchars($message).'</li>';

    }
    function end($checker){
        echo '</ul>';
        echo '<div class="results">';
        if($checker->nbError){
            echo $checker->nbError, ' errors';
        }
        if($checker->nbWarning){
            echo $checker->nbWarning, ' warnings';
        }
    }
}
$check = new jInstallCheck(new jHtmlInstallChecker);

header("Content-type:text/html;charset=UTF-8");

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en_EN" lang="en_EN">
<head>
    <meta content="text/html; charset=UTF-8" http-equiv="content-type"/>
    <title>Installation checker for jelix</title>

    <style type="text/css">
    body {font-family: verdana, sans-serif;}

    ul.checkresults {
        border:3px solid black;
        margin: 2em;
        padding:1em;
        list-style-type:none;
        
    }
    ul.checkresults li { margin:0; padding:5px; border-top:1px solid black; }

    li.checkerror   { background-color:#ff6666;}
    li.checkok      { background-color:#a4ffa9;}
    li.checkwarning { background-color:#ffbc8f;}


    </style>

</head><body >
    <h1>Installation checker for jelix</h1>

<?php $check->run(); ?>
</body>
</html>







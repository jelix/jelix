<?php

/**
* check a jelix installation
*
* @package     jelix
* @subpackage  core
* @author      Jouanneau Laurent
* @copyright   2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
* @since       1.0b2
*/

/**
 *
 */
include dirname(__FILE__).'/core/jInstallChecker.class.php';

/**
 * an HTML reporter for jInstallChecker
 * @package jelix
 */
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
            echo ' ',$checker->nbError, $checker->messages->get( ($checker->nbError > 1?'number.errors':'number.error'));
        }
        if($checker->nbWarning){
            echo ' ',$checker->nbWarning, $checker->messages->get(($checker->nbWarning > 1?'number.warnings':'number.warning'));
        }
        if($checker->nbNotice){
            echo ' ',$checker->nbNotice, $checker->messages->get(($checker->nbNotice > 1?'number.notices':'number.notice'));
        }

        if($checker->nbError){
           echo '<p>',$checker->messages->get(($checker->nbError > 1?'conclusion.errors':'conclusion.error')),'</p>';
        }else  if($checker->nbWarning){
            echo '<p>',$checker->messages->get(($checker->nbWarning > 1?'conclusion.warnings':'conclusion.warning')),'</p>';
        }else  if($checker->nbNotice){
            echo '<p>',$checker->messages->get(($checker->nbNotice > 1?'conclusion.notices':'conclusion.notice')),'</p>';
        }else{
            echo '<p>',$checker->messages->get('conclusion.ok'),'</p>';
        }
        echo "</div>";
    }
}
$check = new jInstallCheck(new jHtmlInstallChecker);

header("Content-type:text/html;charset=UTF-8");

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $check->messages->getLang(); ?>" lang="<?php echo $check->messages->getLang(); ?>">
<head>
    <meta content="text/html; charset=UTF-8" http-equiv="content-type"/>
    <title><?php echo htmlspecialchars($check->messages->get('checker.title')); ?></title>

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
    <h1><?php echo htmlspecialchars($check->messages->get('checker.title')); ?></h1>

<?php $check->run(); ?>
</body>
</html>

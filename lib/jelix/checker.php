<?php

/**
* check a jelix installation
*
* @package     jelix
* @subpackage  core
* @author      Laurent Jouanneau
* @copyright   2007-2015 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
* @since       1.0b2
*/

require "installer/jIInstallReporter.iface.php";
require "installer/jInstallReporterTrait.trait.php";
require "installer/jInstallerMessageProvider.class.php";
require "installer/jInstallChecker.class.php";
require "db/jDbParameters.class.php";

/**
 * an HTML reporter for jInstallChecker
 * @package jelix
 */
class jHtmlInstallChecker implements jIInstallReporter {
    use jInstallerReporterTrait;

    /**
     * @var \jInstallerMessageProvider
     */
    protected $messageProvider;

    function __construct(jInstallerMessageProvider $messageProvider) {
        $this->messageProvider = $messageProvider;
    }

    function start(){
        echo '<ul class="checkresults">';
    }

    function message($message, $type=''){
        $this->addMessageType($type);
        echo '<li class="'.$type.'">'.htmlspecialchars($message).'</li>';
    }
    
    function end(){
        echo '</ul>';

        $nbError = $this->getMessageCounter('error');
        $nbWarning = $this->getMessageCounter('warning');
        $nbNotice = $this->getMessageCounter('notice');

        echo '<div class="results">';
        if ($nbError) {
            echo ' '.$nbError. $this->messageProvider->get( ($nbError > 1?'number.errors':'number.error'));
        }
        if ($nbWarning) {
            echo ' '.$nbWarning. $this->messageProvider->get(($nbWarning > 1?'number.warnings':'number.warning'));
        }
        if ($nbNotice) {
            echo ' '.$nbNotice. $this->messageProvider->get(($nbNotice > 1?'number.notices':'number.notice'));
        }

        if($nbError){
            echo '<p>'.$this->messageProvider->get(($nbError > 1?'conclusion.errors':'conclusion.error')).'</p>';
        }else if($nbWarning){
            echo '<p>'.$this->messageProvider->get(($nbWarning > 1?'conclusion.warnings':'conclusion.warning')).'</p>';
        }else if($nbNotice){
            echo '<p>'.$this->messageProvider->get(($nbNotice > 1?'conclusion.notices':'conclusion.notice')).'</p>';
        }else{
            echo '<p>'.$this->messageProvider->get('conclusion.ok').'</p>';
        }
        echo "</div>";
    }
}

$messages = new jInstallerMessageProvider();
$reporter = new jHtmlInstallChecker($messages);
$check = new jInstallCheck($reporter, $messages);
if (isset($_GET['verbose'])) {
    $check->verbose = true;
}
$check->addDatabaseCheck(array('mysqli', 'sqlite3', 'pgsql', 'oci', 'mssql'), false);

header("Content-type:text/html;charset=UTF-8");

?>
<!DOCTYPE html>
<html lang="<?php echo $check->messages->getLang(); ?>">
<head>
    <meta content="text/html; charset=UTF-8" http-equiv="content-type"/>
    <title><?php echo htmlspecialchars($check->messages->get('checker.title')); ?></title>
    <link type="text/css"  href="jelix/design/jelix.css" rel="stylesheet" />

</head><body >
    <h1 class="apptitle"><?php echo htmlspecialchars($check->messages->get('checker.title')); ?></h1>

<?php $check->run();

if (!$check->verbose) {
?>
<p><a href="?verbose"><?php echo htmlspecialchars($check->messages->get('more.details')); ?></a></p>
<?php } ?>
</body>
</html>

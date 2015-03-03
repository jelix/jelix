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

#includephp installer/jIInstallReporter.iface.php
#includephp installer/jInstallerMessageProvider.class.php
#includephp installer/jInstallChecker.class.php

/**
 * an HTML reporter for jInstallChecker
 * @package jelix
 */
class jHtmlInstallChecker implements jIInstallReporter {

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
        echo '<li class="'.$type.'">'.htmlspecialchars($message).'</li>';
    }
    
    function end($results){
        echo '</ul>';
        
        $nbError = $results['error'];
        $nbWarning = $results['warning'];
        $nbNotice = $results['notice'];

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

class checker extends jInstallCheckerBase {
    function __construct (jIInstallReporter $reporter, $messages) {
        parent::__construct ($reporter, $messages);
        $this->buildProperties = array(
#expand    'PHP_VERSION_TARGET'=>'__PHP_VERSION_TARGET__',
        );
    }
    static function getEnMessages() {
#includephp installer/messageProvider.en.inc.php
    }
    static function getFrMessages() {
#includephp installer/messageProvider.fr.inc.php
    }
}


$en = array_merge(checker::getEnMessages(),
                  array(
#expand             'checker.title'   =>'Check your configuration server for Jelix __LIB_VERSION__',
#expand             'conclusion.error'    =>'You must fix the error in order to run an application correctly with Jelix __LIB_VERSION__.',
#expand             'conclusion.errors'   =>'You must fix errors in order to run an application correctly with Jelix __LIB_VERSION__.',
#expand             'conclusion.warning'  =>'Your application for Jelix __LIB_VERSION__ may run without problems, but it is recommanded to fix the warning.',
#expand             'conclusion.warnings' =>'Your application for Jelix __LIB_VERSION__ may run without problems, but it is recommanded to fix warnings.',
#expand             'conclusion.notice'   =>'You can install an application for Jelix __LIB_VERSION__, although there is a notice.',
#expand             'conclusion.notices'  =>'You can install an application for Jelix __LIB_VERSION__, although there are notices.',
#expand             'conclusion.ok'       =>'You can install an application for Jelix __LIB_VERSION__.',
));

$fr = array_merge(checker::getFrMessages(),
                  array(
#expand             'checker.title'=>'Vérification de votre serveur pour Jelix __LIB_VERSION__',
#expand             'conclusion.error'      =>'Vous devez corriger l\'erreur pour faire fonctionner correctement une application Jelix __LIB_VERSION__.',
#expand             'conclusion.errors'     =>'Vous devez corriger les erreurs pour faire fonctionner correctement une application Jelix __LIB_VERSION__.',
#expand             'conclusion.warning'    =>'Une application Jelix __LIB_VERSION__ peut à priori fonctionner, mais il est préférable de corriger l\'avertissement pour être sûr.',
#expand             'conclusion.warnings'   =>'Une application Jelix __LIB_VERSION__ peut à priori fonctionner, mais il est préférable de corriger les avertissements pour être sûr.',
#expand             'conclusion.notice'     =>'Aucun problème pour installer une application pour Jelix  __LIB_VERSION__ malgré la remarque.',
#expand             'conclusion.notices'    =>'Aucun problème pour installer une application pour Jelix  __LIB_VERSION__ malgré les remarques.',
#expand             'conclusion.ok'         =>'Vous pouvez installer une application avec Jelix __LIB_VERSION__',
));


$messages = new jInstallerMessageProvider(
    '',
    array(
        'en' => $en,
        'fr' => $fr,
    )
);

$reporter = new jHtmlInstallChecker($messages);
$check = new checker($reporter, $messages);
$check->addDatabaseCheck(array('mysql','sqlite','pgsql'), false);

header("Content-type:text/html;charset=UTF-8");

?>
<!DOCTYPE html>
<html lang="<?php echo $check->messages->getLang(); ?>">
<head>
    <meta content="text/html; charset=UTF-8" http-equiv="content-type"/>
    <title><?php echo htmlspecialchars($check->messages->get('checker.title')); ?></title>

    <style type="text/css">
#includeraw ../jelix-www/design/jelix.css
</style>

</head><body >
    <h1 class="apptitle"><?php echo htmlspecialchars($check->messages->get('checker.title')); ?></h1>

<?php $check->run(); ?>
</body>
</html>

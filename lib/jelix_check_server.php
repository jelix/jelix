<?php
/**
* check a jelix installation. Standalone script. Copy it on a web server and call it
* with a browser.
*
* @author      Laurent Jouanneau
* @copyright   2007-2015 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
* @since       1.0b2
*/

#includephp Jelix/Installer/Checker/CheckerBase.php
#includephp Jelix/Installer/ReporterInterface.php
#includephp Jelix/Installer/Reporter/Html.php
#includephp Jelix/SimpleLocalization/Container.php

namespace {

    class checker extends \Jelix\Installer\Checker\CheckerBase {
        function __construct (\Jelix\Installer\ReporterInterface $reporter,
                          \Jelix\SimpleLocalization\Container $messages) {
            parent::__construct ($reporter, $messages);
            $this->buildProperties = array(
#expand    'PHP_VERSION_TARGET'=>'__PHP_VERSION_TARGET__',
            );
        }
        static function getEnMessages() {
#includephp Jelix/Installer/Checker/installmessages.en.php
        }
        static function getFrMessages() {
#includephp Jelix/Installer/Checker/installmessages.fr.php
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

    $messages = new \Jelix\SimpleLocalization\Container(
            array(
                'en' => $en,
                'fr' => $fr,
            )
        );
    $reporter =new \Jelix\Installer\Reporter\Html($messages);

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
#includeraw jelix-www/design/jelix.css
</style>

</head><body >
    <h1 class="apptitle"><?php echo htmlspecialchars($check->messages->get('checker.title')); ?></h1>

<?php $check->run(); ?>
</body>
</html>
<?php
}
?>

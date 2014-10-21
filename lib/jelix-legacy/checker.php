<?php
/**
* check a jelix installation
*
* @author      Laurent Jouanneau
* @copyright   2007-2014 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
* @since       1.0b2
*/

/**
 *
 */
#if STANDALONE_CHECKER
#includephp ../Jelix/Installer/ReporterInterface.php
#includephp ../Jelix/Installer/Reporter/Html.php
#includephp installer/jInstallerMessageProvider.class.php
#includephp installer/jInstallChecker.class.php
#else
include __DIR__.'/../Jelix/Installer/ReporterInterface.php';
include __DIR__.'/../Jelix/Installer/Reporter/Html.php';
include __DIR__.'/installer/jInstallerMessageProvider.class.php';
include __DIR__.'/installer/jInstallChecker.class.php';
#endif

$messages = new jInstallerMessageProvider();
$reporter =new \Jelix\Installer\Reporter\Html($messages);

$check = new jInstallCheck($reporter, $messages);
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

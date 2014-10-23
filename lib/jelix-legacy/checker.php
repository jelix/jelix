<?php
/**
* @author      Laurent Jouanneau
* @copyright   2007-2014 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
* @since       1.0b2
*/
namespace Jelix\Installer\Checker;

include __DIR__.'/installer/jInstallChecker.class.php';

/**
 * show a page with results of jelix environment checking
 */
class CheckerPage {

    static public function show() {

        $messages = new Messages();
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
    <link type="text/css"  href="jelix/design/jelix.css" rel="stylesheet" />
</style>

</head><body >
    <h1 class="apptitle"><?php echo htmlspecialchars($check->messages->get('checker.title')); ?></h1>

<?php $check->run(); ?>
</body>
</html>
<?php
   }
}


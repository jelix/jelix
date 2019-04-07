<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2007-2014 Laurent Jouanneau
 *
 * @see        http://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 *
 * @since       1.0b2
 */

namespace Jelix\Installer\Checker;

/**
 * show a page with results of jelix environment checking.
 */
class CheckerPage
{
    public static function show()
    {
        $messages = new Messages();
        $reporter = new \Jelix\Installer\Reporter\Html($messages);

        $check = new Checker($reporter, $messages);
        if (isset($_GET['verbose'])) {
            $check->verbose = true;
        }
        $check->addDatabaseCheck(array('mysqli', 'sqlite3', 'pgsql', 'oci', 'mssql', 'sqlsrv'), false);
        header('Content-type:text/html;charset=UTF-8'); ?>

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
<?php
        } ?>
</body>
</html>
<?php
    }
}

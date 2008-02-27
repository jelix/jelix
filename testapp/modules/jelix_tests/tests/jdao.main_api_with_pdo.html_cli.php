<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Jouanneau Laurent
* @contributor
* @copyright   2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(dirname(__FILE__).'/jdao.main_api.html_cli.php');
/**
 * same tests as UTDAO, but with a pdo connection
 */
class UTDaoPdo extends UTDao {
    protected $dbProfil ='testapppdo';
    protected $needPDO = true;

}
?>
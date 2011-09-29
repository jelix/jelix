<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2007 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(dirname(__FILE__).'/jdao_main_api.lib.php');
/**
 * same tests as UTDAO, but with a pdo connection
 */
class UTDaoPdo extends UTDao_base {
    protected $dbProfile ='testapppdo';
    protected $needPDO = true;

}
?>
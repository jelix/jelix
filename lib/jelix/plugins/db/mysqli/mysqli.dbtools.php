<?php
/**
* @package    jelix
* @subpackage db_driver
* @author     Gérald Croes, Laurent Jouanneau
* @contributor Laurent Jouanneau
* @contributor Florian Lonqueu-Brochard
* @copyright  2001-2005 CopixTeam, 2005-2011 Laurent Jouanneau
* @copyright  2012 Florian Lonqueu-Brochard
* This class was get originally from the Copix project (CopixDbToolsMysql, Copix 2.3dev20050901, http://www.copix.org)
* Some lines of code are copyrighted 2001-2005 CopixTeam (LGPL licence).
* Initial authors of this Copix class are Gerald Croes and Laurent Jouanneau,
* and this class was adapted/improved for Jelix by Laurent Jouanneau
*
* @link      http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

require_once(JELIX_LIB_PATH.'plugins/db/mysql/mysql.dbtools.php');

/**
 * Provides utilities methods for a mysql database
 * @package    jelix
 * @subpackage db_driver
 */
class mysqliDbTools extends mysqlDbTools {

    

    public function execSQLScript ($file) {
        if(!isset($this->_conn->profile['table_prefix']))
            $prefix = '';
        else
            $prefix = $this->_conn->profile['table_prefix'];
        $sqlQueries = str_replace('%%PREFIX%%', $prefix, file_get_contents($file));
        return $this->_conn->exec_multiple($sqlQueries);
    }

}

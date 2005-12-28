<?php
/**
* @package    jelix
* @subpackage db
* @version    $Id:$
* @author     Croes Gérald, Laurent Jouanneau
* @contributor Laurent Jouanneau
* @copyright  2001-2005 CopixTeam, 2005-2006 Laurent Jouanneau
* @link      http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*
* Classe orginellement issue du framework Copix 2.3dev20050901. http://www.copix.org (CopixDbTools)
* Une partie du code est sous Copyright 2001-2005 CopixTeam (licence LGPL)
* Auteurs initiaux : Gerald Croes et Laurent Jouanneau
* Adaptée et améliorée pour Jelix par Laurent Jouanneau
*/

/**
 * classe d'outils pour gérer une base de données
 */
abstract class jDbTools {
   protected $_connector;
    /**
    * @constructor
    */
   function __construct( $connector){
      $this->_connector = $connector;
   }

   /**
   * returns the table list
   */
   public function getTableList (){
      return $this->_getTableList ();
   }

   /**
   * return the field list of a given table
   */
   public function getFieldList ($tableName){
      return $this->_getFieldList ($tableName);
   }

   abstract protected function _getTableList ();
   abstract protected function _getFieldList ($tableName);

    protected $dbmsStyle = array();

                                        // comment     end of query
    protected $dbmsDefaultStyle = array('/^\s*#/', '/;\s*$/');

    public function execSQLScript ($file) {

        $lines = file($file);
        $cmdSQL = '';
        $nbCmd = 0;

        if(isset($this->dbmsStyle[$this->_connector->dbms])){
            $style=$this->dbmsStyle[$this->_connector->dbms];
        }else{
            $style=$this->dbmsDefaultStyle;
        }

        foreach ((array)$lines as $key=>$line) {
            if ((!preg_match($style[0],$line))&&(strlen(trim($line))>0)) { // la ligne n'est ni vide ni commentaire
               //$line = str_replace("\\'","''",$line);
               //$line = str_replace($this->scriptReplaceFrom, $this->scriptReplaceBy,$line);

                $cmdSQL.=$line;

                if (preg_match($style[1],$line)) {
                    //Si on est à la ligne de fin de la commande on l'execute
                    // On nettoie la commande du ";" de fin et on l'execute
                    $cmdSQL = preg_replace($style[1],'',$cmdSQL);
                    $this->_connector->query ($cmdSQL);
                    $nbCmd++;
                    $cmdSQL = '';
                }
            }
        }
        return $nbCmd;
    }


}
?>
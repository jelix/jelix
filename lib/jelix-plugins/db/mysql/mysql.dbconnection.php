<?php
/**
* @package    jelix
* @subpackage db
* @author     Croes Gérald, Laurent Jouanneau
* @contributor Laurent Jouanneau
* @copyright  2001-2005 CopixTeam, 2005-2007 Laurent Jouanneau
* @link      http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*
* Classe orginellement issue du framework Copix 2.3dev20050901. http://www.copix.org (CopixDbConnectionMysql)
* Une partie du code est sous Copyright 2001-2005 CopixTeam (licence LGPL)
* Auteurs initiaux : Gerald Croes et Laurent Jouanneau
* Adaptée et améliorée pour Jelix par Laurent Jouanneau
*/

/**
 *
 * @package    jelix
 * @subpackage db
 */
class mysqlDbConnection extends jDbConnection {

   protected $_charsets =array( 'UTF-8'=>'utf8', 'ISO-8859-1'=>'latin1');

   function __construct($profil){
      // à cause du @, on est obligé de tester l'existence de mysql, sinon en cas d'absence
      // on a droit à un arret sans erreur 
      if(!function_exists('mysql_connect')){
         throw new jException('jelix~db.error.nofunction','mysql');
      }
      parent::__construct($profil);
   }

   /**
   * begin a transaction
   */
   public function beginTransaction (){
      $this->_doExec ('SET AUTOCOMMIT=0');
      $this->_doExec ('BEGIN');
   }

   /**
   * Commit since the last begin
   */
   public function commit (){
      $this->_doExec ('COMMIT');
      $this->_doExec ('SET AUTOCOMMIT=1');
   }

   /**
   * Rollback since the last BEGIN
   */
   public function rollback (){
      $this->_doExec ('ROLLBACK');
      $this->_doExec ('SET AUTOCOMMIT=1');
   }

   /**
   *
   */
   public function prepare ($query){
       throw new jException('jelix~db.error.feature.unsupported', array('mysql','prepare'));
   }

   public function errorInfo(){
      return array( 'HY000' ,mysql_errno($this->_connection), mysql_error($this->_connection));
   }

   public function errorCode(){
      return mysql_errno($this->_connection);
   }

   protected function _connect (){
      $funcconnect= ($this->profil['persistent']? 'mysql_pconnect':'mysql_connect');
      if($cnx = @$funcconnect ($this->profil['host'], $this->profil['user'], $this->profil['password'])){
         if(isset($this->profil['force_encoding']) && $this->profil['force_encoding'] == true
            && isset($this->_charsets[$GLOBALS['gJConfig']->defaultCharset])){
             mysql_query("SET CHARACTER SET '".$this->_charsets[$GLOBALS['gJConfig']->defaultCharset]."'", $cnx);
         }
         return $cnx;
      }else{
         throw new jException('jelix~db.error.connection',$this->profil['host']);
      }
   }

   protected function _disconnect (){
      return mysql_close ($this->_connection);
   }


   protected function _doQuery ($query){

       // ici et non lors du connect, pour le cas où il y a plusieurs connexion active
      if(!mysql_select_db ($this->profil['database'], $this->_connection)){
         throw new jException('jelix~db.error.database.unknow',$this->profil['database']);
      }

      if ($qI = mysql_query ($query, $this->_connection)){
         return new mysqlDbResultSet ($qI);
      }else{
         throw new jException('jelix~db.error.query.bad',  mysql_error($this->_connection).'('.$query.')');
      }
   }

   protected function _doExec($query){
      if(!mysql_select_db ($this->profil['database'], $this->_connection))
         throw new jException('jelix~db.error.database.unknow',$this->profil['database']);

      if ($qI = mysql_query ($query, $this->_connection)){
         return mysql_affected_rows($this->_connection);
      }else{
         throw new jException('jelix~db.error.query.bad',  mysql_error($this->_connection).'('.$query.')');
      }
   }

   protected function _doLimitQuery ($queryString, $offset, $number){
     $queryString.= ' LIMIT '.$offset.','.$number;
     $result = $this->_doQuery($queryString);
     return $result;
   }


   public function lastInsertId($fromSequence=''){// on n'a pas besoin de l'argument pour mysql
      return mysql_insert_id ();
   }

   /**
   * tell mysql to be autocommit or not
   * @param boolean state the state of the autocommit value
   * @return void
   */
   protected function _autoCommitNotify ($state){
      $this->query ('SET AUTOCOMMIT='.$state ? '1' : '0');
   }

   /**
    * renvoi une chaine avec les caractères spéciaux échappés
    * @access private
    */
   protected function _quote($text){
      return mysql_real_escape_string($text,  $this->_connection );
   }

}
?>

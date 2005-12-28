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
* Classe orginellement issue du framework Copix 2.3dev20050901. http://www.copix.org (CopixDbConnectionMysql)
* Une partie du code est sous Copyright 2001-2005 CopixTeam (licence LGPL)
* Auteurs initiaux : Gerald Croes et Laurent Jouanneau
* Adaptée et améliorée pour Jelix par Laurent Jouanneau
*/

/**
 *
 */
class jDbConnectionMySQL extends jDbConnection {

   /**
   * begin a transaction
   */
   public function beginTransaction (){
      $this->_doQuery ('SET AUTOCOMMIT=0');
      $this->_doQuery ('BEGIN');
   }

   /**
   * Commit since the last begin
   */
   public function commit (){
      $this->_doQuery ('COMMIT');
      $this->_doQuery ('SET AUTOCOMMIT=1');
   }

   /**
   * Rollback since the last BEGIN
   */
   public function rollBack (){
      $this->_doQuery ('ROLLBACK');
      $this->_doQuery ('SET AUTOCOMMIT=1');
   }

   /**
   *
   */
   public function prepare ($query){
       throw new JException('jelix~db.error.feature.unsupported', array('mysql','prepare'));
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
         return $cnx;
      }else{
         throw new JException('jelix~db.error.connection',$this->profil['host']);
      }
   }

   protected function _disconnect (){
      return mysql_close ($this->_connection);
   }


   protected function _doQuery ($queryString){

       // ici et non lors du connect, pour le cas où il y a plusieurs connexion active
      if(!mysql_select_db ($this->profil['database'], $this->_connection))
         throw new JException('jelix~db.error.database.unknow',$this->profil['database']);

      if ($qI = mysql_query ($queryString, $this->_connection)){
         return new jDbResultSetMySQL ($qI);
      }else{
         throw new JException('jelix~db.error.query.bad',  mysql_error($this->_connection).'('.$queryString.')');
      }
   }

   protected function _doExec($query){
     if($this->_doQuery($query)){
         return mysql_affected_rows($this->_connection);
     }else
         return 0;
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

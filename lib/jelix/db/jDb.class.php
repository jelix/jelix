<?php
/**
* @package    jelix
* @subpackage db
* @version    $Id:$
* @author     Laurent Jouanneau
* @contributor
* @copyright  2005-2006 Laurent Jouanneau
* @link      http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*
* API inspirée de la classe CopixDbFactory issue du framework Copix 2.3dev20050901. http://www.copix.org
*/

/**
 *
 */
require_once(JELIX_LIB_DB_PATH.'jDbConnection.class.php');
require_once(JELIX_LIB_DB_PATH.'jDbResultSet.class.php');


/**
 * instancie les differents objets pour jDb
 * @package  jelix
 * @subpackage db
 */
class jDb {
    /**
    * Récupération d'une connection.
    * Utilise un pool local de connection
    * @param string  $name  nom du profil de connection définie dans la configuration
    * @return jDbConnection  objet de connexion vers la base de donnée
    */
    public static function getConnection ($name = null){
        static $cnxPool = array();

        $profil = self::getProfil ($name);

        if (!isset ($cnxPool[$name])){
           $cnxPool[$name] = self::_createConnector ($profil);
        }
        return $cnxPool[$name];
    }

    /**
     * création d'un objet jDBWidget
     */
    public static function getDbWidget($name=null){
        $dbw = new jDbWidget(self::getConnection($name));
        return $dbw;
    }

    /**
    * Récupération des outils de base de données
    * @param string $name Connection name to use
    * @return jDbTools
    */
    public static function getTools ($name=null){
        $profil = self::getProfil ($name);

        $driver = $profil['driver'];

        if($driver == 'pdo'){
           preg_match('/^(\w+)\:.*$/',$profil['dsn'], $m);
           $driver = $m[1];
        }

        //pas de vérification sur l'éventuel partage de l'élément.
        require_once(JELIX_LIB_DB_PATH.'/drivers/'.$driver.'/jDbTools.'.$driver.'.class.php');
        $class = 'jDbTools'.$driver;

        //Création de l'objet
        $cnx = self::getConnection ($name);
        $tools = new $class ($cnx);
        return $tools;
    }

    /**
    * récupération d'un profil de connexion à une base de données.
    * @param string  $name  nom du profil de connexion
    * @return    array   profil de connexion
    */
    public static function getProfil ($name=null){
        static $profils = null;
        global $gJConfig;
        if($profils === null){
           $profils = parse_ini_file(JELIX_APP_CONFIG_PATH.$gJConfig->dbProfils , true);
        }

        if($name == '' && isset($profils['default'])){
           $name=$profils['default'];
        }

        if(isset($profils[$name])){
           $profils[$name]['name'] = $name;
           return $profils[$name];
        }else{
           throw new jException('jelix~db.error.profil.unknow',$name);
        }
    }


    /**
     * pour tester les paramètres d'un profil (lors d'une installation par exemple)
     */
    public function testProfil($profil){
        try{
            self::_createConnector ($profil);
            $ok = true;
        }catch(Exception $e){
           $ok = false;
        }
        return $ok;
    }

    /**
    * création d'une connection.
    * @access private
    * @param string  $profil  nom du profil de connection
    * @return jDbConnection / PDO  l'objet de connection
    */
    private static function _createConnector ($profil){
        if($profil['driver'] == 'pdo'){
          $dbh = new jDbPDOConnection($profil);
          return $dbh;
        }else{

          require_once(JELIX_LIB_DB_PATH.'/drivers/'.$profil['driver'].'/jDbConnection.'.$profil['driver'].'.class.php');
          require_once(JELIX_LIB_DB_PATH.'/drivers/'.$profil['driver'].'/jDbResultSet.'.$profil['driver'].'.class.php');

          $class = 'jDbConnection'.$profil['driver'];

          //Création de l'objet
          $dbh = new $class ($profil);
          return $dbh;
        }
    }

}

?>
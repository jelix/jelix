<?php
/**
* @package    jelix
* @subpackage db
* @author     Laurent Jouanneau
* @contributor
* @copyright  2005-2007 Laurent Jouanneau
* @link      http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*
* API inspirée de la classe CopixDbFactory issue du framework Copix 2.3dev20050901. http://www.copix.org
*/
#if ENABLE_OPTIMIZED_SOURCE

#includephp jDbConnection.class.php
#includephp jDbResultSet.class.php

#else

/**
 *
 */
require_once(JELIX_LIB_DB_PATH.'jDbConnection.class.php');
require_once(JELIX_LIB_DB_PATH.'jDbResultSet.class.php');
#endif

/**
 * factory for database connector and other db utilities
 * @package  jelix
 * @subpackage db
 */
class jDb {
    /**
    * return a database connector
    * Use a local pool.
    * @param string  $name  profil name to use. if empty, use the default one
    * @return jDbConnection  connector
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
     * create a new jDbWidget
     * @param string  $name  profil name to use. if empty, use the default one
     * @return jDbWidget
     */
    public static function getDbWidget($name=null){
        $dbw = new jDbWidget(self::getConnection($name));
        return $dbw;
    }

    /**
    * instancy a jDbTools object
    * @param string $name profil name to use. if empty, use the default one
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
    * load properties of a connector profil
    * @param string  $name  profil name to load. if empty, use the default one
    * @return array  properties
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
     * call it to test a profil (during an install for example)
     * @param array  $profil  profil properties
     * @return boolean  true if properties are ok
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
    * create a connector
    * @param array  $profil  profil properties
    * @return jDbConnection|jDbPDOConnection  database connector
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
<?php
/**
* @package    jelix
* @subpackage db
#if ENABLE_OPTIMIZED_SOURCE
* @author     Laurent Jouanneau
* @contributor
* @copyright  2005-2007 Laurent Jouanneau
*
* Some of this classes were get originally from the Copix project
* (CopixDbConnection, Copix 2.3dev20050901, http://www.copix.org)
* Some lines of code are still copyrighted 2001-2005 CopixTeam (LGPL licence).
* Initial authors of this Copix classes are Gerald Croes and Laurent Jouanneau,
* and this classes were adapted/improved for Jelix by Laurent Jouanneau
*
* @link      http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

#includephp jDbConnection.class.php
#includephp jDbResultSet.class.php

#else
* @author     Laurent Jouanneau
* @contributor
* @copyright  2005-2007 Laurent Jouanneau
*
* API ideas of this class were get originally from the Copix project (CopixDbFactory, Copix 2.3dev20050901, http://www.copix.org)
* No lines of code are copyrighted by CopixTeam
*
* @link      http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

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

        global $gJConfig;
#ifnot ENABLE_OPTIMIZED_SOURCE
        if(!isset($gJConfig->_pluginsPathList_db[$driver])
            || !file_exists($gJConfig->_pluginsPathList_db[$driver]) ){
                throw new jException('jelix~db.error.driver.notfound', $driver);
        }
#endif
        require_once($gJConfig->_pluginsPathList_db[$driver].$driver.'.dbtools.php');
        $class = $driver.'DbTools';

        //Création de l'objet
        $cnx = self::getConnection ($name);
        $tools = new $class ($cnx);
        return $tools;
    }

    /**
    * load properties of a connector profil
    *
    * a profil is a section in the dbprofils.ini.php file
    *
    * with getProfil('myprofil') (or getProfil('myprofil', false)), you get the profil which
    * has the name "myprofil". this name should correspond to a section name in the ini file
    *
    * with getProfil('myprofiltype',true), it will search a parameter named 'myprofiltype' in the ini file. 
    * This parameter should contains a profil name, and the corresponding profil will be loaded.
    *
    * with getProfil(), it will load the default profil, (so the profil of "default" type)
    *
    * @param string   $name  profil name or profil type to load. if empty, use the default one
    * @param boolean  $nameIsProfilType  says if the name is a profil name or a profil type. this parameter exists since 1.0b2
    * @return array  properties
    */
    public static function getProfil ($name='', $nameIsProfilType=false){
        static $profils = null;
        global $gJConfig;
        if($profils === null){
           $profils = parse_ini_file(JELIX_APP_CONFIG_PATH.$gJConfig->dbProfils , true);
        }

        if($name == ''){
            if(isset($profils['default']))
                $name=$profils['default'];
            else
                throw new jException('jelix~db.error.default.profil.unknow');
        }elseif($nameIsProfilType){
            if(isset($profils[$name]) && is_string($profils[$name])){
                $name = $profils[$name];
            }else{
                throw new jException('jelix~db.error.profil.type.unknow',$name);
            }
        }

        if(isset($profils[$name]) && is_array($profils[$name])){
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
            global $gJConfig;
#ifnot ENABLE_OPTIMIZED_SOURCE
            if(!isset($gJConfig->_pluginsPathList_db[$profil['driver']])
                || !file_exists($gJConfig->_pluginsPathList_db[$profil['driver']]) ){
                    throw new jException('jelix~db.error.driver.notfound', $profil['driver']);
            }
#endif
            $p = $gJConfig->_pluginsPathList_db[$profil['driver']].$profil['driver'];
            require_once($p.'.dbconnection.php');
            require_once($p.'.dbresultset.php');

            //creating of the connection
            $class = $profil['driver'].'DbConnection';
            $dbh = new $class ($profil);
            return $dbh;
        }
    }

}

?>
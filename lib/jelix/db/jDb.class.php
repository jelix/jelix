<?php
/**
* @package     jelix
* @subpackage  db
#if ENABLE_OPTIMIZED_SOURCE
* @author      Laurent Jouanneau
* @contributor Yannick Le Guédart, Laurent Raufaste, Christophe Thiriot
* @copyright   2005-2007 Laurent Jouanneau, 2008 Laurent Raufaste
*
* Some of this classes were get originally from the Copix project
* (CopixDbConnection, Copix 2.3dev20050901, http://www.copix.org)
* Some lines of code are still copyrighted 2001-2005 CopixTeam (LGPL licence).
* Initial authors of this Copix classes are Gerald Croes and Laurent Jouanneau,
* and this classes were adapted/improved for Jelix by Laurent Jouanneau
*
* @link     http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

#includephp jDbConnection.class.php
#includephp jDbResultSet.class.php

#else
* @author     Laurent Jouanneau
* @contributor Yannick Le Guédart, Laurent Raufaste
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
require(JELIX_LIB_PATH.'db/jDbConnection.class.php');
require(JELIX_LIB_PATH.'db/jDbResultSet.class.php');
#endif

/**
 * factory for database connector and other db utilities
 * @package  jelix
 * @subpackage db
 */
class jDb {

    static private $_profiles =  null;
    static private $_cnxPool = array();

    /**
    * return a database connector
    * Use a local pool.
    * @param string  $name  profile name to use. if empty, use the default one
    * @return jDbConnection  connector
    */
    public static function getConnection ($name = null){
        $profile = self::getProfile ($name);

        if (!$name) {
            // we set the name to avoid two connection for a same profile, when it is the default profile
            // and when we call getConnection two times, one with no name and on with the name
            $name = $profile['name'];
        }

        if (!isset(self::$_cnxPool[$name])) {
            self::$_cnxPool[$name] = self::_createConnector($profile);
        }
        return self::$_cnxPool[$name];
    }

    /**
     * create a new jDbWidget
     * @param string  $name  profile name to use. if empty, use the default one
     * @return jDbWidget
     */
    public static function getDbWidget($name=null){
        $dbw = new jDbWidget(self::getConnection($name));
        return $dbw;
    }

    /**
    * instancy a jDbTools object
    * @param string $name profile name to use. if empty, use the default one
    * @return jDbTools
    */
    public static function getTools ($name=null){
        $profile = self::getProfile ($name);

        $driver = $profile['driver'];

        if($driver == 'pdo'){
            preg_match('/^(\w+)\:.*$/',$profile['dsn'], $m);
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
    * load properties of a connector profile
    *
    * a profile is a section in the dbprofils.ini.php file
    *
    * with getProfile('myprofile') (or getProfile('myprofile', false)), you get the profile which
    * has the name "myprofile". this name should correspond to a section name in the ini file
    *
    * with getProfile('myprofiletype',true), it will search a parameter named 'myprofiletype' in the ini file. 
    * This parameter should contains a profile name, and the corresponding profile will be loaded.
    *
    * with getProfile(), it will load the default profile, (so the profile of "default" type)
    *
    * @param string   $name  profile name or profile type to load. if empty, use the default one
    * @param boolean  $nameIsProfileType  says if the name is a profile name or a profile type. this parameter exists since 1.0b2
    * @return array  properties
    */
    public static function getProfile ($name='', $nameIsProfileType=false){
        global $gJConfig;
        if(self::$_profiles === null){
            self::$_profiles = parse_ini_file(JELIX_APP_CONFIG_PATH.$gJConfig->dbProfils , true);
        }

        if($name == ''){
            if(isset(self::$_profiles['default']))
                $name=self::$_profiles['default'];
            else
                throw new jException('jelix~db.error.default.profile.unknow');
        }elseif($nameIsProfileType){
            if(isset(self::$_profiles[$name]) && is_string(self::$_profiles[$name])){
                $name = self::$_profiles[$name];
            }else{
                throw new jException('jelix~db.error.profile.type.unknow',$name);
            }
        }

        if(isset(self::$_profiles[$name]) && is_array(self::$_profiles[$name])){
            self::$_profiles[$name]['name'] = $name;
            return self::$_profiles[$name];
        }else{
            throw new jException('jelix~db.error.profile.unknow',$name);
        }
    }

    /**
     * call it to test a profile (during an install for example)
     * @param array  $profile  profile properties
     * @return boolean  true if properties are ok
     */
    public function testProfile($profile){
        try{
            self::_createConnector ($profile);
            $ok = true;
        }catch(Exception $e){
            $ok = false;
        }
        return $ok;
    }

    /**
    * create a connector
    * @param array  $profile  profile properties
    * @return jDbConnection|jDbPDOConnection  database connector
    */
    private static function _createConnector ($profile){
        if($profile['driver'] == 'pdo'){
            $dbh = new jDbPDOConnection($profile);
            return $dbh;
        }else{
            global $gJConfig;
#ifnot ENABLE_OPTIMIZED_SOURCE
            if(!isset($gJConfig->_pluginsPathList_db[$profile['driver']])
                || !file_exists($gJConfig->_pluginsPathList_db[$profile['driver']]) ){
                throw new jException('jelix~db.error.driver.notfound', $profile['driver']);
            }
#endif
            $p = $gJConfig->_pluginsPathList_db[$profile['driver']].$profile['driver'];
            require_once($p.'.dbconnection.php');
            require_once($p.'.dbresultset.php');

            //creating of the connection
            $class = $profile['driver'].'DbConnection';
            $dbh = new $class ($profile);
            return $dbh;
        }
    }

    public static function createVirtualProfile ($name, $params) {
        global $gJConfig;
        if ($name == '') {
           throw new jException('jelix~db.error.virtual.profile.no.name');
        }

        if (! is_array ($params)) {
           throw new jException('jelix~db.error.virtual.profile.invalid.params', $name);
        }

        if (self::$_profiles === null) {
            self::$_profiles = parse_ini_file (JELIX_APP_CONFIG_PATH . $gJConfig->dbProfils, true);
        }
        self::$_profiles[$name] = $params;
        unset (self::$_cnxPool[$name]);
    }
}

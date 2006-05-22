<?php
/**
* @package    jelix
* @subpackage dao
* @version    $Id:$
* @author     Croes Gérald, Laurent Jouanneau
* @contributor Laurent Jouanneau
* @copyright  2001-2005 CopixTeam, 2005-2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*
* Une partie du code est issue de la classe CopixDAOFactory
* du framework Copix 2.3dev20050901. http://www.copix.org
* il est sous Copyright 2001-2005 CopixTeam (licence LGPL)
* Auteurs initiaux : Gerald Croes et Laurent Jouanneau
* Adaptée et améliorée pour Jelix par Laurent Jouanneau
*/


/**
* Factory to create automatic DAO.
*/
class jDao {

    /**
    * creates a DAO from its Id.
    * If no dao is founded, try to compile a DAO from the user definitions.
    */
    public static function create ($DaoId, $profil=''){
        $sel = new jSelectorDao($DaoId, $profil);
        if(!$sel->isValid())
           throw new jException('jelix~errors.selector.invalid',$sel->toString(true));

        $c = $sel->getDaoClass();
        if(!class_exists($c,false)){
            $results = jIncluder::inc($sel);
        }
        $conn = jDb::getConnection ($profil);
        $obj = new $c ($conn);
        return $obj;
    }

    /**
    * Creates a DAO from its ID. Handles a singleton of the DAO.
    */
    public static function get ($DaoId, $profil='') {
       static $_daoSingleton=array();

       $sel = new jSelectorDao($DaoId, $profil);
       $DaoId    = $sel->toString ();

        if (! isset ($_daoSingleton[$DaoId])){
            $_daoSingleton[$DaoId] = self::create ($DaoId,$profil);
        }
        return $_daoSingleton[$DaoId];
    }

    /**
    * creates a record object
    */
    public static function createRecord ($DaoId, $profil=''){
        $sel = new jSelectorDao($DaoId, $profil);
        $c = $sel->getDaoClass();
        if(!class_exists($c,false)){
            $results = jIncluder::inc($sel);
        }
        $c = $sel->getDaoRecordClass();
        $obj = new $c();
        return $obj;
    }

    public static function createConditions ($glueOp = 'AND'){
        $obj = new jDaoConditions ($glueOp);
        return $obj;
    }

}
?>

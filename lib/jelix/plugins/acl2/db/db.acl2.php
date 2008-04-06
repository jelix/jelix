<?php
/**
* @package     jelix
* @subpackage  acl_driver
* @author      Laurent Jouanneau
* @copyright   2006-2008 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * driver for jAcl2 based on a database
 * @package jelix
 * @subpackage acl_driver
 */
class dbAcl2Driver implements jIAcl2Driver {

    /**
     * 
     */
    function __construct (){ }


    protected static $aclres = array();
    protected static $acl = array();

    /**
     * return the value of the right on the given subject (and on the optional resource)
     * @param string $subject the key of the subject
     * @param string $resource the id of a resource
     * @return boolean true if the right is ok
     */
    public function getRight($subject, $resource=null){

        if($resource === null && isset(self::$acl[$subject])){
            return self::$acl[$subject];
        }elseif(isset(self::$aclres[$subject][$resource])){
            return self::$aclres[$subject][$resource];
        }

        if(!jAuth::isConnected()) // not authicated == no rights
            return false;

        $groups = jAcl2DbUserGroup::getGroups();

        if (count($groups) == 0) {
            self::$acl[$subject] = false;
            self::$aclres[$subject][$resource] = false;
            return false;
        }

        $hasRight = false;
        $dao = jDao::get('jelix~jacl2rights', jAcl2Db::getProfil());
        $right = $dao->getRight($subject, $groups);
        self::$acl[$subject] = $hasRight = ($right != false);

        if($resource !== null){
            if($hasRight) {
                self::$aclres[$subject][$resource] = true;
            }
            else {
                $right = $dao->getRightWithRes($subject, $groups, $resource);
                self::$aclres[$subject][$resource] = $hasRight = ($right != false);
            }
        }

        return $hasRight;
    }

    /**
     * clear right cache
     * @since 1.0b2
     */
    public function clearCache(){
        self::$acl = array();
        self::$aclres = array();
    }
    
}

?>
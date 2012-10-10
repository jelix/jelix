<?php
/**
* @package     jelix
* @subpackage  jacl2db module
* @author      Laurent Jouanneau
* @copyright   2011 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jacl2dbModuleUpgrader_newresdefaultvalue extends jInstallerModule {

    public $targetVersions = array('1.4.1', '1.5.2');
    public $date = '2012-10-10 12:45';

    protected $defaultDbProfile = 'jacl2_profile';

    function install() {
        if (!$this->firstDbExec())
            return;
        $cn = $this->dbConnection();
        try {
            $cn->beginTransaction();

            $cn->exec("UPDATE ".$cn->prefixTable('jacl2_rights')." 
            SET id_aclres='-' WHERE id_aclres='' OR id_aclres IS NULL");

            $cn->commit();
        } catch(Exception $e) {
            $cn->rollback();
            throw $e;
        }
    }
}
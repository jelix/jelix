<?php


class jelix_testsModuleUpgrader_testdaoset extends \Jelix\Installer\Module\Installer {

    function install(\Jelix\Installer\Module\API\InstallHelpers $helpers) {
        $db = $helpers->database()->dbConnection();
        $db->exec("ALTER TABLE ".$db->prefixTable('product_test')." ADD `dummy` set('created','started','stopped') DEFAULT NULL");
    }

}
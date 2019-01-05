<?php


class jelix_testsModuleUpgrader_productdate extends \Jelix\Installer\Module\Installer {

    function install(\Jelix\Installer\Module\API\InstallHelpers $helpers) {
        $db = $helpers->database()->dbConnection();
        $db->exec("ALTER TABLE ".$db->prefixTable('products')." ADD `publish_date` DATE NOT NULL");
    }
}
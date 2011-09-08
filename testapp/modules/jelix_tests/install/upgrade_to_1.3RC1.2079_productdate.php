<?php


class jelix_testsModuleUpgrader_productdate extends jInstallerModule {

    function install() {
        if (!$this->firstDbExec()) {
            return;
        }
        $db = $this->dbConnection();
        $db->exec("ALTER TABLE ".$db->prefixTable('products')." ADD `publish_date` DATE NOT NULL");
    }
}
<?php


class jelix_testsModuleUpgrader_productdate extends jInstallerModule2 {

    function installEntrypoint(\Jelix\Installer\EntryPoint $entryPoint) {
        if (!$this->firstDbExec()) {
            return;
        }
        $db = $this->dbConnection();
        $db->exec("ALTER TABLE ".$db->prefixTable('products')." ADD `publish_date` DATE NOT NULL");
    }
}
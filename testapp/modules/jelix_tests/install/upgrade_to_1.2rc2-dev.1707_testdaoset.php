<?php


class jelix_testsModuleUpgrader_testdaoset extends jInstallerModule2 {

    function installEntrypoint(\Jelix\Installer\EntryPoint $entryPoint) {
        if (!$this->firstDbExec()) {
            return;
        }
        $db = $this->dbConnection();
        $db->exec("ALTER TABLE ".$db->prefixTable('product_test')." ADD `dummy` set('created','started','stopped') DEFAULT NULL");
    }

}
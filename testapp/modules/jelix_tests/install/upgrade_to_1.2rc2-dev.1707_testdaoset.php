<?php


class jelix_testsModuleUpgrader_testdaoset extends jInstallerModule {

    function install() {
        if (!$this->firstDbExec()) {
            return;
        }
        $db = $this->dbConnection();
        $db->exec("ALTER TABLE ".$db->prefixTable('product_test')." ADD `dummy` set('created','started','stopped') DEFAULT NULL");
    }

}
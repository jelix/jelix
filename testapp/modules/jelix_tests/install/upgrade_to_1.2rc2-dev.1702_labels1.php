<?php

//upgrade_to_1.2b1pre.1303_bar.php

class jelix_testsModuleUpgrader_labels1 extends jInstallerModule2 {

    function installEntrypoint(\Jelix\Installer\EntryPoint $entryPoint) {
        if (!$this->firstDbExec()) {
            return;
        }
        $this->execSQLScript('upgrade_to_1.2RC2-dev.1702');
    }


}
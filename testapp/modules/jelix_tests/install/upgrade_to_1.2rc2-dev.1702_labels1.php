<?php

//upgrade_to_1.2b1pre.1303_bar.php

class jelix_testsModuleUpgrader_labels1 extends \Jelix\Installer\Module\Installer {

    function install(\Jelix\Installer\Module\API\InstallHelpers $helpers) {
        $helpers->database()->execSQLScript('upgrade_to_1.2RC2-dev.1702');
    }
}
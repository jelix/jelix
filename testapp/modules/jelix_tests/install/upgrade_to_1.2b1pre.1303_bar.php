<?php

//upgrade_to_1.2b1pre.1303_bar.php

class jelix_testsModuleUpgrader_bar extends \Jelix\Installer\Module\Installer {

    function preInstall(\Jelix\Installer\Module\API\PreInstallHelpers $helpers) {
       echo  "upgrader jelix_tests  1303 pre install\n"; 
    }

    /**
     * should configure the module, install table into the database etc..
     * If an error occurs during the installation, you are responsible
     * to cancel/revert all things the method did before the error
     * @throws jException  if an error occurs during the install.
     */
    function install(\Jelix\Installer\Module\API\InstallHelpers $helpers) {
       echo  "upgrader jelix_tests 1303 install\n"; 
    }

    /**
     * Redefine this method if you do some additionnal process after the installation of
     * all other modules (dependents modules or the whole application)
     * @throws jException  if an error occurs during the post installation.
     */
    function postInstall(\Jelix\Installer\Module\API\InstallHelpers $helpers) {
       echo  "upgrader jelix_tests 1303  post install\n"; 
    }

}
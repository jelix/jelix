<?php
require ('../application.init.php');
checkAppNotInstalled();

require('../../lib/installwizard/installWizard.php');

$config = '../install/wizard.ini.php';

$install = new installWizard($config);
$install->run();

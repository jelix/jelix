<?php
class testappModuleConfigurator extends \Jelix\Installer\Module\Configurator
{
    public function getDefaultParameters()
    {
        return array();
    }

    public function configure(Jelix\Installer\Module\API\ConfigurationHelpers $helpers)
    {
        $helpers->createEntryPoint('ep/newep.php', 'newep.php', 'ep/config.ini.php', '', 'classic');
    }

    public function localConfigure(Jelix\Installer\Module\API\LocalConfigurationHelpers $helpers)
    {

    }




}

<?php
/**
 * @package     jelix
 * @subpackage  jauthdb
 *
 * @author      Laurent Jouanneau
 * @copyright   2018-2023 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
use Jelix\Installer\Module\API\ConfigurationHelpers;

class jauthdbModuleConfigurator extends \Jelix\Installer\Module\Configurator
{
    public function getDefaultParameters()
    {
        return array(
            'defaultuser' => true,
        );
    }

    public function declareUrls(\Jelix\Routing\UrlMapping\EntryPointUrlModifier $registerOnEntryPoint)
    {
        // no controllers so no urls to declare
    }

    public function configure(ConfigurationHelpers $helpers)
    {
        $this->parameters['defaultuser'] = $helpers->cli()
            ->askConfirmation(
                'Do you want to create an "admin" user in authdb?',
                $this->parameters['defaultuser']
            )
        ;

        $confList = array();
        foreach ($helpers->getEntryPointsList() as $entryPoint) {
            $authConfig = $entryPoint->getCoordPluginConfig('auth');
            if (!$authConfig) {
                continue;
            }
            /** @var \Jelix\IniFile\IniModifier $conf */
            list($conf, $section) = $authConfig;

            $path = Jelix\FileUtilities\Path::shortestPath(jApp::appPath(), $conf->getFileName());
            if (!isset($confList[$path])) {
                $confList[$path] = true;
                $this->setupAuth($helpers, $conf, $section, $entryPoint->getConfigObj());
            }
        }
    }

    /**
     * @param \Jelix\IniFile\IniModifier $conf         auth.coord.plugin.ini.php or main configuration
     * @param string                     $section_auth section name containing the configuration of the auth plugin in $conf
     * @param object                     $epConfig     configuration of the entrypoint
     *
     * @throws \Jelix\IniFile\IniException
     * @throws jException
     */
    protected function setupAuth(ConfigurationHelpers $helpers, Jelix\IniFile\IniModifier $conf, $section_auth, $epConfig)
    {

        // a config for the auth plugin exists, so we can install
        // the module, else we ignore it

        if (isset($epConfig->coordplugin_auth['driver'])) {
            $driver = $epConfig->coordplugin_auth['driver'];
        } else {
            $driver = $conf->getValue('driver', $section_auth);
        }

        if ($section_auth === 0) {
            // the configuration file is a auth.coord.plugin.ini.php
            $section_driver = $driver ? $driver : 'Db';
        } else {
            // the configuration file is the main configuration file
            $section_driver = 'auth_'.($driver ? strtolower($driver) : 'db');
        }

        if ($driver == '') {
            $driver = 'Db';
            $conf->setValue('driver', $driver, $section_auth);
            $conf->setValue('dao', 'jauthdb~jelixuser', $section_driver);
            $conf->save();
        }

    }

}

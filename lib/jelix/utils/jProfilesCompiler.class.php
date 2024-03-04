<?php

/**
 * @package     jelix
 * @subpackage  profiles
 *
 * @author      Laurent Jouanneau
 * @copyright   2015-2023 Laurent Jouanneau
 *
 * @see         https://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * read and consolidate data from a profile, and store results into a cache file.
 */
class jProfilesCompiler
{
    /**
     * @var array representing ini content
     */
    protected $sources;

    /**
     * @param string path of the ini file, containing profiles data
     * @param mixed $sourceFile
     */
    public function __construct($sourceFile)
    {
        $this->sources = parse_ini_file($sourceFile, true, INI_SCANNER_TYPED);
    }

    /**
     * @var jProfilesCompilerPlugin[]
     */
    protected $plugins = array();

    /**
     * read all profiles from the source file, and returns an array
     * with consolidated data, processed by plugins.
     *
     * @return array consolidated data
     */
    public function compile()
    {
        $this->plugins = array();
        // sort to be sure to have categories sections and common sections before profiles sections
        ksort($this->sources);
        foreach ($this->sources as $name => $profile) {
            if (!is_array($profile)) {
                continue;
            }
            if (strpos($name, ':') === false) {
                // category section: it contains aliases
                if (!isset($this->plugins[$name])) {
                    $this->loadPlugin($name);
                }
                $this->plugins[$name]->setAliases($profile);
            } else {
                list($category, $pname) = explode(':', $name);
                if (!isset($this->plugins[$category])) {
                    $this->loadPlugin($category);
                }
                if ($pname == '__common__') {
                    $this->plugins[$category]->setCommon($profile);
                } else {
                    $this->plugins[$category]->addProfile($pname, $profile);
                }
            }
        }
        $profiles = array();
        foreach ($this->plugins as $plugin) {
            $plugin->getProfiles($profiles);
        }

        return $profiles;
    }

    protected function loadPlugin($name)
    {
        $plugin = jApp::loadPlugin($name, 'profiles', '.profiles.php', $name.'ProfilesCompiler', $name);
        if (!$plugin) {
            $plugin = new jProfilesCompilerPlugin($name);
        }
        $this->plugins[$name] = $plugin;
    }
}

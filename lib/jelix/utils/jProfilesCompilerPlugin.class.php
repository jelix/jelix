<?php

/**
 * @author      Laurent Jouanneau
 * @copyright   2015 Laurent Jouanneau
 *
 * @see        http://jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * default plugin for jProfilesCompiler, and base plugin for other plugins.
 */
class jProfilesCompilerPlugin
{
    protected $aliases = array();

    protected $common = array();

    protected $profiles = array();

    protected $category = '';

    /**
     * @param string $category the category of profiles
     */
    public function __construct($category)
    {
        $this->category = $category;
    }

    /**
     * @param array list of aliases  alias=>profile name
     * @param mixed $aliases
     */
    public function setAliases($aliases)
    {
        $this->aliases = $aliases;
    }

    /**
     * @param array list of options that will be share by other profile of the category
     * @param mixed $common
     */
    public function setCommon($common)
    {
        $this->common = $common;
    }

    /**
     * @param array list of options of a profile
     * @param mixed $name
     * @param mixed $profile
     */
    public function addProfile($name, $profile)
    {
        $this->profiles[$name] = $profile;
    }

    /**
     * @param array the array in which analysed profiles should be stored
     * @param mixed $profiles
     */
    public function getProfiles(&$profiles)
    {
        if (count($this->common)) {
            $profiles[$this->category.':__common__'] = $this->common;
        }
        foreach ($this->profiles as $name => $profile) {
            if (count($this->common)) {
                $profile = array_merge($this->common, $profile);
            }
            $profile['_name'] = $name;
            $profiles[$this->category.':'.$name] = $this->consolidate($profile);
        }
        foreach ($this->aliases as $alias => $profileName) {
            if (isset($profiles[$this->category.':'.$profileName])) {
                $profiles[$this->category.':'.$alias] = $profiles[$this->category.':'.$profileName];
            }
        }
    }

    /**
     * the method to be redefined in child class, to analyse, change, add, del, options in the
     * given profile data.
     *
     * @param array $profile the option of a profile. It already contains common options
     *
     * @return array final options values
     */
    protected function consolidate($profile)
    {
        return $profile;
    }
}

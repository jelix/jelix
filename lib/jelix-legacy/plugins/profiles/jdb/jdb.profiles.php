<?php

/**
 * @package     jelix
 * @subpackage  profiles
 *
 * @author      Laurent Jouanneau
 * @copyright   2015-2023 Laurent Jouanneau
 *
 * @see         https://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
class jdbProfilesCompiler extends \Jelix\Profiles\ReaderPlugin
{
    /**
     * @var string[][]
     */
    protected $uniqueProfiles = array();

    protected $timeouts = array();

    protected function consolidate($profile)
    {
        $options = array(
            'filePathParser' => 'jDb::parseSqlitePath'
        );

        $parameters = new \Jelix\Database\AccessParameters($profile, $options);

        $newProfile =  $parameters->getNormalizedParameters();
        if ($newProfile['driver'] != 'pgsql') {
            return $newProfile;
        }

        // we try to detect if the profile is already defined into another
        // profile. If this is the case, we declare it as an alias, so
        // jDb/jProfiles will use the same connector instead of creating two connectors.
        // If we don't do this, it duplicates connection, or, if the PHP extension
        // use the same real connection for both jDb connector, when jDb will
        // close connection, we will have an error on the second connection closing (because already closed)

        // parameters that are used for a connection and that identify a unique connection
        $connectionParameters = array( 'service', 'host', 'port', 'user', 'password', 'database',
            'timeout', 'pg_options', 'force_new');

        // parameters used to change some properties using the connection, and if there
        // are different between two profiles having same connection parameters, we should
        // have a different connection (by setting a different timeout)
        $settingParameters = array('search_path', 'session_role', 'single_transaction');

        $profileToTest = array_merge(array(
            'service' => '',
            'host' => '',
            'port' => 5432,
            'user' => '',
            'password' => '',
            'database' => '',
            'timeout' => 0,
            'pg_options' => '',
            'force_new' => 0,
            'search_path' => '',
            'session_role' => '',
            'single_transaction' => 0
        ), $newProfile);

        $connectionKey = '';
        foreach($connectionParameters as $p) {
            $connectionKey.='/'.$profileToTest[$p];
        }
        $settingKey = '';
        foreach($settingParameters as $p) {
            $settingKey.='/'.$profileToTest[$p];
        }

        if (isset($this->uniqueProfiles[$connectionKey])) {
            // we found a profile that have same connection parameters
            if (isset($this->uniqueProfiles[$connectionKey][$settingKey])) {
                // if search_path, session_role and single_transaction are the same values
                // then we can declare the profile as an alias
                $newProfile = $this->uniqueProfiles[$connectionKey][$settingKey];
            }
            else {
                // else, we modify the timeout to have a real different pgsql connection
                $timeout = $this->uniqueProfiles[$connectionKey]['timeout'];
                if ($timeout == 0) {
                    $timeout = 180;
                }
                while (in_array($timeout, $this->timeouts)) {
                    $timeout++;
                }
                $this->timeouts[] = $newProfile['timeout'] = $profileToTest['timeout'] = $timeout;

                $newConnectionKey = '';
                foreach($connectionParameters as $p) {
                    $newConnectionKey.='/'.$profileToTest[$p];
                }

                if (isset($this->uniqueProfiles[$newConnectionKey][$settingKey])) {
                    // maybe there is already a profile with the same new connection parameters,
                    // so we reuse same profile (aka, it is an alias).
                    $newProfile = $this->uniqueProfiles[$newConnectionKey][$settingKey];
                }
                else {
                    // we store the new profile, so other profiles having same connection parameter
                    // and new timeout will be an alias
                    $this->uniqueProfiles[$newConnectionKey][$settingKey] = $newProfile;
                    // we store the new profile as if it didn't change, so other profiles having same
                    // connection parameter and previous timeout will be an alias of the new profile
                    $this->uniqueProfiles[$connectionKey][$settingKey] = $newProfile;
                }
            }
        }
        else {
            // no profile with same connection parameters
            $this->uniqueProfiles[$connectionKey][$settingKey] = $newProfile;
            $timeout = intval($profileToTest['timeout']);
            if (!isset($this->uniqueProfiles[$connectionKey]['timeout'])) {
                $this->uniqueProfiles[$connectionKey]['timeout'] = $timeout;
            }
            $this->timeouts[] = $timeout;
        }

        return $newProfile;
    }
}

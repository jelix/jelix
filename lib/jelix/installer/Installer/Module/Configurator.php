<?php
/**
* @author      Laurent Jouanneau
* @copyright   2018 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
namespace Jelix\Installer\Module;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Helper\QuestionHelper;

/**
 * Base class for classes which configure a module
 * @since 1.7
 */
class Configurator implements ConfiguratorInterface {

    use InstallerHelpersTrait;
    use UninstallerHelpersTrait;

    /**
     * @var string name of the component
     */
    private $componentName;

    /**
     * @var string name of the installer
     */
    private $name;


    /**
     * The path of the module
     * @var string
     */
    private $path;

    /**
     * @var string the version for which the installer is called
     */
    private $version = '0';

    /**
     * global setup
     * @var \Jelix\Installer\GlobalSetup
     */
    private $globalSetup;

    private $forLocalConfiguration = false;

    /**
     * parameters for the installer, indicated in the configuration file or
     * dynamically, by a launcher in a command line for instance.
     * @var array
     */
    protected $parameters = array();

    /**
     * @var QuestionHelper
     */
    protected $questionHelper = null;

    /**
     * @var InputInterface
     */
    protected $consoleInput = null;

    /**
     * @var OutputInterface
     */
    protected $consoleOutput = null;

    /**
     * @param string $componentName name of the component
     * @param string $name name of the installer
     * @param string $path the component path
     * @param string $version version of the component
     */
    function __construct ($componentName, $name, $path, $version, $forLocalConfiguration = false) {
        $this->path = $path;
        $this->version = $version;
        $this->name = $name;
        $this->componentName = $componentName;
        $this->forLocalConfiguration = $forLocalConfiguration;
    }


    // ----- ConfiguratorInterface implementation

    /**
     * @inheritdoc
     */
    public function getDefaultParameters() {
        return array();
    }

    /**
     * @inheritdoc
     */
    public function setParameters($parameters) {
        $this->parameters = $parameters;
    }

    /**
     * @inheritdoc
     */
    public function askParameters() {

    }

    /**
     * @inheritdoc
     */
    public function getParameters() {
        return $this->parameters;
    }

    /**
     * @inheritdoc
     */
    public function preConfigure() {

    }

    /**
     * @inheritdoc
     */
    public function configure() {

    }

    /**
     * @inheritdoc
     */
    public function postConfigure() {

    }


    /**
     * @inheritdoc
     */
    public function preUnconfigure() {

    }

    /**
     * @inheritdoc
     */
    public function unconfigure() {

    }

    /**
     * @inheritdoc
     */
    public function postUnconfigure() {

    }

    // ----- other methods

    function getName() {
        return $this->name;
    }

    function getVersion() {
        return $this->version;
    }

    function getParameter($name) {
        if (isset($this->parameters[$name]))
            return $this->parameters[$name];
        else
            return null;
    }

    /**
     * @return bool true if the configuration is local, false if it is for the
     * application
     */
    function getConfigurationMode() {
        return $this->forLocalConfiguration;
    }


    function setGlobalSetup(\Jelix\Installer\GlobalSetup $setup) {
        $this->globalSetup = $setup;
    }

    function setInteractiveComponent(QuestionHelper $helper, InputInterface $input, OutputInterface $output) {
        $this->questionHelper = $helper;
        $this->consoleInput = $input;
        $this->consoleOutput = $output;
    }

    /**
     * Ask a confirmation.
     *
     * To call from askParameters().
     *
     * @param string $questionMessage the question
     * @param bool $defaultResponse the default response
     * @return boolean true it the user has confirmed
     */
    protected function askConfirmation($questionMessage, $defaultResponse = false) {
        $question = new ConfirmationQuestion($questionMessage, $defaultResponse);
        return $this->questionHelper->ask($this->consoleInput, $this->consoleOutput, $question);
    }

    /**
     * Ask a value to the user.
     *
     * To call from askParameters().
     *
     * @param string $questionMessage
     * @param bool $defaultResponse
     * @param string[]|false $autoCompleterValues list of values for autocompletion
     * @param callable|null $validator function to validate the value. It accepts
     *   a string as parameter, should return the value (may be modified), and
     *   should throw an exception when the value is invalid.
     * @return string the value given by the user
     */
    protected function askInformation($questionMessage, $defaultResponse = false,
                                      $autoCompleterValues = false, $validator = null) {
        $question = new Question($questionMessage, $defaultResponse);
        if (is_array($autoCompleterValues)) {
            $question->setAutocompleterValues($autoCompleterValues);
        }
        $question->setNormalizer(function ($value) {
            // $value can be null here
            return $value ? trim($value) : '';
        });

        if ($validator) {
            $question->setValidator($validator);
            $question->setMaxAttempts(10);
        }

        return $this->questionHelper->ask($this->consoleInput, $this->consoleOutput, $question);
    }

    /**
     * Ask a hidden value to the user, like a password
     *
     * To call from askParameters().
     *
     * @param string $questionMessage
     * @return string the value
     */
    protected function askSecretInformation($questionMessage, $defaultResponse = false) {
        $question = new Question($questionMessage, $defaultResponse);
        $question->setHidden(true);
        $question->setHiddenFallback(false);

        return $this->questionHelper->ask($this->consoleInput, $this->consoleOutput, $question);
    }

    /**
     * Ask a value from a choice
     *
     * To call from askParameters().
     *
     * @param string $questionMessage
     * @param array $choice list of possible values
     * @param int $defaultResponse the default value
     * @param bool $multipleChoice true if the user can choose different values
     * @param string $errorMessage error message when the user didn't indicate a value from the choice
     * @return string|string[]  responses from the user
     */
    protected function askInChoice($questionMessage, array $choice,
                                   $defaultResponse=0, $multipleChoice = false,
                                   $errorMessage='%s is invalid') {
        if (is_array($defaultResponse)) {
            $defaultResponse = implode(',', $defaultResponse);
        }
        $question = new ChoiceQuestion($questionMessage, $choice, $defaultResponse);
        $question->setErrorMessage($errorMessage);
        if ($multipleChoice) {
            $question->setMultiselect(true);
        }
        return $this->questionHelper->ask($this->consoleInput, $this->consoleOutput, $question);
    }

    /**
     * Ask to choose an entry point
     *
     * To call from askParameters().
     *
     * @param string $questionMessage
     * @param string $entryPointType the type of entry point. Empty value means any entry points
     * @param bool $multipleChoice true if the user can choose different entry point
     * @param string $errorMessage error message when the user didn't indicate a value from the choice
     * @return string|string[]|false  list of entry points id, selected by the user.
     *                                returns false if there is no choice
     */
    protected function askEntryPoints($questionMessage, $entryPointType='',
                                      $multipleChoice = false, $preselectedChoice = array(),
                                   $errorMessage='%s is an unknown entry points') {

        if ($entryPointType == '') {
            $choice = array_keys($this->globalSetup->getEntryPointsList());
        }
        else {
            $choice = array_keys($this->globalSetup->getEntryPointsByType($entryPointType));
        }
        if (!count($choice)) {
            return false;
        }

        if ($multipleChoice && count($choice) > 1) {
            if ($this->askConfirmation($questionMessage. ' Select all of these entry points: '.implode(', ',$choice).'?', false)) {
                return $choice;
            }
            $questionMessage .= "\nseveral values can be choice, separate them by a coma.";
        }

        $question = new ChoiceQuestion($questionMessage, $choice, implode(',', $preselectedChoice));
        $question->setErrorMessage($errorMessage);
        if ($multipleChoice && count($choice)) {
            $question->setMultiselect(true);
        }
        return $this->questionHelper->ask($this->consoleInput, $this->consoleOutput, $question);
    }


    private $dbProfileProperties = array(
        'mssql'=> array('host', 'database', 'user', 'password', 'persistent'),
        'mysqli'=> array('host', 'database', 'user', 'password', 'persistent',
            'force_encoding', 'ssl'),
        'oci'=> array(array('dsn', array('host', 'port', 'database')), 'user',
            'password', 'persistent'),
        'pgsql'=> array(array('service', array('host', 'port','database',
            'user', 'password')), 'persistent', 'force_encoding', 'timeout',
            'pg_options', 'search_path', 'single_transaction'),
        'sqlite3'=> array('database','persistent','extensions','busytimeout'),
        'sqlsrv'=> array('host','database','user','password','force_encoding'),
    );

    /**
     * Ask parameters to access to a database
     *
     * To call from askParameters().
     *
     */
    protected function askDbProfile($currentProfileValues = array()) {

        $profile = array();

        $profile['driver'] = $this->askInChoice(
            "Which is the type of your database?",
            array_keys($this->dbProfileProperties),
            (isset($currentProfileValues['driver'])?$currentProfileValues['driver']:'mysqli')
        );
        $properties = $this->dbProfileProperties[$profile['driver']];
        foreach($properties as $property) {
            if (is_array($property)) {
                if (!$this->askDbProperty($property[0], $profile, $currentProfileValues)) {
                    foreach($property[1] as $p) {
                        $this->askDbProperty($p, $profile, $currentProfileValues);
                    }
                }
            }
            else {
                $this->askDbProperty($property, $profile, $currentProfileValues);
            }
        }

        if ( $this->askConfirmation('Use a PDO to connect to the database?',
            (isset($currentProfileValues['usepdo'])?$currentProfileValues['usepdo']:false))
        ) {
            $profile['usepdo'] = true;
        }
        ;
        if ( $this->askConfirmation('For all tables accessible from this connection, are they name prefixed?',
            (isset($currentProfileValues['table_prefix']) && $currentProfileValues['table_prefix'])
            )
        ) {
            $value = $this->askConfirmation('Indicate the prefix',
                (isset($currentProfileValues['table_prefix'])?$currentProfileValues['table_prefix']:false));
            if ( $value ) {
                $profile['table_prefix'] = $value;
            }
        }

        return $profile;
    }

    private function askDbProperty($property, &$profile, &$currentProfileValues) {
        $defaultValue = (isset($currentProfileValues['$property'])?$currentProfileValues['$property']:false);
        switch($property) {
            case 'host':
                $host = $this->askInformation('Host of the database server', $defaultValue, array('localhost'));
                if ($host != '' || $profile['driver'] !== 'pgsql') {
                    $profile['host'] = $host;
                }
                break;

            case 'port':
                $port = $this->askInformation('Port of the database server',
                    $defaultValue, false, function($answer) {
                        if (!is_numeric($answer) || intval($answer) == 0) {
                            throw new \RuntimeException(
                                'The given value is not a number'
                            );
                        }
                        return $answer;
                    });
                if ($port) {
                    $profile['port'] = $port;
                }
                break;

            case 'database':
                if ($profile['driver'] == 'sqlite3') {
                    $question = 'The database file name';
                }
                else {
                    $question = 'The database name';
                }
                $profile['database'] = $this->askInformation($question, $defaultValue);
                break;

            case 'user':
                $profile['user'] = $this->askInformation('The login to authenticate against the database server', $defaultValue);
                break;

            case 'password':
                $profile['password'] = $this->askSecretInformation('The password to authenticate against the database server', $defaultValue);
                break;

            case 'persistent':
                $profile['persistent'] = $this->askConfirmation('Use a persistent connection?', $defaultValue);
                break;

            case 'force_encoding':
                $profile['force_encoding'] = $this->askConfirmation('Should the encoding be forced during the connection?', $defaultValue);
                break;

            case 'ssl':
                $profile['ssl'] = $this->askConfirmation('Use ssl to connect to the server?', $defaultValue);
                if ($profile['ssl']) {
                    $profile['ssl_key_pem'] = $this->askInformation('Path to the ssl key pem', $defaultValue);
                    $profile['ssl_cert_pem'] = $this->askInformation('Path to the ssl cert pem', $defaultValue);
                    $profile['ssl_cacert_pem'] = $this->askInformation('Path to the ssl cacert pem', $defaultValue);
                }
                break;

            case 'dsn':
                $dsn = $this->askInformation('Indicate the DSN to connect to the server, or leave empty to indicate host, database etc separately', $defaultValue);
                if ($dsn) {
                    $profile['dsn'] = $dsn;
                    return true;
                }
                else {
                    return false;
                }
                break;

            case 'service':
                $service = $this->askInformation('Indicate the service name to connect to the server, or leave empty to indicate host, database etc separately', $defaultValue);
                if ($service) {
                    $profile['service'] = $service;
                    return true;
                }
                else {
                    return false;
                }
                break;
            case 'timeout':
                $timeout = $this->askInformation('Connection timeout', $defaultValue);
                if ( $timeout ) {
                    $profile['timeout'] = $timeout;
                }
                break;

            case 'pg_options':
                $value = $this->askInformation('Options connection for Postgresql', $defaultValue);
                if ( $value ) {
                    $profile['pg_options'] = $value;
                }
                break;

            case 'search_path':
                $value = $this->askInformation('Search path for schema', $defaultValue);
                if ( $value ) {
                    $profile['search_path'] = $value;
                }
                break;

            case 'single_transaction':
                $value = $this->askConfirmation('Use a single transaction during the process of the http request?', $defaultValue);
                if ( $value ) {
                    $profile['single_transaction'] = $value;
                }

                break;

            case 'extensions':
                $value = $this->askInformation('Extensions to load', $defaultValue);
                if ( $value ) {
                    $profile['extensions'] = $value;
                }

                break;

            case 'busytimeout':
                $value = $this->askInformation('busy timeout (milliseconds)', $defaultValue);
                if ( $value ) {
                    $profile['busytimeout'] = $value;
                }
                break;
            default:
                return false;
        }
        return true;
    }


    /**
     * default config and main config combined
     * @return \Jelix\IniFile\IniModifierArray
     */
    protected function getConfigIni() {
        if ($this->forLocalConfiguration) {
            return $this->globalSetup->getLocalConfigIni();
        }
        return $this->globalSetup->getConfigIni();
    }

    /**
     * List of entry points of the application
     *
     * @return \Jelix\Installer\EntryPointConfigurator[]
     */
    protected function getEntryPointsList() {
        $list = $this->globalSetup->getEntryPointsList();
        $globalSetup = $this->globalSetup;
        $flc = $this->forLocalConfiguration;
        return array_map(function($ep) use($globalSetup, $flc) {
            return new \Jelix\Installer\EntryPointConfigurator($ep, $globalSetup, $flc);
        }, $list);
    }

    /**
     * @param string $type
     * @return \Jelix\Installer\EntryPointConfigurator[]
     */
    protected function getEntryPointsByType($type='classic') {
        $list = $this->globalSetup->getEntryPointsByType($type);
        $globalSetup = $this->globalSetup;
        $flc = $this->forLocalConfiguration;
        return array_map(function($ep) use($globalSetup, $flc) {
            return new \Jelix\Installer\EntryPointConfigurator($ep, $globalSetup, $flc);
        }, $list);
    }

    /**
     * @param $epId
     * @return \Jelix\Installer\EntryPointConfigurator
     */
    protected function getEntryPointsById($epId) {
        $ep = $this->globalSetup->getEntryPointById($epId);
        if ($ep) {
            $ep = new \Jelix\Installer\EntryPointConfigurator($ep, $this->globalSetup, $this->forLocalConfiguration);
        }
        return $ep;
    }

    protected function getProfilesIni() {
        return $this->globalSetup->getProfilesIni();
    }
}

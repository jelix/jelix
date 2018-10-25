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
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Helper\QuestionHelper;

class InteractiveConfigurator {

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
     * @var \Jelix\Scripts\InputHelpers
     */
    protected $inputHelpers = null;

    function __construct(QuestionHelper $helper,
                         InputInterface $input,
                         OutputInterface $output)
    {
        $this->questionHelper = $helper;
        $this->consoleInput = $input;
        $this->consoleOutput = $output;
        $this->inputHelpers = new \Jelix\Scripts\InputHelpers($helper, $input, $output);
    }

    /**
     * Ask a confirmation.
     *
     * @param string $questionMessage the question
     * @param bool $defaultResponse the default response
     * @return boolean true it the user has confirmed
     */
    public function askConfirmation($questionMessage, $defaultResponse = false) {
        return $this->inputHelpers->askConfirmation($questionMessage, $defaultResponse);
    }

    /**
     * Ask a value to the user.
     *
     * @param string $questionMessage
     * @param bool $defaultResponse
     * @param string[]|false $autoCompleterValues list of values for autocompletion
     * @param callable|null $validator function to validate the value. It accepts
     *   a string as parameter, should return the value (may be modified), and
     *   should throw an exception when the value is invalid.
     * @return string the value given by the user
     */
    public function askInformation($questionMessage, $defaultResponse = false,
                                      $autoCompleterValues = false, $validator = null) {
        return $this->inputHelpers->askInformation($questionMessage, $defaultResponse,
            $autoCompleterValues, $validator);
    }

    /**
     * Ask a hidden value to the user, like a password
     *
     * To call from askParameters().
     *
     * @param string $questionMessage
     * @return string the value
     */
    public function askSecretInformation($questionMessage, $defaultResponse = false) {
        return $this->inputHelpers->askSecretInformation($questionMessage, $defaultResponse);
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
    public function askInChoice($questionMessage, array $choice,
                                   $defaultResponse=0, $multipleChoice = false,
                                   $errorMessage='%s is invalid') {
        return $this->inputHelpers->askInChoice($questionMessage, $choice,
            $defaultResponse, $multipleChoice, $errorMessage);
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
    public function askEntryPoints($questionMessage, $entryPointType='',
                                      $multipleChoice = false, $preselectedChoice = array(),
                                      $errorMessage='%s is an unknown entry points') {
        $questionMessage = "<question>$questionMessage</question>";
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
            if ($this->inputHelpers->askConfirmation($questionMessage. "\n".' Select all of these entry points: '.implode(', ',$choice).'?', false)) {
                return $choice;
            }
            $questionMessage .= "\nseveral values can be choice, separate them by a coma.";
        }
        if ($preselectedChoice) {
            if (strpos($questionMessage, "\n") !== false) {
                $questionMessage.="\n";
            }
            $questionMessage .= " (default is '".implode(',', $preselectedChoice)."')";
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
    public function askDbProfile($currentProfileValues = array()) {

        $profile = array();

        $profile['driver'] = $this->inputHelpers->askInChoice(
            "Which is the type of your database? ",
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

        if ( $this->inputHelpers->askConfirmation('Use a PDO to connect to the database?',
            (isset($currentProfileValues['usepdo'])?$currentProfileValues['usepdo']:false))
        ) {
            $profile['usepdo'] = true;
        }
        ;
        if ( $this->inputHelpers->askConfirmation('For all tables accessible from this connection, are they name prefixed?',
            (isset($currentProfileValues['table_prefix']) && $currentProfileValues['table_prefix'])
        )
        ) {
            $value = $this->inputHelpers->askConfirmation('Indicate the prefix',
                (isset($currentProfileValues['table_prefix'])?$currentProfileValues['table_prefix']:false));
            if ( $value ) {
                $profile['table_prefix'] = $value;
            }
        }

        return $profile;
    }

    private function askDbProperty($property, &$profile, &$currentProfileValues) {
        $defaultValue = (isset($currentProfileValues[$property])?$currentProfileValues[$property]:false);
        switch($property) {
            case 'host':
                $host = $this->inputHelpers->askInformation('Host of the database server?', $defaultValue, array('localhost'));
                if ($host != '' || $profile['driver'] !== 'pgsql') {
                    $profile['host'] = $host;
                }
                break;

            case 'port':
                $port = $this->inputHelpers->askInformation('Port of the database server (leave empty for the default one)? ',
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
                $profile['database'] = $this->inputHelpers->askInformation($question, $defaultValue);
                break;

            case 'user':
                $profile['user'] = $this->inputHelpers->askInformation('The login to authenticate against the database server', $defaultValue);
                break;

            case 'password':
                $profile['password'] = $this->inputHelpers->askSecretInformation('The password to authenticate against the database server', $defaultValue);
                break;

            case 'persistent':
                $defaultValue = (isset($currentProfileValues[$property])?$currentProfileValues[$property]:true);
                $profile['persistent'] = $this->inputHelpers->askConfirmation('Use a persistent connection?', $defaultValue);
                break;

            case 'force_encoding':
                $profile['force_encoding'] = $this->inputHelpers->askConfirmation('Should the encoding be forced during the connection?', $defaultValue);
                break;

            case 'ssl':
                $profile['ssl'] = $this->inputHelpers->askConfirmation('Use ssl to connect to the server?', $defaultValue);
                if ($profile['ssl']) {
                    $profile['ssl_key_pem'] = $this->inputHelpers->askInformation('Path to the ssl key pem', $defaultValue);
                    $profile['ssl_cert_pem'] = $this->inputHelpers->askInformation('Path to the ssl cert pem', $defaultValue);
                    $profile['ssl_cacert_pem'] = $this->inputHelpers->askInformation('Path to the ssl cacert pem', $defaultValue);
                }
                break;

            case 'dsn':
                $dsn = $this->inputHelpers->askInformation('Indicate the DSN to connect to the server, or leave empty to indicate host, database etc separately', $defaultValue);
                if ($dsn) {
                    $profile['dsn'] = $dsn;
                    return true;
                }
                else {
                    return false;
                }
                break;

            case 'service':
                $service = $this->inputHelpers->askInformation('Indicate the service name to connect to the server, or leave empty to indicate host, database etc separately', $defaultValue);
                if ($service) {
                    $profile['service'] = $service;
                    return true;
                }
                else {
                    return false;
                }
                break;
            case 'timeout':
                $timeout = $this->inputHelpers->askInformation('Connection timeout', $defaultValue);
                if ( $timeout ) {
                    $profile['timeout'] = $timeout;
                }
                break;

            case 'pg_options':
                $value = $this->inputHelpers->askInformation('Options connection for Postgresql', $defaultValue);
                if ( $value ) {
                    $profile['pg_options'] = $value;
                }
                break;

            case 'search_path':
                $value = $this->inputHelpers->askInformation('Search path for schema', $defaultValue);
                if ( $value ) {
                    $profile['search_path'] = $value;
                }
                break;

            case 'single_transaction':
                $value = $this->inputHelpers->askConfirmation('Use a single transaction during the process of the http request?', $defaultValue);
                if ( $value ) {
                    $profile['single_transaction'] = $value;
                }

                break;

            case 'extensions':
                $value = $this->inputHelpers->askInformation('Extensions to load if any (names separated by a coma)', $defaultValue);
                if ( $value ) {
                    $profile['extensions'] = $value;
                }

                break;

            case 'busytimeout':
                $value = $this->inputHelpers->askInformation('Busy timeout (milliseconds) (default: empty)', $defaultValue);
                if ( $value ) {
                    $profile['busytimeout'] = $value;
                }
                break;
            default:
                return false;
        }
        return true;
    }

}
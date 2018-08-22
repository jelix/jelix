<?php
/**
* @package     jelix
* @subpackage  installer
* @author      Laurent Jouanneau
* @copyright   2018 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Helper\QuestionHelper;

/**
 *
 * @package     jelix
 * @subpackage  installer
 * @since 1.7
 */
class jInstallerModuleConfigurator implements jIInstallerComponentConfigurator {

    use jInstallerInstallerHelpersTrait;
    use jInstallerUninstallerHelpersTrait;

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
     * @var jInstallerGlobalSetup
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


    // ----- jIInstallerComponentConfigurator implementation

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

    function getConfigurationMode() {
        return $this->forLocalConfiguration;
    }


    function setGlobalSetup(jInstallerGlobalSetup $setup) {
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
     * @param string[]|false $autocompleterValues list of values for autocompletion
     * @param callable|null $validator function to validate the value. It accepts
     *   a string as parameter, should return the value (may be modified), and
     *   should throw an exception when the value is invalid.
     * @return string the value given by the user
     */
    protected function askInformation($questionMessage, $defaultResponse = false, $autocompleterValues = false, $validator = null) {
        $question = new Question($questionMessage, $defaultResponse);
        if (is_array($autocompleterValues)) {
            $question->setAutocompleterValues($autocompleterValues);
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
    protected function askSecretInformation($questionMessage) {
        $question = new Question($questionMessage);
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
     * @return jInstallerEntryPointConfigurator[]
     */
    protected function getEntryPointsList() {
        $list = $this->globalSetup->getEntryPointsList();
        $globalSetup = $this->globalSetup;
        $flc = $this->forLocalConfiguration;
        return array_map(function($ep) use($globalSetup, $flc) {
            return new jInstallerEntryPointConfigurator($ep, $globalSetup, $flc);
        }, $list);
    }

    /**
     * @param string $type
     * @return jInstallerEntryPointConfigurator[]
     */
    protected function getEntryPointsByType($type='classic') {
        $list = $this->globalSetup->getEntryPointsByType($type);
        $globalSetup = $this->globalSetup;
        $flc = $this->forLocalConfiguration;
        return array_map(function($ep) use($globalSetup, $flc) {
            return new jInstallerEntryPointConfigurator($ep, $globalSetup, $flc);
        }, $list);
    }

    /**
     * @param $epId
     * @return jInstallerEntryPointConfigurator
     */
    protected function getEntryPointsById($epId) {
        $ep = $this->globalSetup->getEntryPointById($epId);
        if ($ep) {
            $ep = new jInstallerEntryPointConfigurator($ep, $this->globalSetup, $this->forLocalConfiguration);
        }
        return $ep;
    }

    protected function getProfilesIni() {
        return $this->globalSetup->getProfilesIni();
    }
}

<?php

/**
 * @author      Laurent Jouanneau
 * @contributor Loic Mathaud
 * @contributor Gildas Givaja (bug #83)
 * @contributor Christophe Thiriot
 * @contributor Bastien Jaillot
 * @contributor Dominique Papin, Olivier Demah
 *
 * @copyright   2005-2019 Laurent Jouanneau, 2006 Loic Mathaud, 2007 Gildas Givaja, 2007 Christophe Thiriot, 2008 Bastien Jaillot, 2008 Dominique Papin
 * @copyright   2011 Olivier Demah
 *
 * @see        http://www.jelix.org
 * @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
 */

namespace Jelix\DevHelper\Command;

use Jelix\FileUtilities\Path;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateApp extends \Jelix\DevHelper\AbstractCommand
{
    const COMPJSON_NONE = 'none';
    const COMPJSON_NEW = 'new';
    const COMPJSON_CURRENT = 'current';
    const COMPJSON_NEW_CURRENT_JELIX = 'new-current-jelix';

    /**
     * @var \Symfony\Component\Console\Application
     */
    protected $appApplication;

    protected $defaultRuleForComposerJson = 'current';

    protected $forbiddenRuleForComposerJson = '';

    protected $jelixPath;
    protected $jelixInstalledAsComposerPackage;
    protected $vendorPath;

    /**
     * @var string on of COMPJSON_* const
     */
    protected $composerMode = '';

    public function __construct($jelixPath, $jelixAsComposerPackage, $vendorPath, $defaultRule, $forbiddenRule = '')
    {
        $this->defaultRuleForComposerJson = $defaultRule;
        $this->forbiddenRuleForComposerJson = $forbiddenRule;
        $this->jelixPath = $jelixPath;
        $this->vendorPath = $vendorPath;
        $this->jelixInstalledAsComposerPackage = $jelixAsComposerPackage;
        parent::__construct(new \Jelix\DevHelper\CommandConfig());
    }

    protected function configure()
    {
        $help = 'It creates all files for a new Jelix application.

Parameter is the path of the new application, which will be created for you.

The main option is --composer-mode, indicating how to setup the composer.json 
file. A value should be given to this option. 

Recognised values are:

- "none": it doesn\'t create a composer.json file, and application.init.php will
   include directly files from Jelix and from the vendor/directory. Use this
   option if you don\'t want to use Composer at all.
- "current": the composer.json provided with create-jelix-app will
  be used for the new application, and so it will be used by you to declare other packages.
- "new": it creates a new composer.json into the new application directory,
  and will install a new Jelix package.
- "new-current-jelix": creates a new composer.json into the new 
   application directory, but uses the Jelix sources installed with create-jelix-app.
   
Default option value: "'.$this->defaultRuleForComposerJson.'"
';
        if ($this->forbiddenRuleForComposerJson) {
            $help .= 'Because how create-jelix-app was installed, the value "'.$this->forbiddenRuleForComposerJson.'" is forbidden'."\n";
        }

        $this
            ->setName('app:create')
            ->setDescription('Creates an application')
            ->setHelp($help)
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'The path of the new application directory'
            )
            ->addOption(
                'composer-mode',
                null,
                InputOption::VALUE_REQUIRED,
                'indicates how to create the composer.json file, and so, how to include the jelix package',
                $this->defaultRuleForComposerJson
            )
            ->addOption(
                'no-default-module',
                null,
                InputOption::VALUE_NONE,
                'Indicate to not create a default module'
            )
            ->addOption(
                'module-name',
                null,
                InputOption::VALUE_REQUIRED,
                'The name of the default module. By default: the name of the application directory.'
            )
            ->addOption(
                'wwwpath',
                null,
                InputOption::VALUE_REQUIRED,
                'The path to the web directory'
            )
        ;
    }

    protected function prepareSubCommandApp()
    {
        if ($this->appApplication) {
            return;
        }
        $this->appApplication = new Application();
        $this->appApplication->add(new CreateCtrl($this->config));
        $this->appApplication->add(new CreateModule($this->config));
        $this->appApplication->add(new CreateEntryPoint($this->config));
    }

    protected function executeSubCommand($name, $arguments, $output)
    {
        $command = $this->appApplication->find($name);
        $input = new ArrayInput($arguments);

        return $command->run($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $appPath = $input->getArgument('path');
        $appPath = Path::normalizePath($appPath, 0, getcwd());
        // at this point, $appPath is always an absolute path
        $appName = basename($appPath);
        $appPath .= '/';

        if (file_exists($appPath.'/project.xml')) {
            throw new \Exception('this application is already created');
        }

        $this->composerMode = $input->getOption('composer-mode');
        if ($this->forbiddenRuleForComposerJson != '' && $this->composerMode == $this->forbiddenRuleForComposerJson) {
            throw new \UnexpectedValueException('The given composer-mode value is not available, because of how create-jelix-app was installed');
        }

        if (!in_array($this->composerMode, array('none', 'new', 'current', 'new-current-jelix'))) {
            throw new \UnexpectedValueException('Invalid value for composer-mode option');
        }

        $code = parent::execute($input, $output);
        if ($code) {
            return $code;
        }

        $this->config = \Jelix\DevHelper\JelixScript::loadConfig($appName);

        if ($input->isInteractive()) {
            $this->askAppInfos($input, $output);
        }
        $this->config->initAppPaths($appPath);
        $this->prepareSubCommandApp();

        \jApp::setEnv('jelix-scripts');

        \Jelix\Scripts\Utils::checkTempPath();

        if ($p = $input->getOption('wwwpath')) {
            $wwwpath = Path::shortestPath($appPath, $p).'/';
        } else {
            $wwwpath = \jApp::wwwPath();
        }

        if ($output->isVerbose()) {
            $output->writeln("Create directories and files at {$appPath}");
        }
        $param = $this->_createSkeleton($appPath, $appName, $wwwpath, $input);

        \jApp::declareModulesDir(array($appPath.'/modules/'));

        // launch configuration of the jelix module
        $reporter = new \Jelix\Installer\Reporter\Console(
            $output,
            ($output->isVerbose() ? 'notice' : 'error'),
            'Configuration'
        );
        $globalSetup = new \Jelix\Installer\GlobalSetup();
        $configurator = new \Jelix\Installer\Configurator(
            $reporter,
            $globalSetup,
            $this->getHelper('question'),
            $input,
            $output
        );

        $configurator->configureModules(array('jelix'), 'index', false, true);

        // launch the installer for this new application
        $reporter = new \Jelix\Installer\Reporter\Console(
            $output,
            ($output->isVerbose() ? 'notice' : 'warning'),
            'Installation'
        );
        $installer = new \Jelix\Installer\Installer($reporter, $globalSetup);
        $installer->installApplication();

        if (!$input->getOption('no-default-module')) {
            try {
                if ($output->isVerbose()) {
                    $output->writeln('Create default module '.$param['modulename']);
                }
                $options = array(
                    'module' => $param['modulename'],
                    '--add-install-zone' => true,
                    '--no-registration' => true,
                );
                if ($output->isVerbose()) {
                    $options['-v'] = true;
                }

                $this->executeSubCommand('module:create', $options, $output);
                if ($output->isVerbose()) {
                    $output->writeln('Create main template');
                }
                $this->createFile($appPath.'modules/'.$param['modulename'].'/templates/main.tpl', 'module/main.tpl.tpl', $param, 'Main template');
            } catch (\Exception $e) {
                $output->writeln('<error>The module has not been created because of this error: '.$e->getMessage().'</error>');
                $output->writeln('However the application has been created');
            }
        }
        return 0;
    }

    protected function askAppInfos(InputInterface $input, OutputInterface $output)
    {
        $cliHelpers = new \Jelix\Scripts\InputHelpers($this->getHelper('question'), $input, $output);
        $this->output->writeln('<comment>Please give some informations to store in file headers and module/project identity files</comment>');
        $this->config->newAppInfoWebsite = $cliHelpers->askInformation('The web site of your company', $this->config->newAppInfoWebsite);
        $this->config->generateUndefinedProperties(true);

        $this->config->newAppInfoLicence = $cliHelpers->askInformation('The licence of your application and modules', $this->config->newAppInfoLicence);
        $this->config->newAppInfoLicenceUrl = $cliHelpers->askInformation('The url to the licence if any', $this->config->newAppInfoLicenceUrl);
        $this->config->newAppInfoCopyright = $cliHelpers->askInformation('Copyright on your application and modules', $this->config->newAppInfoCopyright);
        $this->config->newAppInfoIDSuffix = $cliHelpers->askInformation('The suffix of your modules id', $this->config->newAppInfoIDSuffix);

        $this->config->infoCreatorName = $cliHelpers->askInformation('The creator name (your name for example)', $this->config->infoCreatorName);
        $this->config->infoCreatorMail = $cliHelpers->askInformation('The email of the creator', $this->config->infoCreatorMail);

        $this->config->copyAppInfo(false);
    }

    protected function convertRp($rp)
    {
        if (strpos($rp, './') === 0) {
            $rp = substr($rp, 2);
        }
        if (strpos($rp, '../') !== false) {
            return 'realpath(__DIR__.\'/'.$rp."').'/'";
        }
        if (DIRECTORY_SEPARATOR == '/' && $rp[0] == '/') {
            return "'".$rp."'";
        }
        if (DIRECTORY_SEPARATOR == '\\' && preg_match('/^[a-z]\:/i', $rp)) { // windows
            return "'".$rp."'";
        }

        return '__DIR__.\'/'.$rp."'";
    }

    /**
     * @param string $appPath absolute path to the new application directory
     * @param string $appName
     * @param string $wwwpath
     *
     * @return array list of relative path of some directories for the application
     */
    protected function _createSkeleton($appPath, $appName, $wwwpath, InputInterface $input)
    {
        $this->createDir($appPath);
        $this->createDir(\jApp::tempBasePath());
        $this->createDir($wwwpath);

        $varPath = \jApp::varPath();
        $configPath = \jApp::varConfigPath();
        $this->createDir($varPath);
        $this->createDir(\jApp::logPath());
        $this->createDir(\jApp::appSystemPath());
        $this->createDir($configPath);
        $this->createDir(\jApp::appSystemPath('index/'));
        $this->createDir(\jApp::appPath('app/overloads/'));
        $this->createDir(\jApp::appPath('app/themes'));
        $this->createDir(\jApp::appPath('app/themes/default/'));
        $this->createDir($varPath.'uploads/');
        $this->createDir($varPath.'sessions/');
        $this->createDir($varPath.'mails/');
        $this->createDir($varPath.'db/sqlite3/');

        $this->createDir($appPath.'install/uninstall/');
        $this->createDir($appPath.'modules');
        $this->createDir($appPath.'plugins');
        $this->createDir(\jApp::appPath('app/responses'));
        $this->createDir($appPath.'tests');

        $param = array();
        $param['default_id'] = $appName.$this->config->infoIDSuffix;

        if ($input->getOption('no-default-module')) {
            $param['tplname'] = 'jelix~defaultmain';
            $param['modulename'] = 'jelix';
        } else {
            $moduleName = $input->getOption('module-name');
            if (!$moduleName) {
                // note: since module name are used for name of generated name,
                // only this characters are allowed
                $moduleName = preg_replace('/([^a-zA-Z_0-9])/', '_', $appName);
            }
            $param['modulename'] = $moduleName;
            $param['tplname'] = $moduleName.'~main';
        }

        $param['config_file'] = 'index/config.ini.php';

        $param['rp_temp'] = Path::shortestPath($appPath, \jApp::tempBasePath()).'/';
        $param['rp_var'] = Path::shortestPath($appPath, \jApp::varPath()).'/';
        $param['rp_log'] = Path::shortestPath($appPath, \jApp::logPath()).'/';
        $param['rp_conf'] = Path::shortestPath($appPath, $configPath).'/';
        $param['rp_www'] = Path::shortestPath($appPath, $wwwpath).'/';
        $param['rp_app'] = Path::shortestPath($wwwpath, $appPath).'/';
        $param['php_rp_temp'] = $this->convertRp($param['rp_temp']);
        $param['php_rp_var'] = $this->convertRp($param['rp_var']);
        $param['php_rp_log'] = $this->convertRp($param['rp_log']);
        $param['php_rp_conf'] = $this->convertRp($param['rp_conf']);
        $param['php_rp_www'] = $this->convertRp($param['rp_www']);

        $param['jelix_package_name'] = 'jelix';
        $param['jelix_version'] = \jFramework::version();

        if ($this->composerMode == self::COMPJSON_NEW) {
            $param['rp_jelix'] = 'vendor/jelix/jelix/lib/jelix/';
            $param['rp_lib'] = 'vendor/jelix/jelix/lib/';
            $param['rp_vendor'] = 'vendor/';
            $this->createFile($appPath.'composer.json', 'composer_new.json.tpl', $param, 'Composer file');
        } elseif ($this->composerMode == self::COMPJSON_NEW_CURRENT_JELIX) {
            $param['rp_jelix'] = Path::shortestPath($appPath, $this->jelixPath).'/';
            $param['rp_lib'] = Path::shortestPath($appPath, dirname(rtrim($this->jelixPath, DIRECTORY_SEPARATOR))).'/';
            $param['rp_vendor'] = 'vendor/';
            $this->createFile($appPath.'composer.json', 'composer_new_current.json.tpl', $param, 'Composer file');
        } else { // composer mode COMPJSON_CURRENT and COMPJSON_NONE
            $param['rp_jelix'] = Path::shortestPath($appPath, $this->jelixPath).'/';
            $param['rp_lib'] = Path::shortestPath($appPath, dirname(rtrim($this->jelixPath, DIRECTORY_SEPARATOR))).'/';
            $param['rp_vendor'] = Path::shortestPath($appPath, $this->vendorPath.'/').'/';
        }

        $param['php_rp_jelix'] = $this->convertRp($param['rp_jelix']);
        if ($param['rp_vendor']) {
            $param['php_rp_vendor'] = $this->convertRp($param['rp_vendor']);
            $this->createFile($appPath.'application.init.php', 'application2.init.php.tpl', $param, 'Bootstrap file');
        } else {
            $this->createFile($appPath.'application.init.php', 'application.init.php.tpl', $param, 'Bootstrap file');
        }

        $this->createFile($appPath.'.htaccess', 'htaccess_deny', $param, 'Configuration file for Apache');
        $this->createFile($appPath.'.gitignore', 'git_ignore.tpl', $param, '.gitignore');
        $this->createFile($appPath.'project.xml', 'project.xml.tpl', $param, 'Project description file');
        $this->createFile($appPath.'dev.php', 'dev.php.tpl', $param, 'Script for developer commands');
        $this->createFile($appPath.'console.php', 'console.php.tpl', $param, 'Script for module commands');

        $this->createFile(\jApp::logPath().'.dummy', 'dummy.tpl', array());
        $this->createFile(\jApp::varPath().'mails/.dummy', 'dummy.tpl', array());
        $this->createFile(\jApp::varPath().'sessions/.dummy', 'dummy.tpl', array());
        $this->createFile(\jApp::appPath().'app/overloads/.dummy', 'dummy.tpl', array());
        $this->createFile(\jApp::appPath().'app/themes/default/.dummy', 'dummy.tpl', array());
        $this->createFile(\jApp::varPath().'uploads/.dummy', 'dummy.tpl', array());
        $this->createFile($appPath.'plugins/.dummy', 'dummy.tpl', array());
        $this->createFile(\jApp::tempBasePath().'.dummy', 'dummy.tpl', array());

        $this->createFile(\jApp::appSystemPath('mainconfig.ini.php'), 'app/system/mainconfig.ini.php.tpl', $param, 'Main configuration file');
        $this->createFile(\jApp::appSystemPath('framework.ini.php'), 'app/system/framework.ini.php.tpl', $param, 'framework setup file');
        $this->createFile($configPath.'localconfig.ini.php.dist', 'var/config/localconfig.ini.php.tpl', $param, 'Configuration file for specific environment');
        $this->createFile($configPath.'profiles.ini.php', 'var/config/profiles.ini.php.tpl', $param, 'Profiles file');
        $this->createFile($configPath.'profiles.ini.php.dist', 'var/config/profiles.ini.php.tpl', $param, 'Profiles file for your repository');
        //$this->createFile(\jApp::appSystemPath('preferences.ini.php'), 'app/system/preferences.ini.php.tpl', $param, 'Preferences file');
        $this->createFile(\jApp::appSystemPath('urls.xml'), 'app/system/urls.xml.tpl', $param, 'URLs mapping file');

        $this->createFile(\jApp::appSystemPath('index/config.ini.php'), 'app/system/index/config.ini.php.tpl', $param, 'Entry point configuration file');
        $this->createFile($appPath.'app/responses/myHtmlResponse.class.php', 'app/responses/myHtmlResponse.class.php.tpl', $param, 'Main response class');
        $this->createFile($appPath.'install/installer.php', 'installer/installer.php.tpl', $param, 'Installer script');
        $this->createFile($appPath.'install/configurator.php', 'installer/configurator.php.tpl', $param, 'Installer script');
        $this->createFile($appPath.'install/uninstall/uninstaller.ini.php', 'installer/uninstall/uninstaller.ini.php', $param, 'uninstaller.ini.php file');
        //$this->createFile($appPath.'tests/runtests.php', 'tests/runtests.php', $param, 'Tests script');

        $this->createFile($wwwpath.'index.php', 'www/index.php.tpl', $param, 'Main entry point');
        $this->createFile($wwwpath.'.htaccess', 'htaccess_allow', $param, 'Configuration file for Apache');

        $this->updateGitIgnoreForTemp($appPath, $appName);

        return $param;
    }

    /**
     * @param string $appPath absolute path of the application
     * @param string $appName
     */
    protected function updateGitIgnoreForTemp($appPath, $appName)
    {
        $tempParentDir = realpath(dirname(rtrim(\jApp::tempBasePath(), '/')));
        if (strpos($tempParentDir, rtrim($appPath, '/')) === 0) {
            // temp is inside the application, we modify the .gitignore application
            $tempPattern = Path::shortestPath($appPath, \jApp::tempBasePath()).'/';
            if (file_exists($appPath.'/.gitignore')) {
                $gitignore = file_get_contents($appPath.'/.gitignore');
                if (strpos($gitignore, $tempPattern) === false) {
                    $gitignore .= "\n".$tempPattern.'*';
                    $gitignore .= "\n!".$tempPattern.".dummy\n";
                    file_put_contents($appPath.'/.gitignore', $gitignore);
                }
            } else {
                $gitignore = "\n".$tempPattern.'*';
                $gitignore .= "\n!".$tempPattern.".dummy\n";
                file_put_contents($appPath.'/.gitignore', $gitignore);
            }
        } else {
            // temp is outside the application, we modify the .gitignore of the temp directory
            $tempPath = \jApp::tempBasePath();
            if (file_exists($tempPath.'/.gitignore')) {
                $gitignore = file_get_contents($tempPath.'/.gitignore');
                if (strpos($gitignore, "\n*\n") === false) {
                    $gitignore .= "\n*\n!.dummy\n!.gitignore\n";
                    file_put_contents($tempPath.'/.gitignore', $gitignore);
                }
            } else {
                $gitignore = "\n*\n!.dummy\n!.gitignore\n";
                file_put_contents($tempPath.'/.gitignore', $gitignore);
            }
        }
    }
}

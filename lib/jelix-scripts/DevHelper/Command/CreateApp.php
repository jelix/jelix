<?php

/**
* @package     jelix-scripts
* @author      Laurent Jouanneau
* @contributor Loic Mathaud
* @contributor Gildas Givaja (bug #83)
* @contributor Christophe Thiriot
* @contributor Bastien Jaillot
* @contributor Dominique Papin, Olivier Demah
* @copyright   2005-2016 Laurent Jouanneau, 2006 Loic Mathaud, 2007 Gildas Givaja, 2007 Christophe Thiriot, 2008 Bastien Jaillot, 2008 Dominique Papin
* @copyright   2011 Olivier Demah
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/


namespace Jelix\DevHelper\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Jelix\FileUtilities\Path;

class CreateApp extends \Jelix\DevHelper\AbstractCommand
{

    /**
     * @var \Symfony\Component\Console\Application
     */
    protected $appApplication;
    
    public function __construct()
    {
        parent::__construct();
        
        
    }

    protected function configure()
    {
        $this
            ->setName('app:create')
            ->setDescription('Creates an application')
            ->setHelp('')
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'The path of the new application directory'
            )
            ->addOption(
               'nodefaultmodule',
               null,
               InputOption::VALUE_NONE,
               'Indicate to not create a default module'
            )
            ->addOption(
               'withcmdline',
               null,
               InputOption::VALUE_NONE,
               'Indicate to add a command line script'
            )
            ->addOption(
               'modulename',
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

    protected function prepareSubCommandApp($appName, $appPath) {
        $this->config = \Jelix\DevHelper\JelixScript::loadConfig($appName);
        $this->config->infoIDSuffix = $this->config->newAppInfoIDSuffix;
        $this->config->infoWebsite = $this->config->newAppInfoWebsite;
        $this->config->infoLicence = $this->config->newAppInfoLicence;
        $this->config->infoLicenceUrl = $this->config->newAppInfoLicenceUrl;
        $this->config->infoLocale = $this->config->newAppInfoLocale;
        $this->config->infoCopyright = $this->config->newAppInfoCopyright;
        $this->config->initAppPaths($appPath);

        if ($this->appApplication) {
            return;
        }
        $this->appApplication = new Application();
        $this->appApplication->add(new CreateCtrl($this->config));
        $this->appApplication->add(new CreateModule($this->config));
        $this->appApplication->add(new CreateEntryPoint($this->config));
    }

    protected function executeSubCommand($name, $arguments, $output) {
        $command = $this->appApplication->find($name);
        $input = new ArrayInput($arguments);
        return $command->run($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $appPath = $input->getArgument('path');
        $appPath = Path::normalizePath($appPath, 0, getcwd());
        $appName = basename($appPath);
        $appPath .= '/';

        if (file_exists($appPath.'/project.xml')) {
            throw new \Exception("this application is already created");
        }

        $this->prepareSubCommandApp($appName, $appPath);

        \jApp::setEnv('jelix-scripts');

        \Jelix\DevHelper\JelixScript::checkTempPath();

        if ($p = $input->getOption('wwwpath')) {
            $wwwpath = Path::shortestPath($appPath , $p).'/';
        }
        else {
            $wwwpath = \jApp::wwwPath();
        }

        if ($output->isVerbose()) {
            $output->writeln("Create directories and files at $appPath");
        }
        $param = $this->_createSkeleton($appPath, $appName, $wwwpath, $input);

        \jApp::declareModulesDir(array($appPath.'/modules/'));

        //require_once (JELIX_LIB_PATH.'installer/jInstaller.class.php');
        $installer = new \jInstaller(new \textInstallReporter(($output->isVerbose()?'notice':'warning')));
        $installer->installApplication();

        $moduleok = true;

        if (!$input->getOption('nodefaultmodule')) {
            try {
                if ($output->isVerbose()) {
                    $output->writeln("Create default module ".$param['modulename']);
                }
                $options = array(
                    'module'=>$param['modulename'],
                    '--addinstallzone' => true,
                    '--noregistration' => true,
                );
                if ($output->isVerbose()) {
                    $options['-v'] = true;
                }
                
                $this->executeSubCommand('module:create', $options, $output);
                if ($output->isVerbose()) {
                    $output->writeln("Create main template");
                }
                $this->createFile($appPath.'modules/'.$param['modulename'].'/templates/main.tpl', 'module/main.tpl.tpl', $param, "Main template");
            } catch (\Exception $e) {
                $moduleok = false;
                $output->writeln("<error>The module has not been created because of this error: ".$e->getMessage()."</error>");
                $output->writeln("However the application has been created");
            }
        }

        if ($input->getOption('withcmdline')) {
            if(!$input->getOption('nodefaultmodule') && $moduleok){
                if ($output->isVerbose()) {
                    $output->writeln("Create a controller in the default module for the cli script");
                }

                $options = array(
                    'module'=>$param['modulename'],
                    'controller'=>'default',
                    'method'=>'index',
                    '--cmdline'=> true,
                );
                if ($output->isVerbose()) {
                    $options['-v'] = true;
                }
                $this->executeSubCommand('module:createctrl', $options, $output);
            }
            if ($output->isVerbose()) {
                $output->writeln("Create the cli script");
            }

            $options = array(
                'entrypoint'=>$param['modulename'],
                '--type' => 'cmdline'
            );
            if ($output->isVerbose()) {
                $options['-v'] = true;
            }
            $this->executeSubCommand('app:createentrypoint', $options, $output);
        }
    }

    protected function convertRp($rp) {
        if(strpos($rp, './') === 0) {
            $rp = substr($rp, 2);
        }
        if (strpos($rp, '../') !== false) {
            return 'realpath(__DIR__.\'/'.$rp."').'/'";
        }
        else if (DIRECTORY_SEPARATOR == '/' && $rp[0] == '/') {
            return "'".$rp."'";
        }
        else if (DIRECTORY_SEPARATOR == '\\' && preg_match('/^[a-z]\:/i', $rp)) { // windows
            return "'".$rp."'";
        }
        else {
            return '__DIR__.\'/'.$rp."'";
        }
    }

    protected function _createSkeleton($appPath, $appName, $wwwpath, InputInterface $input) {
        

        $this->createDir($appPath);
        $this->createDir(\jApp::tempBasePath());
        $this->createDir($wwwpath);

        $varPath = \jApp::varPath();
        $configPath = \jApp::configPath();
        $this->createDir($varPath);
        $this->createDir(\jApp::logPath());
        $this->createDir(\jApp::appConfigPath());
        $this->createDir($configPath);
        $this->createDir(\jApp::appConfigPath('index/'));
        $this->createDir(\jApp::appPath('app/overloads/'));
        $this->createDir(\jApp::appPath('app/themes'));
        $this->createDir(\jApp::appPath('app/themes/default/'));
        $this->createDir($varPath.'uploads/');
        $this->createDir($varPath.'sessions/');
        $this->createDir($varPath.'mails/');

        $this->createDir($appPath.'install');
        $this->createDir($appPath.'modules');
        $this->createDir($appPath.'plugins');
        $this->createDir(\jApp::appPath('app/responses'));
        $this->createDir($appPath.'tests');
        $this->createDir(\jApp::scriptsPath());

        $param = array();
        $param['default_id'] = $appName.$this->config->infoIDSuffix;

        if($input->getOption('nodefaultmodule')) {
            $param['tplname']    = 'jelix~defaultmain';
            $param['modulename'] = 'jelix';
        }
        else {
            $moduleName = $input->getOption('modulename');
            if (!$moduleName) {
                // note: since module name are used for name of generated name,
                // only this characters are allowed
                $moduleName = preg_replace('/([^a-zA-Z_0-9])/','_',$appName);
            }
            $param['modulename'] = $moduleName;
            $param['tplname']    = $moduleName.'~main';
        }

        $param['config_file'] = 'index/config.ini.php';

        $param['rp_temp']  = Path::shortestPath($appPath, \jApp::tempBasePath()).'/';
        $param['rp_var']   = Path::shortestPath($appPath, \jApp::varPath()).'/';
        $param['rp_log']   = Path::shortestPath($appPath, \jApp::logPath()).'/';
        $param['rp_conf']  = Path::shortestPath($appPath, $configPath).'/';
        $param['rp_www']   = Path::shortestPath($appPath, $wwwpath).'/';
        $param['rp_cmd']   = Path::shortestPath($appPath, \jApp::scriptsPath()).'/';
        $param['rp_jelix'] = Path::shortestPath($appPath, JELIX_LIB_PATH).'/';
        $param['rp_lib']   = Path::shortestPath($appPath, LIB_PATH).'/';
        $param['rp_vendor'] = '';
        foreach (array(LIB_PATH. 'vendor/',   // jelix is installed from a zip/tgz package
                        LIB_PATH . '../vendor/', // jelix is installed from git
                        LIB_PATH. '../../../' // jelix is installed with Composer
                        ) as $path) {
           if (file_exists($path)) {
              $param['rp_vendor'] = Path::shortestPath($appPath, realpath($path).'/').'/';
              break;
           }
        }

        $param['rp_app']   = Path::shortestPath($wwwpath, $appPath).'/';

        $this->createFile(\jApp::logPath().'.dummy', 'dummy.tpl', array());
        $this->createFile(\jApp::varPath().'mails/.dummy', 'dummy.tpl', array());
        $this->createFile(\jApp::varPath().'sessions/.dummy', 'dummy.tpl', array());
        $this->createFile(\jApp::appPath().'app/overloads/.dummy', 'dummy.tpl', array());
        $this->createFile(\jApp::appPath().'app/themes/default/.dummy', 'dummy.tpl', array());
        $this->createFile(\jApp::varPath().'uploads/.dummy', 'dummy.tpl', array());
        $this->createFile($appPath.'plugins/.dummy', 'dummy.tpl', array());
        $this->createFile(\jApp::scriptsPath().'.dummy', 'dummy.tpl', array());
        $this->createFile(\jApp::tempBasePath().'.dummy', 'dummy.tpl', array());

        $this->createFile($appPath.'.htaccess', 'htaccess_deny', $param, "Configuration file for Apache");
        $this->createFile($appPath.'.gitignore','git_ignore.tpl', $param, ".gitignore");
        $this->createFile($appPath.'project.xml','project.xml.tpl', $param, "Project description file");
        $this->createFile($appPath.'composer.json','composer.json.tpl', $param, "Composer file");
        $this->createFile($appPath.'cmd.php','cmd.php.tpl', $param, "Script for developer commands");
        $this->createFile(jApp::appConfigPath('mainconfig.ini.php'), 'app/config/mainconfig.ini.php.tpl', $param, "Main configuration file");
        $this->createFile($configPath.'localconfig.ini.php.dist', 'var/config/localconfig.ini.php.tpl', $param, "Configuration file for specific environment");
        $this->createFile($configPath.'profiles.ini.php', 'var/config/profiles.ini.php.tpl', $param, "Profiles file");
        $this->createFile($configPath.'profiles.ini.php.dist', 'var/config/profiles.ini.php.tpl', $param, "Profiles file for your repository");
        $this->createFile($configPath.'preferences.ini.php', 'var/config/preferences.ini.php.tpl', $param, "Preferences file");
        $this->createFile(\jApp::appConfigPath('urls.xml'), 'app/config/urls.xml.tpl', $param, "URLs mapping file");

        $this->createFile(\jApp::appConfigPath('index/config.ini.php'), 'app/config/index/config.ini.php.tpl', $param, "Entry point configuration file");
        $this->createFile($appPath.'app/responses/myHtmlResponse.class.php', 'app/responses/myHtmlResponse.class.php.tpl', $param, "Main response class");
        $this->createFile($appPath.'install/installer.php','installer/installer.php.tpl',$param, "Installer script");
        $this->createFile($appPath.'tests/runtests.php','tests/runtests.php', $param, "Tests script");

        $temp = dirname(rtrim(\jApp::tempBasePath(),'/'));
        if ($temp != rtrim($appPath,'/')) {
            if (file_exists($temp.'/.gitignore')) {
                $gitignore = file_get_contents($temp.'/.gitignore'). "\n" .$appName."/*\n";
                file_put_contents($temp.'/.gitignore', $gitignore);
            }
            else {
                file_put_contents($temp.'/.gitignore', $appName."/*\n");
            }
        }
        else {
            $gitignore = file_get_contents($appPath.'.gitignore'). "\n".basename(rtrim(\jApp::tempBasePath(),'/'))."/*\n";
            file_put_contents($appPath.'.gitignore', $gitignore);
        }

        $this->createFile($wwwpath.'index.php', 'www/index.php.tpl',$param, "Main entry point");
        $this->createFile($wwwpath.'.htaccess', 'htaccess_allow',$param, "Configuration file for Apache");

        $param['php_rp_temp'] = $this->convertRp($param['rp_temp']);
        $param['php_rp_var']  = $this->convertRp($param['rp_var']);
        $param['php_rp_log']  = $this->convertRp($param['rp_log']);
        $param['php_rp_conf'] = $this->convertRp($param['rp_conf']);
        $param['php_rp_www']  = $this->convertRp($param['rp_www']);
        $param['php_rp_cmd']  = $this->convertRp($param['rp_cmd']);
        $param['php_rp_jelix']  = $this->convertRp($param['rp_jelix']);
        if ($param['rp_vendor']) {
           $param['php_rp_vendor']  = $this->convertRp($param['rp_vendor']);
           $this->createFile($appPath.'application.init.php','application2.init.php.tpl',$param, "Bootstrap file");
        }
        else {
           $this->createFile($appPath.'application.init.php','application.init.php.tpl',$param, "Bootstrap file");
        }
        return $param;
    }
}

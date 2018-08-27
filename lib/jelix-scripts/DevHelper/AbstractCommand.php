<?php
/**
* @author      Laurent Jouanneau
* @copyright   2016 Laurent Jouanneau
*
* @link        http://www.jelix.org
* @licence     MIT
*/
namespace Jelix\DevHelper;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

abstract class AbstractCommand extends Command
{
    /**
     * @var CommandConfig
     */
    protected $config;

    private $isVerbose = false;

    /** @var OutputInterface */
    protected $output = null;

    public function __construct(CommandConfig $config)
    {
        $this->config = $config;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->isVerbose = (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity());
        if (!$this->isVerbose && $this->config->verboseMode) {
            $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
            $this->isVerbose = true;
        }
        $this->output = $output;
        $this->setUpOutput($output);
    }

    protected function verbose()
    {
        return $this->isVerbose;
    }

    /**
     * helper method to create a file from a template stored in the templates/
     * directory of jelix-scripts. it set the rights
     * on the file as indicated in the configuration of jelix-scripts.
     *
     * @param string $filename the path of the new file created from the template
     * @param string $template relative path to the templates/ directory, of the
     *                         template file
     * @param array  $param    template values, which will replace some template variables
     *
     * @return bool true if it is ok
     */
    protected function createFile($filename, $template, $tplparam = array(), $fileType = 'File')
    {
        $parts = explode('/', $filename);
        while (count($parts) > 3) {
            array_shift($parts);
        }

        $displayedFilename = implode('/', $parts);

        $defaultparams = array(
            'default_website' => $this->config->infoWebsite ,
            'default_license' => $this->config->infoLicence,
            'default_license_url' => $this->config->infoLicenceUrl,
            'default_creator_name' => $this->config->infoCreatorName,
            'default_creator_email' => $this->config->infoCreatorMail,
            'default_copyright' => $this->config->infoCopyright,
            'createdate' => date('Y-m-d'),
            'jelix_version' => \jFramework::version(),
            'appname' => $this->config->appName,
            'default_timezone' => $this->config->infoTimezone,
            'default_locale' => $this->config->infoLocale,
         );

        $v = explode('.', $defaultparams['jelix_version']);
        if (count($v) < 2) {
            $v[1] = '0';
        }

        $defaultparams['jelix_version_next'] = $v[0].'.'.$v[1].'.*';

        $tplparam = array_merge($defaultparams, $tplparam);

        if (file_exists($filename)) {
            $this->output->writeln('<error>Warning: '.$fileType.' '.$displayedFilename.' already exists.</error>');
            return false;
        }
        $tplpath = JELIX_SCRIPTS_PATH.'templates/'.$template;

        if (!file_exists($tplpath)) {
            $this->output->writeln('<error>Warning:  to create '.$displayedFilename.', template file "'.$tplpath.'" doesn\'t exists.</error>');
            return false;
        }
        $tpl = file($tplpath);

        $callback = function ($matches) use (&$tplparam) {
            if (isset($tplparam[$matches[1]])) {
                return $tplparam[$matches[1]];
            } else {
                return '';
            }
        };

        foreach ($tpl as $k => $line) {
            $tpl[$k] = preg_replace_callback('|\%\%([a-zA-Z0-9_]+)\%\%|',
                                           $callback,
                                           $line);
        }

        file_put_contents($filename, implode('', $tpl));

        if ($this->config->doChmod) {
            chmod($filename, intval($this->config->chmodFileValue, 8));
        }

        if ($this->config->doChown) {
            chown($filename, $this->config->chownUser);
            chgrp($filename, $this->config->chownGroup);
        }
        if (!file_exists($filename)) {
            $this->output->writeln('<error>Error:'.$fileType.' '.$displayedFilename.' could not be created</error>');
            return false;
        }
        if ($this->verbose()) {
            $this->output->writeln('<notice> '.$fileType.' '.$displayedFilename.' has been created.</notice>');
        }

        return true;
    }

    /**
     * helper method to create a new directory. it set the rights
     * on the directory as indicated in the configuration of jelix-scripts.
     *
     * @param string $dirname the path of the directory
     */
    protected function createDir($dirname)
    {
        if ($dirname == '' || $dirname == '/') {
            return;
        }

        if (!file_exists($dirname)) {
            $this->createDir(dirname($dirname));

            mkdir($dirname);
            if ($this->config->doChmod) {
                chmod($dirname, intval($this->config->chmodDirValue, 8));
            }

            if ($this->config->doChown) {
                chown($dirname, $this->config->chownUser);
                chgrp($dirname, $this->config->chownGroup);
            }
        }
    }

    /**
     * Fix version for non built lib.
     */
    protected function fixVersion($version)
    {
        switch ($version) {
            case '__LIB_VERSION_MAX__':
                return \jFramework::versionMax();
            case '__LIB_VERSION__':
                return \jFramework::version();
            case '__VERSION__':
                return \jApp::version();
        }

        return trim($version);
    }

    protected function setUpOutput(OutputInterface $output) {
        $outputStyle = new OutputFormatterStyle('cyan', 'default');
        $output->getFormatter()->setStyle('question', $outputStyle);

        $outputStyle = new OutputFormatterStyle('yellow', 'default', array('bold'));
        $output->getFormatter()->setStyle('inputstart', $outputStyle);

    }
}

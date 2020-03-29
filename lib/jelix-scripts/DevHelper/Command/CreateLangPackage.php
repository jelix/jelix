<?php
/**
 * @author      Florian Lonqueu-Brochard
 * @contributor Laurent Jouanneau
 *
 * @copyright   2011 Florian Lonqueu-Brochard, 2011-2016 Laurent Jouanneau
 *
 * @see        http://www.jelix.org
 * @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
 */

namespace Jelix\DevHelper\Command;

use Jelix\Core\App as App;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateLangPackage extends \Jelix\DevHelper\AbstractCommandForApp
{
    protected function configure()
    {
        $this
            ->setName('app:create-lang-package')
            ->setDescription('Create properties file for a new lang, from locales stored in each modules, of a specific lang.')
            ->setHelp('')
            ->addArgument(
                'lang',
                InputArgument::REQUIRED,
                'the language code of the new lang'
            )
            ->addArgument(
                'model_lang',
                InputArgument::OPTIONAL,
                'The language code '
            )
            ->addOption(
                'to-overload',
                'o',
                InputOption::VALUE_NONE,
                'Indicate to store new locales into the app/overload/ dir instead of app/locales/'
            )
        ;
        parent::configure();
    }

    protected function _execute(InputInterface $input, OutputInterface $output)
    {
        $config = App::config();

        $model_lang = $input->getArgument('model_lang');
        if (!$model_lang) {
            $model_lang = $config->locale;
        }
        $lang = $input->getArgument('lang');

        foreach ($config->_modulesPathList as $module => $dir) {
            $source_dir = $dir.'locales/'.$model_lang.'/';
            if (!file_exists($source_dir)) {
                continue;
            }

            if ($input->getOption('to-overload')) {
                $target_dir = App::appPath('app/overloads/'.$module.'/locales/'.$lang.'/');
            } else {
                $target_dir = App::appPath('app/locales/'.$lang.'/'.$module.'/locales/');
            }

            \jFile::createDir($target_dir);

            if ($dir_r = opendir($source_dir)) {
                while (($fich = readdir($dir_r)) !== false) {
                    if ($fich != '.' && $fich != '..'
                        && is_file($source_dir.$fich)
                        && strpos($fich, '.'.$config->charset.'.properties')
                        && !file_exists($target_dir.$fich)) {
                        copy($source_dir.$fich, $target_dir.$fich);
                        if ($this->verbose()) {
                            $output->writeln("Copy Locales file ${fich} from ${source_dir} to ${target_dir}.");
                        }
                    }
                }
                closedir($dir_r);
            }
        }
    }
}

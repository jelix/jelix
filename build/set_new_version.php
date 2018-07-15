<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018 Laurent Jouanneau
 * @link        http://jelix.org
 * @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
 */

require __DIR__.'/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Jelix\Version\Version;

class BumpVersion  extends Command {

    protected function configure()
    {
        $this
            ->setName('version:bump')
            ->setDescription('Bump jelix version to a new version and tag/commit.')
            ->setHelp('')
            ->addArgument(
                'version',
                InputArgument::REQUIRED,
                'Version to commit'
            )
            ->addArgument(
                'nextVersion',
                InputArgument::OPTIONAL,
                'Version of the development version for the futur release'
            )
            ->addOption(
                'no-commit',
                '',
                InputOption::VALUE_NONE,
                ''
            )
        ;
    }

    const FILES = array(
          'lib/jelix/VERSION' => 'raw',
          'testapp/VERSION' => 'raw',
          'lib/jelix/init.php' => 'JELIX_VERSION',
          'lib/jelix-admin-modules/jacl2db_admin/module.xml'=>'modulexml',
          'lib/jelix-admin-modules/jauthdb_admin/module.xml'=>'modulexml',
          'lib/jelix-admin-modules/jpref_admin/module.xml'=>'modulexml',
          'lib/jelix-admin-modules/master_admin/module.xml'=>'modulexml',
          'lib/jelix-modules/jacl/module.xml'=>'modulexml',
          'lib/jelix-modules/jacl2/module.xml'=>'modulexml',
          'lib/jelix-modules/jacl2db/module.xml'=>'modulexml',
          'lib/jelix-modules/jacldb/module.xml'=>'modulexml',
          'lib/jelix-modules/jauth/module.xml'=>'modulexml',
          'lib/jelix-modules/jauthdb/module.xml'=>'modulexml',
          'lib/jelix-modules/jpref/module.xml'=>'modulexml',
          'lib/jelix/core-modules/jelix/module.xml'=>'modulexml3',
          'testapp/project.xml'=>'modulexml3',
          'testapp/modules/articles/module.xml'=>'modulexml2',
          'testapp/modules/jelix_tests/module.xml'=>'modulexml3',
          'testapp/modules/news/module.xml'=>'modulexml2',
          'testapp/modules/testapp/module.xml'=>'modulexml3',
          'testapp/modules/testinstall1/module.xml'=>'modulexml3',
          'testapp/modules/testinstall2/module.xml'=>'modulexml3',
          'testapp/modules/testinstall3/module.xml'=>'modulexml3',
          'testapp/modules/testurls/module.xml'=>'modulexml3',
    );


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $strVersion = $input->getArgument('version');
        $strNextVersion = $input->getArgument('nextVersion');
        $output->writeln("ver $strVersion.");

        $version = \Jelix\Version\Parser::parse($strVersion);
        $this->changeVersion($output, $version);

        if (!$input->getOption('no-commit')) {
            $this->commitChange("Release Jelix $strVersion");
        }

        if ($strNextVersion) {
            $nextVersion = \Jelix\Version\Parser::parse($strNextVersion);
            $this->changeVersion($output, $nextVersion);
            if (!$input->getOption('no-commit')) {
                $this->commitChange("Bumped version to  $strNextVersion");
            }
        }

    }

    protected function changeVersion(OutputInterface $output, Version $version) {
        foreach (self::FILES as $file => $process) {

            $path = __DIR__.'/../'.$file;

            if (!file_exists($path)) {
                $output->writeln("<info>File $path does not exists</info>");
                continue;
            }

            if ($process == 'raw') {
                file_put_contents($path, $version->toString());
            }
            else if ($process == 'JELIX_VERSION') {
                $content = file_get_contents($path);
                $find = 'define (\'JELIX_VERSION\', \'1.7.0-beta.2\');';
                $find = 'define (\'JELIX_VERSION\', \'1.7.0-beta.2\');';
                preg_replace(
                    "/(define\s*\\('JELIX_VERSION',\s*')([^']+)('\\);)/",
                    "\\1".$version->toString()."\\2",
                    $content);
                file_put_contents($path, $content);
            }
            else if ($process == 'modulexml') {
                $verMax = ($version->getStabilityVersion() ?
                    $version->getMajor().'.'.$version->getMinor().'.'.$version->getPatch() :
                    $version->getNextPatchVersion()
                );
                if (!$this->changeJelixVersion($path, '', $verMax)) {
                    $output->writeln("<info>File $path does not content a jelix tag</info>");
                }
            }
            else if ($process == 'modulexml2') {
                $verMax = ($version->getStabilityVersion() ?
                    $version->getMajor().'.'.$version->getMinor().'.'.$version->getPatch() :
                    $version->getNextPatchVersion()
                );
                if (!$this->changeJelixVersion($path, $version->toString(), $verMax)) {
                    $output->writeln("<info>File $path does not content a jelix tag</info>");
                }
            }
            else if ($process == 'modulexml3') {
                $verMax = ($version->getStabilityVersion() ?
                    $version->getMajor().'.'.$version->getMinor().'.'.$version->getPatch() :
                    $version->getNextPatchVersion()
                );
                if (!$this->changeJelixVersion($path, '', $verMax, $version->toString())) {
                    $output->writeln("<info>File $path does not content a jelix tag</info>");
                }
            }
        }
    }

    protected function changeJelixVersion($path, $minversion, $maxversion, $moduleVersion='') {
        $document = new \DOMDocument();
        $document->load($path);
        $jelix = $this->findJelixNode($document);
        if (!$jelix) {
            return false;
        }
        if ($minversion) {
            $jelix->setAttribute('minversion', $minversion);
        }
        if ($maxversion) {
            $jelix->setAttribute('maxversion', $maxversion);
        }
        if ($moduleVersion) {
            $versionNode = $this->findVersionNode($document);
            $versionNode->setAttribute('date', date('Y-m-d H:i'));
            $versionNode->textContent = $moduleVersion;
        }
        if ($minversion && $maxversion) {
            $this->updateModuleNode($document, 'jelix_tests', $minversion);
            $this->updateModuleNode($document, 'testurls', $minversion);
        }
        else if ($maxversion && $moduleVersion) {
            $this->updateModuleNode($document, 'jelix_tests', $moduleVersion);
            $this->updateModuleNode($document, 'testurls', $moduleVersion);
        }
        $document->save($path);
        return true;
    }

    protected function findJelixNode($document) {
        $dep = $document->getElementsByTagName('dependencies');
        if ($dep->length) {
            $dep = $dep[0];
            $jelixList = $dep->getElementsByTagName('jelix');
            if ($jelixList->length) {
                return $jelixList[0];
            }
            $jelixList = $dep->getElementsByTagName('module');
            foreach($jelixList as $module) {
                if ($module->getAttribute('name') == 'jelix') {
                    return $module;
                }
            }
        }
        return null;
    }

    protected function updateModuleNode($document, $name, $version) {
        $dep = $document->getElementsByTagName('dependencies');
        if ($dep->length) {
            $dep = $dep[0];
            $moduleList = $dep->getElementsByTagName('module');
            foreach($moduleList as $module) {
                if ($module->getAttribute('name') == $name) {
                    $module->setAttribute('minversion', $version);
                    $module->setAttribute('maxversion', $version);
                    return $module;
                }
            }
        }
        return null;
    }

    protected function findVersionNode($document) {
        $info = $document->getElementsByTagName('info');
        if ($info->length) {
            $info = $info[0];
            $versionList = $info->getElementsByTagName('version');
            if ($versionList->length) {
                return $versionList[0];
            }
        }
        return null;
    }

    protected function commitChange($message) {
        return;
        foreach (self::FILES as $file => $process) {
            passthru('git add "'.$file.'"');
        }
        passthru('git commit -m "'.$message.'"');
    }
}

class SetNewVersionApplication extends Application
{
    /**
     * Gets the name of the command based on input.
     *
     * @param InputInterface $input The input interface
     *
     * @return string The command name
     */
    protected function getCommandName(InputInterface $input)
    {
        return 'version:bump';
    }

    /**
     * Gets the default commands that should always be available.
     *
     * @return array An array of default Command instances
     */
    protected function getDefaultCommands()
    {
        // Keep the core default commands to have the HelpCommand
        // which is used when using the --help option
        $defaultCommands = parent::getDefaultCommands();

        $defaultCommands[] = new BumpVersion();

        return $defaultCommands;
    }

    /**
     * Overridden so that the application doesn't expect the command
     * name to be the first argument.
     */
    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        // clear out the normal first argument, which is the command name
        $inputDefinition->setArguments();

        return $inputDefinition;
    }
}


$application = new SetNewVersionApplication();
$application->run();


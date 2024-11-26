<?php
/**
 * @author      Laurent Jouanneau
 * @copyright   2018-2023 Laurent Jouanneau
 * @link        http://jelix.org
 * @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
 */
use Symfony\Component\Console\Output\OutputInterface;
use Jelix\Version\Version;

class ChangeVersion {

    /**
     * @var OutputInterface
     */
    protected $output;

    protected $jelixPath;

    const FILES = array(
        'lib/jelix/VERSION' => 'raw',
        'lib/jelix/init.php' => 'JELIX_VERSION',
        'lib/jelix-admin-modules/jacl2db_admin/module.xml'=>'modulexml3',
        'lib/jelix-admin-modules/jauthdb_admin/module.xml'=>'modulexml3',
        'lib/jelix-admin-modules/jpref_admin/module.xml'=>'modulexml3',
        'lib/jelix-admin-modules/master_admin/module.xml'=>'modulexml3',
        'lib/jelix-modules/jacl/module.xml'=>'modulexml3',
        'lib/jelix-modules/jacl2/module.xml'=>'modulexml3',
        'lib/jelix-modules/jacl2db/module.xml'=>'modulexml3',
        'lib/jelix-modules/jacldb/module.xml'=>'modulexml3',
        'lib/jelix-modules/jauth/module.xml'=>'modulexml3',
        'lib/jelix-modules/jauthdb/module.xml'=>'modulexml3',
        'lib/jelix-modules/jpref/module.xml'=>'modulexml3',
        'lib/jelix/core-modules/jelix/module.xml'=>'jelixmodule',
    );
    const TESTAPP_FILES = array(
        'testapp/VERSION' => 'raw',
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



    public function __construct($jelixPath, ?OutputInterface $output = null) {
        $jelixPath = rtrim($jelixPath, '/') . '/';
        $this->jelixPath = $jelixPath;
        $this->output = $output;
    }

    protected function outputMsg($message) {
        if ($this->output) {
            $this->output->writeln($message);
        }
        else {
            echo strip_tags($message)."\n";
        }
    }

    public function changeVersionInJelix($strVersion)
    {
        $version = \Jelix\Version\Parser::parse($strVersion);
        foreach (self::FILES as $file => $process) {
            $path = $this->jelixPath . $file;
            $this->changeVersionInFile($path, $version, $process);
        }
    }

    public function changeVersionInTestapp($strVersion) {
        $version = \Jelix\Version\Parser::parse($strVersion);
        foreach (self::TESTAPP_FILES as $file => $process) {
            $path = $this->jelixPath . $file;
            $this->changeVersionInFile($path, $version, $process);
        }
    }

    protected function changeVersionInFile($path, Version $version, $process) {
        $this->outputMsg("<info>Change version in file $path  -> $process</info>");
        if (!file_exists($path)) {
            $this->outputMsg("<info>File $path does not exists</info>");
            return;
        }
        $verMax = ($version->getStabilityVersion() ?
            $version->getMajor().'.'.$version->getMinor().'.'.$version->getPatch() :
            $version->getNextPatchVersion()
        );

        if ($process == 'raw') {
            file_put_contents($path, $version->toString());
        }
        else if ($process == 'JELIX_VERSION') {
            $content = file_get_contents($path);
            $content = preg_replace(
                "/(define\s*\\('JELIX_VERSION',\s*')([^']+)('\\);)/",
                '${1}'.$version->toString().'${3}',
                $content);
            file_put_contents($path, $content);
        }
        else if ($process == 'modulexml') {
            if (!$this->changeJelixVersion($path, '', $verMax)) {
                $this->outputMsg("<info>File $path does not content a jelix tag</info>");
            }
        }
        else if ($process == 'modulexml2') {
            if (!$this->changeJelixVersion($path, $version->toString(), $verMax)) {
                $this->outputMsg("<info>File $path does not content a jelix tag</info>");
            }
        }
        else if ($process == 'modulexml3') {
            if (!$this->changeJelixVersion($path, '', $verMax, $version->toString())) {
                $this->outputMsg("<info>File $path does not content a jelix tag</info>");
            }
        }
        else if ($process == 'jelixmodule') {
            if (!$this->changeJelixVersion($path, $version->toString(), $verMax, $version->toString())) {
                $this->outputMsg("<info>File $path does not content a jelix tag</info>");
            }
        }
        else {
            $this->outputMsg("<comment>unknown process $process</comment>");
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
            $this->updateModuleDependencyNode($document, 'jelix_tests', $minversion);
            $this->updateModuleDependencyNode($document, 'testurls', $minversion);
        }
        else if ($maxversion && $moduleVersion) {
            $this->updateModuleDependencyNode($document, 'jelix_tests', $moduleVersion);
            $this->updateModuleDependencyNode($document, 'testurls', $moduleVersion);
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

    protected function updateModuleDependencyNode($document, $name, $version) {
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


}
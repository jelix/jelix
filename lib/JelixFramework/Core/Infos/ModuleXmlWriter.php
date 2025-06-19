<?php
/**
 * @author Laurent Jouanneau
 * @copyright 2018 Laurent Jouanneau
 *
 * @see      http://jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Core\Infos;

class ModuleXmlWriter extends XmlWriterAbstract
{
    protected function getEmptyDocument()
    {
        $doc = new \DOMDocument('1.1', 'utf-8');
        $doc->loadXML(
            '<module xmlns="http://jelix.org/ns/module/1.0"></module>'
        );

        return $doc;
    }

    /**
     * @param \DOMDocument $doc
     * @param ModuleInfos  $infos
     */
    protected function writeData($doc, $infos)
    {
        $this->writeDependencies($doc, $infos);
        $this->writeAutoload($doc, $infos);
    }

    /**
     * @param \DOMDocument $doc
     * @param ModuleInfos  $infos
     */
    protected function writeDependencies($doc, $infos)
    {
        $dependencies = $doc->createElement('dependencies');

        foreach ($infos->dependencies as $dep) {
            if ($dep['type'] == 'choice') {
                $elem = $doc->createElement('choice');
                foreach ($dep['choice'] as $dep2) {
                    $elem->appendChild($this->createModuleElem($doc, $dep2));
                }
                $dependencies->appendChild($elem);

                continue;
            }
            $dependencies->appendChild($this->createModuleElem($doc, $dep));
        }
        if (count($infos->incompatibilities)) {
            $elem = $doc->createElement('conflict');
            foreach ($infos->incompatibilities as $dep) {
                $elem->appendChild($this->createModuleElem($doc, $dep));
            }
            $dependencies->appendChild($elem);
        }
        if ($dependencies->firstChild) {
            $doc->documentElement->appendChild($dependencies);
        }
    }

    /**
     * @param \DOMDocument $doc
     * @param array        $moduleInfos
     */
    protected function createModuleElem($doc, $moduleInfos)
    {
        $module = $doc->createElement('module');
        if ($moduleInfos['id'] !== '') {
            $module->setAttribute('id', $moduleInfos['id']);
        }
        $module->setAttribute('name', $moduleInfos['name']);
        if ($moduleInfos['minversion'] !== '' && $moduleInfos['minversion'] !== '0') {
            $module->setAttribute('minversion', $moduleInfos['minversion']);
        }
        if ($moduleInfos['maxversion'] !== '' && $moduleInfos['maxversion'] !== '*') {
            $module->setAttribute('maxversion', $moduleInfos['maxversion']);
        }
        /*if ($moduleInfos['version'] !== '') {
            $module->setAttribute('version', $moduleInfos['version'] );
        }*/
        return $module;
    }

    /**
     * @param \DOMDocument $doc
     * @param ModuleInfos  $infos
     */
    protected function writeAutoload($doc, $infos)
    {
        $autoload = $doc->createElement('autoload');

        foreach ($infos->autoloaders as $file) {
            $elem = $doc->createElement('autoloader');
            $elem->setAttribute('file', $file);
            $autoload->appendChild($elem);
        }

        foreach ($infos->autoloadClasses as $name => $file) {
            $elem = $doc->createElement('class');
            $elem->setAttribute('name', $name);
            $elem->setAttribute('file', $file);
            $autoload->appendChild($elem);
        }

        foreach ($infos->autoloadClassPatterns as $pattern => $dir) {
            $elem = $doc->createElement('classPattern');
            $elem->setAttribute('pattern', $pattern);
            $elem->setAttribute('dir', $dir[0]);
            if ($dir[1] != '.php') {
                $elem->setAttribute('suffix', $dir[1]);
            }
            $autoload->appendChild($elem);
        }

        foreach ($infos->autoloadPsr0Namespaces as $ns => $dir) {
            foreach ($dir as $d) {
                $elem = $this->createAutoloadElement($doc, 'psr0', $ns, $d);
                $autoload->appendChild($elem);
            }
        }

        foreach ($infos->autoloadPsr4Namespaces as $ns => $dir) {
            foreach ($dir as $d) {
                $elem = $this->createAutoloadElement($doc, 'psr4', $ns, $d);
                $autoload->appendChild($elem);
            }
        }

        foreach ($infos->autoloadIncludePath as $dir) {
            $elem = $this->createAutoloadElement($doc, 'includePath', '', $dir);
            $autoload->appendChild($elem);
        }

        if ($autoload->firstChild) {
            $doc->documentElement->appendChild($autoload);
        }
    }

    protected function createAutoloadElement($doc, $name, $ns, $dir)
    {
        $elem = $doc->createElement($name);
        if ($ns) {
            $elem->setAttribute('namespace', $ns);
        }
        $elem->setAttribute('dir', $dir[0]);
        if ($dir[1] != '.php') {
            $elem->setAttribute('suffix', $dir[1]);
        }

        return $elem;
    }
}

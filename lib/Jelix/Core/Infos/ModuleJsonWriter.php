<?php
/**
 * @author Laurent Jouanneau
 * @copyright 2018-2023 Laurent Jouanneau
 *
 * @see      http://jelix.org
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Core\Infos;

class ModuleJsonWriter extends JsonWriterAbstract
{
    /**
     * @param array         $json
     * @param InfosAbstract $infos
     */
    protected function writeData(&$json, $infos)
    {
        $this->writeDependencies($json, $infos);
        $this->writeAutoload($json, $infos);
    }

    /**
     * @param array       $json
     * @param ModuleInfos $infos
     */
    protected function writeDependencies(&$json, $infos)
    {
        $dependencies = array();
        $dependenciesChoice = array();

        foreach ($infos->dependencies as $dep) {
            if ($dep['type'] == 'choice') {
                $elem = array();
                foreach ($dep['choice'] as $dep2) {
                    $elem[$dep2['name']] = $this->getVersion($dep2);
                }
                $dependenciesChoice[] = $elem;

                continue;
            }
            $dependencies[$dep['name']] = $this->getVersion($dep);
        }
        if (count($dependencies)) {
            $json['required-modules'] = $dependencies;
        }
        if (count($dependenciesChoice)) {
            $json['required-modules-choice'] = $dependenciesChoice;
        }
        $conflicts = array();
        if (count($infos->incompatibilities)) {
            foreach ($infos->incompatibilities as $dep) {
                $conflicts[$dep['name']] = $this->getVersion($dep);
            }
        }
        if (count($conflicts)) {
            $json['conflict'] = $conflicts;
        }
    }

    /**
     * @param array $moduleInfos
     */
    protected function getVersion($moduleInfos)
    {
        if ($moduleInfos['version'] == '') {
            $version = '';
            if ($moduleInfos['minversion'] !== '' && $moduleInfos['minversion'] !== '0') {
                $version = '>='.$moduleInfos['minversion'];
            }
            if ($moduleInfos['maxversion'] !== '' && $moduleInfos['maxversion'] !== '*') {
                if ($version != '') {
                    $version .= ',';
                }
                $version .= '<='.$moduleInfos['maxversion'];
            }

            return $version;
        }

        return $moduleInfos['version'];
    }

    protected function writeAutoload(&$json, $infos)
    {
        $autoload = array();
        if (count($infos->autoloaders)) {
            $autoload['files'] = $infos->autoloaders;
        }
        if (count($infos->autoloadIncludePath)) {
            $autoload['include-path'] = array_map(function ($d) {
                if ($d[1] == '.php') {
                    return $d[0];
                }

                return $d;
            }, $infos->autoloadIncludePath);
        }

        $autoload['classmap'] = $infos->autoloadClassMap;

        if (count($infos->autoloadPsr0Namespaces)) {
            $autoload['psr-0'] = array();
        }
        foreach ($infos->autoloadPsr0Namespaces as $ns => $directories) {
            $directoryPaths = array();
            foreach ($directories as $dirInfo) {
                if ($dirInfo[1] == '.php') {
                    $directoryPaths[] = $dirInfo[0];
                }
            }

            if (count($directoryPaths) == 0) {
                continue;
            }
            if (count($directoryPaths) == 1) {
                $directoryPaths = $directoryPaths[0];
            }

            if ($ns === 0) {
                $autoload['psr-0'][''] = $directoryPaths;
            } else {
                $autoload['psr-0'][$ns] = $directoryPaths;
            }
        }

        foreach ($infos->autoloadPsr4Namespaces as $ns => $directories) {
            $directoryPaths = array();
            foreach ($directories as $dirInfo) {
                if ($dirInfo[1] == '.php') {
                    $directoryPaths[] = $dirInfo[0];
                }
            }

            if (count($directoryPaths) == 0) {
                continue;
            }
            if (count($directoryPaths) == 1) {
                $directoryPaths = $directoryPaths[0];
            }

            if ($ns === 0) {
                $autoload['psr-4'][''] = $directoryPaths;
            } else {
                $autoload['psr-4'][$ns] = $directoryPaths;
            }
        }

        $json['autoload'] = $autoload;
    }
}

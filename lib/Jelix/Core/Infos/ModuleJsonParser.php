<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2015-2023 Laurent Jouanneau
 *
 * @see       http://jelix.org
 * @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Core\Infos;

use Composer\ClassMapGenerator\ClassMapGenerator;

/**
 * Parse a jelix-module.json file.
 *
 * ```
 * {
 *   "required-modules" : { "<name>": "<version>", ... },
 *   "required-modules-choice" : [
 *      { "<name>" : "<version>", ... },
 *      ...
 *   ],
 *   "conflict" : { "<name>": "<version>", ... },
 *   "autoload" : {
 *      "autoloaders": ["<rel_filepath>", ... ],
 *      "include-path": ["<rel_dirpath>", ... ],
 *      "classmap": [ "<rel_dirpath>", ... ],
 *      "psr-0": { "": "<rel_dirpath>" or ['<rel_dirpath>', ...],
 *              "<namespace>": "<rel_dirpath>" or ['<rel_dirpath>', ...],
 *              ...
 *      },
 *      "psr-4": { "": "<rel_dirpath>" or ['<rel_dirpath>', ...],
 *              "<namespace>": "<rel_dirpath>" or ['<rel_dirpath>', ...],
 *      }
 *   }
 * }
 *
 * ```
 */
class ModuleJsonParser extends JsonParserAbstract
{
    protected function createInfos()
    {
        return new ModuleInfos($this->path, false);
    }

    public function _parse(array $json, InfosAbstract $infos)
    {
        parent::_parse($json, $infos);

        $json = array_merge(array(
            'required-modules' => array(),
            'required-modules-choice' => array(),
            'conflict' => array(),
            'autoload' => array(),
        ), $json);

        $json['autoload'] = array_merge(array(
            'files' => array(),
            'classmap' => array(),
            'psr-0' => array(),
            'psr-4' => array(),
        ), $json['autoload']);

        // @var array of array('type'=>'module','version'=>'','id'=>'','name'=>'')

        foreach ($json['required-modules'] as $name => $version) {
            $infos->dependencies[] = array(
                'type' => 'module',
                'version' => $version,
                'name' => $name,
            );
        }

        foreach ($json['required-modules-choice'] as $choicesList) {
            $choice = array();
            foreach ($choicesList as $name => $version) {
                $choice[] = array(
                    'type' => 'module',
                    'version' => $version,
                    'name' => $name,
                );
            }
            if (count($choice) > 1) {
                $infos->dependencies[] = array(
                    'type' => 'choice',
                    'choice' => $choice,
                );
            } elseif (count($choice) == 1) {
                $infos->dependencies[] = $choice[0];
            }
        }

        foreach ($json['conflict'] as $name => $version) {
            $infos->incompatibilities[] = array(
                'type' => 'module',
                'version' => $version,
                'name' => $name,
                'forbiddenby' => $infos->name,
            );
        }

        // module
        if (isset($json['autoload']['psr-4'])) {
            foreach ($json['autoload']['psr-4'] as $ns => $dir) {
                if (!is_array($dir)) {
                    $dir = array($dir);
                }
                $dir = array_map(function ($d) {
                    return array($d, '.php');
                }, $dir);

                if ($ns == '') {
                    $infos->autoloadPsr4Namespaces[0] = $dir;
                } else {
                    $infos->autoloadPsr4Namespaces[trim($ns, '\\')] = $dir;
                }
            }
        }

        if (isset($json['autoload']['psr-0'])) {
            foreach ($json['autoload']['psr-0'] as $ns => $dir) {
                if (!is_array($dir)) {
                    $dir = array($dir);
                }
                $dir = array_map(function ($d) {
                    return array($d, '.php');
                }, $dir);
                if ($ns == '') {
                    $infos->autoloadPsr0Namespaces[0] = $dir;
                } else {
                    $infos->autoloadPsr0Namespaces[trim($ns, '\\')] = $dir;
                }
            }
        }

        if (isset($json['autoload']['classmap'])) {
            $basepath = dirname($this->path).'/';
            foreach ($json['autoload']['classmap'] as $path) {
                $classes = ClassMapGenerator::createMap($basepath.$path);
                // remove directory base path
                $classes = array_map(function ($c) use ($basepath) {
                    if (strpos($c, $basepath) === 0) {
                        return substr($c, strlen($basepath));
                    }

                    return $c;
                }, $classes);
                $infos->autoloadClasses = array_merge($infos->autoloadClasses, $classes);
            }
        }

        if (isset($json['autoload']['files'])) {
            $infos->autoloaders = $json['autoload']['files'];
        }
        if (isset($json['autoload']['include-path'])) {
            $infos->autoloadIncludePath = array_map(function ($d) {
                return array($d, '.php');
            }, $json['autoload']['include-path']);
        }

        return $infos;
    }
}

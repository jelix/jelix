<?php
/**
* @author     Laurent Jouanneau
* @copyright  2015 Laurent Jouanneau
* @link       http://jelix.org
* @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/
namespace Jelix\Core\Infos;

/**
 * Parse a jelix-module.json file
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
class ModuleJsonParser extends JsonParserAbstract {


    /**
     *
     */
    public function parse(InfosAbstract $object) {

        parent::parse($object);

        $json = array_merge(array(
            "required-modules" => array(),
            "required-modules-choice" => array(),
            "conflict" => array(),
            "autoload" => array(),
        ), $this->json);

        $json['autoload'] = array_merge(array(
            'autoloaders'=>array(),
            'classmap'=>array(),
            'psr-0'=>array(),
            'psr-4'=>array(),
        ), $json['autoload']);

        /**
        * @var array of array('type'=>'module','version'=>'','id'=>'','name'=>'')
        */

        foreach($json['required-modules'] as $name=>$version) {
            $object->dependencies[] = array(
                'version'=> $version,
                'name' => $name
            );
        }

        foreach($json['required-modules-choice'] as $choicesList) {
            $choice = array();
            foreach ($choicesList as $name=>$version) {
                $choice[] = array(
                    'version'=> $version,
                    'name' => $name
                );
            }
            if (count($choice) > 1) {
                $object->alternativeDependencies[] = $choice;
            }
            else if (count($choice) == 1) {
                $object->dependencies[] = $choice[0];
            }
        }

        foreach($json['conflict'] as $name=>$version) {
            $object->incompatibilities[] = array(
                'version'=> $version,
                'name' => $name
            );
        }

        // module
        if (isset($json['autoload']['psr-4'])) {
            foreach($json['autoload']['psr-4'] as $ns => $dir) {
                if(!is_array($dir)) {
                    $dir = array($dir);
                }
                $dir = array_map(function($d) {
                    return array($d, '.php');
                }, $dir);

                if ($ns == '') {
                    $object->autoloadPsr4Namespaces[0] = $dir;
                }
                else {
                    $object->autoloadPsr4Namespaces[trim($ns,'\\')] = $dir;
                }
            }
        }

        if (isset($json['autoload']['psr-0'])) {
            foreach($json['autoload']['psr-0'] as $ns => $dir) {
                if(!is_array($dir)) {
                    $dir = array($dir);
                }
                $dir = array_map(function($d) {
                    return array($d, '.php');
                }, $dir);
                if ($ns == '') {
                    $object->autoloadPsr0Namespaces[0] = $dir;
                }
                else {
                    $object->autoloadPsr0Namespaces[trim($ns,'\\')] = $dir;
                }
            }
        }

        if (isset ($json['autoload']['classmap'])) {
            $basepath = $this->path;
            foreach($json['autoload']['classmap'] as $path) {
                $classes = \Jelix\External\ClassMapGenerator::createMap($basepath.$path);
                // remove directory base path
                $classes = array_map(function($c) use ($basepath) {
                    if (strpos($c, $basepath) === 0) {
                        return substr($c, strlen($basepath));
                    }
                    return $c;
                }, $classes);
                $object->autoloadClasses = array_merge($object->autoloadClasses, $classes);
            }
        }

        if (isset($json['autoload']['autoloaders'])) {
            $object->autoloaders            = $json['autoload']['autoloaders'];
        }

        if (isset($json['autoload']['include-path'])) {
            $object->autoloadIncludePath = array_map(function($d) {
                return array($d, '.php');
            }, $json['autoload']['include-path']);
        }

        return $object;
    }
}
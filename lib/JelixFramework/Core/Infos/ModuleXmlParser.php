<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2014-2018 Laurent Jouanneau
 *
 * @see       http://jelix.org
 * @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

namespace Jelix\Core\Infos;

/**
 * Class to parse the module.xml file of a module.
 */
class ModuleXmlParser extends XmlParserAbstract
{
    protected function createInfos()
    {
        return new ModuleInfos($this->path, true);
    }

    protected function parseDependencies(\XMLReader $xml, ModuleInfos $object)
    {
        /*
        <dependencies>
            <jelix minversion="1.0" maxversion="1.0" edition="dev/opt/gold"/>
            <module id="" name="" minversion="" maxversion="" />
            <choice>
               <modules>
                  <module id="" name="" minversion="" maxversion="" />
                  <module id="" name="" minversion="" maxversion="" />
               </modules>
               <module id="" name="" minversion="" maxversion="" />
            </choice>
            <conflict>
                  <module id="" name="" minversion="" maxversion="" />
            </conflict>
        </dependencies>
        */
        while ($xml->read()) {
            if ($xml->nodeType == \XMLReader::END_ELEMENT && $xml->name == 'dependencies') {
                break;
            }

            if ($xml->nodeType == \XMLReader::ELEMENT) {
                if ($xml->name == 'conflict') {
                    while ($xml->read()) {
                        if ($xml->nodeType == \XMLReader::END_ELEMENT && $xml->name == 'conflict') {
                            break;
                        }
                        if ($xml->nodeType == \XMLReader::ELEMENT && $xml->name == 'module') {
                            $info2 = $this->readComponentDependencyInfo($xml);
                            $info2['forbiddenby'] = $object->name;
                            $object->incompatibilities[] = $info2;
                        }
                    }

                    continue;
                }

                if ($xml->name == 'choice') {
                    $choice = array();
                    while ($xml->read()) {
                        if ($xml->nodeType == \XMLReader::END_ELEMENT && $xml->name == 'choice') {
                            break;
                        }
                        if ($xml->nodeType == \XMLReader::ELEMENT && $xml->name == 'module') {
                            $choice[] = $this->readComponentDependencyInfo($xml);
                        }
                    }

                    if (count($choice) > 1) {
                        $object->dependencies[] = array(
                            'type' => 'choice',
                            'choice' => $choice,
                        );
                    } elseif (count($choice) == 1) {
                        $object->dependencies[] = $choice[0];
                    }

                    continue;
                }

                if ($xml->name != 'jelix' && $xml->name != 'module') {
                    continue;
                }

                $info = $this->readComponentDependencyInfo($xml);

                if ($info['name'] == 'jelix') {
                    if ($object->name != 'jelix') {
                        $object->dependencies[] = array(
                            'type' => 'module',
                            'id' => 'jelix@jelix.org',
                            'name' => 'jelix',
                            'minversion' => $info['minversion'],
                            'maxversion' => $info['maxversion'],
                            'version' => $info['version'],
                            'optional' => false,
                        );
                    }
                } else {
                    $object->dependencies[] = $info;
                }
            }
        }

        return $object;
    }

    /**
     * @return array
     */
    protected function readComponentDependencyInfo(\XMLReader $xml)
    {
        $name = ($xml->name == 'jelix' ? 'jelix' : '');
        $id = '';
        $versionRange = '';
        $minversion = '0';
        $maxversion = '*';
        $optional = false;
        while ($xml->moveToNextAttribute()) {
            $attrName = $xml->name;
            if ($attrName == 'minversion' && $xml->value != '') { // old attribute
                $minversion = $this->fixVersion($xml->value);
            } elseif ($attrName == 'maxversion' && $xml->value != '') { // old attribute
                $maxversion = $this->fixVersion($xml->value);
            }
            //else if ($attrName == 'version' && $xml->value != '') {
            //    $versionRange = $this->fixVersion($xml->value);
            //}
            elseif ($attrName == 'name' && $xml->value != '') {
                $name = $xml->value;
            } elseif ($attrName == 'id' && $xml->value != '') {
                $id = $xml->value;
            } elseif ($attrName == 'optional' && $xml->value == 'true') {
                $optional = true;
            }
        }
        if ($versionRange == '') {
            if ($minversion != '0') {
                $versionRange = '>='.$minversion;
            }
            if ($maxversion != '*') {
                $v = '<='.$maxversion;
                if ($versionRange != '') {
                    $v = ','.$v;
                }
                $versionRange .= $v;
            }
            if ($versionRange == '') {
                $versionRange = '*';
            }
        }

        return array(
            'type' => 'module',
            'id' => $id,
            'name' => $name,
            'minversion' => $minversion,
            'maxversion' => $maxversion,
            'version' => $versionRange,
            'optional' => $optional,
        );
    }

    protected function parseAutoload(\XMLReader $xml, ModuleInfos $object)
    {
        while ($xml->read()) {
            if ($xml->nodeType == \XMLReader::END_ELEMENT && $xml->name == 'autoload') {
                break;
            }

            if ($xml->nodeType == \XMLReader::ELEMENT) {
                $name = $xml->name;
                $attr = array();
                while ($xml->moveToNextAttribute()) {
                    $attr[$xml->name] = $xml->value;
                }

                $suffix = '.php';
                $dir = '';
                if (isset($attr['suffix']) && $attr['suffix'] != '.php') {
                    $suffix = $attr['suffix'];
                }
                if (isset($attr['dir'])) {
                    $dir = array($attr['dir'], $suffix);
                }

                switch ($name) {
                    case 'autoloader':
                        $object->autoloaders[] = $attr['file'];

                        break;

                    case 'class':
                        $object->autoloadClasses[$attr['name']] = $attr['file'];

                        break;

                    case 'classPattern':
                        if ($dir != '') {
                            $object->autoloadClassPatterns[$attr['pattern']] = $dir;
                        }

                        break;

                    case 'namespace':
                    case 'psr0':
                        if ($dir == '') {
                            break;
                        }
                        if (isset($attr['namespace'])) {
                            $namespace = $attr['namespace'];
                        } else {
                            $namespace = (isset($attr['name']) ? $attr['name'] : '');
                        }
                        if ($namespace == '') {
                            $object->autoloadPsr0Namespaces[0][] = $dir;
                        } else {
                            $object->autoloadPsr0Namespaces[trim($namespace, '\\')][] = $dir;
                        }

                        break;

                    case 'namespacePathMap':
                    case 'psr4':
                        if ($dir == '') {
                            break;
                        }
                        if (isset($attr['namespace'])) {
                            $namespace = $attr['namespace'];
                        } else {
                            $namespace = (isset($attr['name']) ? $attr['name'] : '');
                        }
                        if ($namespace == '') {
                            $object->autoloadPsr4Namespaces[0][] = $dir;
                        } else {
                            $object->autoloadPsr4Namespaces[trim($namespace, '\\')][] = $dir;
                        }

                        break;

                    case 'includePath':
                        if ($dir != '') {
                            $object->autoloadIncludePath[] = $dir;
                        }

                        break;
                }
            }
        }

        return $object;
    }
}

<?php
/**
* @author     Laurent Jouanneau
* @copyright  2014 Laurent Jouanneau
* @link       http://jelix.org
* @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/
namespace Jelix\Core\Infos;

/**
 * Class to parse the module.xml file of a module
 */
class ModuleXmlParser extends XmlParserAbstract {

    protected function parseDependencies (\XMLReader $xml, ModuleInfos $object) {

        while ($xml->read()) {

            if ($xml->nodeType == \XMLReader::END_ELEMENT && 'dependencies' == $xml->name) {
                break;
            }

            if ($xml->nodeType == \XMLReader::ELEMENT) {
                if ($xml->name == 'conflict') {
                    while ($xml->read()) {
                        if ($xml->nodeType == \XMLReader::END_ELEMENT && 'conflict' == $xml->name) {
                            break;
                        }
                        if ($xml->nodeType == \XMLReader::ELEMENT && 'module' == $xml->name) {
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
                        if ($xml->nodeType == \XMLReader::END_ELEMENT && 'choice' == $xml->name) {
                            break;
                        }
                        if ($xml->nodeType == \XMLReader::ELEMENT && 'module' == $xml->name) {
                            $choice[] = $this->readComponentDependencyInfo($xml);
                        }
                    }

                    if (count($choice) > 1) {
                        $object->alternativeDependencies[] = $choice;
                    }
                    else if (count($choice) == 1) {
                        $object->dependencies[] = $choice[0];
                    }
                    continue;
                }

                if ($xml->name != 'jelix' && $xml->name != 'module') {
                    continue;
                }

                $object->dependencies [] = $this->readComponentDependencyInfo($xml);
            }
        }
        return $object;
    }

    /**
     * @param string $type
     * @param \XMLReader $xml
     * @return array
     */
    protected function readComponentDependencyInfo(\XMLReader $xml)
    {
        $dependency = array(
            'type'=>$xml->name,
            'name'=>'',
            'version'=>''
        );
        $dependency['type'] = $xml->name;
        if ($xml->name == 'jelix') {
            $dependency['type'] = 'module';
            $dependency['name'] = 'jelix';
        }

        while ($xml->moveToNextAttribute()) {
            $attrName = $xml->name;
            if ($attrName == 'minversion' && $xml->value != '') { // old attribute
                $v = '>='.$this->fixVersion($xml->value);
                if ($dependency['version'] != '') {
                    $v = ','.$v;
                }
                $dependency['version'] .= $v;
            }
            else if ($attrName == 'maxversion' && $xml->value != '') { // old attribute
                $v = '<='.$this->fixVersion($xml->value);
                if ($dependency['version'] != '') {
                    $v = ','.$v;
                }
                $dependency['version'] .= $v;
            }
            else if ($attrName == 'version' && $xml->value != '') {
                $dependency['version'] = $this->fixVersion($xml->value);
            }
            else if ($attrName != 'minversion' &&
                $attrName != 'maxversion' &&
                $attrName != 'version') {
                $dependency[$attrName] = $xml->value;
            }
        }
        return $dependency;
    }

    protected function parseAutoload (\XMLReader $xml, ModuleInfos $object) {

        while ($xml->read()) {

            if ($xml->nodeType == \XMLReader::END_ELEMENT && 'autoload' == $xml->name) {
                break;
            }

            if ($xml->nodeType == \XMLReader::ELEMENT) {

                $name = $xml->name;
                $val = $xml->value;
                $attr = array();
                while ($xml->moveToNextAttribute()) {
                    $attr[$xml->name] = $xml->value;
                }

                $suffix = ".php";
                $dir = "";
                if (isset($attr['suffix']) && $attr['suffix'] != '.php') {
                    $suffix = $attr['suffix'];
                }
                if (isset($attr['dir'])) {
                    $dir = array($attr['dir'], $suffix);
                }
                switch($name) {
                    case 'autoloader':
                        $object->autoloaders[] = $attr['file'];
                        break;
                    case 'class':
                        $object->autoloadClasses[$attr['name']] = $attr['file'];
                        break;
                    case 'classPattern':
                        $object->autoloadClassPatterns[$attr['pattern']] = $dir;
                        break;
                    case 'namespace':
                    case 'psr0':
                        if ($dir == '') {
                            break;
                        }
                        $namespace = (isset($attr['name'])?$attr['name']:'');
                        if ($namespace == '') {
                            $object->autoloadPsr0Namespaces[0][] = $dir;
                        }
                        else {
                            $object->autoloadPsr0Namespaces[trim($namespace,'\\')][] = $dir;
                        }
                        break;
                    case 'namespacePathMap':
                    case 'psr4':
                        if ($dir == '') {
                            break;
                        }
                        $namespace = (isset($attr['name'])?$attr['name']:'');
                        if ($namespace == '') {
                            $object->autoloadPsr4Namespaces[0][] = $dir;
                        }
                        else {
                            $object->autoloadPsr4Namespaces[trim($namespace,'\\')][] = $dir;
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

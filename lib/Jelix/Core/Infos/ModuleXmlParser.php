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

    protected function parseAutoload (\XMLReader $xml, ModuleInfos $object) {
        $property = $xml->name;

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

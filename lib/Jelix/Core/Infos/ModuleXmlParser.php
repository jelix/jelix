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

                switch($name) {
                    case 'autoloader':
                        $object->autoloaders[] = $attr['file'];
                        break;
                    case 'class':
                        $object->autoloadClasses[] = $attr;
                        break;
                    case 'classPattern':
                        $object->autoloadClassPatterns[] = $attr;
                        break;
                    case 'namespace':
                        $object->autoloadNamespaces[] = $attr;
                        break;
                    case 'namespacePathMap':
                        $object->autoloadPsr4Namespaces[] = $attr;
                        break;
                    case 'includePath':
                        $object->autoloadIncludePath[] = $attr;
                        break;
                }
            }
        }

        return $object;
    }

}

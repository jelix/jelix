<?php
/**
* @author     Laurent Jouanneau
* @copyright  2015 Laurent Jouanneau
* @link       http://jelix.org
* @licence    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/
namespace Jelix\Core\Infos;

/**
 *
 */
class AppJsonParser extends JsonParserAbstract {

    /**
     *
     */
    public function parse(InfosAbstract $object) {

        parent::parse($object);

        $json = array_merge(array(
            "entrypoints" => array(),
            "directories" => array()
        ),$this->json);

        $json['directories'] = array_merge(array(
            "config" => "",
            "var"=> "",
            "log"=>"",
            "www"=> "",
            "temp"=> "",
        ), $json['directories']);

        if(is_array($json['entrypoints'])) {
            foreach($json['entrypoints'] as $ep) {
                $file = $ep['file'];
                if (strpos($file, '.php') === false) {
                    $file .= '.php';
                }
                $object->entrypoints[$file] = array('config'=>$ep['config'],
                                                    'file'=> $file,
                                                    'type'=>(isset($ep['type'])?$ep['type']:'classic'));
            }
        }

        $j = $json['directories'];
        $object->configPath = $j['config'];
        $object->logPath    = $j['log'];
        $object->varPath    = $j['var'];
        $object->wwwPath    = $j['www'];
        $object->tempPath   = $j['temp'];

        return $object;
    }
}
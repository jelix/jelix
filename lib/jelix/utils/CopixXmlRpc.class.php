<?php
/**
* @package     jelix
* @subpackage  utils
* @version     $Id:$
* @author      Jouanneau Laurent
* @contributor Jouanneau Laurent for jelix
* @copyright   2001-2005 CopixTeam, 2005-2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
* adaptation pour Jelix par Laurent Jouanneau
*/


require_once(JELIX_LIB_UTILS_PATH.'CopixDate.lib.php');

/**
 * objet permettant d'encoder/décoder des request/responses XMl-RPC
 * pour les specs, voir http://www.xmlrpc.com/spec
 */
class CopixXmlRpc {

     private function __construct(){}

    /**
     *
     * @param
     * @return
     */
    public static function decodeRequest($xmlcontent){
        $xml = simplexml_load_string($xmlcontent);
        if($xml == false){

        }
        $methodname = (string)$xml->methodname;
        if(isset($xml->params)){
            if(isset($xml->params->param)){
                $params = array();
                foreach($xml->params->param as $param){
                    if(isset($param->value)){
                        $params[] = self::_decodeValue($param->value);
                    }
                }
            }
        }

        return array($methodname, $params);
    }

    /**
     *
     * @param
     * @return
     */
    public static function encodeRequest($methodname, $params){
          $request =  '<?xml version="1.0"?>
<methodCall><methodName>'.htmlspecialchars($methodname).'</methodName><params>';
           foreach($params as $param){
               $request.= '<param>'.self::_encodeValue($param).'</param>';
           }

        $request.='</params></methodCall>';
        return $request;

    }

    /**
     *
     * @param
     * @return
     */
    public static function decodeResponse($xmlcontent){
        $xml = simplexml_load_string($xmlcontent);
        if($xml == false){

        }
        $response=array();
        if(isset($xml->params)){
            if(isset($xml->params->param)){
                $params = array();
                foreach($xml->params->param as $param){
                    if(isset($param->value)){
                        $params[] = self::_decodeValue($param->value);
                    }
                }
                $response[0] = true;
                $response[1]=$params;
            }
        }else if(isset($xml->fault)){
            $response[0] = false;
            if(isset($xml->fault->value))
                $response[1] = self::_decodeValue($xml->fault->value);
            else
                $response[1] = null;
        }

        return $response;
    }

    /**
     *
     * @param
     * @return
     */
    public static function encodeResponse($params){
        return '<?xml version="1.0"?>
<methodResponse><params><param>'.self::_encodeValue($params).'</param></params></methodResponse>';
    }

    /**
     *
     * @param
     * @return
     */
    public static function encodeFaultResponse($code, $message){
        return '<?xml version="1.0"?>
<methodResponse><fault><value><struct>
<member><name>faultCode</name><value><int>'.intval($code).'</int></value></member>
<member><name>faultString</name><value><string>'.htmlspecialchars($message).'</string></value></member>
</struct></value></fault></methodResponse>';
    }

    /**
     *
     * @param
     * @return
     * @access private
     */
    private static function _decodeValue($valuetag){
        $children= $valuetag->children();
        $value = null;
        if(count($children)){
            if(isset($valuetag->i4)){
                $value= intval((string) $valuetag->i4);
            }else if(isset($valuetag->int)){
                $value= intval((string) $valuetag->int);
            }else if(isset($valuetag->double)){
                $value= doubleval((string)$valuetag->double);
            }else if(isset($valuetag->string)){
                $value= html_entity_decode((string)$valuetag->string);
            }else if(isset($valuetag->boolean)){
                $value= intval((string)$valuetag->boolean)?true:false;
            }else if(isset($valuetag->array)){
                $value=array();
                if(isset($valuetag->array->data->value)){
                    foreach($valuetag->array->data->value as $val){
                        $value[] = self::_decodeValue($val);
                    }
                }
            }else if(isset($valuetag->struct)){
                $value=array();
                if(isset($childs[0]->member)){
                    $listvalue = is_array($childs[0]->member)?$childs[0]->member:array($childs[0]->member);
                    foreach($listvalue as $val){
                        if(isset($val->name) && isset($val->value)){
                            $value[$val->name->content()] = self::_decodeValue($val->value);
                        }
                    }
                }
            }else if(isset($valuetag->{'dateTime.iso8601'})){
                    $value = new CopixDateTime();
                    $value->setFromString((string)$valuetag->{'dateTime.iso8601'}, CopixDateTime::ISO8601_FORMAT);
                    break;
            }else if(isset($valuetag->base64)){
                    $value = new CopixBinary();
                    $value->setFromBase64String((string)$valuetag->base64);
                    break;
            }

        }else{
            $value = (string) $valuetag;
        }
        return $value;
    }

    /**
     *
     * @param
     * @return
     * @access private
     */
    private static function _encodeValue($value){
        $response='<value>';
        if(is_array($value)){

            $isArray = true;
            $datas = array();
            $structkeys = array();
            foreach($value as $key => $val){
                if(!is_numeric($key))
                    $isArray=false;

                $structkeys[]='<name>'.$key.'</name>';
                $datas[]=self::_encodeValue($val);
            }

            if($isArray){
                $response .= '<array><data>'.implode(' ',$datas).'</data></array>';
            }else{
                $response .= '<struct>';
                foreach($datas as $k=>$v){
                    $response.='<member>'.$structkeys[$k].$v.'</member>';
                }
                $response .= '</struct>';
            }
        }else if(is_bool($value)){
            $response .= '<boolean>'.($value?1:0).'</boolean>';
        }else if(is_int($value)){
            $response .= '<int>'.intval($value).'</int>';
        }else if(is_string($value)){
            $response .= '<string>'.htmlspecialchars($value).'</string>';
        }else if(is_float($value) ){
            $response .= '<double>'.doubleval($value).'</double>';
        }else if(is_object($value)){
            switch(get_class($value)){
                case 'copixdatetime':
                    $response .= '<dateTime.iso8601>'.$value->toString($value->ISO8601_FORMAT).'</dateTime.iso8601>';
                    break;
                case 'copixbinary':
                    $response .= '<base64>'.$value->toBase64String().'</base64>';
                    break;
            }
        }
        return $response.'</value>';
    }
}


class Copixbinary  {
    private $data;

    public function toBase64String(){
        return base64_encode($this->data);
    }

    public function setFromBase64String($string){
        $this->data = base64_decode($string);
    }
}
?>
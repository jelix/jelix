<?php
/**
* @package     testapp
* @subpackage  unittest
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class jUnitTestCase extends UnitTestCase {


    function assertComplexIdentical($value, $file){
        $xml = simplexml_load_file($file);
        if(!$xml){
            trigger_error('Impossible de charger le fichier '.$file,E_USER_ERROR);
            return false;
        }
        return $this->_checkIdentical($xml, $value, '$value');
    }

    function assertComplexIdenticalStr($value, $string){
        $xml = simplexml_load_string($string);
        if(!$xml){
            trigger_error('mauvais contenu xml',E_USER_ERROR);
            return false;
        }
        return $this->_checkIdentical($xml, $value, '$value');
    }


    function _checkIdentical($xml, $value, $name){
        $nodename  = dom_import_simplexml($xml)->nodeName;
        switch($nodename){
            case 'object':
                if(isset($xml['class'])){
                    $ok = $this->assertIsA($value,(string)$xml['class'], $name.': not a '.(string)$xml['class'].' object');
                }else
                    $ok = $this->assertTrue(is_object($value),  $name.': not an object ');
                if(!$ok) return false;

                foreach ($xml->children() as $child) {
                    if(isset($child['property'])){
                        $n = (string)$child['property'];
                        $v = $value->$n;
                    }elseif(isset($child['p'])){
                        $n = (string)$child['p'];
                        $v = $value->$n;
                    }elseif(isset($child['method'])){
                        $n = (string)$child['method'];
                        eval('$v=$value->'.$n.';');
                    }elseif(isset($child['m'])){
                        $n = (string)$child['m'];
                        eval('$v=$value->'.$n.';');
                    }else{
                        trigger_error('no method or attribute on '.(dom_import_simplexml($child)->nodeName), E_USER_WARNING);
                        continue;
                    }
                    $ok &= $this->_checkIdentical($child, $v, $name.'->'.$n);
                }

                if(!$ok)
                    $this->fail($name.' : objets non identiques');
                return $ok;

            case 'array':
                $ok = $this->assertIsA($value,'array', $name.': not an array');
                if(!$ok) return false;

                if(trim((string)$xml) != ''){
                    if( false === eval('$v='.(string)$xml.';')){
                        $this->fail("invalid php array syntax");
                        return false;
                    }
                    return $this->assertEqual($value,$v,'negative test on '.$name.': %s');
                }else{
                    $key=0;
                    foreach ($xml->children() as $child) {
                        if(isset($child['key'])){
                            $n = (string)$child['key'];
                            if(is_numeric($n))
                                $key = intval($n);
                        }else{
                            $n = $key ++;
                        }
                        if($this->assertTrue(isset($value[$n]),$name.'['.$n.'] doesn\'t exists')){
                            $v = $value[$n];
                            $ok &= $this->_checkIdentical($child, $v, $name.'['.$n.']');
                        }else $ok= false;
                    }
                    return $ok;
                }
                break;

            case 'string':
                $ok = $this->assertIsA($value,'string', $name.': not a string');
                if(!$ok) return false;
                if(isset($xml['value']))
                    return $this->assertEqual($value, (string)$xml['value'],$name.': bad value. %s');
                else
                    return true;
            case 'int':
            case 'integer':
                $ok = $this->assertIsA($value,'integer', $name.': not an integer');
                if(!$ok) return false;
                if(isset($xml['value'])){
                    return $this->assertEqual($value, intval((string)$xml['value']),$name.': bad value. %s');
                }else
                    return true;
            case 'float':
            case 'double':
                $ok = $this->assertIsA($value,'float', $name.': not a float');
                if(!$ok) return false;
                if(isset($xml['value'])){
                    return $this->assertEqual($value, floatval((string)$xml['value']),$name.': bad value. %s');
                }else
                    return true;
            case 'boolean':
                $ok = $this->assertIsA($value,'boolean', $name.': not a boolean');
                if(!$ok) return false;
                if(isset($xml['value'])){
                    $v = ((string)$xml['value'] == 'true');
                    return $this->assertEqual($value, $v ,$name.': bad value. %s');
                }else
                    return true;
            case 'null':
                return $this->assertNull($value, $name.': not null');
            case 'notnull':
                return $this->assertNotNull($value, $name.' is null');
            case 'resource':
                return $this->assertIsA($value,'resource', $name.': not a resource');
            default:
                $this->fail("_checkIdentical: balise inconnue ".$nodename);
        }

    }
}

/*

<object class="jDaoMethod">
    <string property="name" value="" />
    <string property="type" value="" />
    <string property="distinct" value="" />

    <object method="getConditions()" class="jDaoConditions">
        <array property="order">array()</array>
        <array property="fields">array()</array>
        <object property="condition" class="jDaoCondition">
            <null property="parent"/>
            <array property="conditions"> array(...)</array>
            <array property="group">
                <object key="" class="jDaoConditions" test="#foo" />
             </array>
        </object>

    </object>
</object>


<ressource />
<string value="" />
<integer value="" />
<float value=""/>
<null />
<boolean value="" />
<array>
<object class="">
</object>*/


?>
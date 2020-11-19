<?php
/**
 * @package     jelix
 * @subpackage  jelix-tests
 * @author      Laurent Jouanneau
 * @contributor Christophe Thiriot
 * @copyright   2006-2019 Laurent Jouanneau
 * @link        https://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
namespace Jelix\UnitTests;
use PHPUnit\Framework\TestCase;

require (JELIX_LIB_CORE_PATH.'request/jClassicRequest.class.php');

class CoordinatorForTest extends \jCoordinator {
    function testSetRequest($request) {
        $this->setRequest($request);
    }
}


class UnitTestCase extends TestCase {

    /**
     * indicates if PDO is needed. If yes, PDO will be checked
     * and if not present, tests will be skipped
     * @var boolean
     */
    protected $needPDO = false;

    /**
     * profile name for jDb
     * @var string
     */
    protected $dbProfile ='';

    protected function setUp(): void {
        parent::setUp();
        if($this->needPDO && false === class_exists('PDO',false)){
            $this->markTestSkipped('PDO does not exists ! You should install PDO because tests need it.');
        }
    }

    /**
     * init jelix configuration.
     *
     * If you need to setup a full jelix environment with a coordinator,
     * prefer to call initClassicRequest
     * @param string $config the configuration file to use, as if you were inside an entry point
     * @param string $entryPoint the entrypoint name as indicated into project.xml
     */
    protected static function initJelixConfig($config = 'index/config.ini.php', $entryPoint = 'index.php') {
        $config = \jConfigCompiler::read($config, true, true, $entryPoint);
        \jApp::setConfig($config);
        \jApp::setCoord(null);
    }

    /**
     * @var \Jelix\FakeServerConf\ApacheMod
     */
    protected static $fakeServer = null;

    /**
     * initialize a full jelix environment with a coordinator, a request object etc.
     *
     * it initializes a coordinator, a classic request object. It sets jApp::coord(),
     * @param string $url the full requested URL (with http://, the domaine name etc.)
     * @param string $config the configuration file to use, as if you were inside an entry point
     * @param string $entryPoint the entrypoint name as indicated into project.xml
     */
    protected static function initClassicRequest($url, $config = 'index/config.ini.php', $entryPoint = 'index.php') {
        self::$fakeServer = new \Jelix\FakeServerConf\ApacheMod(jApp::wwwPath(), '/'.$entryPoint);
        self::$fakeServer->setHttpRequest($url);

        $config = \jConfigCompiler::read($config, true, false, '/'.$entryPoint);
        $coord = new CoordinatorForTest($config, false);
        \jApp::setCoord($coord);
        $request = new \jClassicRequest();
        $coord->testSetRequest($request);
    }

    //    complex equality
    public function assertComplexIdentical($value, $file, $errormessage=''){
        $xml = simplexml_load_file($file);
        if(!$xml){
            trigger_error('Unable to load file '.$file,E_USER_ERROR);
            return false;
        }
        return $this->_checkIdentical($xml, $value, '$value', $errormessage);
    }

    public function assertComplexIdenticalStr($value, $string, $errormessage=''){
        $xml = simplexml_load_string($string);
        if(!$xml){
            trigger_error('Wrong xml content '.$string,E_USER_ERROR);
            return false;
        }
        if($errormessage != '')
            $errormessage = ' ('.$errormessage.')';
        return $this->_checkIdentical($xml, $value, '$value', $errormessage);
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

    function _checkIdentical($xml, $value, $name, $errormessage){
        $nodename  = dom_import_simplexml($xml)->nodeName;
        switch($nodename){
            case 'object':
                if (isset($xml['class'])) {
                    $this->assertInstanceOf((string)$xml['class'], $value, $name.': not a '.(string)$xml['class'].' object'.$errormessage);
                } else {
                    $this->assertTrue(is_object($value),  $name.': not an object'.$errormessage);
                }

                foreach ($xml->children() as $child) {
                    if(isset($child['property'])){
                        $n = (string)$child['property'];
                        $v = $value->$n;
                    }elseif(isset($child['p'])){
                        $n = (string)$child['p'];
                        $v = $value->$n;
                    }elseif(isset($child['method'])){
                        $n = (string)$child['method'];
                        $n = trim(str_replace("()", "", $n));
                        $v = $value->$n();
                    }elseif(isset($child['m'])){
                        $n = (string)$child['m'];
                        $n = trim(str_replace("()", "", $n));
                        $v = $value->$n();
                    }else{
                        trigger_error('no method or attribute on '.(dom_import_simplexml($child)->nodeName), E_USER_WARNING);
                        continue;
                    }
                    $this->_checkIdentical($child, $v, $name.'->'.$n,$errormessage);
                }
                return true;

            case 'array':
                $this->assertIsArray($value, $name.': not an array'.$errormessage);
                if(trim((string)$xml) != '') {
                    $xmlstr = trim((string)$xml);
                    if (strpos($xmlstr, 'array') === 0) {
                        // @deprecated
                        if( false === eval('$v='.$xmlstr.';')){
                            $this->fail("invalid php array syntax");
                            return false;
                        }
                    }
                    else {
                        $v = json_decode($xmlstr, true);
                        if ($v === null || !is_array($v)) {
                            $this->fail("invalid json array syntax ".(string)$xml);
                            return false;
                        }
                    }
                    $this->assertEquals($v, $value, 'negative test on '.$name.': '.$errormessage);
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
                        $this->assertTrue(array_key_exists($n,$value),$name.'['.$n.'] doesn\'t exist arrrg'.$errormessage);
                        $v = $value[$n];
                        $this->_checkIdentical($child, $v, $name.'['.$n.']',$errormessage);
                    }
                }
                return true;

            case 'string':
                $this->assertIsString($value, $name.': not a string'.$errormessage);
                if(isset($xml['value'])){
                    $this->assertEquals((string)$xml['value'],$value, $name.': bad value. '.$errormessage);
                }
                return true;
            case 'int':
            case 'integer':
                $this->assertTrue(is_integer($value), $name.': not an integer ('.$value.') '.$errormessage);
                if (isset($xml['value'])) {
                    $this->assertEquals(intval((string)$xml['value']),$value, $name.': bad value. '.$errormessage);
                }
                return true;
            case 'float':
            case 'double':
                $this->assertIsFloat($value,$name.': not a float ('.$value.') '.$errormessage);
                if(isset($xml['value'])){
                    $this->assertEquals( floatval((string)$xml['value']),$value,$name.': bad value. '.$errormessage);
                }
                return true;
            case 'boolean':
                $this->assertIsBool($value,$name.': not a boolean ('.$value.') '.$errormessage);
                if(isset($xml['value'])){
                    $v = ((string)$xml['value'] == 'true');
                    $this->assertEquals($v ,$value, $name.': bad value. '.$errormessage);
                }
                return true;
            case 'null':
                $this->assertNull($value, $name.': not null ('.$value.') '.$errormessage);
                return true;
            case 'notnull':
                $this->assertNotNull($value, $name.' is null'.$errormessage);
                return true;
            case 'resource':
                $this->assertIsResource($value,$name.': not a resource'.$errormessage);
                return true;
            default:
                $this->fail("_checkIdentical: unknown element ".$nodename.$errormessage);
                return false;
        }
    }
}

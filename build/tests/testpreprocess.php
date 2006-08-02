<?php

/**
* @package     jBuildTools
* @version     $Id$
* @author      Jouanneau Laurent
* @contributor
* @copyright   2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

require_once(dirname(__FILE__).'/../preprocessor.lib.php');

require_once(dirname(__FILE__).'/../../lib/simpletest/unit_tester.php');
require_once(dirname(__FILE__).'/../../lib/simpletest/reporter.php');

define('PP_DATA_DIR','ppdatas/');

class PreProcTestCase extends UnitTestCase {
    protected $proc;
    
    protected $testcase = array(
      'source1.txt'=>array( 
          'source1.txt'=>array()
          ),
      'source2.txt'=>array(
           'result2_1.txt'=>array(),
           'result2_2.txt'=>array('FOO'=>true),
          ),
      'source3.txt'=>array(
           'result3_1.txt'=>array(),
           'result3_2.txt'=>array('FOO'=>true),
          ),
      'source4.txt'=>array(
           'result4_1.txt'=>array(),
           'result4_2.txt'=>array('FOO'=>true),
           'result4_3.txt'=>array('BAR'=>true),
           'result4_4.txt'=>array('FOO'=>true, 'BAR'=>true),
          ),
      'source5.txt' =>array(
           'result5_1.txt'=>array(),
           'result5_2.txt'=>array('FOO'=>"une variable foo", "BAR"=>"le bar est ouvert"),
          ),
      'source6.txt'=>array(
           'result6_1.txt'=>array(),
           'result6_2.txt'=>array('FOO'=>true),
           'result6_3.txt'=>array('BAR'=>true),
           'result6_4.txt'=>array('FOO'=>true, 'BAR'=>true),
          ),          
      'source7.txt'=>array(
           'result7_1.txt'=>array(),
           'result7_2.txt'=>array('FOO'=>true),
           'result7_3.txt'=>array('BAR'=>true),
           'result7_4.txt'=>array('BAZ'=>true),
           'result7_5.txt'=>array('BAZ'=>true, 'BAR'=>true),
          ),
      'source_define.txt'=>array(
            'result_define.txt'=>array('FOO'=>true),
          ),
      'source_define2.txt'=>array(
            'result_define2.txt'=>array('FOO'=>'ok'),
          ),
    );
    
    function __construct() {
        $this->UnitTestCase();
    }
    
    function setUp() {
    }
    
    function tearDown() {
        
    }
    
    function testSimple(){
      $proc = new jPreProcessor();
      foreach($this->testcase as $source=>$datas){
         foreach($datas as $result=>$vars){          
           $proc->setVars($vars);
           $res = $proc->parseFile(PP_DATA_DIR.$source);
           $this->assertEqual($res, file_get_contents(PP_DATA_DIR.$result));
         }
      }
    }
}
    

$test = new PreProcTestCase();
$test->run(new TextReporter());

?>
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

require_once(dirname(__FILE__).'/../lib/preprocessor.lib.php');

require_once(dirname(__FILE__).'/../../lib/simpletest/unit_tester.php');
require_once(dirname(__FILE__).'/../../lib/simpletest/reporter.php');
require_once(dirname(__FILE__).'/../../lib/diff/difflib.php');

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
      'source_include1.txt'=>array(
            'result_include1.txt'=>array('FOO'=>'ok'),
          ),
      'source_include2.txt'=>array(
            'result_include2.txt'=>array('FOO'=>'ok'),
          ),

    );


    protected $testcase2 = array(
      'source2.txt'=>array(
           'result2_1.txt'=>array('FOO'=>''),
           'result2_2.txt'=>array('FOO'=>true),
          ),
      'source3.txt'=>array(
           'result3_1.txt'=>array('FOO'=>''),
           'result3_2.txt'=>array('FOO'=>true),
          ),
      'source4.txt'=>array(
           'result4_1.txt'=>array('FOO'=>'', 'BAR'=>''),
           'result4_2.txt'=>array('FOO'=>true, 'BAR'=>''),
           'result4_3.txt'=>array('BAR'=>true, 'FOO'=>''),
           'result4_4.txt'=>array('FOO'=>true, 'BAR'=>true),
          ),
      'source5.txt' =>array(
           'result5_1.txt'=>array('FOO'=>'', 'BAR'=>''),
           'result5_2.txt'=>array('FOO'=>"une variable foo", "BAR"=>"le bar est ouvert"),
          ),
      'source6.txt'=>array(
           'result6_1.txt'=>array('FOO'=>'', 'BAR'=>''),
           'result6_2.txt'=>array('FOO'=>true),
           'result6_3.txt'=>array('BAR'=>true),
           'result6_4.txt'=>array('FOO'=>true, 'BAR'=>true),
          ),
      'source7.txt'=>array(
           'result7_1.txt'=>array('FOO'=>'', 'BAR'=>'', 'BAZ'=>''),
           'result7_2.txt'=>array('FOO'=>true, 'BAR'=>'', 'BAZ'=>''),
           'result7_3.txt'=>array('BAR'=>true, 'FOO'=>''),
           'result7_4.txt'=>array('BAZ'=>true),
           'result7_5.txt'=>array('BAZ'=>true, 'BAR'=>true),
          ),
       'source_if1.txt'=>array(
           'result2_1.txt'=>array('FOO'=>''),
           'result2_2.txt'=>array('FOO'=>true),
        ),
       'source_if2.txt'=>array(
           'result2_1.txt'=>array('FOO'=>''),
           'result2_2.txt'=>array('FOO'=>14),
        ),
       'source_if3.txt'=>array(
           'result2_1.txt'=>array('FOO'=>'', 'BAR'=>'toto'),
           'result2_2.txt'=>array('FOO'=>'toto',  'BAR'=>'toto'),
        ),
       'source_if4.txt'=>array(
           'result2_1.txt'=>array('FOO'=>true),
           'result2_2.txt'=>array('FOO'=>false),
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
           if(!$this->assertEqual($res, file_get_contents(PP_DATA_DIR.$result), "test $source / $result ")){
                $this->showDiff(file_get_contents(PP_DATA_DIR.$result), $res);
           }
         }
      }
    }

    function testSimple2(){
      $proc = new jPreProcessor();
      foreach($this->testcase2 as $source=>$datas){
         foreach($datas as $result=>$vars){
           $proc->setVars($vars);
           $res = $proc->parseFile(PP_DATA_DIR.$source);
           if(!$this->assertEqual($res, file_get_contents(PP_DATA_DIR.$result), "test $source / $result ")){
                $this->showDiff(file_get_contents(PP_DATA_DIR.$result), $res);
           }
         }
      }
    }


    protected $errortestcase = array(
        'source_err1.txt'=>array(1,'source_err1.txt',8), // err syntax
        'source_err2.txt'=>array(2,'source_err2.txt',7), // err if missing
        'source_err3.txt'=>array(2,'source_err3.txt',5), // err if missing
        'source_err4.txt'=>array(3,'source_err4.txt',13), // err endif missing
        'source_err5.txt'=>array(4,'source_err5.txt',7), // err invalid filename
        'source_err6.txt'=>array(4,'subdir/inc_err.txt',11), // err invalid filename
        'source_if_err1.txt'=>array(5,'source_if_err1.txt',5), // err syntax err expression
        'source_if_err2.txt'=>array(6,'source_if_err2.txt',5), // err syntax err expression tok

    );
    function testErreurs(){

      foreach($this->errortestcase as $source=>$datas){

         try{
           $proc = new jPreProcessor();
           $res = $proc->parseFile(PP_DATA_DIR.$source);
           $this->fail($source.' : pas d\'erreur !');
         }catch(jExceptionPreProc $e){
            $err=false;
            if($e->getCode() != $datas[0]){
                $this->fail($source . ' : mauvais code erreur ('.$e->getCode().')');
                $err=true;
            }

            if($e->sourceFilename != PP_DATA_DIR.$datas[1]){
                $s = substr($e->sourceFilename, - strlen(PP_DATA_DIR.$datas[1]));
                if($s != PP_DATA_DIR.$datas[1]){
                    $this->fail($source . ' : mauvais fichier source indiqué ('.$e->sourceFilename.')');
                    $err=true;
                }
            }

            if($e->sourceLine != $datas[2]){
                $this->fail($source . ' : mauvais numero de ligne du source ('.$e->sourceLine.')');
                $err=true;
            }

            if(!$err){
                $this->pass($source . ' : ok');
            }

         }catch(Exception $e){
            $this->fail($source . ' : exception inattendue');
         }
      }
    }

    protected function showDiff($str1, $str2){
        $diff = new Diff(explode("\n",$str1),explode("\n",$str2));

        if($diff->isEmpty()) {
            $this->fail("No difference ???");
        }else{
            $fmt = new UnifiedDiffFormatter();
            $this->fail($fmt->format($diff));
        }
    }

}


$test = new PreProcTestCase();
$test->run(new TextReporter());

?>
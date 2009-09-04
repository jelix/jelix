<?php

/**
* @package     jBuildTools
* @author      Laurent Jouanneau
* @contributor
* @copyright   2009 Laurent Jouanneau
* @link        http://jelix.org
* @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
*/

require_once(dirname(__FILE__).'/../lib/jManifest.class.php');

require_once(dirname(__FILE__).'/../../lib/simpletest/unit_tester.php');
require_once(dirname(__FILE__).'/../../lib/simpletest/reporter.php');
require_once(dirname(__FILE__).'/../../lib/diff/difflib.php');

define('SC_DATA_DIR','scdata/');


class testManifest extends jManifest {
    static function compressSource($content) {
        return self::stripPhpComments($content);
    }
}



class StripCommentTestCase extends UnitTestCase {

    protected $testcase = array(
      'source1.txt'=>'result1.txt',
    );

    function setUp() {
    }

    function tearDown() {

    }

    function testSimple(){
        foreach($this->testcase as $source=>$result){
            $res = testManifest::compressSource(file_get_contents(SC_DATA_DIR.$source));
            //if (!file_exists(SC_DATA_DIR.$result))
            //    file_put_contents(SC_DATA_DIR.$result,$res);

            $expected = file_get_contents(SC_DATA_DIR.$result);

            if(!$this->assertEqual($res, $expected, "test $source / $result ")){
                
                $this->showDiff($expected, $res);
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


$test = new StripCommentTestCase();
$test->run(new TextReporter());

?>
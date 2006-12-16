<?php
/**
* @package    jelix
* @subpackage utils
* @author     Loic Mathaud
* @contributor
* @copyright  2006 Loic Mathaud
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 * utilities functions for command line
 * @package    jelix
 * @subpackage utils
 * @static
 */
class jCmdUtils {

    private function __construct() {}

    public static function getOptionsAndParams($argv, $sws, $params) {
        $switches = array();
        $parameters = array();

        //---------- get the switches
        while (count($argv) && $argv[0]{0} == '-') {
            if (isset($sws[$argv[0]])) {
                if ($sws[$argv[0]]) {
                    if (isset($argv[1]) && $argv[1]{0} != '-') {
                        $sw = array_shift($argv);
                        $switches[$sw] = array_shift($argv);
                    } else {
                        die("Error: parameter missing for the '".$argv[0]."' option\n");
                    }
                } else {
                    $sw = array_shift($argv);
                    $switches[$sw] = true;
                }
            } else {
                die("Error: unknow option '".$argv[0]."' \n");
            }
        }

        //---------- get the parameters
        foreach ($params as $pname => $needed) {
            if (count($argv) == 0) {
                if ($needed) {
                    die("Error: '".$pname."' parameter missing\n");
                } else {
                    break;
                }
            }
            $parameters[$pname]=array_shift($argv);
        }

        if (count($argv)) {
            die("Error: two many parameters\n");
        }

        return array($switches , $parameters);
    }

}

?>

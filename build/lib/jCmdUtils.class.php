<?php
/**
* @package    jelix
* @subpackage utils
* @version    $Id:$
* @author     Loic Mathaud
* @contributor Laurent Jouanneau
* @copyright  2006 Loic Mathaud
* @link        http://www.jelix.org
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

/**
 *
 * @package    jelix
 * @subpackage utils
 */
class jCmdUtils {

    private function __construct() {}

    /**
     *
     * a list of options is a list of switches, a word or letter beginning by a "-"
     * and following by an optionnal value.
     * $sws is the list of switches : key = the switch name (ex: "-d") and the value is :
     *   false if the switch doesn't expected a value
     *   1 if a value is expected
     *   2 if the switch can be repeated many times with different values
     * 
     * a list of parameters is an array :
     *    key = the parameter name
     *    value : false if it is optionnal
     * @param array $argv list of command line parameter (most of time, should be $_SERVER['argv'])
     * @param array $sws list of possible switches
     * @param array $params list of possible parameters
     * @param boolean $fromArgv  true it $argv is $_SERVER['argv']
     */
    public static function getOptionsAndParams($argv, $sws, $params, $fromArgv=true) {
        $switches = array();
        $parameters = array();

        if($fromArgv)
            array_shift($argv); // shift the script name

        //---------- get the switches
        while (count($argv) && $argv[0]{0} == '-') {
            if (isset($sws[$argv[0]])) {
                if ($sws[$argv[0]]) {
                    $multiple=($sws[$argv[0]] > 1);
                    if (isset($argv[1]) && $argv[1]{0} != '-') {
                        $sw = array_shift($argv);
                        if($multiple)
                            $switches[$sw][] = array_shift($argv);
                        else
                            $switches[$sw] = array_shift($argv);
                    } else {
                        throw new Exception("Error: value missing for the '".$argv[0]."' option\n");
                    }
                } else {
                    $sw = array_shift($argv);
                    $switches[$sw] = true;
                }
            } else {
                throw new Exception("Error: unknown option '".$argv[0]."' \n");
            }
        }

        //---------- get the parameters
        foreach ($params as $pname => $needed) {
            if (count($argv) == 0) {
                if ($needed) {
                    throw new Exception("Error: '$pname' parameter missing\n");
                } else {
                    break;
                }
            }
            $parameters[$pname]=array_shift($argv);
        }

        if (count($argv)) {

            throw new Exception("Error: two many parameters\n");
        }

        return array($switches , $parameters);
    }

}

?>

<?php
/**
* @package    jelix
* @subpackage utils
* @author     Loic Mathaud
* @contributor Laurent Jouanneau
* @copyright  2006 Loic Mathaud, 2008 Laurent Jouanneau
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
                        throw new jException('jelix~errors.cli.option.value.missing', $argv[0]);
                    }
                } else {
                    $sw = array_shift($argv);
                    $switches[$sw] = true;
                }
            } else {
                throw new jException('jelix~errors.cli.unknow.option', $argv[0]);
            }
        }

        //---------- get the parameters
        foreach ($params as $pname => $needed) {
            if (count($argv) == 0) {
                if ($needed) {
                    throw new jException('jelix~errors.cli.param.missing', $pname);
                } else {
                    break;
                }
            }
            $parameters[$pname]=array_shift($argv);
        }

        if (count($argv)) {
            throw new jException('jelix~errors.cli.two.many.parameters');
        }

        return array($switches , $parameters);
    }

}

?>

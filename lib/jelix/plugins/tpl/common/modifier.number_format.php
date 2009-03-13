<?php
/**
 * jTpl plugin that wraps PHP number_format function
 * @package    jelix
 * @subpackage jtpl_plugin
 * @author     Julien Issler
 * @contributor
 * @copyright  2008 Julien Issler
 * @link       http://www.jelix.org
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 * @since 1.1
 */

/**
 * NumberFormat plugin for jTpl that wraps PHP number_format function
 *
 * @param float $number the number to format
 * @param int $decimals the number of decimals to return
 * @param string $dec_point the separator string for the decimals
 * @param string $thousands_sep the separator string for the thousands
 * @return string
 */
function jtpl_modifier_common_number_format($number, $decimals=0, $dec_point=false, $thousands_sep=false){
    if ($dec_point == false) {
        $dec_point = jLocale::get('jelix~format.decimal_point');
    }
    if ($thousands_sep == false) {
        $thousands_sep = jLocale::get('jelix~format.thousands_sep');
    }
    return number_format($number, $decimals, $dec_point, $thousands_sep);
}

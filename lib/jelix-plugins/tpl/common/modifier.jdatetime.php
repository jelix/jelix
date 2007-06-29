<?php
/**
* @package    jelix
* @subpackage jtpl_plugin
* @version    $Id$
* @author     Jouanneau Laurent
* @copyright   2006 Jouanneau laurent
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * modifier plugin : change the format of a date
 *
 * @param string
 * @param string
 * @param string
 * @return string
 * @see jDateTime
 */
function jtpl_modifier_jdatetime($date, $format_in = 'db_datetime',
                                 $format_out = 'lang_date') {
    $formats = array(
        'lang_date' => jDateTime::LANG_DFORMAT,
        'lang_datetime' => jDateTime::LANG_DTFORMAT,
        'lang_time' => jDateTime::LANG_TFORMAT,
        'db_date' => jDateTime::BD_DFORMAT,
        'db_datetime' => jDateTime::BD_DTFORMAT,
        'db_time' => jDateTime::BD_TFORMAT,
        'iso8601' => jDateTime::ISO8601_FORMAT,
        'timestamp' => jDateTime::TIMESTAMP_FORMAT,
        'rfc822'=> jDateTime::RFC822_FORMAT);

    $dt = new jDateTime();
    $dt->setFromString($date, $formats[$format_in]);
    return $dt->toString($formats[$format_out]);
}

?>
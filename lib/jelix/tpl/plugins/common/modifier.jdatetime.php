<?php

/**
 * jTpl jdatetime modifier plugin
 *
 * Type:     modifier<br>
 * Name:     jdatetime<br>
 * Purpose:  change the format of a date
 *
 * @param string
 * @param string
 * @param string
 * @return string
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
        'timestamp' => jDateTime::TIMESTAMP_FORMAT);

    $dt = new jDateTime();
    $dt->setFromString($date, $formats[$format_in]);
    return $dt->toString($formats[$format_out]);
}

?>

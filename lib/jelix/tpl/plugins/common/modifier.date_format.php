<?php

/**
 * jTpl date_format modifier plugin
 * adapted from date_format plugin of Smarty
 * 
 * Input:
 *		 - string: input date string
 *		 - format: strftime format for output
 *		 - default_date: default date if $string is empty
 * 
 * @author        Smarty team
 * @contributor   Yannick Le Guédart <yannick at over-blog dot com>
 * @copyright     Smarty team, Yannick Le Guédart
 * @link          http://smarty.php.net
 * @param string
 * @param string
 * @param string
 * @return string|void
 */
function jtpl_modifier_date_format( $string, $format="%b %e, %Y",
                                    $default_date=null) {

    if (substr(PHP_OS,0,3) == 'WIN') {
        $_win_from = array ('%e',  '%T',	   '%D');
        $_win_to   = array ('%#d', '%H:%M:%S', '%m/%d/%y');
        $format	= str_replace($_win_from, $_win_to, $format);
    }

    if($string != '') {

        return strftime($format, strtotime($string));

    } elseif (isset($default_date) && $default_date != '') {

        return strftime($format, strtotime($default_date));

    } else {

        return '';
    }
}

?>
 <?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty include template plugin
 *
 * Type:     function<br>
 * Name:     include<br>
 * Purpose:  include a template into another template
 * @param template
 * @param string
 */
function jtpl_function_include($t, $string) {
    $t->display($string);
}

?>

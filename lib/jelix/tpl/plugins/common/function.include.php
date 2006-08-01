 <?php
/**
 * Plugin from smarty project and adapted for jtpl
 * @package    jelix
 * @subpackage jtpl_plugin
 * @version    $Id$
 * @author
 * @copyright  2001-2003 ispi of Lincoln, Inc.
 * @link http://smarty.php.net/
 * @link http://jelix.org/
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * function plugin :  include a template into another template
 *
 * usage : {include 'myModule~foo'}
 * @param jTpl $t template engine
 * @param string $string the template selector
 */
function jtpl_function_include($t, $string) {
    $t->display($string);
}

?>

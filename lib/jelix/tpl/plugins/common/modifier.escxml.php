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
 * Modifier plugin : escape all forbiden xml characters
 *
 * it escape <,>,",',&
 * Example:  {$var|escxml}
 * @param string $string the string to be escaped
 * @return string
 */
function jtpl_modifier_escxml($string)
{
    return htmlspecialchars($string);
}

?>
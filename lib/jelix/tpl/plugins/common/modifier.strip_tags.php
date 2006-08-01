<?php
/**
* @package    jelix
* @subpackage jtpl_plugin
* @version    $Id$
* @author     Yannick Le Guédart
* @contributor
* @copyright   2006 Yannick Le Guédart
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * Modifier plugin : strip all html tags in the string
 *
 * @param string $string
 * @return string
 */
function jtpl_modifier_strip_tags($string)
{
    return strip_tags($string);
}

?>
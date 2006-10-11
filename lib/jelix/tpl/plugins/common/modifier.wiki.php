<?php
/**
 *
 * @package    jelix
 * @subpackage jtpl_plugin
 * @author     Laurent Jouanneau
 * @copyright  2006 Laurent Jouanneau
 * @link http://wikirenderer.berlios.de/
 * @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

require_once(JELIX_LIB_UTILS_PATH.'jWiki.class.php');
/**
 * modifier plugin :  transform a wiki text to another format (default: XHTML)
 *
 * Example:  {$var|wiki} {$var|wiki:"classicwr_to_xhtml"}
 * @param string $text the wiki texte
 * @param string
 * @return string
 */
function jtpl_modifier_wiki($text, $config = 'wr3_to_xhtml')
{
    $wr = new jWiki($config);
    return $wr->render($text);
}

?>
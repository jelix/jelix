<?php
/**
* @package    jelix
* @subpackage utils
* @author     Laurent Jouanneau
* @contributor
* @copyright  2006 Laurent Jouanneau
* @link       http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * include the wikirenderer class
 */
require_once(LIB_PATH.'wikirenderer/WikiRenderer.lib.php');

/**
 * transform a wiki text into a document (html or else)
 * @package    jelix
 * @subpackage utils
 * @link http://wikirenderer.berlios.de/
 * @since 1.0b1
 */
class jWiki extends  WikiRenderer {
    // rien à surcharger pour le moment
    // Profitons surtout de l'autoload :-)

}
?>
<?php
/**
* @package    jelix
* @subpackage utils
* @version    $Id$
* @author     Jouanneau Laurent
* @contributor
* @copyright  2006 Jouanneau laurent
* @link       http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

require_once(LIB_PATH.'wikirenderer/WikiRenderer.lib.php');

/**
 *
 * @package    jelix
 * @subpackage utils
 * @link http://wikirenderer.berlios.de/
 */
class jWiki extends  WikiRenderer {
    // rien à surcharger pour le moment
    // Profitons surtout de l'autoload :-)


    static function getConfig($name){
        $f = WIKIRENDERER_PATH.'rules/'.basename($config).'.php';
        if(file_exists($f)){
            require_once($f);
            return new $config();
        }else
            throw new Exception('Wikirenderer : bad config name');
    }

}
?>
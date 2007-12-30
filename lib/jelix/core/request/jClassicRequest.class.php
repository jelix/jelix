<?php
/**
* @package     jelix
* @subpackage  core_request
* @author      Laurent Jouanneau
* @contributor
* @copyright   2005-2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
 * handle "classical" request
 * it just gets parameters from the url query and the post content. And responses can
 * be in many format : text, html, xml...
 * @package     jelix
 * @subpackage  core_request
 */
class jClassicRequest extends jRequest {

    public $type = 'classic';

    public $defaultResponseType = 'html';

    protected function _initParams(){

        $url  = jUrl::parse($this->urlScript, $this->urlPathInfo, $_GET);
        $this->params = array_merge($url->params, $_POST);
    }

}
?>
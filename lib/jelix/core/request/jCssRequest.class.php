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
 * handle classical request but only to control and produce css content
 * @package     jelix
 * @subpackage  core_request
 * @since 1.0b1
 */
class jCssRequest extends jRequest {

    public $type = 'css';

    public $defaultResponseType = 'css';

    protected function _initParams(){
        $url  = jUrl::parse($this->urlScript, $this->urlPathInfo, $_GET);
        $this->params = array_merge($url->params, $_POST);
    }

    public function allowedResponses(){ return array('jResponseCss');}
}
?>
<?php
/**
* @package     jelix
* @subpackage  core_request
* @author      Laurent Jouanneau
* @contributor
* @copyright   2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


/**
 * Handle a request which needs a RDF content as response.
 * @package     jelix
 * @subpackage  core_request
 */
class jRdfRequest extends jRequest {

    public $type = 'rdf';

    public $defaultResponseType = 'rdf';

    protected function _initParams(){
        $url  = jUrl::parse($this->urlScript, $this->urlPathInfo, $_GET);
        $this->params = array_merge($url->params, $_POST);
    }
    public function allowedResponses(){ return array('jResponseRdf');}

}
?>

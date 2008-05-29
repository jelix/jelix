<?php
/**
* @package    testapp
* @subpackage jelix_tests
* @author     Jouanneau Laurent
* @contributor
* @copyright  2008 Jouanneau laurent
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class urlhandlermoduleUrlsHandler implements jIUrlSignificantHandler {

    function parse($url){
        if(preg_match("/^\/myhand\/(.*)\/(.*)$/",$url->pathInfo,$match)){
            $urlact = new jUrlAction($url->params);
            $urlact->setParam('action',$match[1].':'.$match[2]);
            return $urlact;
        }else
            return false;
    }

    function create($urlact, $url){

        list($aa,$bb) = explode(':',$urlact->getParam('action'));

        $url->pathInfo = "/myhand/$aa/$bb";
        $url->delParam('action');
    }
}

?>
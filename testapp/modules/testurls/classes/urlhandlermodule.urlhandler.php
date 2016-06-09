<?php
/**
* @package    testapp
* @subpackage jelix_tests
* @author     Laurent Jouanneau
* @contributor
* @copyright  2008 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class urlhandlermoduleUrlsHandler implements \Jelix\Routing\UrlMapping\CustomUrlHandlerInterface {

    function parse(jUrl $url){
        if(preg_match("/^\/myhand\/(.*)\/(.*)$/",$url->pathInfo,$match)){
            $urlact = new jUrlAction($url->params);
            $urlact->setParam('action',$match[1].':'.$match[2]);
            return $urlact;
        }else
            return false;
    }

    function create(jUrlAction $urlact, jUrl $url){

        list($aa,$bb) = explode(':',$urlact->getParam('action'));

        $url->pathInfo = "/myhand/$aa/$bb";
        $url->delParam('action');
    }
}

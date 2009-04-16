<?php
/**
* @package    testapp
* @subpackage jelix_tests
* @author     Jouanneau Laurent
* @contributor
* @copyright  2009 Jouanneau laurent
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class urlsigwikiUrlsHandler implements jIUrlSignificantHandler {

    function parse($url){
        $urlact = new jUrlAction($url->params);
        $urlact->setParam('page', $url->pathInfo);
        return $urlact;
    }

    function create($urlact, $url){
        $url->pathInfo = '/'.trim($url->getParam('page'),'/');
        $url->delParam('page');
    }
}

?>
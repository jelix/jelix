<?php
/**
* @package    testapp
* @subpackage jelix_tests
* @author     Jouanneau Laurent
* @contributor
* @copyright  2005-2007 Jouanneau laurent
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class urlsigUrlsHandler implements jIUrlSignificantHandler {

    // exemple de handler.
    // ici les traitements sont simples, c'est juste pour montrer le principe

    // on peut utiliser le même handler pour plusieurs actions
    // il suffit de tester les parametres de l'objet url

    function parse($url){
        if(preg_match("/^\/withhandler\/(.*)\/(.*)$/",$url->pathInfo,$match)){
            $urlact = new jUrlAction($url->params);
            $urlact->setParam('first',jUrl::unescape($match[1]));
            $urlact->setParam('second',jUrl::unescape($match[2]));
            return $urlact;
        }else
            return false;
    }

    function create($urlact, $url){

        $f=jUrl::escape($url->getParam('first'));
        $s=jUrl::escape($url->getParam('second'));

        $url->pathInfo = "/withhandler/$f/$s";

        $url->delParam('first');
        $url->delParam('second');
    }
}

?>
<?php
/**
* @package    testapp
* @subpackage jelix_tests
* @author     Laurent Jouanneau
* @contributor
* @copyright  2009-2023 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence    GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class urlsigwikiUrlsHandler implements \Jelix\Routing\UrlMapping\CustomUrlHandlerInterface {

    function parse(\jUrl $url)
    {
        $urlact = new jUrlAction($url->params);
        $urlact->setParam('page', $url->pathInfo);
        return $urlact;
    }

    function create(\jUrlAction $urlact, \jUrl $url)
    {
        $url->pathInfo = '/'.trim($url->getParam('page'),'/');
        $url->delParam('page');
    }
}

?>
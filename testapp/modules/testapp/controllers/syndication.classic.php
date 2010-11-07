<?php
/**
* @package     testapp
* @subpackage  testapp module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2006 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class syndicationCtrl extends jController {

    function rss(){

        $rep = $this->getResponse('rss2.0');
        $rep->infos->title = 'Test syndication testapp jelix';
        $rep->infos->webSiteUrl = 'http://testapp.jelix.org';
        $rep->infos->copyright = ' 2006 jelix.org';
        $rep->infos->categories = array('foo','bar');
        $rep->infos->description = 'test de syndication en rss 1.0 dans testapp';

        $rep->addItem($rep->createItem('foo1','http://testapp.jelix.org/1', '2006-11-11 12:32:41'));
        $rep->addItem($rep->createItem('foo2','http://testapp.jelix.org/2', '2006-11-11 12:32:42'));
        $rep->addItem($rep->createItem('foo3','http://testapp.jelix.org/3', '2006-11-11 12:32:43'));
        return $rep;
    }

    function atom(){
        $rep = $this->getResponse('atom1.0');
        $rep->infos->title = 'Test syndication testapp jelix';
        $rep->infos->webSiteUrl = 'http://testapp.jelix.org';
        $rep->infos->id = 'http://testapp.jelix.org';
        $rep->infos->copyright = ' 2006 jelix.org';
        $rep->infos->categories = array('foo','bar');
        $rep->infos->description = 'test de syndication en rss 1.0 dans testapp';

        $rep->addItem($rep->createItem('foo1','http://testapp.jelix.org/1', '2006-11-11 12:32:41'));
        $rep->addItem($rep->createItem('foo2','http://testapp.jelix.org/2', '2006-11-11 12:32:42'));
        $rep->addItem($rep->createItem('foo3','http://testapp.jelix.org/3', '2006-11-11 12:32:43'));
        return $rep;
   }
}

?>
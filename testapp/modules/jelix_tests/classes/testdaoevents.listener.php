<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Jouanneau Laurent
* @contributor
* @copyright   2008 Jouanneau laurent
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class testdaoeventsListener extends jEventListener{

    function onDaoDeleteBefore ($event) {
      global $TEST_DAO_EVENTS;
      $TEST_DAO_EVENTS['onDaoDeleteBefore'] = array('dao'=>$event->getParam('dao'),
                                                    'keys'=>$event->getParam('keys'));
    }

    function onDaoDeleteAfter ($event) {
      global $TEST_DAO_EVENTS;
      $TEST_DAO_EVENTS['onDaoDeleteAfter'] = array('dao'=>$event->getParam('dao'),
                                                   'keys'=>$event->getParam('keys'),
                                                   'result'=>$event->getParam('result'));
    }

    function onDaoDeleteByBefore ($event) {
      global $TEST_DAO_EVENTS;
      $TEST_DAO_EVENTS['onDaoDeleteByBefore'] = array('dao'=>$event->getParam('dao'),
                                                      'keys'=>$event->getParam('keys'),
                                                      'criterias'=>$event->getParam('criterias'));
    }

    function onDaoDeleteByAfter ($event) {
      global $TEST_DAO_EVENTS;
      $TEST_DAO_EVENTS['onDaoDeleteByAfter'] = array('dao'=>$event->getParam('dao'),
                                                     'keys'=>$event->getParam('keys'),
                                                     'criterias'=>$event->getParam('criterias'),
                                                     'result'=>$event->getParam('result'));
    }

    function onDaoUpdateBefore ($event) {
      global $TEST_DAO_EVENTS;
      $TEST_DAO_EVENTS['onDaoUpdateBefore'] = array('dao'=>$event->getParam('dao'),
                                                    'record'=>(clone $event->getParam('record')));
    }

    function onDaoUpdateAfter ($event) {
      global $TEST_DAO_EVENTS;
      $TEST_DAO_EVENTS['onDaoUpdateAfter'] = array('dao'=>$event->getParam('dao'),
                                                   'record'=>(clone $event->getParam('record')));
    }

    function onDaoInsertBefore ($event) {
      global $TEST_DAO_EVENTS;
      $TEST_DAO_EVENTS['onDaoInsertBefore'] = array('dao'=>$event->getParam('dao'),
                                                    'record'=>(clone $event->getParam('record')));
    }

    function onDaoInsertAfter ($event) {
      global $TEST_DAO_EVENTS;
      $TEST_DAO_EVENTS['onDaoInsertAfter'] = array('dao'=>$event->getParam('dao'),
                                                   'record'=> (clone $event->getParam('record')));
    }

}
?>
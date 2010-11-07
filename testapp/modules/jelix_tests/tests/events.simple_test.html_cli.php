<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Laurent Jouanneau
* @contributor
* @copyright   2006-2007 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class UTEvents extends UnitTestCase {

    function testEvents() {
      $response = jEvent::notify('TestEvent');
      $response = $response->getResponse ();
      $response = serialize($response[0]);
      $temoin = serialize(array('module'=>'jelix_tests','ok'=>true));

      $this->assertTrue($temoin == $response, 'évenement simple');

      $temoin = array('hello'=>'world');
      $response = jEvent::notify('TestEventWithParams',$temoin );
      $response = $response->getResponse ();

      $this->assertTrue(($response[0]['params'] == 'world'), 'évenement avec paramètres');
    }
}

?>
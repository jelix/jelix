<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Brice Tencé
* @contributor 
* @copyright    2011 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class UTjrooturl extends UnitTestCase {

    public function testjRootUrl(){

        $this->assertEqual( jUrl::getRootUrl( 'test' ), 'http://www.junittest.com/' );
        $this->assertEqual( jUrl::getRootUrl( 'secure_test' ), 'https://www.junittest.com/' );
        $this->assertEqual( jUrl::getRootUrl( '/themes' ), 'http://themes.junittest.com/' );
        $this->assertEqual( jUrl::getRootUrl( 'foo_relPath' ), $GLOBALS['gJConfig']->urlengine['basePath'].'foo' );
        $this->assertEqual( jUrl::getRootUrl( 'foo_absPath' ), '/foo' );
        $this->assertEqual( jUrl::getRootUrl( 'notInConfig' ), $GLOBALS['gJConfig']->urlengine['basePath'] );
        $this->assertEqual( jUrl::getRootUrl( '/notInConfig' ), $GLOBALS['gJConfig']->urlengine['basePath'] );
    }
}

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

        $this->assertEqual( jRootUrl::get( 'test' ), 'http://www.junittest.com/' );
        $this->assertEqual( jRootUrl::get( 'secure_test' ), 'https://www.junittest.com/' );
        $this->assertEqual( jRootUrl::get( '/themes' ), 'http://themes.junittest.com/' );
        $this->assertEqual( jRootUrl::get( 'foo_relPath' ), $GLOBALS['gJConfig']->urlengine['basePath'].'foo' );
        $this->assertEqual( jRootUrl::get( 'foo_absPath' ), '/foo' );
        $this->assertEqual( jRootUrl::get( 'notInConfig' ), $GLOBALS['gJConfig']->urlengine['basePath'] );
        $this->assertEqual( jRootUrl::get( '/notInConfig' ), $GLOBALS['gJConfig']->urlengine['basePath'] );
    }
}

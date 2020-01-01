<?php
/**
* @package     testapp
* @subpackage  jelix_tests module
* @author      Brice Tencé
* @contributor Laurent Jouanneau
* @copyright   2011-2012 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

class UTjrooturl extends \Jelix\UnitTests\UnitTestCase {

    function setUp() : void {
        self::initJelixConfig();
        parent::setUp();
    }

    public function testjRootUrl(){

        $this->assertEquals( 'http://www.junittest.com/',    jUrl::getRootUrl( 'test' ));
        $this->assertEquals( 'https://www.junittest.com/',   jUrl::getRootUrl( 'secure_test' ));
        $this->assertEquals( 'http://themes.junittest.com/', jUrl::getRootUrl( '/themes' ));
        $this->assertEquals( jApp::urlBasePath().'foo', jUrl::getRootUrl( 'foo_relPath' ));
        $this->assertEquals( '/foo',                                jUrl::getRootUrl( 'foo_absPath' ));
        $this->assertEquals( jApp::urlBasePath(), jUrl::getRootUrl( 'notInConfig' ));
        $this->assertEquals( jApp::urlBasePath(), jUrl::getRootUrl( '/notInConfig' ));
    }
}

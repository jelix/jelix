<?php
/**
* @package     testapp
* @subpackage  testsjelix
* @author      Laurent Jouanneau
* @contributor
* @copyright   2014 Laurent Jouanneau
* @link        http://www.jelix.org
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/


class simpleLocaleTest extends PHPUnit_Framework_TestCase {


    function testSingleFileEn() {

        $messages = new \Jelix\SimpleLocalization\Container(__DIR__.'/messages/severallang.php', 'en');

        $this->assertEquals('en', $messages->getLang());
        $this->assertEquals('english', $messages->get('foo'));
        $this->assertEquals('boat', $messages->get('bar'));

    }

    function testSingleFileFr() {
        $messages = new \Jelix\SimpleLocalization\Container(__DIR__.'/messages/severallang.php', 'fr');
        $this->assertEquals('fr', $messages->getLang());
        $this->assertEquals('français', $messages->get('foo'));
        $this->assertEquals('bateau', $messages->get('bar'));

    }

    function testSingleFileEs() {
        $messages = new \Jelix\SimpleLocalization\Container(__DIR__.'/messages/severallang.php', 'es');
            // no es lang, we should then have english messages
        $this->assertEquals('es', $messages->getLang());
        $this->assertEquals('english', $messages->get('foo'));
        $this->assertEquals('boat', $messages->get('bar'));
    }

    function testSeparateFileEn() {
        $messages = new \Jelix\SimpleLocalization\Container(__DIR__.'/messages/separate.%LANG%.php', 'en');
        $this->assertEquals('en', $messages->getLang());
        $this->assertEquals('english2', $messages->get('foo2'));
        $this->assertEquals('boat2', $messages->get('bar2'));
    }

    function testSeparateFileFr() {
        $messages = new \Jelix\SimpleLocalization\Container(__DIR__.'/messages/separate.%LANG%.php', 'fr');
        $this->assertEquals('fr', $messages->getLang());
        $this->assertEquals('français2', $messages->get('foo2'));
        $this->assertEquals('bateau2', $messages->get('bar2'));
    }

    function testCombinedFileEn() {
        $messages = new \Jelix\SimpleLocalization\Container(array(__DIR__.'/messages/separate.%LANG%.php', __DIR__.'/messages/severallang.php'), 'en');
        $this->assertEquals('en', $messages->getLang());
        $this->assertEquals('english2', $messages->get('foo2'));
        $this->assertEquals('boat2', $messages->get('bar2'));
        $this->assertEquals('english', $messages->get('foo'));
        $this->assertEquals('boat', $messages->get('bar'));
    }

    function testCombinedFileFr() {
        $messages = new \Jelix\SimpleLocalization\Container(array(__DIR__.'/messages/separate.%LANG%.php', __DIR__.'/messages/severallang.php'), 'fr');
        $this->assertEquals('fr', $messages->getLang());
        $this->assertEquals('français2', $messages->get('foo2'));
        $this->assertEquals('bateau2', $messages->get('bar2'));
        $this->assertEquals('français', $messages->get('foo'));
        $this->assertEquals('bateau', $messages->get('bar'));
    }

}
<?php
/**
 * @package     testapp
 * @subpackage  jelix_tests module
 * @author      Laurent Jouanneau
 * @contributor
 * @copyright   2019 Laurent Jouanneau
 * @link        http://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

require_once(JELIX_LIB_PATH.'forms/jForms.class.php');

class jforms_uploadTest extends \Jelix\UnitTests\UnitTestCase {


    function testUniqueName() {
        $ctrl = new jFormsControlUpload2('up');
        $dir = __DIR__.'/';
        $this->assertEquals('foo.test',
            $ctrl->getUniqueFileName(__DIR__, 'foo.test')
        );
        $this->assertEquals('bar/foo.test',
            $ctrl->getUniqueFileName(__DIR__, 'bar/foo.test')
        );
        $this->assertEquals('bar/foo.test',
            $ctrl->getUniqueFileName(realpath(__DIR__.'/../'), 'bar/foo.test')
        );
        $this->assertEquals('jforms_uploadTest1.php',
            $ctrl->getUniqueFileName(__DIR__.'/', 'jforms_uploadTest.php')
        );
        $this->assertEquals('forms/jforms_uploadTest1.php',
            $ctrl->getUniqueFileName(realpath(__DIR__.'/../'), 'forms/jforms_uploadTest.php')
        );
    }

}

<?php

/**
 * @package     testapp
 * @subpackage  jelix_tests module
 * @author      Laurent Jouanneau
 * @copyright   2025-2026 Laurent Jouanneau
 * @link        https://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

use Jelix\Core\App;
use Jelix\Template\TemplateWarmupCompiler;


class templateWarmupCompilerForTests extends TemplateWarmupCompiler
{
    function testReadModuleTemplateFlavors($moduleName, $modulePath, $tplName = '')
    {
        $this->allFileFlavors = [];
        $this->localesList = ['en_US', 'fr_FR'];
        $this->readModuleTemplateFlavors($moduleName, $modulePath, $tplName);
        return $this->allFileFlavors;
    }

    function testConsolidateTemplateFlavors($moduleName, $modulePath, $tplName = '')
    {
        $this->reset();
        $this->readModuleTemplateFlavors($moduleName, $modulePath, $tplName);
        $this->consolidateAllFilesPath();
        return $this->allFileFlavors;
    }
}

class jtpl_resolverTest extends \Jelix\UnitTests\UnitTestCase
{
    public function setUp() : void {
        self::initClassicRequest(TESTAPP_URL.'index.php');
        parent::setUp();
    }

    function tearDown() : void {
    }


    protected function getExpectedFlavors()
    {
        $appPath = App::appPath();

        return array(
            // only defined into the module
            'test1.ctpl' => array (
                'default' => array(
                    '_' => $appPath.'modules/news/templates/test1.ctpl',
                )
            ),
            // redefined into the app theme directory
            'test3.ctpl' => array (
                'default' => array(
                    '_' => $appPath.'app/themes/default/news/test3.ctpl',
                )
            ),
            // only defined into the module, for each locale
            'test2locale.ctpl' => array (
                'default' => array(
                    'en_US' => $appPath.'modules/news/templates/en_US/test2locale.ctpl',
                    'fr_FR' => $appPath.'modules/news/templates/fr_FR/test2locale.ctpl',
                )
            ),
            // redefined into the var theme directory for the default theme
            // redefined into the app theme directory for the fancy theme
            'test4.ctpl' => array (
                'default' => array(
                    '_' => $appPath.'var/themes/default/news/test4.ctpl',

                ),
                'fancy' => array(
                    '_' => $appPath.'app/themes/fancy/news/test4.ctpl',
                )
            ),
            // only defined into the module for the fancy theme
            'test5.ctpl' => array (
                'fancy' => array(
                    '_' => $appPath.'modules/news/templates/themes/fancy/test5.ctpl',
                )
            ),
            // redefined into the app theme directory for the fancy theme and the fr_FR locale
            'test6.ctpl' => array (
                'default' => array(
                    '_' => $appPath.'modules/news/templates/test6.ctpl',
                ),
                'fancy' => array(
                    'fr_FR' => $appPath.'app/themes/fancy/news/fr_FR/test6.ctpl',
                )
            ),
        );
    }

    protected function getExpectedConsolidatedFlavors()
    {
        $appPath = App::appPath();
        return array(
            'test1.ctpl' => array (
                'default' => array(
                    'en_US' => $appPath.'modules/news/templates/test1.ctpl',
                    'fr_FR' => $appPath.'modules/news/templates/test1.ctpl',
                    '_' => $appPath.'modules/news/templates/test1.ctpl',
                ),
                'fancy' => array(
                    'en_US' => $appPath.'modules/news/templates/test1.ctpl',
                    'fr_FR' => $appPath.'modules/news/templates/test1.ctpl',
                )
            ),
            'test3.ctpl' => array (
                'default' => array(
                    'en_US' => $appPath.'app/themes/default/news/test3.ctpl',
                    'fr_FR' => $appPath.'app/themes/default/news/test3.ctpl',
                    '_' => $appPath.'app/themes/default/news/test3.ctpl',
                ),
                'fancy' => array(
                    'en_US' => $appPath.'app/themes/default/news/test3.ctpl',
                    'fr_FR' => $appPath.'app/themes/default/news/test3.ctpl',
                )
            ),
            'test2locale.ctpl' => array (
                'default' => array(
                    'en_US' => $appPath.'modules/news/templates/en_US/test2locale.ctpl',
                    'fr_FR' => $appPath.'modules/news/templates/fr_FR/test2locale.ctpl',
                ),
                'fancy' => array(
                    'en_US' => $appPath.'modules/news/templates/en_US/test2locale.ctpl',
                    'fr_FR' => $appPath.'modules/news/templates/fr_FR/test2locale.ctpl',
                )
            ),
            'test4.ctpl' => array (
                'default' => array(
                    'en_US' => $appPath.'var/themes/default/news/test4.ctpl',
                    'fr_FR' => $appPath.'var/themes/default/news/test4.ctpl',
                    '_' => $appPath.'var/themes/default/news/test4.ctpl',
                ),
                'fancy' => array(
                    'en_US' => $appPath.'app/themes/fancy/news/test4.ctpl',
                    'fr_FR' => $appPath.'app/themes/fancy/news/test4.ctpl',
                    '_' => $appPath.'app/themes/fancy/news/test4.ctpl',
                )
            ),
            'test5.ctpl' => array (
                'default' => array(
                    'en_US' => '',
                    'fr_FR' => '',
                ),
                'fancy' => array(
                    'en_US' => $appPath.'modules/news/templates/themes/fancy/test5.ctpl',
                    'fr_FR' => $appPath.'modules/news/templates/themes/fancy/test5.ctpl',
                    '_' => $appPath.'modules/news/templates/themes/fancy/test5.ctpl',
                )
            ),
            'test6.ctpl' => array (
                'default' => array(
                    'en_US' => $appPath.'modules/news/templates/test6.ctpl',
                    'fr_FR' => $appPath.'modules/news/templates/test6.ctpl',
                    '_' => $appPath.'modules/news/templates/test6.ctpl',
                ),
                'fancy' => array(
                    'en_US' => $appPath.'modules/news/templates/test6.ctpl',
                    'fr_FR' => $appPath.'app/themes/fancy/news/fr_FR/test6.ctpl',
                )
            ),
        );
    }

    function testFlavorsReader()
    {
        $resolver = new templateWarmupCompilerForTests(App::app());
        $result = $resolver->testReadModuleTemplateFlavors('news', \jApp::getModulePath('news'));
        $expected = $this->getExpectedFlavors();
        $this->assertEquals($expected, $result);
    }


    function testSingleTemplateFlavorsReader()
    {
        $resolver = new templateWarmupCompilerForTests(App::app());
        $expected = $this->getExpectedFlavors();
        foreach($expected as $tplName => $expectedFlavors) {
            $result = $resolver->testReadModuleTemplateFlavors('news', \jApp::getModulePath('news'), $tplName);
            $this->assertEquals([$tplName => $expectedFlavors], $result);
        }
    }

    function testConsolidating()
    {
        $resolver = new templateWarmupCompilerForTests(App::app());
        $result = $resolver->testConsolidateTemplateFlavors('news', \jApp::getModulePath('news'));
        $expected = $this->getExpectedConsolidatedFlavors();
        $this->assertEquals($expected, $result);
    }

    function testSingleTemplateConsolidating()
    {
        $resolver = new templateWarmupCompilerForTests(App::app());
        $expected = $this->getExpectedConsolidatedFlavors();
        foreach($expected as $tplName => $expectedFlavors) {
            $result = $resolver->testConsolidateTemplateFlavors('news', \jApp::getModulePath('news'), $tplName);
            $this->assertEquals([$tplName => $expectedFlavors], $result);
        }
    }
}

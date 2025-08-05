<?php

/**
 * @package     testapp
 * @subpackage  jelix_tests module
 * @author      Laurent Jouanneau
 * @copyright   2025 Laurent Jouanneau
 * @link        https://www.jelix.org
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
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
        $appPath = jApp::appPath();

        return array(
            'test1.tpl' => array (
                'default' => array(
                    '_' => $appPath.'modules/news/templates/test1.tpl',
                )
            ),
            'test3.tpl' => array (
                'default' => array(
                    '_' => $appPath.'app/themes/default/news/test3.tpl',
                )
            ),
            'test2locale.tpl' => array (
                'default' => array(
                    'en_US' => $appPath.'modules/news/templates/en_US/test2locale.tpl',
                    'fr_FR' => $appPath.'modules/news/templates/fr_FR/test2locale.tpl',
                )
            ),
            'test4.tpl' => array (
                'default' => array(
                    '_' => $appPath.'var/themes/default/news/test4.tpl',

                ),
                'fancy' => array(
                    '_' => $appPath.'app/themes/fancy/news/test4.tpl',
                )
            ),
            'test5.tpl' => array (
                'fancy' => array(
                    '_' => $appPath.'modules/news/templates/themes/fancy/test5.tpl',
                )
            ),
            'test6.tpl' => array (
                'default' => array(
                    '_' => $appPath.'modules/news/templates/test6.tpl',
                ),
                'fancy' => array(
                    'fr_FR' => $appPath.'app/themes/fancy/news/fr_FR/test6.tpl',
                )
            ),
        );
    }

    protected function getExpectedConsolidatedFlavors()
    {
        $appPath = jApp::appPath();
        return array(
            'test1.tpl' => array (
                'default' => array(
                    'en_US' => $appPath.'modules/news/templates/test1.tpl',
                    'fr_FR' => $appPath.'modules/news/templates/test1.tpl',
                ),
                'fancy' => array(
                    'en_US' => $appPath.'modules/news/templates/test1.tpl',
                    'fr_FR' => $appPath.'modules/news/templates/test1.tpl',
                )
            ),
            'test3.tpl' => array (
                'default' => array(
                    'en_US' => $appPath.'app/themes/default/news/test3.tpl',
                    'fr_FR' => $appPath.'app/themes/default/news/test3.tpl',
                ),
                'fancy' => array(
                    'en_US' => $appPath.'app/themes/default/news/test3.tpl',
                    'fr_FR' => $appPath.'app/themes/default/news/test3.tpl',
                )

            ),
            'test2locale.tpl' => array (
                'default' => array(
                    'en_US' => $appPath.'modules/news/templates/en_US/test2locale.tpl',
                    'fr_FR' => $appPath.'modules/news/templates/fr_FR/test2locale.tpl',
                ),
                'fancy' => array(
                    'en_US' => $appPath.'modules/news/templates/en_US/test2locale.tpl',
                    'fr_FR' => $appPath.'modules/news/templates/fr_FR/test2locale.tpl',
                )
            ),
            'test4.tpl' => array (
                'default' => array(
                    'en_US' => $appPath.'var/themes/default/news/test4.tpl',
                    'fr_FR' => $appPath.'var/themes/default/news/test4.tpl',
                ),
                'fancy' => array(
                    'en_US' => $appPath.'app/themes/fancy/news/test4.tpl',
                    'fr_FR' => $appPath.'app/themes/fancy/news/test4.tpl',
                )
            ),
            'test5.tpl' => array (
                'default' => array(
                    'en_US' => '',
                    'fr_FR' => '',
                ),
                'fancy' => array(
                    'en_US' => $appPath.'modules/news/templates/themes/fancy/test5.tpl',
                    'fr_FR' => $appPath.'modules/news/templates/themes/fancy/test5.tpl',
                )
            ),
            'test6.tpl' => array (
                'default' => array(
                    'en_US' => $appPath.'modules/news/templates/test6.tpl',
                    'fr_FR' => $appPath.'modules/news/templates/test6.tpl',
                ),
                'fancy' => array(
                    'en_US' => $appPath.'modules/news/templates/test6.tpl',
                    'fr_FR' => $appPath.'app/themes/fancy/news/fr_FR/test6.tpl',
                )
            ),
        );
    }

    function testFlavorsReader()
    {
        $resolver = new templateWarmupCompilerForTests(jApp::app());
        $result = $resolver->testReadModuleTemplateFlavors('news', \jApp::getModulePath('news'));
        $expected = $this->getExpectedFlavors();
        $this->assertEquals($expected, $result);
    }


    function testSingleTemplateFlavorsReader()
    {
        $resolver = new templateWarmupCompilerForTests(jApp::app());
        $expected = $this->getExpectedFlavors();
        foreach($expected as $tplName => $expectedFlavors) {
            $result = $resolver->testReadModuleTemplateFlavors('news', \jApp::getModulePath('news'), $tplName);
            $this->assertEquals([$tplName => $expectedFlavors], $result);
        }
    }

    function testConsolidating()
    {
        $resolver = new templateWarmupCompilerForTests(jApp::app());
        $result = $resolver->testConsolidateTemplateFlavors('news', \jApp::getModulePath('news'));
        $expected = $this->getExpectedConsolidatedFlavors();
        $this->assertEquals($expected, $result);
    }

    function testSingleTemplateConsolidating()
    {
        $resolver = new templateWarmupCompilerForTests(jApp::app());
        $expected = $this->getExpectedConsolidatedFlavors();
        foreach($expected as $tplName => $expectedFlavors) {
            $result = $resolver->testConsolidateTemplateFlavors('news', \jApp::getModulePath('news'), $tplName);
            $this->assertEquals([$tplName => $expectedFlavors], $result);
        }
    }
}

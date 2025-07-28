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
    function testReadModuleTemplateFlavors($moduleName, $modulePath)
    {
        $this->allFileFlavors = [];
        $this->localesList = ['en_US', 'fr_FR'];
        $this->readModuleTemplateFlavors($moduleName, $modulePath);
        return $this->allFileFlavors;
    }

    function testConsolidateTemplateFlavors($moduleName, $modulePath)
    {
        $this->reset();
        $this->readModuleTemplateFlavors($moduleName, $modulePath);
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

    function testFlavorsReader()
    {
        $resolver = new templateWarmupCompilerForTests(jApp::app());
        $result = $resolver->testReadModuleTemplateFlavors('news', \jApp::getModulePath('news'));
        $appPath = jApp::appPath();

        $expected = array(
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
        $this->assertEquals($expected, $result);
    }

    function testConsolidating()
    {
        $resolver = new templateWarmupCompilerForTests(jApp::app());
        $result = $resolver->testConsolidateTemplateFlavors('news', \jApp::getModulePath('news'));
        $appPath = jApp::appPath();

        $expected = array(
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
        $this->assertEquals($expected, $result);
    }
}

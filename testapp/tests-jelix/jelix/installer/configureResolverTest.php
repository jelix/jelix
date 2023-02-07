<?php


use Jelix\Installer\ModuleStatus;
use Jelix\Core\Infos\ModuleInfos;

class configureResolverTest extends \Jelix\UnitTests\UnitTestCase
{

    function testConfigureNoModules()
    {
        $resolver = new \Jelix\Installer\InstallationResolver();
        $modules = $resolver->getItemsToConfigure([], [], false);
        $this->assertEquals([], $modules);
    }

    function testUnconfigureNoModules()
    {
        $resolver = new \Jelix\Installer\InstallationResolver();
        $modules = $resolver->getItemsToUnConfigure([], []);
        $this->assertEquals([], $modules);
    }

    protected function getComponentsList()
    {
        $modInfosJelix = ModuleInfos::load(__DIR__.'/../../../vendor/jelix/jelix-essential/jelix/core-modules/jelix');
        $modStatusJelix = new ModuleStatus('jelix', '/', [
            'jelix.installed' => true,
            'jelix.enabled' => true,
            'jelix.version' => $modInfosJelix->version,
            'jelix.dbprofile' => '',
        ]);
        $modStatusTestinstall1 = new ModuleStatus('testinstall1', '/', [
            'testinstall1.installed' => false,
            'testinstall1.enabled' => false,
            'testinstall1.version' => '1.0',
            'testinstall1.dbprofile' => '',
            'testinstall1.localconf' => false,
        ]);
        $modStatusTestinstall2 = new ModuleStatus('testinstall2', '/', [
            'testinstall2.installed' => false,
            'testinstall2.enabled' => false,
            'testinstall2.version' => '2.0',
            'testinstall2.dbprofile' => '',
            'testinstall2.localconf' => false,
        ]);
        $modStatusTestinstall3 = new ModuleStatus('testinstall3', '/', [
            'testinstall3.installed' => false,
            'testinstall3.enabled' => false,
            'testinstall3.version' => '3.0',
            'testinstall3.dbprofile' => '',
            'testinstall3.localconf' => false,
        ]);

        $modInfosTestinstall1 = ModuleInfos::load(__DIR__.'/../../../modules/testinstall1');
        $modInfosTestinstall1->version = '1.0';
        $modInfosTestinstall2 = ModuleInfos::load(__DIR__.'/../../../modules/testinstall2');
        $modInfosTestinstall2->version = '2.0';
        $modInfosTestinstall3 = ModuleInfos::load(__DIR__.'/../../../modules/testinstall3');
        $modInfosTestinstall3->version = '3.0';

        return array(
            'jelix'=>[$modInfosJelix, $modStatusJelix],
            'testinstall1'=>[$modInfosTestinstall1, $modStatusTestinstall1],
            'testinstall2'=>[$modInfosTestinstall2, $modStatusTestinstall2],
            'testinstall3'=>[$modInfosTestinstall3, $modStatusTestinstall3],
        );
    }

    function testGetItemsToConfigure()
    {
        $resolver = new \Jelix\Installer\InstallationResolver();
        $componentsList = $this->getComponentsList();
        // configure no modules
        $modules = $resolver->getItemsToConfigure($componentsList, [], false);
        $this->assertEquals([], $modules);

        // configure one module
        $modules = $resolver->getItemsToConfigure($componentsList, ['testinstall2'], false);
        $this->assertEquals(1, count($modules));
        $module = $modules[0];
        $this->assertEquals('testinstall2', $module->getName());
        $this->assertFalse($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals('2.0', $module->getCurrentVersion());
        $this->assertEquals(null, $module->getNextVersion());
        $this->assertEquals(1, $module->getAction());
        $dependencies = $module->getDependencies();
        $this->assertEquals(1, count($dependencies));
        $this->assertTrue(isset($dependencies['jelix']));
        $this->assertEquals([], $module->getAlternativeDependencies());

        // configure two modules
        $modules = $resolver->getItemsToConfigure($componentsList, ['testinstall2', 'testinstall1'], false);
        $this->assertEquals(2, count($modules));

        $module = $modules[0];
        $this->assertEquals('testinstall2', $module->getName());
        $this->assertFalse($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals('2.0', $module->getCurrentVersion());
        $this->assertEquals(null, $module->getNextVersion());
        $this->assertEquals(1, $module->getAction());
        $dependencies = $module->getDependencies();
        $this->assertEquals(1, count($dependencies));
        $this->assertTrue(isset($dependencies['jelix']));
        $this->assertEquals([], $module->getAlternativeDependencies());

        $module = $modules[1];
        $this->assertEquals('testinstall1', $module->getName());
        $this->assertFalse($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals('1.0', $module->getCurrentVersion());
        $this->assertEquals(null, $module->getNextVersion());
        $this->assertEquals(1, $module->getAction());
        $dependencies = $module->getDependencies();
        $this->assertEquals(1, count($dependencies));
        $this->assertTrue(isset($dependencies['jelix']));
        $this->assertEquals([], $module->getAlternativeDependencies());

    }



}
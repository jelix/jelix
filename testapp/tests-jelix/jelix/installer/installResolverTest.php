<?php


use Jelix\Installer\ModuleStatus;
use Jelix\Core\Infos\ModuleInfos;

class installResolverTest extends \Jelix\UnitTests\UnitTestCase
{
    function testInstallNoModules()
    {
        $resolver = new \Jelix\Installer\InstallationResolver();
        $modules = $resolver->getAllItemsToInstall([], []);
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
        ], true);
        $modStatusTestinstall1 = new ModuleStatus('testinstall1', '/', [
            'testinstall1.installed' => false,
            'testinstall1.enabled' => false,
            'testinstall1.version' => '1.0',
            'testinstall1.dbprofile' => '',
            'testinstall1.localconf' => false,
        ], true);
        $modStatusTestinstall2 = new ModuleStatus('testinstall2', '/', [
            'testinstall2.installed' => false,
            'testinstall2.enabled' => false,
            'testinstall2.version' => '2.0',
            'testinstall2.dbprofile' => '',
            'testinstall2.localconf' => false,
        ], true);
        $modStatusTestinstall3 = new ModuleStatus('testinstall3', '/', [
            'testinstall3.installed' => false,
            'testinstall3.enabled' => false,
            'testinstall3.version' => '3.0',
            'testinstall3.dbprofile' => '',
            'testinstall3.localconf' => false,
        ], true);

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

    function testInstallNoModuleConfigured()
    {
        $resolver = new \Jelix\Installer\InstallationResolver();
        $componentsList = $this->getComponentsList();
        // configure no modules
        $modules = $resolver->getAllItemsToInstall($componentsList, []);
        $this->assertEquals([], $modules);
    }

    function testInstallOneModuleConfigured()
    {
        $resolver = new \Jelix\Installer\InstallationResolver();
        $componentsList = $this->getComponentsList();

        $componentsList['testinstall2'][1]->isEnabled = true;

        $modules = $resolver->getAllItemsToInstall($componentsList, []);
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
    }

    function testInstallModuleAlreadyInstalled()
    {
        $resolver = new \Jelix\Installer\InstallationResolver();
        $componentsList = $this->getComponentsList();

        $componentsList['testinstall2'][1]->isInstalled  = true;
        $componentsList['testinstall2'][1]->isEnabled = true;

        $modules = $resolver->getAllItemsToInstall($componentsList, []);
        $this->assertEquals([], $modules);

    }

    function testInstallModuleToUpgrade()
    {
        $resolver = new \Jelix\Installer\InstallationResolver();
        $componentsList = $this->getComponentsList();

        $componentsList['testinstall2'][1]->isInstalled  = true;
        $componentsList['testinstall2'][1]->isEnabled = true;
        $componentsList['testinstall2'][0]->version = '2.1';

        $modules = $resolver->getAllItemsToInstall($componentsList, []);
        $this->assertEquals(1, count($modules));
        $module = $modules[0];
        $this->assertEquals('testinstall2', $module->getName());
        $this->assertTrue($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals('2.0', $module->getCurrentVersion());
        $this->assertEquals('2.1', $module->getNextVersion());
        $this->assertEquals(2, $module->getAction());
        $dependencies = $module->getDependencies();
        $this->assertEquals(1, count($dependencies));
        $this->assertTrue(isset($dependencies['jelix']));
        $this->assertEquals([], $module->getAlternativeDependencies());

    }


    function testInstallModuleUnConfigured()
    {
        $resolver = new \Jelix\Installer\InstallationResolver();
        $componentsList = $this->getComponentsList();

        $component = $componentsList['testinstall2'];

        $component[1]->isInstalled  = true;
        $component[1]->isEnabled = false;

        unset($componentsList['testinstall2']);

        $modules = $resolver->getAllItemsToInstall($componentsList, [$component]);
        $this->assertEquals(1, count($modules));
        /** @var \Jelix\Dependencies\Item $module */
        $module = $modules[0];
        $this->assertEquals('testinstall2', $module->getName());
        $this->assertTrue($module->isInstalled());
        $this->assertFalse($module->canBeInstalled());
        $this->assertEquals('2.0', $module->getCurrentVersion());
        $this->assertNull($module->getNextVersion());
        $this->assertEquals(\Jelix\Dependencies\Resolver::ACTION_REMOVE, $module->getAction());
        $dependencies = $module->getDependencies();
        $this->assertEquals(1, count($dependencies));
        $this->assertTrue(isset($dependencies['jelix']));
        $this->assertEquals([], $module->getAlternativeDependencies());
    }

}
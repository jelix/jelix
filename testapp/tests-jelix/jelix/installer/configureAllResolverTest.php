<?php


use Jelix\Installer\ModuleStatus;
use Jelix\Core\Infos\ModuleInfos;
use Jelix\Dependencies\ItemException;
use Jelix\Dependencies\Resolver;

class configureAllResolverTest extends \Jelix\UnitTests\UnitTestCase
{


    protected function getComponentsList()
    {
        $modInfosJelix = ModuleInfos::load(__DIR__.'/../../../vendor/jelix/jelix-essential/jelix-legacy/core-modules/jelix');
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
        ], true);
        $modStatusTestinstall2 = new ModuleStatus('testinstall2', '/', [
            'testinstall2.installed' => false,
            'testinstall2.enabled' => false,
            'testinstall2.version' => '2.0',
            'testinstall2.dbprofile' => '',
        ], true);
        $modStatusTestinstall3 = new ModuleStatus('testinstall3', '/', [
            'testinstall3.installed' => false,
            'testinstall3.enabled' => false,
            'testinstall3.version' => '3.0',
            'testinstall3.dbprofile' => '',
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


    function testConfigureNoModules()
    {
        $resolver = new \Jelix\Installer\InstallationResolver();
        $modules = $resolver->getAllItemsToConfigureAtInstance([]);
        $this->assertEquals([], $modules);
    }

    function testConfigureNotConfiguredModules()
    {
        $componentsList = $this->getComponentsList();
        $resolver = new \Jelix\Installer\InstallationResolver();
        $modules = $resolver->getAllItemsToConfigureAtInstance($componentsList);
        $this->assertEquals(1, count($modules));

        $module = $modules[0];
        $this->assertEquals('jelix', $module->getName());
        $this->assertTrue($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals(1, $module->getAction());
        $this->assertEquals([], $module->getDependencies());
        $this->assertEquals([], $module->getAlternativeDependencies());
    }

    function testConfigureNotConfiguredModulesWithDependencies()
    {
        $componentsList = $this->getComponentsList();
        $componentsList['testinstall1'][0]->dependencies[] =
            array('name' => 'testinstall2', 'type' => 'module', 'version' => '2.0.*', 'optional' => false)
        ;

        $resolver = new \Jelix\Installer\InstallationResolver();
        $modules = $resolver->getAllItemsToConfigureAtInstance($componentsList);
        $this->assertEquals(1, count($modules));

        $module = $modules[0];
        $this->assertEquals('jelix', $module->getName());
        $this->assertTrue($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals(1, $module->getAction());
        $this->assertEquals([], $module->getDependencies());
        $this->assertEquals([], $module->getAlternativeDependencies());
    }

    function testConfigureConfiguredModules()
    {
        $componentsList = $this->getComponentsList();

        $componentsList['testinstall1'][1]->isInstalled  = true;
        $componentsList['testinstall1'][1]->isEnabled = true;

        $componentsList['testinstall2'][1]->isInstalled  = true;
        $componentsList['testinstall2'][1]->isEnabled = true;


        $resolver = new \Jelix\Installer\InstallationResolver();
        $modules = $resolver->getAllItemsToConfigureAtInstance($componentsList);

        $this->assertEquals(3, count($modules));

        $module = $modules[0];
        $this->assertEquals('jelix', $module->getName());
        $this->assertTrue($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals(1, $module->getAction());
        $this->assertEquals([], $module->getDependencies());
        $this->assertEquals([], $module->getAlternativeDependencies());

        $module = $modules[2];
        $this->assertEquals('testinstall2', $module->getName());
        $this->assertTrue($module->isInstalled());
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
        $this->assertTrue($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals('1.0', $module->getCurrentVersion());
        $this->assertEquals(null, $module->getNextVersion());
        $this->assertEquals(1, $module->getAction());
        $dependencies = $module->getDependencies();
        $this->assertEquals(1, count($dependencies));
        $this->assertTrue(isset($dependencies['jelix']));
        $this->assertEquals([], $module->getAlternativeDependencies());

    }

    function testConfigureConfiguredModulesWithBadDependencies()
    {
        $componentsList = $this->getComponentsList();

        $componentsList['testinstall1'][1]->isInstalled = true;
        $componentsList['testinstall1'][1]->isEnabled = true;
        $componentsList['testinstall1'][0]->dependencies[] =
            array('name' => 'testinstall2', 'type' => 'module', 'version' => '2.5.*', 'optional' => false)
        ;

        $componentsList['testinstall2'][1]->isInstalled = true;
        $componentsList['testinstall2'][1]->isEnabled = true;


        $resolver = new \Jelix\Installer\InstallationResolver();

        $this->expectException(ItemException::class);
        $this->expectExceptionCode(ItemException::ERROR_BAD_ITEM_VERSION);
        $resolver->getAllItemsToConfigureAtInstance($componentsList);
    }

    function testConfigureConfiguredModulesWithDependencies()
    {
        $componentsList = $this->getComponentsList();

        $componentsList['testinstall1'][1]->isInstalled  = true;
        $componentsList['testinstall1'][1]->isEnabled = true;
        $componentsList['testinstall1'][0]->dependencies[] =
            array('name'=>'testinstall2', 'type'=>'module', 'version'=>'2.0.*', 'optional'=>false)
        ;

        $componentsList['testinstall2'][1]->isInstalled  = true;
        $componentsList['testinstall2'][1]->isEnabled = true;


        $resolver = new \Jelix\Installer\InstallationResolver();
        $modules = $resolver->getAllItemsToConfigureAtInstance($componentsList);
        $this->assertEquals(3, count($modules));

        $module = $modules[0];
        $this->assertEquals('jelix', $module->getName());
        $this->assertTrue($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals(1, $module->getAction());
        $this->assertEquals([], $module->getDependencies());
        $this->assertEquals([], $module->getAlternativeDependencies());

        $module = $modules[1];
        $this->assertEquals('testinstall2', $module->getName());
        $this->assertTrue($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals('2.0', $module->getCurrentVersion());
        $this->assertEquals(null, $module->getNextVersion());
        $this->assertEquals(1, $module->getAction());
        $dependencies = $module->getDependencies();
        $this->assertEquals(1, count($dependencies));
        $this->assertTrue(isset($dependencies['jelix']));
        $this->assertEquals([], $module->getAlternativeDependencies());

        $module = $modules[2];
        $this->assertEquals('testinstall1', $module->getName());
        $this->assertTrue($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals('1.0', $module->getCurrentVersion());
        $this->assertEquals(null, $module->getNextVersion());
        $this->assertEquals(1, $module->getAction());
        $dependencies = $module->getDependencies();
        $this->assertEquals(2, count($dependencies));
        $this->assertTrue(isset($dependencies['jelix']));
        $this->assertTrue(isset($dependencies['testinstall2']));
        $this->assertEquals([], $module->getAlternativeDependencies());
    }

    function testConfigureConfiguredModulesToUpgrade()
    {
        $componentsList = $this->getComponentsList();

        $componentsList['testinstall1'][1]->isInstalled  = true;
        $componentsList['testinstall1'][1]->isEnabled = true;
        $componentsList['testinstall1'][0]->version = '1.5';
        $componentsList['testinstall1'][0]->dependencies[] =
            array('name'=>'testinstall2', 'type'=>'module', 'version'=>'2.5.*', 'optional'=>false)
        ;

        $componentsList['testinstall2'][1]->isInstalled  = true;
        $componentsList['testinstall2'][1]->isEnabled = true;
        $componentsList['testinstall2'][0]->version = '2.5.1';


        $resolver = new \Jelix\Installer\InstallationResolver();
        $modules = $resolver->getAllItemsToConfigureAtInstance($componentsList);
        $this->assertEquals(3, count($modules));

        $module = $modules[0];
        $this->assertEquals('jelix', $module->getName());
        $this->assertTrue($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals(1, $module->getAction());
        $this->assertEquals([], $module->getDependencies());
        $this->assertEquals([], $module->getAlternativeDependencies());

        $module = $modules[1];
        $this->assertEquals('testinstall2', $module->getName());
        $this->assertTrue($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals('2.0', $module->getCurrentVersion());
        $this->assertEquals('2.5.1', $module->getNextVersion());
        $this->assertEquals(Resolver::ACTION_UPGRADE, $module->getAction());
        $dependencies = $module->getDependencies();
        $this->assertEquals(1, count($dependencies));
        $this->assertTrue(isset($dependencies['jelix']));
        $this->assertEquals([], $module->getAlternativeDependencies());

        $module = $modules[2];
        $this->assertEquals('testinstall1', $module->getName());
        $this->assertTrue($module->isInstalled());
        $this->assertTrue($module->canBeInstalled());
        $this->assertEquals('1.0', $module->getCurrentVersion());
        $this->assertEquals('1.5', $module->getNextVersion());
        $this->assertEquals(Resolver::ACTION_UPGRADE, $module->getAction());
        $dependencies = $module->getDependencies();
        $this->assertEquals(2, count($dependencies));
        $this->assertTrue(isset($dependencies['jelix']));
        $this->assertTrue(isset($dependencies['testinstall2']));
        $this->assertEquals([], $module->getAlternativeDependencies());

    }


}